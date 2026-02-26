import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useModules() {
    const page = usePage();

    const modules = computed(() => page.props.modules || {});

    const isModuleEnabled = (moduleKey: string): boolean => {
        const module = modules.value[moduleKey];
        return module?.is_enabled ?? false;
    };

    return {
        modules,
        isModuleEnabled,
    };
}
