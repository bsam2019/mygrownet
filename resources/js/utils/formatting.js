export const formatCurrency = (value, currency = 'ZMW') => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: currency
    }).format(value);
};

export const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

export const formatShortDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    });
};

export const formatPercentage = (value) => {
    return `${Number(value || 0).toFixed(2)}%`;
};

export const formatAmount = (activity, currency = 'ZMW') => {
    if (activity.type === 'withdrawal') {
        return `-${formatCurrency(activity.amount, currency)}`;
    }
    return formatCurrency(activity.amount, currency);
};

export const formatTime = (dateTime) => {
    const date = new Date(dateTime);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

export const formatKwacha = (value, currency = 'ZMW') => {
    const symbol = currency === 'ZMW' ? 'K' : '$';
    return `${symbol}${Number(value || 0).toLocaleString('en-ZM', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};
