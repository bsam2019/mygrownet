<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';

interface Props {
    siteName: string;
    logo?: string | null;
    primaryColor?: string;
    style?: 'none' | 'minimal' | 'pulse' | 'wave' | 'gradient' | 'particles' | 'elegant';
    tagline?: string;
}

const props = withDefaults(defineProps<Props>(), {
    primaryColor: '#2563eb',
    style: 'minimal',
    tagline: '',
});

const emit = defineEmits<{
    (e: 'loaded'): void;
}>();

const isVisible = ref(true);
const isHiding = ref(false);

// Generate lighter/darker shades
const colorShades = computed(() => {
    const hex = props.primaryColor.replace('#', '');
    const r = parseInt(hex.substr(0, 2), 16);
    const g = parseInt(hex.substr(2, 2), 16);
    const b = parseInt(hex.substr(4, 2), 16);
    
    return {
        light: `rgba(${r}, ${g}, ${b}, 0.2)`,
        medium: `rgba(${r}, ${g}, ${b}, 0.5)`,
        dark: props.primaryColor,
    };
});

const hide = () => {
    isHiding.value = true;
    setTimeout(() => {
        isVisible.value = false;
        emit('loaded');
    }, 500);
};

onMounted(() => {
    // Auto-hide after content loads or max 2 seconds
    setTimeout(hide, 1500);
});

defineExpose({ hide });
</script>

<template>
    <Teleport to="body">
        <Transition name="splash-fade">
            <div v-if="isVisible && style !== 'none'" class="splash-overlay" :class="[`splash-${style}`, { 'is-hiding': isHiding }]">
                
                <!-- Style 1: Minimal - Clean fade with logo -->
                <div v-if="style === 'minimal'" class="splash-content">
                    <div class="splash-logo-wrapper minimal-bounce">
                        <img v-if="logo" :src="logo" :alt="siteName" class="splash-logo" />
                        <div v-else class="splash-logo-text" :style="{ backgroundColor: primaryColor }">
                            {{ siteName.charAt(0) }}
                        </div>
                    </div>
                    <h1 class="splash-title">{{ siteName }}</h1>
                    <p v-if="tagline" class="splash-tagline">{{ tagline }}</p>
                    <div class="minimal-loader" :style="{ backgroundColor: colorShades.light }">
                        <div class="minimal-loader-bar" :style="{ backgroundColor: primaryColor }"></div>
                    </div>
                </div>

                <!-- Style 2: Pulse - Pulsing logo effect -->
                <div v-else-if="style === 'pulse'" class="splash-content">
                    <div class="pulse-container">
                        <div class="pulse-ring" :style="{ borderColor: colorShades.light }"></div>
                        <div class="pulse-ring delay-1" :style="{ borderColor: colorShades.light }"></div>
                        <div class="pulse-ring delay-2" :style="{ borderColor: colorShades.light }"></div>
                        <div class="splash-logo-wrapper">
                            <img v-if="logo" :src="logo" :alt="siteName" class="splash-logo" />
                            <div v-else class="splash-logo-text" :style="{ backgroundColor: primaryColor }">
                                {{ siteName.charAt(0) }}
                            </div>
                        </div>
                    </div>
                    <h1 class="splash-title">{{ siteName }}</h1>
                </div>

                <!-- Style 3: Wave - Animated wave background -->
                <div v-else-if="style === 'wave'" class="splash-content wave-bg" :style="{ '--wave-color': primaryColor }">
                    <svg class="wave-svg" viewBox="0 0 1440 320" preserveAspectRatio="none">
                        <path class="wave-path wave-1" :fill="colorShades.light" d="M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,234.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                        <path class="wave-path wave-2" :fill="colorShades.medium" d="M0,256L48,240C96,224,192,192,288,181.3C384,171,480,181,576,197.3C672,213,768,235,864,224C960,213,1056,171,1152,165.3C1248,160,1344,192,1392,208L1440,224L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                    </svg>
                    <div class="wave-content">
                        <div class="splash-logo-wrapper">
                            <img v-if="logo" :src="logo" :alt="siteName" class="splash-logo" />
                            <div v-else class="splash-logo-text" :style="{ backgroundColor: primaryColor }">
                                {{ siteName.charAt(0) }}
                            </div>
                        </div>
                        <h1 class="splash-title">{{ siteName }}</h1>
                        <p v-if="tagline" class="splash-tagline">{{ tagline }}</p>
                    </div>
                </div>

                <!-- Style 4: Gradient - Animated gradient background -->
                <div v-else-if="style === 'gradient'" class="splash-content gradient-bg" :style="{ '--gradient-color': primaryColor }">
                    <div class="gradient-overlay"></div>
                    <div class="gradient-content">
                        <div class="splash-logo-wrapper gradient-float">
                            <img v-if="logo" :src="logo" :alt="siteName" class="splash-logo" />
                            <div v-else class="splash-logo-text white-bg">
                                {{ siteName.charAt(0) }}
                            </div>
                        </div>
                        <h1 class="splash-title text-white">{{ siteName }}</h1>
                        <p v-if="tagline" class="splash-tagline text-white-80">{{ tagline }}</p>
                        <div class="gradient-dots">
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                        </div>
                    </div>
                </div>

                <!-- Style 5: Particles - Floating particles effect -->
                <div v-else-if="style === 'particles'" class="splash-content particles-bg">
                    <div class="particles-container">
                        <div v-for="i in 20" :key="i" class="particle" :style="{ 
                            backgroundColor: i % 2 === 0 ? primaryColor : colorShades.light,
                            animationDelay: `${i * 0.1}s`,
                            left: `${Math.random() * 100}%`,
                            animationDuration: `${3 + Math.random() * 2}s`
                        }"></div>
                    </div>
                    <div class="particles-content">
                        <div class="splash-logo-wrapper">
                            <img v-if="logo" :src="logo" :alt="siteName" class="splash-logo" />
                            <div v-else class="splash-logo-text" :style="{ backgroundColor: primaryColor }">
                                {{ siteName.charAt(0) }}
                            </div>
                        </div>
                        <h1 class="splash-title">{{ siteName }}</h1>
                        <p v-if="tagline" class="splash-tagline">{{ tagline }}</p>
                    </div>
                </div>

                <!-- Style 6: Elegant - Sophisticated with subtle animations -->
                <div v-else-if="style === 'elegant'" class="splash-content elegant-bg">
                    <div class="elegant-pattern"></div>
                    <div class="elegant-content">
                        <div class="elegant-line top" :style="{ backgroundColor: primaryColor }"></div>
                        <div class="splash-logo-wrapper elegant-scale">
                            <img v-if="logo" :src="logo" :alt="siteName" class="splash-logo" />
                            <div v-else class="splash-logo-text elegant-text" :style="{ color: primaryColor, borderColor: primaryColor }">
                                {{ siteName.charAt(0) }}
                            </div>
                        </div>
                        <h1 class="splash-title elegant-title">{{ siteName }}</h1>
                        <p v-if="tagline" class="splash-tagline elegant-tagline">{{ tagline }}</p>
                        <div class="elegant-line bottom" :style="{ backgroundColor: primaryColor }"></div>
                        <div class="elegant-spinner" :style="{ borderTopColor: primaryColor }"></div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
