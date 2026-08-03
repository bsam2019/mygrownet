import axios from 'axios';

const SESSION_KEY = 'growstream.session_id';
const QUEUE_KEY = 'growstream.metrics_queue';

interface MetricEvent {
    event: string;
    video_id?: number;
    creator_id?: number;
    session_id?: string;
    metadata?: Record<string, any>;
}

function getSessionId(): string {
    try {
        let id = localStorage.getItem(SESSION_KEY);
        if (!id) {
            id = `${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 10)}`;
            localStorage.setItem(SESSION_KEY, id);
        }
        return id;
    } catch {
        return 'anon';
    }
}

function queue(): MetricEvent[] {
    try {
        const raw = localStorage.getItem(QUEUE_KEY);
        return raw ? (JSON.parse(raw) as MetricEvent[]) : [];
    } catch {
        return [];
    }
}

function saveQueue(events: MetricEvent[]): void {
    try {
        localStorage.setItem(QUEUE_KEY, JSON.stringify(events.slice(-50)));
    } catch {
        // ignore
    }
}

async function flush(): Promise<void> {
    const events = queue();
    if (events.length === 0) return;

    saveQueue([]);

    try {
        await axios.post('/api/v1/growstream/metrics', events[0], {
            headers: { Accept: 'application/json' },
        });
    } catch {
        // Re-queue on failure so events are not lost (fire-and-forget)
        saveQueue(events);
    }

    // Flush the rest best-effort
    for (const event of events.slice(1)) {
        try {
            await axios.post('/api/v1/growstream/metrics', event, {
                headers: { Accept: 'application/json' },
            });
        } catch {
            // drop on subsequent failure to avoid unbounded retries
        }
    }
}

function track(event: string, data: { video_id?: number; creator_id?: number; metadata?: Record<string, any> } = {}): void {
    const metricEvent: MetricEvent = {
        event,
        video_id: data.video_id,
        creator_id: data.creator_id,
        session_id: getSessionId(),
        metadata: data.metadata,
    };

    saveQueue([...queue(), metricEvent]);

    // Debounce batching: flush at most every 5s
    if (!(window as any).__gsMetricsTimer) {
        (window as any).__gsMetricsTimer = setTimeout(() => {
            (window as any).__gsMetricsTimer = null;
            void flush();
        }, 5000);
    }
}

// Flush remaining events on page unload (beacon-style)
if (typeof window !== 'undefined') {
    window.addEventListener('beforeunload', () => {
        const events = queue();
        if (events.length === 0) return;
        navigator.sendBeacon('/api/v1/growstream/metrics', JSON.stringify(events[0]));
    });
}

export function useGrowStreamMetrics() {
    return {
        trackBrowseToPlay(videoId: number): void {
            track('browse.play', { video_id: videoId });
        },
        trackWatchStart(videoId: number): void {
            track('watch.start', { video_id: videoId });
        },
        trackWatchEnded(videoId: number, metadata?: Record<string, any>): void {
            track('watch.ended', { video_id: videoId, metadata });
        },
        trackSearch(query: string): void {
            track('search.submit', { metadata: { query } });
        },
        trackCreatorSubscribe(creatorId: number): void {
            track('creator.subscribe', { creator_id: creatorId });
        },
        trackPlaybackFailure(videoId: number, metadata?: Record<string, any>): void {
            track('playback.failure', { video_id: videoId, metadata });
        },
        trackPlaybackRetry(videoId: number): void {
            track('playback.retry', { video_id: videoId });
        },
        track: track as (event: string, data?: { video_id?: number; creator_id?: number; metadata?: Record<string, any> }) => void,
    };
}
