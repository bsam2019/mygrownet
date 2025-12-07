import { ref, onMounted } from 'vue';

type Theme = 'light' | 'dark' | 'system';

const STORAGE_KEY = 'bizboost-theme';

// Global reactive state
const currentTheme = ref<Theme>('system');
const isDark = ref(false);

export function useBizBoostTheme() {
    const setTheme = (theme: Theme) => {
        currentTheme.value = theme;
        localStorage.setItem(STORAGE_KEY, theme);
        applyTheme(theme);
    };

    const toggleTheme = () => {
        const newTheme = isDark.value ? 'light' : 'dark';
        setTheme(newTheme);
    };

    const applyTheme = (theme: Theme) => {
        const root = document.documentElement;
        
        if (theme === 'system') {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            isDark.value = prefersDark;
        } else {
            isDark.value = theme === 'dark';
        }

        if (isDark.value) {
            root.classList.add('dark');
            root.style.colorScheme = 'dark';
        } else {
            root.classList.remove('dark');
            root.style.colorScheme = 'light';
        }
    };

    const initTheme = () => {
        const stored = localStorage.getItem(STORAGE_KEY) as Theme | null;
        const theme = stored || 'system';
        currentTheme.value = theme;
        applyTheme(theme);

        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (currentTheme.value === 'system') {
                isDark.value = e.matches;
                applyTheme('system');
            }
        });
    };

    onMounted(() => {
        initTheme();
    });

    return {
        currentTheme,
        isDark,
        setTheme,
        toggleTheme,
        initTheme,
    };
}
