/**
 * StockFlow Route Helper
 * 
 * Provides context-aware routing for StockFlow module.
 * Automatically uses subdomain routes when on company subdomain,
 * or main site routes when accessed from main app.
 * 
 * Usage:
 *   const { sfRoute } = useStockFlowRoute();
 *   router.get(sfRoute('items.index'));
 *   <Link :href="sfRoute('sales.create')">
 */

export function useStockFlowRoute() {
    /**
     * Generate StockFlow route based on context
     * 
     * @param routeName - Route name without prefix (e.g., 'items.index', 'sales.create')
     * @param params - Optional route parameters
     * @returns Full route URL
     * 
     * Examples:
     *   sfRoute('items.index') 
     *     → On subdomain: /items (stockflow.sub.items.index)
     *     → On main site: /stock-audit/items (stock-audit.items.index)
     * 
     *   sfRoute('sales.show', { id: 123 })
     *     → On subdomain: /sales/123
     *     → On main site: /stock-audit/sales/123
     */
    const sfRoute = (routeName: string, params?: any) => {
        // Check if we're on a StockFlow company subdomain
        const isSubdomain = !!(window as any).__sfSubdomain;
        
        // Use subdomain routes (stockflow.sub.*) or main site routes (stock-audit.*)
        const fullRouteName = isSubdomain 
            ? `stockflow.sub.${routeName}` 
            : `stock-audit.${routeName}`;
        
        // Call the global route() helper with the correct prefix
        return (window as any).route(fullRouteName, params);
    };

    return {
        sfRoute
    };
}
