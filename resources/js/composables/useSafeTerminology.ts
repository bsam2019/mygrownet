/**
 * Safe Terminology Composable
 * 
 * Provides terminology mapping to transform MLM/investment language
 * into product-focused, legally compliant language for MyGrowNet platform.
 * 
 * Usage:
 * const { getSafeTerm, replaceTerminology } = useSafeTerminology();
 * const safeText = getSafeTerm('earnings'); // Returns 'store credit'
 * const safeContent = replaceTerminology('Check your earnings'); // Returns 'Check your store credit'
 */

/**
 * Financial terminology keys that need to be replaced
 */
export type FinancialTermKey =
  | 'earnings'
  | 'commissions'
  | 'wallet balance'
  | 'investment'
  | 'investments'
  | 'returns'
  | 'profit share'
  | 'profit shares'
  | 'dividends'
  | 'payout'
  | 'payouts'
  | 'income'
  | 'profits'
  | 'ROI'
  | 'interest';

/**
 * Network/MLM terminology keys that need to be replaced
 */
export type NetworkTermKey =
  | 'downline'
  | 'upline'
  | 'matrix'
  | 'MLM'
  | 'recruitment'
  | 'team building'
  | 'sponsor'
  | 'recruiter';

/**
 * Product/tier terminology keys that need to be replaced
 */
export type ProductTermKey =
  | 'package'
  | 'packages'
  | 'position'
  | 'positions'
  | 'level'
  | 'levels'
  | 'rank'
  | 'ranks';

/**
 * Union of all terminology keys
 */
export type TerminologyKey = FinancialTermKey | NetworkTermKey | ProductTermKey;

/**
 * Terminology mapping type with strict typing
 */
export type TerminologyMapping = Record<TerminologyKey, string>;

/**
 * Core terminology mapping object
 * Maps unsafe/MLM terms to safe, product-focused alternatives
 */
const terminologyMap: Record<TerminologyKey, string> = {
  // Financial Terms
  'earnings': 'store credit',
  'commissions': 'referral rewards',
  'wallet balance': 'rewards balance',
  'investment': 'subscription',
  'investments': 'subscriptions',
  'returns': 'benefits',
  'profit share': 'community rewards',
  'profit shares': 'community rewards',
  'dividends': 'profit-sharing benefits',
  'payout': 'reward distribution',
  'payouts': 'reward distributions',
  'income': 'rewards',
  'profits': 'benefits',
  'ROI': 'value received',
  'interest': 'bonus',
  
  // Network Terms
  'downline': 'referred members',
  'upline': 'referrer',
  'matrix': 'network',
  'MLM': 'community network',
  'recruitment': 'referrals',
  'team building': 'community growth',
  'sponsor': 'referrer',
  'recruiter': 'referrer',
  
  // Product Terms
  'package': 'subscription tier',
  'packages': 'subscription tiers',
  'position': 'membership',
  'positions': 'memberships',
  'level': 'tier',
  'levels': 'tiers',
  'rank': 'membership level',
  'ranks': 'membership levels',
};

/**
 * Type guard to check if a string is a valid terminology key
 */
function isTerminologyKey(key: string): key is TerminologyKey {
  return key.toLowerCase() in terminologyMap;
}

/**
 * Case-insensitive terminology mapping for text replacement
 * Includes variations with different capitalizations
 */
const getCaseInsensitiveMap = (): Record<string, string> => {
  const caseInsensitiveMap: Record<string, string> = {};
  
  Object.entries(terminologyMap).forEach(([key, value]) => {
    // Lowercase
    caseInsensitiveMap[key.toLowerCase()] = value.toLowerCase();
    
    // Title Case
    const titleCaseKey = key.split(' ').map(word => 
      word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
    ).join(' ');
    const titleCaseValue = value.split(' ').map(word => 
      word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
    ).join(' ');
    caseInsensitiveMap[titleCaseKey] = titleCaseValue;
    
    // UPPERCASE
    caseInsensitiveMap[key.toUpperCase()] = value.toUpperCase();
  });
  
  return caseInsensitiveMap;
};

export function useSafeTerminology() {
  /**
   * Get the safe alternative for a specific term
   * @param term - The unsafe term to replace
   * @returns The safe alternative term
   */
  const getSafeTerm = (term: TerminologyKey | string): string => {
    const lowerTerm = term.toLowerCase();
    return (terminologyMap as Record<string, string>)[lowerTerm] || term;
  };

  /**
   * Replace all unsafe terminology in a text string
   * Handles case-insensitive replacement while preserving original case style
   * @param text - The text containing unsafe terminology
   * @returns Text with all unsafe terms replaced
   */
  const replaceTerminology = (text: string): string => {
    if (!text) return text;
    
    let result = text;
    const caseMap = getCaseInsensitiveMap();
    
    // Sort keys by length (descending) to replace longer phrases first
    const sortedKeys = Object.keys(caseMap).sort((a, b) => b.length - a.length);
    
    sortedKeys.forEach(unsafeTerm => {
      const safeTerm = caseMap[unsafeTerm];
      // Use word boundaries to avoid partial word replacements
      const regex = new RegExp(`\\b${unsafeTerm}\\b`, 'gi');
      result = result.replace(regex, (match) => {
        // Preserve the case style of the original match
        if (match === match.toUpperCase()) {
          return safeTerm.toUpperCase();
        } else if (match[0] === match[0].toUpperCase()) {
          return safeTerm.charAt(0).toUpperCase() + safeTerm.slice(1);
        }
        return safeTerm;
      });
    });
    
    return result;
  };

  /**
   * Check if a text contains any unsafe terminology
   * @param text - The text to check
   * @returns True if unsafe terms are found
   */
  const containsUnsafeTerms = (text: string): boolean => {
    if (!text) return false;
    
    const lowerText = text.toLowerCase();
    return Object.keys(terminologyMap).some(unsafeTerm => {
      const regex = new RegExp(`\\b${unsafeTerm}\\b`, 'i');
      return regex.test(lowerText);
    });
  };

  /**
   * Get all unsafe terms found in a text
   * @param text - The text to analyze
   * @returns Array of unsafe terms found
   */
  const findUnsafeTerms = (text: string): string[] => {
    if (!text) return [];
    
    const lowerText = text.toLowerCase();
    const found: string[] = [];
    
    Object.keys(terminologyMap).forEach(unsafeTerm => {
      const regex = new RegExp(`\\b${unsafeTerm}\\b`, 'i');
      if (regex.test(lowerText)) {
        found.push(unsafeTerm);
      }
    });
    
    return found;
  };

  /**
   * Get the complete terminology mapping
   * @returns The full terminology mapping object
   */
  const getTerminologyMap = (): Readonly<Record<TerminologyKey, string>> => {
    return Object.freeze({ ...terminologyMap });
  };

  /**
   * Add or update a custom terminology mapping
   * @param unsafeTerm - The unsafe term to map
   * @param safeTerm - The safe alternative
   */
  const addCustomMapping = (unsafeTerm: string, safeTerm: string): void => {
    // Type assertion needed since we're adding dynamic keys
    (terminologyMap as Record<string, string>)[unsafeTerm.toLowerCase()] = safeTerm.toLowerCase();
  };

  return {
    getSafeTerm,
    replaceTerminology,
    containsUnsafeTerms,
    findUnsafeTerms,
    getTerminologyMap,
    addCustomMapping,
    isTerminologyKey,
  };
}
