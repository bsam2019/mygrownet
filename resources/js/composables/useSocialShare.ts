import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

export interface ShareStats {
    today_count: number;
    total_count: number;
    threshold_reached: boolean;
    remaining_for_bonus: number;
}

export function useSocialShare() {
    const stats = ref<ShareStats | null>(null);
    const loading = ref(false);

    /**
     * Record a social share event
     */
    const recordShare = async (
        shareType: 'referral_link' | 'content' | 'platform_link',
        platform?: 'facebook' | 'twitter' | 'whatsapp' | 'linkedin' | 'copy_link' | 'other',
        sharedUrl?: string
    ) => {
        try {
            loading.value = true;
            const response = await axios.post('/social-shares/record', {
                share_type: shareType,
                platform,
                shared_url: sharedUrl,
            });

            if (response.data.success) {
                stats.value = response.data.stats;
                return response.data;
            }
        } catch (error) {
            console.error('Failed to record share:', error);
            throw error;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Get user's sharing statistics
     */
    const fetchStats = async () => {
        try {
            loading.value = true;
            const response = await axios.get('/social-shares/stats');
            
            if (response.data.success) {
                stats.value = response.data.stats;
                return response.data.stats;
            }
        } catch (error) {
            console.error('Failed to fetch share stats:', error);
            throw error;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Share on Facebook
     */
    const shareOnFacebook = (url: string, quote?: string) => {
        const shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}${quote ? `&quote=${encodeURIComponent(quote)}` : ''}`;
        window.open(shareUrl, '_blank', 'width=600,height=400');
        recordShare('referral_link', 'facebook', url);
    };

    /**
     * Share on Twitter
     */
    const shareOnTwitter = (url: string, text?: string) => {
        const shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}${text ? `&text=${encodeURIComponent(text)}` : ''}`;
        window.open(shareUrl, '_blank', 'width=600,height=400');
        recordShare('referral_link', 'twitter', url);
    };

    /**
     * Share on WhatsApp
     */
    const shareOnWhatsApp = (url: string, text?: string) => {
        const message = text ? `${text} ${url}` : url;
        const shareUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
        window.open(shareUrl, '_blank');
        recordShare('referral_link', 'whatsapp', url);
    };

    /**
     * Share on LinkedIn
     */
    const shareOnLinkedIn = (url: string) => {
        const shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`;
        window.open(shareUrl, '_blank', 'width=600,height=400');
        recordShare('referral_link', 'linkedin', url);
    };

    /**
     * Copy link to clipboard
     */
    const copyToClipboard = async (url: string) => {
        try {
            await navigator.clipboard.writeText(url);
            recordShare('referral_link', 'copy_link', url);
            return true;
        } catch (error) {
            console.error('Failed to copy to clipboard:', error);
            return false;
        }
    };

    return {
        stats,
        loading,
        recordShare,
        fetchStats,
        shareOnFacebook,
        shareOnTwitter,
        shareOnWhatsApp,
        shareOnLinkedIn,
        copyToClipboard,
    };
}
