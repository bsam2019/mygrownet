<template>
    <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 scale-95"
        leave-active-class="transition duration-150 ease-in"
        leave-to-class="opacity-0 scale-95"
    >
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" @click.self="close">
            <div class="bg-[#16120f] border border-outline-variant/60 rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl text-on-surface">
                <!-- Modal Header -->
                <div class="px-6 py-5 border-b border-outline-variant/40 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-2xl">rocket_launch</span>
                        <h2 class="text-lg font-bold text-on-surface">Market &amp; Share Content</h2>
                    </div>
                    <button @click="close" class="p-1 rounded-full text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high transition-colors">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-6 max-h-[80vh] overflow-y-auto scrollbar-none">
                    <!-- Video Preview Card -->
                    <div class="flex items-center gap-4 p-3 rounded-2xl bg-surface-container border border-outline-variant/40">
                        <img v-if="item.thumbnail_url || item.poster_url" :src="item.thumbnail_url || item.poster_url" :alt="item.title" class="w-20 h-14 object-cover rounded-xl border border-outline-variant/40 shrink-0" />
                        <div v-else class="w-20 h-14 bg-primary/20 rounded-xl flex items-center justify-center text-primary font-bold shrink-0">
                            <span class="material-symbols-outlined text-2xl">movie</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-bold text-sm text-on-surface truncate">{{ item.title }}</h3>
                            <p class="text-xs text-on-surface-variant truncate">{{ item.creator_name || 'GrowStream' }}</p>
                        </div>
                    </div>

                    <!-- Tabs: Social Share / QR Code / Embed -->
                    <div class="flex border-b border-outline-variant/40 gap-4 text-xs font-semibold">
                        <button
                            @click="activeTab = 'social'"
                            :class="['pb-2.5 transition-colors border-b-2', activeTab === 'social' ? 'border-primary text-primary font-bold' : 'border-transparent text-on-surface-variant hover:text-on-surface']"
                        >
                            Social Networks
                        </button>
                        <button
                            @click="activeTab = 'qr'"
                            :class="['pb-2.5 transition-colors border-b-2', activeTab === 'qr' ? 'border-primary text-primary font-bold' : 'border-transparent text-on-surface-variant hover:text-on-surface']"
                        >
                            QR Code (Print)
                        </button>
                        <button
                            @click="activeTab = 'embed'"
                            :class="['pb-2.5 transition-colors border-b-2', activeTab === 'embed' ? 'border-primary text-primary font-bold' : 'border-transparent text-on-surface-variant hover:text-on-surface']"
                        >
                            Embed Code
                        </button>
                    </div>

                    <!-- TAB 1: One-Click Social Network Share -->
                    <div v-if="activeTab === 'social'" class="space-y-5">
                        <p class="text-xs text-on-surface-variant">Click to instantly post with pre-formatted promo text &amp; tracking tags:</p>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <!-- WhatsApp -->
                            <a
                                :href="whatsappUrl"
                                target="_blank"
                                rel="noopener"
                                class="p-3 rounded-2xl bg-[#25D366]/10 border border-[#25D366]/30 hover:bg-[#25D366]/20 transition-all flex flex-col items-center justify-center text-center group"
                            >
                                <span class="material-symbols-outlined text-2xl text-[#25D366] mb-1 group-hover:scale-110 transition-transform">chat</span>
                                <span class="font-bold text-xs text-neutral-200">WhatsApp</span>
                            </a>

                            <!-- Facebook -->
                            <a
                                :href="facebookUrl"
                                target="_blank"
                                rel="noopener"
                                class="p-3 rounded-2xl bg-[#1877F2]/10 border border-[#1877F2]/30 hover:bg-[#1877F2]/20 transition-all flex flex-col items-center justify-center text-center group"
                            >
                                <span class="material-symbols-outlined text-2xl text-[#1877F2] mb-1 group-hover:scale-110 transition-transform">share</span>
                                <span class="font-bold text-xs text-neutral-200">Facebook</span>
                            </a>

                            <!-- Twitter / X -->
                            <a
                                :href="twitterUrl"
                                target="_blank"
                                rel="noopener"
                                class="p-3 rounded-2xl bg-sky-500/10 border border-sky-500/30 hover:bg-sky-500/20 transition-all flex flex-col items-center justify-center text-center group"
                            >
                                <span class="material-symbols-outlined text-2xl text-sky-400 mb-1 group-hover:scale-110 transition-transform">send</span>
                                <span class="font-bold text-xs text-neutral-200">Twitter / X</span>
                            </a>

                            <!-- LinkedIn -->
                            <a
                                :href="linkedinUrl"
                                target="_blank"
                                rel="noopener"
                                class="p-3 rounded-2xl bg-[#0A66C2]/10 border border-[#0A66C2]/30 hover:bg-[#0A66C2]/20 transition-all flex flex-col items-center justify-center text-center group"
                            >
                                <span class="material-symbols-outlined text-2xl text-[#0A66C2] mb-1 group-hover:scale-110 transition-transform">work</span>
                                <span class="font-bold text-xs text-neutral-200">LinkedIn</span>
                            </a>
                        </div>

                        <!-- Direct Copy Link Box -->
                        <div class="space-y-2">
                            <label class="text-xs font-semibold text-on-surface-variant">Shareable Short Link with Attribution Tracking</label>
                            <div class="flex items-center gap-2">
                                <input
                                    type="text"
                                    readonly
                                    :value="shareUrl"
                                    class="flex-1 bg-surface-container border border-outline-variant/60 rounded-xl px-3.5 py-2.5 text-xs text-on-surface font-mono outline-none"
                                />
                                <button
                                    @click="copyLink"
                                    class="px-4 py-2.5 rounded-xl bg-primary text-on-primary text-xs font-bold hover:bg-[#c94918] transition-colors shrink-0 flex items-center gap-1"
                                >
                                    <span class="material-symbols-outlined text-sm">{{ copied ? 'check' : 'content_copy' }}</span>
                                    <span>{{ copied ? 'Copied!' : 'Copy Link' }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: QR Code for Flyers & Print Marketing -->
                    <div v-else-if="activeTab === 'qr'" class="flex flex-col items-center text-center space-y-4 py-2">
                        <p class="text-xs text-on-surface-variant max-w-sm">
                            Download or scan this QR Code for posters, flyers, business cards, or print materials.
                        </p>
                        <div class="p-4 bg-white rounded-2xl shadow-xl">
                            <img :src="qrCodeUrl" alt="Video QR Code" class="w-48 h-48" />
                        </div>
                        <a
                            :href="qrCodeUrl"
                            download="growstream-qrcode.png"
                            target="_blank"
                            class="px-5 py-2.5 rounded-full bg-primary text-on-primary text-xs font-bold hover:bg-[#c94918] transition-colors flex items-center gap-1.5"
                        >
                            <span class="material-symbols-outlined text-sm">download</span> Download High-Res QR Code
                        </a>
                    </div>

                    <!-- TAB 3: Website Embed Code -->
                    <div v-else-if="activeTab === 'embed'" class="space-y-3">
                        <p class="text-xs text-on-surface-variant">Copy this HTML snippet to embed this video player on your website or blog:</p>
                        <textarea
                            readonly
                            rows="3"
                            :value="embedSnippet"
                            class="w-full bg-surface-container border border-outline-variant/60 rounded-xl p-3 text-xs text-primary font-mono outline-none"
                        ></textarea>
                        <button
                            @click="copyEmbed"
                            class="px-4 py-2 rounded-xl bg-surface-container-high text-on-surface text-xs font-bold hover:bg-surface-container-highest transition-colors flex items-center gap-1"
                        >
                            <span class="material-symbols-outlined text-sm">{{ copiedEmbed ? 'check' : 'content_copy' }}</span>
                            <span>{{ copiedEmbed ? 'Copied Snippet!' : 'Copy Embed Snippet' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

interface ShareableItem {
    title: string;
    slug: string;
    thumbnail_url?: string;
    poster_url?: string;
    creator_name?: string;
}

interface Props {
    show: boolean;
    item: ShareableItem;
}

const props = defineProps<Props>();
const emit = defineEmits(['close']);

const activeTab = ref<'social' | 'qr' | 'embed'>('social');
const copied = ref(false);
const copiedEmbed = ref(false);

const close = () => {
    emit('close');
};

const baseUrl = typeof window !== 'undefined' ? window.location.origin : 'https://growstream.mygrownet.com';

const shareUrl = computed(() => {
    return `${baseUrl}/video/${props.item.slug}?ref=social`;
});

const promoCaption = computed(() => {
    return `🍿 Watch '${props.item.title}' on GrowStream! Check it out here: ${shareUrl.value}`;
});

const whatsappUrl = computed(() => `https://api.whatsapp.com/send?text=${encodeURIComponent(promoCaption.value)}`);
const facebookUrl = computed(() => `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl.value)}`);
const twitterUrl = computed(() => `https://twitter.com/intent/tweet?text=${encodeURIComponent(promoCaption.value)}`);
const linkedinUrl = computed(() => `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(shareUrl.value)}`);

const qrCodeUrl = computed(() => {
    return `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(shareUrl.value)}`;
});

const embedSnippet = computed(() => {
    return `<iframe src="${baseUrl}/video/${props.item.slug}?embed=true" width="100%" height="400" frameborder="0" allowfullscreen></iframe>`;
});

const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(shareUrl.value);
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2500);
    } catch {
        // fallback
    }
};

const copyEmbed = async () => {
    try {
        await navigator.clipboard.writeText(embedSnippet.value);
        copiedEmbed.value = true;
        setTimeout(() => { copiedEmbed.value = false; }, 2500);
    } catch {
        // fallback
    }
};
</script>
