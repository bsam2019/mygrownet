export const formatKwacha = (value: number): string => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
};

// Alias for backward compatibility
export const formatCurrency = formatKwacha;

export const formatPercent = (value: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'percent',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value / 100);
};

export const formatNumber = (value: number): string => {
    return new Intl.NumberFormat('en-US').format(value);
};

export const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    });
};

export const formatTime = (date: string): string => {
    return new Date(date).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
};

export const formatDateRange = (startDate: string, endDate: string): string => {
    const start = new Date(startDate);
    const end = new Date(endDate);
    return `${formatDate(startDate)} - ${formatDate(endDate)}`;
};

export const formatPeriodDate = (date: string, period: string): string => {
    const formatOptions: Intl.DateTimeFormatOptions = {
        day: 'numeric',
        month: period === 'year' ? 'short' : 'long',
        year: period === 'year' ? 'numeric' : undefined
    };
    return new Date(date).toLocaleDateString('en-US', formatOptions);
};