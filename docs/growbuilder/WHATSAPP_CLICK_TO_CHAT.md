# WhatsApp Click-to-Chat Feature

**Last Updated:** January 19, 2026  
**Status:** Development (Phase 2 - GrowBuilder Roadmap)

## Overview

WhatsApp Click-to-Chat allows GrowBuilder users to add WhatsApp contact buttons to their websites, enabling visitors to start conversations instantly. This feature aligns with the "WhatsApp-first" vision for Zambian businesses.

## Features

### 1. WhatsApp Section Component
- Drag-and-drop section for page builder
- Customizable button text, style, and colors
- Pre-filled message support
- Mobile-optimized (opens WhatsApp app directly)
- Responsive design

### 2. Site-Wide Settings
- Configure WhatsApp number in Site Settings
- Set default pre-filled message
- Optional floating button (global)
- Floating button position control

## Implementation

### Database Schema

**Migration:** `database/migrations/2026_01_19_035211_add_whatsapp_settings_to_growbuilder_sites_table.php`

Added columns to `growbuilder_sites` table:
- `whatsapp_number` (string, nullable) - Phone number with country code
- `whatsapp_default_message` (text, nullable) - Default pre-filled message
- `whatsapp_floating_button` (boolean, default: false) - Enable global floating button
- `whatsapp_floating_position` (string, default: 'bottom-right') - Position of floating button

### Frontend Components

#### 1. WhatsApp Section Component
**File:** `resources/js/pages/GrowBuilder/Editor/components/sections/WhatsAppSection.vue`

Features:
- Customizable button text
- Three button styles: solid, outline, minimal
- Three sizes: sm, md, lg
- Alignment options: left, center, right
- Optional WhatsApp icon
- Auto-formats WhatsApp URL with pre-filled message
- Mobile detection for app deep-linking

#### 2. WhatsApp Inspector
**File:** `resources/js/pages/GrowBuilder/Editor/components/inspectors/WhatsAppInspector.vue`

Settings panel with:
- Phone number input (auto-formats Zambian numbers)
- Pre-filled message textarea
- Button style selector
- Button size selector
- Alignment controls
- Icon toggle
- Color customization (button and text)

### Configuration Files

#### Section Registration
**Files:**
- `resources/js/pages/GrowBuilder/Editor/config/sectionBlocks.ts` - Added WhatsApp to Forms category
- `resources/js/pages/GrowBuilder/Editor/config/sectionDefaults.ts` - Default content for WhatsApp section
- `resources/js/pages/GrowBuilder/Editor/config/sectionSchemas.ts` - Field schema for inspector
- `resources/js/pages/GrowBuilder/Editor/components/sections/index.ts` - Component registry
- `resources/js/pages/GrowBuilder/Editor/types/index.ts` - Added 'whatsapp' to SectionType

#### Default Content
```typescript
{
    phoneNumber: '',
    message: `Hi! I'm interested in learning more about ${siteName}.`,
    buttonText: 'Chat on WhatsApp',
    buttonStyle: 'solid',
    buttonSize: 'md',
    alignment: 'center',
    showIcon: true,
    backgroundColor: '#25D366', // WhatsApp green
    textColor: '#ffffff',
}
```

## Usage

### For Site Owners

#### Adding WhatsApp Section
1. Open GrowBuilder editor
2. Click "Add" tab in left sidebar
3. Scroll to "Forms" category
4. Drag "WhatsApp" section to page
5. Configure phone number and message
6. Customize button style and colors
7. Save and publish

#### Configuring Site Settings
1. Go to Site Settings
2. Enter WhatsApp number (with country code)
3. Set default message
4. Enable floating button (optional)
5. Choose floating button position
6. Save settings

### For Developers

#### WhatsApp URL Format
```javascript
const whatsappUrl = computed(() => {
    const phone = props.section.content.phoneNumber?.replace(/\s+/g, '');
    const message = encodeURIComponent(props.section.content.message || '');
    return `https://wa.me/${phone}?text=${message}`;
});
```

#### Mobile Detection
```javascript
const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
// Mobile users get direct app link, desktop users get web.whatsapp.com
```

## Customization Options

### Button Styles
- **Solid:** Full background color, white text
- **Outline:** Border only, colored text
- **Minimal:** No border, colored text with hover effect

### Button Sizes
- **Small (sm):** `px-4 py-2 text-sm`
- **Medium (md):** `px-6 py-3 text-base`
- **Large (lg):** `px-8 py-4 text-lg`

### Color Presets
- WhatsApp Green: `#25D366`
- WhatsApp Dark Green: `#128C7E`
- WhatsApp Teal: `#075E54`
- Primary Blue: `#2563eb`
- Purple: `#7c3aed`

## Best Practices

### Phone Number Format
- Always include country code (e.g., `+260` for Zambia)
- Remove spaces in URL generation
- Display with spaces for readability: `+260 XXX XXX XXX`

### Pre-filled Messages
- Keep messages concise and relevant
- Include context about the business
- Make it easy for customers to start conversation
- Example: "Hi! I'm interested in learning more about [Product/Service]."

### Button Placement
- Add to contact sections for easy access
- Consider floating button for persistent availability
- Place near CTAs on product/service pages
- Test on mobile devices for optimal UX

## Roadmap

### Phase 1: Core Implementation ✅
- [x] Database migration
- [x] WhatsApp section component
- [x] Inspector panel
- [x] Section registration
- [x] Type definitions

### Phase 2: Site Settings (Pending)
- [ ] Add WhatsApp fields to Site Settings page
- [ ] Update site settings controller
- [ ] Save WhatsApp configuration

### Phase 3: Floating Button (Pending)
- [ ] Create floating WhatsApp button component
- [ ] Add to published site renderer
- [ ] Position controls (bottom-right, bottom-left, etc.)
- [ ] Show/hide based on site settings

### Phase 4: Published Site Rendering (Pending)
- [ ] Add WhatsApp section to site renderer
- [ ] Test on published sites
- [ ] Mobile optimization
- [ ] Analytics tracking (optional)

## Technical Notes

### Migration Fix
The initial migration used `after('contact_address')` which failed because that column doesn't exist in production. Fixed by removing column positioning clauses and appending columns to end of table.

### Component Architecture
- Uses schema-driven inspector (DynamicSectionInspector)
- Lazy-loaded for performance
- Follows existing section component patterns
- Fully typed with TypeScript

## Testing Checklist

- [ ] Add WhatsApp section to page
- [ ] Configure phone number and message
- [ ] Test all button styles (solid, outline, minimal)
- [ ] Test all button sizes (sm, md, lg)
- [ ] Test alignment options
- [ ] Test color customization
- [ ] Test on mobile devices
- [ ] Test WhatsApp URL generation
- [ ] Test with/without icon
- [ ] Verify published site rendering

## Changelog

### January 19, 2026
- Created database migration for WhatsApp settings
- Fixed migration error (removed `after()` clauses)
- Created WhatsApp section component
- Created WhatsApp inspector component
- Registered section in configuration files
- Added type definitions
- Created documentation
