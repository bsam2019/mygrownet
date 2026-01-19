# Marketplace Social Sharing

**Last Updated:** January 19, 2026
**Status:** Production

## Overview

Social sharing for marketplace shops and products using Open Graph (OG) and Twitter Card meta tags. When users share links on Facebook, Twitter, WhatsApp, etc., the platform displays rich previews with images, titles, and descriptions.

## Implementation

### Open Graph Meta Tags

#### For Seller Shops

**Image Priority:**
1. Seller's cover image (`cover_image_url`)
2. Seller's logo (`logo_url`)
3. Default marketplace shop image: `public/images/marketplace-default-shop.jpg`

**Meta Tags:**
- `og:type`: "website"
- `og:url`: Shop URL
- `og:title`: "{Business Name} - MyGrowNet Marketplace"
- `og:description`: Seller description or default text
- `og:image`: Cover image, logo, or default
- `og:image:width`: 1200
- `og:image:height`: 630

#### For Products

**Image Priority:**
1. Product's primary image (`primary_image_url`)
2. Default marketplace product image: `public/images/marketplace-default-product.jpg`

**Meta Tags:**
- `og:type`: "product"
- `og:url`: Product URL
- `og:title`: "{Product Name} - MyGrowNet Marketplace"
- `og:description`: Product description (first 150 characters)
- `og:image`: Primary product image or default
- `og:price:amount`: Product price
- `og:price:currency`: "ZMW"

### Twitter Card Meta Tags

Both shops and products include Twitter-specific meta tags:
- `twitter:card`: "summary_large_image"
- `twitter:url`: Same as og:url
- `twitter:title`: Same as og:title
- `twitter:description`: Same as og:description
- `twitter:image`: Same as og:image

## Files Modified

### Backend
- `app/Http/Controllers/Marketplace/HomeController.php`
  - Added `meta` array to `seller()` method
  - Added `meta` array to `product()` method

### Frontend
- `resources/js/pages/Marketplace/Seller.vue`
  - Added OG and Twitter meta tags in Head component
  - Added `meta` prop to interface

### Models
- `app/Models/MarketplaceProduct.php`
  - Updated `getImageUrlsAttribute()` to handle marketplace paths
  - Updated `getPrimaryImageUrlAttribute()` to handle marketplace paths

## Required Default Images

Create these default images in `public/images/`:

1. **marketplace-default-shop.jpg** (1200x630px)
   - Generic shop/store image
   - MyGrowNet branding
   - Professional appearance

2. **marketplace-default-product.jpg** (1200x630px)
   - Generic product placeholder
   - MyGrowNet branding
   - Clean, modern design

### Image Specifications

**Dimensions:** 1200x630 pixels (Facebook/OG standard)
**Format:** JPG or PNG
**File Size:** < 1MB for fast loading
**Content:** 
- Include MyGrowNet logo
- Use brand colors (blue #2563eb, orange #f97316)
- Keep text minimal and readable
- Avoid important content near edges (safe zone: 1200x600)

## Testing Social Sharing

### Facebook Debugger
URL: https://developers.facebook.com/tools/debug/
- Enter your shop/product URL
- Click "Scrape Again" to refresh cache
- Verify image, title, and description appear correctly

### Twitter Card Validator
URL: https://cards-dev.twitter.com/validator
- Enter your shop/product URL
- Verify card preview displays correctly

### LinkedIn Post Inspector
URL: https://www.linkedin.com/post-inspector/
- Enter your shop/product URL
- Check preview appearance

## Usage

### Sharing a Shop

When users click the share button on a seller's shop page:
1. Copy link option copies the shop URL
2. Social media options open share dialogs with pre-filled URL
3. Social platforms fetch OG meta tags and display rich preview

### Sharing a Product

Products can be shared directly from:
- Product detail page
- Share buttons in product cards (future enhancement)
- Direct URL sharing

## Troubleshooting

### Image Not Showing

**Problem:** Social platform shows no image or wrong image

**Solutions:**
1. Check image URL is publicly accessible
2. Verify image meets size requirements (1200x630)
3. Clear social platform cache using debugger tools
4. Ensure image is served over HTTPS
5. Check image file size is < 8MB

### Wrong Information Displayed

**Problem:** Old title/description showing

**Solutions:**
1. Clear social platform cache
2. Use Facebook Debugger "Scrape Again"
3. Wait 24 hours for cache to expire naturally
4. Verify meta tags in page source

### Image Not Loading on Mobile

**Problem:** Image works on desktop but not mobile

**Solutions:**
1. Check image is responsive
2. Verify CDN/storage is accessible from mobile networks
3. Test image URL directly in mobile browser
4. Check for CORS issues

## Future Enhancements

- [ ] Generate dynamic OG images with product info overlay
- [ ] Add share tracking/analytics
- [ ] Implement share buttons on product cards
- [ ] Add WhatsApp-specific optimizations
- [ ] Create category-specific default images
- [ ] Add seller-specific share templates

## Changelog

### January 19, 2026
- Initial implementation of OG meta tags for shops and products
- Added Twitter Card support
- Updated image URL handling in MarketplaceProduct model
- Created documentation
