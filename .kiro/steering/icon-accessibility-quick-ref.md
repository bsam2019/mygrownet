---
inclusion: always
---

# Icon Accessibility Quick Reference

Use this guide when adding icons to components.

## Icon-Only Buttons

Always add aria-label:

```vue
<!-- ✅ Correct -->
<button aria-label="Close menu">
  <XMarkIcon class="h-5 w-5" aria-hidden="true" />
</button>

<!-- ❌ Wrong -->
<button>
  <XMarkIcon class="h-5 w-5" />
</button>
```

## Buttons with Text

Mark icon as decorative:

```vue
<!-- ✅ Correct -->
<button>
  <PlusIcon class="h-5 w-5" aria-hidden="true" />
  <span>Add Item</span>
</button>

<!-- ⚠️ Acceptable but not optimal -->
<button>
  <PlusIcon class="h-5 w-5" />
  <span>Add Item</span>
</button>
```

## Navigation Items

Use aria-current for active state:

```vue
<button
  :aria-label="`Navigate to ${name}`"
  :aria-current="isActive ? 'page' : undefined"
>
  <HomeIcon class="h-6 w-6" aria-hidden="true" />
  <span>{{ name }}</span>
</button>
```

## Icon Sizes

- **xs (h-4 w-4)**: Badges, inline indicators
- **sm (h-5 w-5)**: Buttons, menu items
- **md (h-6 w-6)**: Card headers, navigation
- **lg (h-8 w-8)**: Page headers
- **xl (h-12 w-12)**: Empty states

## Semantic Colors

- **Blue**: Primary actions, navigation
- **Green**: Success, money, earnings
- **Purple/Indigo**: Premium features
- **Orange/Amber**: Warnings, pending
- **Red**: Errors, critical actions
- **Gray**: Disabled, inactive

## Common Patterns

### Modal Close Button
```vue
<button
  @click="close"
  aria-label="Close [modal name] modal"
  class="p-2 hover:bg-white/20 rounded-full"
>
  <XMarkIcon class="h-5 w-5" aria-hidden="true" />
</button>
```

### Search Toggle
```vue
<button
  @click="toggleSearch"
  aria-label="Toggle search"
  class="p-3 rounded-lg"
>
  <MagnifyingGlassIcon class="h-5 w-5" aria-hidden="true" />
</button>
```

### Section Header
```vue
<h3 class="flex items-center gap-2">
  <UserIcon class="h-4 w-4" aria-hidden="true" />
  Account
</h3>
```

## Remember

1. Icon-only buttons → aria-label required
2. Icons with text → aria-hidden="true"
3. Use Heroicons (not custom SVGs)
4. Follow size standards
5. Use semantic colors

See ICON_STANDARDS.md for complete guidelines.
