/**
 * Format currency values for display in Zambian Kwacha
 */
export function formatCurrency(amount: number): string {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(amount);
}

/**
 * Format currency values with K prefix (legacy support)
 */
export function formatKwacha(amount: number): string {
    const formatted = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(amount);

    return `K${formatted}`;
}

/**
 * Format percentage values
 */
export function formatPercentage(value: number, decimals: number = 1): string {
    return `${value.toFixed(decimals)}%`;
}

/**
 * Format large numbers with K, M, B suffixes
 */
export function formatCompactNumber(num: number): string {
    if (num >= 1000000000) {
        return (num / 1000000000).toFixed(1) + 'B';
    }
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    }
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
}

/**
 * Format date for display
 */
export function formatDate(date: string | Date, format: 'short' | 'long' | 'relative' = 'short'): string {
    const dateObj = typeof date === 'string' ? new Date(date) : date;

    switch (format) {
        case 'long':
            return dateObj.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        case 'relative':
            const now = new Date();
            const diffTime = Math.abs(now.getTime() - dateObj.getTime());
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays === 1) return 'Yesterday';
            if (diffDays < 7) return `${diffDays} days ago`;
            if (diffDays < 30) return `${Math.ceil(diffDays / 7)} weeks ago`;
            if (diffDays < 365) return `${Math.ceil(diffDays / 30)} months ago`;
            return `${Math.ceil(diffDays / 365)} years ago`;
        default:
            return dateObj.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
    }
}

/**
 * Format time duration
 */
export function formatDuration(months: number): string {
    if (months < 12) {
        return `${months} month${months !== 1 ? 's' : ''}`;
    }
    const years = Math.floor(months / 12);
    const remainingMonths = months % 12;

    if (remainingMonths === 0) {
        return `${years} year${years !== 1 ? 's' : ''}`;
    }

    return `${years} year${years !== 1 ? 's' : ''} ${remainingMonths} month${remainingMonths !== 1 ? 's' : ''}`;
}

/**
 * Format status with appropriate styling
 */
export function getStatusColor(status: string): string {
    switch (status.toLowerCase()) {
        case 'approved':
        case 'active':
        case 'completed':
            return 'text-green-600 bg-green-50';
        case 'pending':
        case 'processing':
            return 'text-yellow-600 bg-yellow-50';
        case 'rejected':
        case 'cancelled':
        case 'failed':
            return 'text-red-600 bg-red-50';
        case 'draft':
        case 'inactive':
            return 'text-gray-600 bg-gray-50';
        default:
            return 'text-gray-600 bg-gray-50';
    }
}

/**
 * Truncate text with ellipsis
 */
export function truncateText(text: string, maxLength: number): string {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

/**
 * Format phone number
 */
export function formatPhoneNumber(phone: string): string {
    const cleaned = phone.replace(/\D/g, '');
    const match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
    if (match) {
        return `(${match[1]}) ${match[2]}-${match[3]}`;
    }
    return phone;
}