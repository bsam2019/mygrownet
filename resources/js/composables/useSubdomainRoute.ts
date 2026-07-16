import { usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

export function useSubdomainRoute() {
    const page = usePage();

    const isSubdomain = (): boolean => {
        const ziggy = (page.props as any).ziggy;
        return ziggy?.location ? /^https?:\/\/[a-z0-9-]+\.mygrownet\.com\//i.test(ziggy.location) : false;
    };

    const saRoute = (name: string, params?: Record<string, unknown> | string, absolute?: boolean): string => {
        if (isSubdomain()) {
            // Convert stockflow.* to stockflow.sub.* for subdomain routes
            let subName = name;
            if (name.startsWith('stockflow.') && !name.startsWith('stockflow.sub.')) {
                subName = name.replace('stockflow.', 'stockflow.sub.');
            }
            try {
                return route(subName, params as any, absolute);
            } catch {
                // Fallback to original route name
            }
        }
        return route(name as any, params as any, absolute);
    };

    return { saRoute, isSubdomain };
}
