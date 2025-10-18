import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermissions() {
    const page = usePage();

    const hasPermission = (permission: string) => {
        const user = page.props.auth?.user;
        return user?.permissions?.includes(permission) ?? false;
    };

    const hasRole = (role: string) => {
        const user = page.props.auth?.user;
        return user?.roles?.includes(role) ?? false;
    };

    const isAdmin = computed(() => {
        const user = page.props.auth?.user;
        return user?.roles?.includes('admin') ?? false;
    });

    return {
        hasPermission,
        hasRole,
        isAdmin
    };
}
