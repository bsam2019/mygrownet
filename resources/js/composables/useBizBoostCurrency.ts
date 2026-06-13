export function formatBizBoostPrice(price: number, currency?: string | null): string {
  const symbol = currency === 'USD' ? '$' : 'K';
  return `${symbol}${price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

export function formatBizBoostPriceShort(price: number, currency?: string | null): string {
  const symbol = currency === 'USD' ? '$' : 'K';
  if (price >= 1000000) {
    return `${symbol}${(price / 1000000).toFixed(1)}M`;
  }
  if (price >= 1000) {
    return `${symbol}${(price / 1000).toFixed(1)}K`;
  }
  return `${symbol}${price.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`;
}

export function bizBoostCurrencySymbol(currency?: string | null): string {
  return currency === 'USD' ? '$' : 'K';
}

export function getBizBoostPrice(price: number, currency?: string | null): string {
  return formatBizBoostPrice(price, currency);
}
