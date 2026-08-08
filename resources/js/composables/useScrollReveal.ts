import { onMounted, onUnmounted, shallowRef, type Ref } from 'vue';

/**
 * Scroll-reveal composable for landing-page animations.
 *
 * Returns a ref you bind to a container; children with `data-reveal` get
 * `is-revealed` added when they enter the viewport (respecting a stagger
 * delay via `data-reveal-delay`). Designed for CSS-driven transitions so no
 * animation library is required.
 */
export function useScrollReveal<T extends HTMLElement = HTMLElement>(): Ref<T | null> {
    let observer: IntersectionObserver | null = null;
    const rootRef: Ref<T | null> = shallowRef<T | null>(null);

    const observe = () => {
        if (!rootRef.value || typeof IntersectionObserver === 'undefined') return;

        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        const el = entry.target as HTMLElement;
                        el.classList.add('is-revealed');
                        observer?.unobserve(el);
                    }
                });
            },
            { threshold: 0.15, rootMargin: '0px 0px -40px 0px' }
        );

        rootRef.value.querySelectorAll('[data-reveal]').forEach((el) => observer?.observe(el));
    };

    onMounted(() => {
        observe();
        if (rootRef.value) {
            // Support late-bound DOM (v-if) by observing after next tick.
            requestAnimationFrame(observe);
        }
    });

    onUnmounted(() => observer?.disconnect());

    return rootRef;
}
