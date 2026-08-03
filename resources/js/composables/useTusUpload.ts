import axios from 'axios';

/**
 * Minimal tus (resumable upload) client for Cloudflare Stream.
 *
 * Protocol:
 *  - init:   server returns a one-time upload URL (Location) for a file of size N
 *  - HEAD:   get current offset (for resume)
 *  - PATCH:  append a chunk, send Upload-Offset header, receive new offset
 *
 * Chunk sizing (Cloudflare): min 5MB, recommended 50MB, max ~200MB,
 * and must be divisible by 256 KiB.
 */

const CHUNK_SIZE = 50 * 1024 * 1024; // 50 MB recommended
const MIN_CHUNK = 5 * 1024 * 1024; // 5 MB
const DIVISOR = 256 * 1024; // 256 KiB

function alignChunk(size: number): number {
    const base = Math.max(CHUNK_SIZE, MIN_CHUNK);
    return Math.floor(base / DIVISOR) * DIVISOR;
}

export interface TusProgress {
    offset: number;
    total: number;
    percent: number;
    uploaded: boolean;
}

export interface TusUploadResult {
    uploadedBytes: number;
    bytesTotal: number;
}

export async function createTusUpload(
    endpoint: string,
    fileSize: number
): Promise<{ uploadUrl: string; videoId: number; providerVideoId: string }> {
    const res = await axios.post(
        endpoint,
        { file_size: fileSize },
        { headers: { Accept: 'application/json' } }
    );
    return res.data;
}

export async function completeTusUpload(completeUrl: string, videoId: number): Promise<void> {
    await axios.post(completeUrl, {}, {
        headers: { Accept: 'application/json' },
    });
}

async function headOffset(uploadUrl: string): Promise<number> {
    const res = await axios.request({
        method: 'HEAD',
        url: uploadUrl,
        headers: { 'Tus-Resumable': '1.0.0' },
        validateStatus: () => true,
    });
    const offset = res.headers['upload-offset'];
    return offset !== undefined ? parseInt(String(offset), 10) : 0;
}

/**
 * Upload a File to a Cloudflare tus URL with progress + resume support.
 */
export async function tusUpload(
    file: File,
    uploadUrl: string,
    onProgress?: (p: TusProgress) => void
): Promise<TusUploadResult> {
    const total = file.size;
    const chunkSize = alignChunk(total);

    // Resume from where Cloudflare says we are.
    let offset = await headOffset(uploadUrl);
    if (offset >= total) {
        onProgress?.({ offset: total, total, percent: 100, uploaded: true });
        return { uploadedBytes: total, bytesTotal: total };
    }

    let lastEmit = 0;

    while (offset < total) {
        const end = Math.min(offset + chunkSize, total);
        const blob = file.slice(offset, end);

        const res = await axios.request({
            method: 'PATCH',
            url: uploadUrl,
            data: blob,
            headers: {
                'Content-Type': 'application/offset+octet-stream',
                'Upload-Offset': String(offset),
                'Tus-Resumable': '1.0.0',
            },
            validateStatus: () => true,
        });

        if (res.status < 200 || res.status >= 300) {
            throw new Error(`tus upload failed at ${offset} (HTTP ${res.status})`);
        }

        const serverOffset = res.headers['upload-offset'];
        const newOffset = serverOffset !== undefined ? parseInt(String(serverOffset), 10) : end;
        offset = Math.max(newOffset, offset);

        const now = Date.now();
        if (now - lastEmit > 250 || offset >= total) {
            lastEmit = now;
            onProgress?.({
                offset,
                total,
                percent: Math.min(100, Math.round((offset / total) * 100)),
                uploaded: offset >= total,
            });
        }
    }

    return { uploadedBytes: offset, bytesTotal: total };
}
