/**
 * Navigation Style Options
 * Configuration for different navigation layouts
 */

import type { NavStyleOption, ThemeColor, SocialPlatformOption } from '../types';

/**
 * Available navigation styles
 */
export const navStyleOptions: NavStyleOption[] = [
    { id: 'default', name: 'Default', description: 'Clean & simple layout' },
    { id: 'centered', name: 'Centered', description: 'Logo in the middle' },
    { id: 'split', name: 'Split', description: 'Links on both sides of logo' },
    { id: 'floating', name: 'Floating', description: 'Rounded with shadow' },
    { id: 'transparent', name: 'Transparent', description: 'Overlay on hero' },
    { id: 'dark', name: 'Dark', description: 'Dark background' },
    { id: 'sidebar', name: 'Sidebar', description: 'Hamburger menu' },
    { id: 'mega', name: 'Mega Menu', description: 'Dropdown columns' },
];

/**
 * Theme color presets
 */
export const themeColors: ThemeColor[] = [
    { name: 'White', value: '#ffffff' },
    { name: 'Light Gray', value: '#f9fafb' },
    { name: 'Gray', value: '#f3f4f6' },
    { name: 'Blue', value: '#eff6ff' },
    { name: 'Green', value: '#f0fdf4' },
    { name: 'Purple', value: '#faf5ff' },
    { name: 'Dark', value: '#1f2937' },
    { name: 'Navy', value: '#1e3a5f' },
];

/**
 * Social platform options
 */
export const socialPlatforms: SocialPlatformOption[] = [
    { value: 'facebook', label: 'Facebook' },
    { value: 'twitter', label: 'Twitter/X' },
    { value: 'instagram', label: 'Instagram' },
    { value: 'linkedin', label: 'LinkedIn' },
    { value: 'youtube', label: 'YouTube' },
    { value: 'tiktok', label: 'TikTok' },
];

/**
 * Footer layout options
 */
export const footerLayoutOptions = [
    { id: 'columns', name: 'Columns', description: 'Multi-column layout' },
    { id: 'centered', name: 'Centered', description: 'Center-aligned content' },
    { id: 'minimal', name: 'Minimal', description: 'Simple copyright only' },
    { id: 'split', name: 'Split', description: 'Two-column layout' },
    { id: 'stacked', name: 'Stacked', description: 'Vertically stacked' },
    { id: 'newsletter', name: 'Newsletter', description: 'With email signup' },
    { id: 'social', name: 'Social Focus', description: 'Emphasize social links' },
    { id: 'contact', name: 'Contact', description: 'With contact info' },
];
