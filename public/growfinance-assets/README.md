# GrowFinance PWA Icons

Place the following icon files in this directory for the PWA to work properly:

## Required Icons

| File | Size | Purpose |
|------|------|---------|
| `icon-72x72.png` | 72x72 | Android legacy |
| `icon-96x96.png` | 96x96 | Favicon, shortcuts |
| `icon-128x128.png` | 128x128 | Android |
| `icon-144x144.png` | 144x144 | Android |
| `icon-152x152.png` | 152x152 | iOS |
| `icon-192x192.png` | 192x192 | Android, iOS (main) |
| `icon-384x384.png` | 384x384 | Android splash |
| `icon-512x512.png` | 512x512 | Android splash, store |

## Icon Design Guidelines

- **Background**: Emerald green (#059669) or white
- **Symbol**: Dollar sign with growth/leaf element
- **Style**: Clean, modern, rounded corners
- **Format**: PNG with transparency (for maskable icons, use solid background)

## Generating Icons

You can use tools like:
- [PWA Asset Generator](https://www.pwabuilder.com/imageGenerator)
- [RealFaviconGenerator](https://realfavicongenerator.net/)
- [Maskable.app](https://maskable.app/) for testing maskable icons

## Quick Start

1. Create a 512x512 master icon
2. Use a PWA icon generator to create all sizes
3. Place all generated icons in this folder
4. Test with Chrome DevTools > Application > Manifest
