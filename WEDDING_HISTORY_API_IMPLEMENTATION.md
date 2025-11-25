# Wedding Website - History API Implementation

**Last Updated:** November 25, 2025  
**Status:** Complete

## Overview

Implemented clean URL navigation using the HTML5 History API for the wedding website, replacing hash-based routing (#) with path-based routing (/).

## Changes Made

### 1. Frontend (WeddingWebsite.vue)

#### Navigation Links
- Changed all `href="#tab"` to `href="javascript:void(0)"`
- Added `@click.prevent` to prevent default anchor behavior
- Applies to both desktop and mobile navigation

#### History API Integration

**New Functions:**
```javascript
// Initialize tab from URL on page load
const initializeTabFromURL = () => {
  const path = window.location.pathname
  const match = path.match(/\/(story|program|qa|travel|rsvp)$/)
  if (match) {
    activeTab.value = match[1]
    window.history.replaceState({ tab: match[1] }, '', path)
  } else {
    activeTab.value = 'home'
    window.history.replaceState({ tab: 'home' }, '', path)
  }
}

// Handle browser back/forward buttons
const handlePopState = (event) => {
  if (event.state && event.state.tab) {
    activeTab.value = event.state.tab
  } else {
    const path = window.location.pathname
    const match = path.match(/\/(story|program|qa|travel|rsvp)$/)
    activeTab.value = match ? match[1] : 'home'
  }
}
```

**Updated Tab Navigation:**
```javascript
const setActiveTab = (tab) => {
  activeTab.value = tab
  // Clean URL construction
  const basePath = window.location.pathname.replace(/\/(story|program|qa|travel|rsvp)$/, '')
  const newPath = tab === 'home' ? basePath : `${basePath}/${tab}`
  window.history.pushState({ tab }, '', newPath)
}
```

**Lifecycle Hooks:**
```javascript
onMounted(() => {
  startBalloonAnimation()
  initializeTabFromURL()
  window.addEventListener('popstate', handlePopState)
})

onUnmounted(() => {
  stopBalloonAnimation()
  window.removeEventListener('popstate', handlePopState)
})
```

### 2. Backend Routes (routes/web.php)

Added support for tab-based URLs:

```php
// Specific wedding URL with tab support
Route::get('/kaoma-and-mubanga-dec-2025', [WeddingController::class, 'demoWebsite'])
    ->name('wedding.kaoma-mubanga');
Route::get('/kaoma-and-mubanga-dec-2025/{tab}', [WeddingController::class, 'demoWebsite'])
    ->where('tab', 'story|program|qa|travel|rsvp')
    ->name('wedding.kaoma-mubanga.tab');

// Generic wedding routes with tab support
Route::prefix('wedding')->name('wedding.')->group(function () {
    Route::get('/demo', [WeddingController::class, 'demoWebsite'])->name('demo');
    Route::get('/demo/{tab}', [WeddingController::class, 'demoWebsite'])
        ->where('tab', 'story|program|qa|travel|rsvp')
        ->name('demo.tab');
    Route::get('/{slug}', [WeddingController::class, 'weddingWebsite'])->name('website');
    Route::get('/{slug}/{tab}', [WeddingController::class, 'weddingWebsite'])
        ->where('tab', 'story|program|qa|travel|rsvp')
        ->name('website.tab');
    Route::post('/{id}/rsvp', [WeddingController::class, 'submitRSVP'])->name('rsvp.submit');
});
```

## URL Structure

### Before (Hash-based)
```
/kaoma-and-mubanga-dec-2025#home
/kaoma-and-mubanga-dec-2025#story
/kaoma-and-mubanga-dec-2025#program
```

### After (Clean URLs)
```
/kaoma-and-mubanga-dec-2025
/kaoma-and-mubanga-dec-2025/story
/kaoma-and-mubanga-dec-2025/program
/kaoma-and-mubanga-dec-2025/qa
/kaoma-and-mubanga-dec-2025/travel
```

## Features

### ✅ Clean URLs
- No hash symbols in URLs
- SEO-friendly paths
- Shareable tab-specific links

### ✅ Browser Navigation
- Back button works correctly
- Forward button works correctly
- Browser history maintained

### ✅ Direct Access
- Users can bookmark specific tabs
- Direct URL access loads correct tab
- Page refresh maintains tab state

### ✅ Mobile Support
- Works on mobile menu
- Consistent behavior across devices
- Smooth transitions

## Testing

Test the following scenarios:

1. **Tab Navigation**
   - Click through all tabs
   - Verify URL changes without page reload
   - Check active tab styling

2. **Browser Navigation**
   - Navigate to different tabs
   - Click browser back button
   - Click browser forward button
   - Verify correct tab displays

3. **Direct Access**
   - Visit `/kaoma-and-mubanga-dec-2025/story` directly
   - Verify "Our Story" tab loads
   - Test all tab URLs

4. **Page Refresh**
   - Navigate to a tab
   - Refresh the page
   - Verify same tab displays

5. **Mobile Menu**
   - Open mobile menu
   - Select different tabs
   - Verify URL updates and menu closes

## Browser Compatibility

The History API is supported in:
- Chrome 5+
- Firefox 4+
- Safari 5+
- Edge (all versions)
- Opera 11.5+
- iOS Safari 4.2+
- Android Browser 2.2+

## Notes

- RSVP button opens modal (doesn't change URL)
- Home tab uses base URL without `/home` suffix
- Tab parameter is validated server-side with regex constraint
- State object includes tab name for popstate handling
- Initial state is set on mount using `replaceState`

## Files Modified

1. `resources/js/pages/Wedding/WeddingWebsite.vue`
   - Updated navigation links
   - Added History API functions
   - Added lifecycle hooks for popstate

2. `routes/web.php`
   - Added tab-based route variants
   - Added regex constraints for valid tabs