/* Base Styles */
.splash-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    transition: opacity 0.5s ease, transform 0.5s ease;
}

.splash-overlay.is-hiding {
    opacity: 0;
    transform: scale(1.02);
}

.splash-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    padding: 2rem;
}

.splash-logo-wrapper {
    width: 80px;
    height: 80px;
    margin-bottom: 1.5rem;
}

.splash-logo {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.splash-logo-text {
    width: 100%;
    height: 100%;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: 700;
    color: white;
}

.splash-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
    text-align: center;
}

.splash-tagline {
    font-size: 1rem;
    color: #6b7280;
    text-align: center;
}

/* Style 1: Minimal */
.minimal-bounce {
    animation: minimalBounce 1.5s ease-in-out infinite;
}

@keyframes minimalBounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.minimal-loader {
    width: 200px;
    height: 4px;
    border-radius: 2px;
    margin-top: 2rem;
    overflow: hidden;
}

.minimal-loader-bar {
    height: 100%;
    width: 0%;
    border-radius: 2px;
    animation: minimalLoad 1.5s ease-in-out infinite;
}

@keyframes minimalLoad {
    0% { width: 0%; margin-left: 0; }
    50% { width: 70%; margin-left: 0; }
    100% { width: 0%; margin-left: 100%; }
}
</style>

<style scoped>
/* Style 2: Pulse */
.pulse-container {
    position: relative;
    width: 120px;
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
}

