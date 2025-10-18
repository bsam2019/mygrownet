export const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW'
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

export const formatAmount = (activity) => {
    if (activity.type === 'withdrawal') {
        return `-${formatCurrency(activity.amount)}`;
    }
    return formatCurrency(activity.amount);
};

export const formatTime = (dateTime) => {
    const date = new Date(dateTime);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

export const formatKwacha = (value) => {
    return `K${Number(value || 0).toLocaleString('en-ZM', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};
