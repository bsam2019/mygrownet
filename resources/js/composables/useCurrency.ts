import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useCurrency() {
    const page = usePage();

    const currency = computed(() => {
        return (page.props as any).company?.currency ?? 'ZMW';
    });

    const localeMap: Record<string, string> = {
        ZMW: 'en-ZM',
        MWK: 'en-MW',
        USD: 'en-US',
        EUR: 'de-DE',
        GBP: 'en-GB',
        ZAR: 'en-ZA',
    };

    const formatCurrency = (amount: number): string => {
        try {
            return new Intl.NumberFormat(localeMap[currency.value] || 'en-US', {
                style: 'currency',
                currency: currency.value,
                minimumFractionDigits: 2,
            }).format(amount);
        } catch {
            return `${currency.value} ${amount.toFixed(2)}`;
        }
    };

    return { currency, formatCurrency };
}
