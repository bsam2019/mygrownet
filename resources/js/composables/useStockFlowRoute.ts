import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Composable for generating StockFlow subdomain routes with the account parameter.
 * 
 * Usage:
 *   const { route } = useStockflowRoute();
 *   route('stockflow.sub.items.create')  // Automatically includes account
 */
export function useStockflowRoute() {
    const page = usePage();

    const account = computed(() => {
        // Get from shared props (set by HandleInertiaRequests middleware)
        const stockflowAccount = (page.props as any).stockflowAccount;
        if (stockflowAccount) {
            return stockflowAccount;
        }

        // Fallback: extract from hostname
        const hostParts = window.location.hostname.split('.');
        if (hostParts.length > 2) {
            return hostParts[0];
        }

        return '';
    });

    /**
     * Generate a route URL with account parameter automatically included.
     * 
     * @param name - Route name (e.g., 'stockflow.sub.items.create')
     * @param params - Additional route parameters
     */
    const route = (name: string, params: Record<string, any> = {}): string => {
        // Import route helper from Ziggy
        const routeHelper = (window as any).route;
        
        if (!routeHelper) {
            console.error('Ziggy route helper not found');
            return '#';
        }

        // Auto-inject account parameter for stockflow.sub.* routes
        if (name.startsWith('stockflow.sub.')) {
            return routeHelper(name, { ...params, account: account.value });
        }

        // For non-subdomain routes, just use the route helper as-is
        return routeHelper(name, params);
    };

    return {
        route,
        account,
    };
}
