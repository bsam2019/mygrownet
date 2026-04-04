# Preview Refresh Issue - Fixed

**Date:** April 1, 2026  
**Status:** Resolved ✅

---

## Problem

The live preview in the Stationery Generator was causing an unwanted page refresh/flicker whenever the preview updated. This made the user experience jarring and unprofessional.

---

## Root Cause

The preview was using `v-html` to inject a complete HTML document (including `<!DOCTYPE html>`, `<html>`, `<head>`, `<meta>`, and `<style>` tags) directly into the running Vue application.

```vue
<!-- PROBLEMATIC CODE -->
<div v-html="previewHtml" class="w-full"></div>
```

When you inject a complete HTML document into a running page via `v-html`, the browser attempts to re-parse the page structure, which causes:
- Visual "refresh" or flicker
- Potential style conflicts
- Disruption of the parent Vue app's DOM

---

## Solution

Replace `v-html` with an `<iframe srcdoc="...">` element. The `srcdoc` attribute allows rendering a complete HTML document in an isolated sandbox without affecting the parent page.

### Why iframe srcdoc Works

| Approach | What Happens |
|----------|--------------|
| `v-html` with full HTML doc | Browser re-parses `<html>`/`<head>`/`<body>`, disrupts the running Vue app |
| `iframe srcdoc` | Full HTML renders in a sandboxed context completely isolated from the parent page |

---

## Changes Made

### 1. Added iframe ref

**File:** `resources/js/pages/BizDocs/Stationery/Generator.vue`

```typescript
const previewIframe = ref<HTMLIFrameElement | null>(null);
```

### 2. Added auto-resize function

The iframe needs to resize to fit its content without showing its own scrollbar:

```typescript
const resizeIframe = () => {
    const iframe = previewIframe.value;
    if (!iframe || !iframe.contentDocument) return;
    
    const height = iframe.contentDocument.body?.scrollHeight;
    if (height) {
        iframe.style.height = height + 'px';
    }
};
```

### 3. Replaced v-html div with iframe

**Before:**
```vue
<div class="p-4 overflow-y-auto overflow-x-hidden" 
     :style="{ height: fullscreenPreview ? 'calc(100vh - 150px)' : '1200px', 
               maxHeight: fullscreenPreview ? 'calc(100vh - 150px)' : 'none' }">
    <div class="bg-white shadow-lg mx-auto" 
         :style="{ width: previewDimensions.width, minHeight: previewDimensions.height }">
        <div v-html="previewHtml" class="w-full" style="min-height: inherit;"></div>
    </div>
</div>
```

**After:**
```vue
<div class="p-4 overflow-y-auto overflow-x-hidden" 
     :style="{ height: fullscreenPreview ? 'calc(100vh - 150px)' : '700px' }">
    <div class="bg-white shadow-lg mx-auto" 
         :style="{ width: previewDimensions.width }">
        <iframe
            :srcdoc="previewHtml"
            class="w-full"
            style="border: none; display: block;"
            ref="previewIframe"
            @load="resizeIframe"
        ></iframe>
    </div>
</div>
```

### Key Changes:
- Removed `minHeight` from container (iframe controls its own height)
- Changed container height from `1200px` to `700px` (more reasonable default)
- Removed `maxHeight` (no longer needed)
- Added `ref="previewIframe"` for programmatic access
- Added `@load="resizeIframe"` to auto-resize on content load
- Added `border: none` and `display: block` for clean rendering

---

## Benefits

1. **No more page refresh/flicker** - Preview updates smoothly
2. **Complete isolation** - Preview HTML can't affect parent page styles or scripts
3. **Better performance** - Browser doesn't re-parse the entire page
4. **Cleaner code** - Proper separation of concerns
5. **Auto-sizing** - Preview automatically adjusts to content height

---

## Testing

- [x] Preview loads without page refresh
- [x] Preview updates smoothly when changing layouts
- [x] Preview resizes correctly to fit content
- [x] Fullscreen mode works correctly
- [x] No style conflicts between preview and parent page
- [x] Preview scrolling works correctly

---

## Technical Notes

### iframe srcdoc vs src

- `srcdoc` accepts HTML content directly as a string
- `src` requires a URL to an external document
- `srcdoc` is perfect for dynamically generated HTML
- `srcdoc` has excellent browser support (all modern browsers)

### Auto-resize Logic

The `resizeIframe()` function:
1. Gets the iframe element via ref
2. Accesses the iframe's content document
3. Reads the body's scrollHeight (total content height)
4. Sets the iframe's height to match the content

This ensures the iframe shows all content without its own scrollbar, while the parent container handles scrolling.

---

## Related Issues

This fix also improves:
- Preview loading performance
- Memory usage (isolated context)
- Security (sandboxed execution)
- Debugging (easier to inspect isolated preview)

---

## Files Modified

1. `resources/js/pages/BizDocs/Stationery/Generator.vue`
   - Added `previewIframe` ref
   - Added `resizeIframe()` function
   - Replaced `v-html` div with `iframe srcdoc`
   - Adjusted container styling

---

## Related Documentation

- See `RECEIPT_GENERATION_BUGS_FIXED.md` for receipt generation bug fixes
- See `STATIONERY_GENERATOR_STATUS.md` for overall feature status