.pulse-container .splash-logo-wrapper {
    position: relative;
    z-index: 2;
    margin-bottom: 0;
}

.pulse-ring {
    position: absolute;
    width: 100%;
    height: 100%;
    border: 3px solid;
    border-radius: 50%;
    animation: pulseRing 2s ease-out infinite;
}

.pulse-ring.delay-1 { animation-delay: 0.4s; }
.pulse-ring.delay-2 { animation-delay: 0.8s; }

@keyframes pulseRing {
    0% { transform: scale(0.8); opacity: 1; }
    100% { transform: scale(1.8); opacity: 0; }
}

/* Style 3: Wave */
.wave-bg {
    background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
    position: relative;
    overflow: hidden;
}

.wave-svg {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 40%;
}

.wave-path {
    animation: waveMove 3s ease-in-out infinite;
}

.wave-1 { animation-delay: 0s; }
.wave-2 { animation-delay: 0.5s; }

@keyframes waveMove {
    0%, 100% { transform: translateX(0); }
    50% { transform: translateX(-2%); }
}

.wave-content {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Style 4: Gradient */
.gradient-bg {
    background: linear-gradient(135deg, var(--gradient-color) 0%, color-mix(in srgb, var(--gradient-color) 70%, #000) 100%);
    position: relative;
}

.gradient-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
    animation: gradientShine 3s ease-in-out infinite;
}

@keyframes gradientShine {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.gradient-content {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.gradient-float {
    animation: gradientFloat 2s ease-in-out infinite;
}

@keyframes gradientFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-15px); }
}

.white-bg {
    background: white !important;
    color: var(--gradient-color) !important;
}

.text-white { color: white; }
.text-white-80 { color: rgba(255,255,255,0.8); }

.gradient-dots {
    display: flex;
    gap: 0.5rem;
    margin-top: 2rem;
}

.gradient-dots .dot {
    width: 10px;
    height: 10px;
    background: white;
    border-radius: 50%;
    animation: dotPulse 1.4s ease-in-out infinite;
}

.gradient-dots .dot:nth-child(2) { animation-delay: 0.2s; }
.gradient-dots .dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes dotPulse {
    0%, 80%, 100% { transform: scale(0.6); opacity: 0.5; }
    40% { transform: scale(1); opacity: 1; }
}
</style>

<style scoped>
/* Style 5: Particles */
.particles-bg {
    background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
    position: relative;
    overflow: hidden;
}

.particles-container {
    position: absolute;
    inset: 0;
}

.particle {
    position: absolute;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    bottom: -20px;
    animation: particleRise linear infinite;
}

@keyframes particleRise {
    0% { transform: translateY(0) rotate(0deg); opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { transform: translateY(-100vh) rotate(720deg); opacity: 0; }
}

.particles-content {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.particles-bg .splash-title { color: white; }
.particles-bg .splash-tagline { color: rgba(255,255,255,0.7); }

/* Style 6: Elegant */
.elegant-bg {
    background: #fafafa;
    position: relative;
}

.elegant-pattern {
    position: absolute;
    inset: 0;
    background-image: radial-gradient(circle at 1px 1px, #e5e7eb 1px, transparent 0);
    background-size: 40px 40px;
    opacity: 0.5;
}

.elegant-content {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.elegant-line {
    width: 60px;
    height: 3px;
    border-radius: 2px;
}

.elegant-line.top { margin-bottom: 2rem; }
.elegant-line.bottom { margin-top: 2rem; margin-bottom: 1.5rem; }

.elegant-scale {
    animation: elegantScale 2s ease-in-out infinite;
}

@keyframes elegantScale {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.elegant-text {
    background: transparent !important;
    border: 3px solid;
}

.elegant-title {
    font-weight: 600;
    letter-spacing: 0.05em;
}

.elegant-tagline {
    font-style: italic;
}

.elegant-spinner {
    width: 24px;
    height: 24px;
    border: 2px solid #e5e7eb;
    border-top-width: 2px;
    border-radius: 50%;
    animation: elegantSpin 1s linear infinite;
}

@keyframes elegantSpin {
    to { transform: rotate(360deg); }
}

/* Transition */
.splash-fade-enter-active,
.splash-fade-leave-active {
    transition: opacity 0.5s ease;
}

.splash-fade-enter-from,
.splash-fade-leave-to {
    opacity: 0;
}
</style>
