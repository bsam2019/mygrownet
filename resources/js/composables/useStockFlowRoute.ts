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
                // Return the LAST parameter (skip 'account' subdomain parameter)
                const params = ziggyRoutes[name].parameters;
                return params[params.length - 1];
            }
            const uri = ziggyRoutes[name].uri;
            // Match all parameters, return the last one (skip {account})
            const matches = uri.matchAll(/\{([^}]+)\}/g);
            const allMatches = Array.from(matches);
            if (allMatches.length > 0) {
                return allMatches[allMatches.length - 1][1];
            }
            return null;
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
            const ziggyRoutes = (page.props as any).ziggy?.routes ?? (window as any).Ziggy?.routes;
            if (!ziggyRoutes?.[name]) {
                const mainName = name.replace('stockflow.sub.', 'stockflow.');
                return routeHelper(mainName, params);
            }

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
