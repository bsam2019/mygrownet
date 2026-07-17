import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useStockflowRoute() {
    const page = usePage();

    const account = computed(() => {
        const stockflowAccount = (page.props as any).stockflowAccount;
        if (stockflowAccount) return stockflowAccount;
        const hostParts = window.location.hostname.split('.');
        if (hostParts.length > 2) return hostParts[0];
        return '';
    });

    function getRouteParamName(name: string): string | null {
        try {
            const ziggyRoutes = (page.props as any).ziggy?.routes ?? (window as any).Ziggy?.routes;
            if (!ziggyRoutes?.[name]) return null;
            if (ziggyRoutes[name].parameters?.length) {
                return ziggyRoutes[name].parameters[0];
            }
            const uri = ziggyRoutes[name].uri;
            const match = uri.match(/\{([^}]+)\}/);
            return match ? match[1] : null;
        } catch {
            return null;
        }
    }

    const route = (name: string, params: any = {}): string => {
        const routeHelper = (window as any).route;
        if (!routeHelper) {
            console.error('Ziggy route helper not found');
            return '#';
        }

        if (name.startsWith('stockflow.sub.')) {
            let mergedParams: Record<string, any>;
            if (typeof params === 'object' && params !== null && !Array.isArray(params)) {
                mergedParams = { ...params, account: account.value };
            } else {
                const paramName = getRouteParamName(name) || 'id';
                mergedParams = { [paramName]: params, account: account.value };
            }
            return routeHelper(name, mergedParams);
        }

        return routeHelper(name, params);
    };

    return { route, account };
}
