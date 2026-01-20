/**
 * Section Field Schemas
 * Defines the structure and fields for each section type
 * Used by DynamicSectionInspector to render the appropriate controls
 */

export type FieldType = 
    | 'text' 
    | 'textarea' 
    | 'number'
    | 'select' 
    | 'checkbox' 
    | 'color'
    | 'image'
    | 'video'
    | 'buttonGroup'
    | 'items'
    | 'plans'
    | 'faqItems'
    | 'teamMembers'
    | 'statItems'
    | 'galleryImages'
    | 'divider'
    | 'info'
    | 'link'
    | 'range'
    | 'backgroundType'
    | 'overlay';

export interface FieldOption {
    label: string;
    value: string | number | boolean;
}

export interface BaseField {
    key: string;
    type: FieldType;
    label?: string;
    placeholder?: string;
    helpText?: string;
    condition?: (content: Record<string, any>, style: Record<string, any>) => boolean;
    tab?: 'content' | 'style' | 'advanced';
}

export interface TextField extends BaseField {
    type: 'text' | 'textarea';
    maxLength?: number;
    rows?: number;
}

export interface NumberField extends BaseField {
    type: 'number';
    min?: number;
    max?: number;
    step?: number;
}

export interface SelectField extends BaseField {
    type: 'select';
    options: FieldOption[];
    defaultValue?: string | number;
}

export interface CheckboxField extends BaseField {
    type: 'checkbox';
}

export interface ColorField extends BaseField {
    type: 'color';
    presets?: string[];
}

export interface ImageField extends BaseField {
    type: 'image';
    aspectRatio?: number;
}

export interface VideoField extends BaseField {
    type: 'video';
}

export interface ButtonGroupField extends BaseField {
    type: 'buttonGroup';
    options: FieldOption[];
}

export interface RangeField extends BaseField {
    type: 'range';
    min: number;
    max: number;
    step?: number;
    unit?: string;
}

export interface ItemsField extends BaseField {
    type: 'items';
    itemFields: SchemaField[];
    addLabel?: string;
    maxItems?: number;
}

export interface DividerField extends BaseField {
    type: 'divider';
    key: string;
}

export interface InfoField extends BaseField {
    type: 'info';
    key: string;
    variant?: 'info' | 'warning' | 'success';
    message: string;
}

export interface LinkField extends BaseField {
    type: 'link';
    key: string;
    url: string;
    external?: boolean;
}

export interface BackgroundTypeField extends BaseField {
    type: 'backgroundType';
    options: ('solid' | 'gradient' | 'image' | 'video')[];
}

export interface OverlayField extends BaseField {
    type: 'overlay';
}

export interface GalleryImagesField extends BaseField {
    type: 'galleryImages';
}

export type SchemaField = 
    | TextField 
    | NumberField
    | SelectField 
    | CheckboxField 
    | ColorField 
    | ImageField
    | VideoField
    | ButtonGroupField
    | RangeField
    | ItemsField
    | DividerField
    | InfoField
    | LinkField
    | BackgroundTypeField
    | OverlayField
    | GalleryImagesField;

export interface SectionSchema {
    type: string;
    name: string;
    fields: SchemaField[];
    styleFields?: SchemaField[];
    advancedFields?: SchemaField[];
}

// Common field definitions for reuse
const textPositionField: SelectField = {
    key: 'textPosition',
    type: 'select',
    label: 'Text Position',
    options: [
        { label: 'Left', value: 'left' },
        { label: 'Center', value: 'center' },
        { label: 'Right', value: 'right' },
    ],
};

const backgroundColorField: ColorField = {
    key: 'backgroundColor',
    type: 'color',
    label: 'Background Color',
    tab: 'style',
    presets: ['#ffffff', '#f9fafb', '#f3f4f6', '#111827', '#eff6ff', '#2563eb'],
};

const minHeightField: RangeField = {
    key: 'minHeight',
    type: 'range',
    label: 'Min Height',
    min: 200,
    max: 800,
    step: 50,
    unit: 'px',
    tab: 'style',
};

/**
 * Section Schemas - Define fields for each section type
 */
export const sectionSchemas: Record<string, SectionSchema> = {
    hero: {
        type: 'hero',
        name: 'Hero',
        fields: [
            { key: 'layout', type: 'select', label: 'Layout', defaultValue: 'centered', options: [
                { label: 'Centered', value: 'centered' },
                { label: 'Split - Image Right', value: 'split-right' },
                { label: 'Split - Image Left', value: 'split-left' },
                { label: 'Video Background', value: 'video' },
                { label: 'Slideshow', value: 'slideshow' },
            ]},
            { key: 'title', type: 'text', label: 'Title', placeholder: 'Welcome to Our Business' },
            { key: 'subtitle', type: 'textarea', label: 'Subtitle', rows: 2, placeholder: 'We help you grow and succeed' },
            { key: 'image', type: 'image', label: 'Hero Image', condition: (c) => c.layout === 'split-right' || c.layout === 'split-left' },
            { key: 'divider1', type: 'divider', label: 'Background' },
            { key: 'backgroundType', type: 'backgroundType', label: 'Background Type', options: ['solid', 'gradient', 'image', 'video'], condition: (c) => c.layout !== 'slideshow' },
            { key: 'backgroundImage', type: 'image', label: 'Background Image', condition: (c) => c.layout !== 'slideshow' && (!c.backgroundType || c.backgroundType === 'image') },
            { key: 'videoBackground', type: 'video', label: 'Video Background', condition: (c) => c.layout !== 'slideshow' && c.backgroundType === 'video' },
            { key: 'overlay', type: 'overlay', label: 'Overlay', condition: (c) => c.layout !== 'slideshow' && (c.backgroundImage || c.videoBackground) },
            { key: 'dividerSlides', type: 'divider', label: 'Slides', condition: (c) => c.layout === 'slideshow' },
            { key: 'slides', type: 'items', label: 'Slides', addLabel: 'Add Slide', condition: (c) => c.layout === 'slideshow', itemFields: [
                { key: 'backgroundImage', type: 'image', label: 'Background Image' },
                { key: 'title', type: 'text', label: 'Title' },
                { key: 'subtitle', type: 'text', label: 'Subtitle' },
                { key: 'buttonText', type: 'text', label: 'Button Text' },
                { key: 'buttonLink', type: 'text', label: 'Button Link' },
            ]},
            { key: 'autoPlay', type: 'checkbox', label: 'Auto-play Slides', condition: (c) => c.layout === 'slideshow' },
            { key: 'slideInterval', type: 'select', label: 'Slide Interval', condition: (c) => c.layout === 'slideshow' && c.autoPlay, options: [
                { label: '3 seconds', value: 3000 },
                { label: '5 seconds', value: 5000 },
                { label: '7 seconds', value: 7000 },
                { label: '10 seconds', value: 10000 },
            ]},
            { key: 'divider2', type: 'divider', label: 'Layout' },
            textPositionField,
            { key: 'divider3', type: 'divider', label: 'Buttons', condition: (c) => c.layout !== 'slideshow' },
            { key: 'buttonText', type: 'text', label: 'Primary Button', placeholder: 'Get Started', condition: (c) => c.layout !== 'slideshow' },
            { key: 'buttonLink', type: 'text', label: 'Button Link', placeholder: '#contact', condition: (c) => c.layout !== 'slideshow' },
            { key: 'secondaryButtonText', type: 'text', label: 'Secondary Button', placeholder: 'Learn More', condition: (c) => c.layout !== 'slideshow' },
            { key: 'secondaryButtonLink', type: 'text', label: 'Secondary Link', placeholder: '#about', condition: (c) => c.layout !== 'slideshow' },
        ],
        styleFields: [
            { key: 'backgroundColor', type: 'color', label: 'Background Color' },
            { key: 'textColor', type: 'color', label: 'Text Color', presets: ['#ffffff', '#111827'] },
            minHeightField,
        ],
    },

    'page-header': {
        type: 'page-header',
        name: 'Page Header',
        fields: [
            { key: 'title', type: 'text', label: 'Title', placeholder: 'About Us' },
            { key: 'subtitle', type: 'textarea', label: 'Subtitle', rows: 2 },
            { key: 'backgroundImage', type: 'image', label: 'Background Image' },
            textPositionField,
        ],
        styleFields: [
            { key: 'backgroundColor', type: 'color', label: 'Background Color' },
            { key: 'textColor', type: 'color', label: 'Text Color' },
            { key: 'minHeight', type: 'range', label: 'Height', min: 100, max: 400, step: 20, unit: 'px' },
        ],
    },

    about: {
        type: 'about',
        name: 'About',
        fields: [
            { key: 'layout', type: 'select', label: 'Layout', defaultValue: 'image-right', options: [
                { label: 'Image Right', value: 'image-right' },
                { label: 'Image Left', value: 'image-left' },
                { label: 'Full-width Image Top', value: 'image-top' },
            ]},
            { key: 'title', type: 'text', label: 'Title', placeholder: 'About Us' },
            { key: 'description', type: 'textarea', label: 'Description', rows: 4 },
            { key: 'image', type: 'image', label: 'Image' },
            textPositionField,
        ],
        styleFields: [backgroundColorField],
    },

    services: {
        type: 'services',
        name: 'Services',
        fields: [
            { key: 'layout', type: 'select', label: 'Layout', defaultValue: 'grid', options: [
                { label: 'Grid Cards', value: 'grid' },
                { label: 'List with Icons', value: 'list' },
                { label: 'Cards with Images', value: 'cards-images' },
                { label: 'Alternating Rows', value: 'alternating' },
            ]},
            { key: 'title', type: 'text', label: 'Title', placeholder: 'Our Services' },
            { key: 'subtitle', type: 'textarea', label: 'Subtitle', rows: 2 },
            textPositionField,
            { key: 'columns', type: 'buttonGroup', label: 'Columns', options: [
                { label: '2', value: 2 },
                { label: '3', value: 3 },
                { label: '4', value: 4 },
            ], condition: (c) => c.layout === 'grid' || !c.layout },
            { key: 'divider1', type: 'divider', label: 'Services' },
            { key: 'items', type: 'items', label: 'Services', addLabel: 'Add Service', itemFields: [
                { key: 'icon', type: 'text', label: 'Icon', placeholder: 'chart' },
                { key: 'title', type: 'text', label: 'Title' },
                { key: 'description', type: 'textarea', label: 'Description', rows: 2 },
                { key: 'image', type: 'image', label: 'Image' },
                { key: 'link', type: 'text', label: 'Link (optional)', placeholder: '#' },
            ]},
        ],
        styleFields: [backgroundColorField],
    },

    features: {
        type: 'features',
        name: 'Features',
        fields: [
            { key: 'layout', type: 'select', label: 'Layout', defaultValue: 'grid', options: [
                { label: 'Icon Grid', value: 'grid' },
                { label: 'Checklist', value: 'checklist' },
                { label: 'Numbered Steps', value: 'steps' },
            ]},
            { key: 'title', type: 'text', label: 'Title', placeholder: 'Why Choose Us' },
            { key: 'subtitle', type: 'textarea', label: 'Subtitle', rows: 2 },
            textPositionField,
            { key: 'divider1', type: 'divider', label: 'Features' },
            { key: 'items', type: 'items', label: 'Features', addLabel: 'Add Feature', itemFields: [
                { key: 'icon', type: 'text', label: 'Icon', placeholder: 'check' },
                { key: 'title', type: 'text', label: 'Title' },
                { key: 'description', type: 'textarea', label: 'Description', rows: 2 },
            ]},
        ],
        styleFields: [backgroundColorField],
    },

    testimonials: {
        type: 'testimonials',
        name: 'Testimonials',
        fields: [
            { key: 'layout', type: 'select', label: 'Layout', defaultValue: 'grid', options: [
                { label: 'Cards Grid', value: 'grid' },
                { label: 'Carousel/Slider', value: 'carousel' },
                { label: 'Large Single Quote', value: 'single' },
                { label: 'With Photos', value: 'photos' },
            ]},
            { key: 'title', type: 'text', label: 'Title', placeholder: 'What Our Clients Say' },
            { key: 'subtitle', type: 'textarea', label: 'Subtitle', rows: 2 },
            textPositionField,
            { key: 'autoPlay', type: 'checkbox', label: 'Auto-play', condition: (c) => c.layout === 'carousel' },
            { key: 'divider1', type: 'divider', label: 'Testimonials' },
            { key: 'items', type: 'items', label: 'Testimonials', addLabel: 'Add Testimonial', itemFields: [
                { key: 'name', type: 'text', label: 'Name' },
                { key: 'role', type: 'text', label: 'Role/Company' },
                { key: 'text', type: 'textarea', label: 'Testimonial', rows: 3 },
                { key: 'rating', type: 'select', label: 'Rating', options: [
                    { label: '5 Stars', value: 5 },
                    { label: '4 Stars', value: 4 },
                    { label: '3 Stars', value: 3 },
                ]},
                { key: 'image', type: 'image', label: 'Photo' },
            ]},
        ],
        styleFields: [backgroundColorField],
    },

    pricing: {
        type: 'pricing',
        name: 'Pricing',
        fields: [
            { key: 'layout', type: 'select', label: 'Layout', defaultValue: 'cards', options: [
                { label: 'Cards', value: 'cards' },
                { label: 'Comparison Table', value: 'table' },
                { label: 'Toggle (Monthly/Yearly)', value: 'toggle' },
            ]},
            { key: 'title', type: 'text', label: 'Title', placeholder: 'Pricing Plans' },
            { key: 'subtitle', type: 'textarea', label: 'Subtitle', rows: 2 },
            textPositionField,
            { key: 'divider1', type: 'divider', label: 'Plans' },
            { key: 'plans', type: 'items', label: 'Plans', addLabel: 'Add Plan', itemFields: [
                { key: 'name', type: 'text', label: 'Plan Name' },
                { key: 'price', type: 'text', label: 'Price', placeholder: 'K99/mo' },
                { key: 'yearlyPrice', type: 'text', label: 'Yearly Price', placeholder: 'K999/yr' },
                { key: 'description', type: 'text', label: 'Description', placeholder: 'Perfect for small teams' },
                { key: 'popular', type: 'checkbox', label: 'Mark as Popular' },
                { key: 'features', type: 'textarea', label: 'Features (one per line)', rows: 4, placeholder: 'Feature 1\nFeature 2\nFeature 3' },
                { key: 'buttonText', type: 'text', label: 'Button Text', placeholder: 'Get Started' },
                { key: 'buttonLink', type: 'text', label: 'Button Link', placeholder: '#contact' },
            ]},
        ],
        styleFields: [backgroundColorField],
    },

    products: {
        type: 'products',
        name: 'Products',
        fields: [
            { key: 'info', type: 'info', label: '', variant: 'success', message: 'Products from dashboard appear here with cart & checkout.' },
            { key: 'title', type: 'text', label: 'Title', placeholder: 'Our Products' },
            { key: 'subtitle', type: 'textarea', label: 'Subtitle', rows: 2 },
            textPositionField,
            { key: 'divider1', type: 'divider', label: 'Display Options' },
            { key: 'columns', type: 'buttonGroup', label: 'Columns', options: [
                { label: '2', value: 2 },
                { label: '3', value: 3 },
                { label: '4', value: 4 },
            ]},
            { key: 'limit', type: 'select', label: 'Products to Show', options: [
                { label: '4 products', value: 4 },
                { label: '6 products', value: 6 },
                { label: '8 products', value: 8 },
                { label: '12 products', value: 12 },
            ]},
            { key: 'showCategory', type: 'checkbox', label: 'Show Category' },
            { key: 'showViewAll', type: 'checkbox', label: 'Show View All Button' },
            { key: 'featuredOnly', type: 'checkbox', label: 'Featured Products Only' },
            { key: 'manageLink', type: 'link', label: 'Manage Products', url: '/dashboard/products' },
        ],
        styleFields: [backgroundColorField],
    },

    'product-search': {
        type: 'product-search',
        name: 'Product Search',
        fields: [
            { key: 'info', type: 'info', label: '', variant: 'info', message: 'Search and filter bar for products. Works with Products section below.' },
            { key: 'placeholder', type: 'text', label: 'Search Placeholder', placeholder: 'Search products...' },
            { key: 'divider1', type: 'divider', label: 'Filter Options' },
            { key: 'showCategories', type: 'checkbox', label: 'Show Category Filter' },
            { key: 'showSort', type: 'checkbox', label: 'Show Sort Options' },
        ],
        styleFields: [backgroundColorField],
    },

    contact: {
        type: 'contact',
        name: 'Contact',
        fields: [
            { key: 'layout', type: 'select', label: 'Layout', defaultValue: 'side-by-side', options: [
                { label: 'Form + Info Side by Side', value: 'side-by-side' },
                { label: 'Stacked', value: 'stacked' },
                { label: 'With Embedded Map', value: 'with-map' },
            ]},
            { key: 'title', type: 'text', label: 'Title', placeholder: 'Contact Us' },
            { key: 'description', type: 'textarea', label: 'Description', rows: 2 },
            textPositionField,
            { key: 'divider1', type: 'divider', label: 'Contact Info' },
            { key: 'email', type: 'text', label: 'Email', placeholder: 'hello@example.com' },
            { key: 'phone', type: 'text', label: 'Phone', placeholder: '+260 97 123 4567' },
            { key: 'address', type: 'textarea', label: 'Address', rows: 2 },
            { key: 'mapEmbedUrl', type: 'text', label: 'Google Maps Embed URL', placeholder: 'https://www.google.com/maps/embed?...', condition: (c) => c.layout === 'with-map' },
            { key: 'divider2', type: 'divider', label: 'Form' },
            { key: 'showForm', type: 'checkbox', label: 'Show Contact Form' },
        ],
        styleFields: [backgroundColorField],
    },

    cta: {
        type: 'cta',
        name: 'Call to Action',
        fields: [
            { key: 'layout', type: 'select', label: 'Layout', defaultValue: 'banner', options: [
                { label: 'Banner Full-width', value: 'banner' },
                { label: 'Split with Image', value: 'split' },
                { label: 'Minimal Centered', value: 'minimal' },
            ]},
            { key: 'title', type: 'text', label: 'Title', placeholder: 'Ready to Get Started?' },
            { key: 'description', type: 'textarea', label: 'Description', rows: 2 },
            { key: 'image', type: 'image', label: 'Image', condition: (c) => c.layout === 'split' },
            textPositionField,
            { key: 'buttonText', type: 'text', label: 'Button Text', placeholder: 'Contact Us' },
            { key: 'buttonLink', type: 'text', label: 'Button Link', placeholder: '#contact' },
        ],
        styleFields: [
            { key: 'backgroundColor', type: 'color', label: 'Background Color', presets: ['#2563eb', '#1e40af', '#059669', '#7c3aed'] },
        ],
    },

    'member-cta': {
        type: 'member-cta',
        name: 'Member Signup',
        fields: [
            { key: 'title', type: 'text', label: 'Title', placeholder: 'Join Our Community' },
            { key: 'subtitle', type: 'text', label: 'Subtitle' },
            { key: 'description', type: 'textarea', label: 'Description', rows: 2 },
            { key: 'divider1', type: 'divider', label: 'Benefits' },
            { key: 'benefits', type: 'items', label: 'Benefits', addLabel: 'Add Benefit', maxItems: 6, itemFields: [
                { key: 'text', type: 'text', label: 'Benefit' },
            ]},
            { key: 'divider2', type: 'divider', label: 'Buttons' },
            { key: 'registerText', type: 'text', label: 'Register Button', placeholder: 'Sign Up Now' },
            { key: 'registerButtonStyle', type: 'buttonGroup', label: 'Button Style', options: [
                { label: 'Solid', value: 'solid' },
                { label: 'Outline', value: 'outline' },
            ]},
            { key: 'showLoginLink', type: 'checkbox', label: 'Show Login Link' },
            { key: 'loginText', type: 'text', label: 'Login Text', condition: (c) => c.showLoginLink },
        ],
        styleFields: [
            { key: 'backgroundColor', type: 'color', label: 'Background Color' },
            { key: 'textColor', type: 'color', label: 'Text Color' },
        ],
    },

    faq: {
        type: 'faq',
        name: 'FAQ',
        fields: [
            { key: 'layout', type: 'select', label: 'Layout', defaultValue: 'accordion', options: [
                { label: 'Accordion', value: 'accordion' },
                { label: 'Two Columns', value: 'two-column' },
                { label: 'Simple List', value: 'list' },
            ]},
            { key: 'title', type: 'text', label: 'Title', placeholder: 'Frequently Asked Questions' },
            textPositionField,
            { key: 'divider1', type: 'divider', label: 'Questions' },
            { key: 'items', type: 'items', label: 'FAQs', addLabel: 'Add Question', itemFields: [
                { key: 'question', type: 'text', label: 'Question' },
                { key: 'answer', type: 'textarea', label: 'Answer', rows: 3 },
            ]},
        ],
        styleFields: [backgroundColorField],
    },

    team: {
        type: 'team',
        name: 'Team',
        fields: [
            { key: 'layout', type: 'select', label: 'Layout', defaultValue: 'grid', options: [
                { label: 'Grid Cards', value: 'grid' },
                { label: 'Cards with Social Links', value: 'social' },
                { label: 'Compact List', value: 'compact' },
            ]},
            { key: 'title', type: 'text', label: 'Title', placeholder: 'Meet Our Team' },
            textPositionField,
            { key: 'divider1', type: 'divider', label: 'Team Members' },
            { key: 'items', type: 'items', label: 'Members', addLabel: 'Add Member', itemFields: [
                { key: 'name', type: 'text', label: 'Name' },
                { key: 'role', type: 'text', label: 'Role' },
                { key: 'bio', type: 'textarea', label: 'Bio', rows: 2 },
                { key: 'image', type: 'image', label: 'Photo' },
                { key: 'linkedin', type: 'text', label: 'LinkedIn URL' },
                { key: 'twitter', type: 'text', label: 'Twitter URL' },
                { key: 'email', type: 'text', label: 'Email' },
            ]},
        ],
        styleFields: [backgroundColorField],
    },

    stats: {
        type: 'stats',
        name: 'Stats',
        fields: [
            { key: 'layout', type: 'select', label: 'Layout', defaultValue: 'row', options: [
                { label: 'Row Inline', value: 'row' },
                { label: 'Grid Boxes', value: 'grid' },
                { label: 'With Icons', value: 'icons' },
            ]},
            { key: 'title', type: 'text', label: 'Title', placeholder: 'Our Impact' },
            textPositionField,
            { key: 'divider1', type: 'divider', label: 'Statistics' },
            { key: 'items', type: 'items', label: 'Stats', addLabel: 'Add Stat', maxItems: 6, itemFields: [
                { key: 'icon', type: 'text', label: 'Icon', placeholder: 'users' },
                { key: 'value', type: 'text', label: 'Value', placeholder: '500+' },
                { key: 'label', type: 'text', label: 'Label', placeholder: 'Happy Clients' },
            ]},
        ],
        styleFields: [
            { key: 'backgroundColor', type: 'color', label: 'Background Color', presets: ['#2563eb', '#1e40af', '#111827'] },
            { key: 'textColor', type: 'color', label: 'Text Color', presets: ['#ffffff', '#f9fafb', '#e5e7eb'] },
        ],
    },

    gallery: {
        type: 'gallery',
        name: 'Gallery',
        fields: [
            { key: 'layout', type: 'select', label: 'Layout', defaultValue: 'grid', options: [
                { label: 'Grid', value: 'grid' },
                { label: 'Masonry', value: 'masonry' },
                { label: 'Lightbox Carousel', value: 'lightbox' },
            ]},
            { key: 'title', type: 'text', label: 'Title', placeholder: 'Our Gallery' },
            textPositionField,
            { key: 'columns', type: 'buttonGroup', label: 'Columns', options: [
                { label: '2', value: 2 },
                { label: '3', value: 3 },
                { label: '4', value: 4 },
            ]},
            { key: 'divider1', type: 'divider', label: 'Images' },
            { key: 'images', type: 'galleryImages', label: 'Gallery Images' },
        ],
        styleFields: [backgroundColorField],
    },

    blog: {
        type: 'blog',
        name: 'Blog',
        fields: [
            { key: 'info', type: 'info', label: '', variant: 'info', message: 'Posts from dashboard appear here.' },
            { key: 'title', type: 'text', label: 'Title', placeholder: 'Latest News' },
            { key: 'description', type: 'textarea', label: 'Description', rows: 2 },
            textPositionField,
            { key: 'divider1', type: 'divider', label: 'Layout' },
            { key: 'layout', type: 'buttonGroup', label: 'Layout', options: [
                { label: 'Grid', value: 'grid' },
                { label: 'List', value: 'list' },
                { label: 'Featured', value: 'featured' },
            ]},
            { key: 'columns', type: 'select', label: 'Columns', options: [
                { label: '2 columns', value: 2 },
                { label: '3 columns', value: 3 },
                { label: '4 columns', value: 4 },
            ], condition: (c) => c.layout === 'grid' || !c.layout },
            { key: 'postsCount', type: 'select', label: 'Posts to Show', options: [
                { label: '3 posts', value: 3 },
                { label: '6 posts', value: 6 },
                { label: '9 posts', value: 9 },
            ]},
            { key: 'divider2', type: 'divider', label: 'Display' },
            { key: 'showImage', type: 'checkbox', label: 'Show Image' },
            { key: 'showDate', type: 'checkbox', label: 'Show Date' },
            { key: 'showExcerpt', type: 'checkbox', label: 'Show Excerpt' },
            { key: 'showViewAll', type: 'checkbox', label: 'Show View All Button' },
            { key: 'manageLink', type: 'link', label: 'Manage Posts', url: '/dashboard/posts' },
        ],
        styleFields: [backgroundColorField],
    },

    video: {
        type: 'video',
        name: 'Video',
        fields: [
            { key: 'title', type: 'text', label: 'Title', placeholder: 'Watch Our Story' },
            { key: 'description', type: 'textarea', label: 'Description', rows: 2 },
            { key: 'videoUrl', type: 'video', label: 'Video URL' },
        ],
        styleFields: [backgroundColorField],
    },

    map: {
        type: 'map',
        name: 'Map',
        fields: [
            { key: 'title', type: 'text', label: 'Title', placeholder: 'Find Us' },
            { key: 'embedUrl', type: 'text', label: 'Google Maps Embed URL', placeholder: 'https://www.google.com/maps/embed?...' },
            { key: 'address', type: 'textarea', label: 'Address', rows: 2 },
            { key: 'showAddress', type: 'checkbox', label: 'Show Address Below Map' },
        ],
        styleFields: [backgroundColorField],
    },

    text: {
        type: 'text',
        name: 'Text Block',
        fields: [
            { key: 'content', type: 'textarea', label: 'Content', rows: 8, placeholder: 'Enter your text content here...' },
        ],
        styleFields: [backgroundColorField],
    },

    divider: {
        type: 'divider',
        name: 'Divider',
        fields: [
            { key: 'style', type: 'buttonGroup', label: 'Style', options: [
                { label: 'Line', value: 'line' },
                { label: 'Dots', value: 'dots' },
                { label: 'Space', value: 'space' },
            ]},
            { key: 'height', type: 'range', label: 'Height', min: 20, max: 100, step: 10, unit: 'px' },
        ],
        styleFields: [
            { key: 'color', type: 'color', label: 'Line Color', condition: (c) => c.style === 'line' || c.style === 'dots' },
        ],
    },

    // NEW PHASE 1 SECTIONS

    statsMetrics: {
        type: 'stats',
        name: 'Stats/Metrics',
        fields: [
            { key: 'layout', type: 'select', label: 'Layout', defaultValue: 'horizontal', options: [
                { label: 'Horizontal Bar', value: 'horizontal' },
                { label: 'Grid Cards', value: 'grid' },
                { label: 'Centered', value: 'centered' },
            ]},
            { key: 'title', type: 'text', label: 'Title', placeholder: 'Our Impact' },
            { key: 'subtitle', type: 'textarea', label: 'Subtitle', rows: 2 },
            textPositionField,
            { key: 'animated', type: 'checkbox', label: 'Animate Numbers on Scroll' },
            { key: 'divider1', type: 'divider', label: 'Statistics' },
            { key: 'items', type: 'statItems', label: 'Stats', addLabel: 'Add Stat', itemFields: [
                { key: 'number', type: 'text', label: 'Number', placeholder: '500' },
                { key: 'suffix', type: 'text', label: 'Suffix', placeholder: '+' },
                { key: 'label', type: 'text', label: 'Label', placeholder: 'Happy Clients' },
                { key: 'icon', type: 'text', label: 'Icon', placeholder: 'users' },
            ]},
        ],
        styleFields: [
            backgroundColorField,
            { key: 'accentColor', type: 'color', label: 'Accent Color', presets: ['#2563eb', '#059669', '#7c3aed', '#dc2626'], tab: 'style' },
        ],
    },

    timeline: {
        type: 'timeline',
        name: 'Timeline',
        fields: [
            { key: 'layout', type: 'select', label: 'Layout', defaultValue: 'vertical', options: [
                { label: 'Vertical', value: 'vertical' },
                { label: 'Horizontal', value: 'horizontal' },
                { label: 'Zigzag', value: 'zigzag' },
            ]},
            { key: 'title', type: 'text', label: 'Title', placeholder: 'Our Journey' },
            { key: 'subtitle', type: 'textarea', label: 'Subtitle', rows: 2 },
            textPositionField,
            { key: 'divider1', type: 'divider', label: 'Timeline Items' },
            { key: 'items', type: 'items', label: 'Timeline Items', addLabel: 'Add Item', itemFields: [
                { key: 'year', type: 'text', label: 'Year/Date', placeholder: '2020' },
                { key: 'title', type: 'text', label: 'Title', placeholder: 'Company Founded' },
                { key: 'description', type: 'textarea', label: 'Description', rows: 2 },
                { key: 'icon', type: 'text', label: 'Icon', placeholder: 'star' },
                { key: 'image', type: 'image', label: 'Image (optional)' },
            ]},
        ],
        styleFields: [
            backgroundColorField,
            { key: 'lineColor', type: 'color', label: 'Timeline Line Color', presets: ['#2563eb', '#059669', '#7c3aed'], tab: 'style' },
        ],
    },

    'cta-banner': {
        type: 'cta-banner',
        name: 'CTA Banner',
        fields: [
            { key: 'layout', type: 'select', label: 'Layout', defaultValue: 'centered', options: [
                { label: 'Centered', value: 'centered' },
                { label: 'Split', value: 'split' },
                { label: 'With Image', value: 'with-image' },
            ]},
            { key: 'title', type: 'text', label: 'Title', placeholder: 'Ready to Get Started?' },
            { key: 'subtitle', type: 'textarea', label: 'Subtitle', rows: 2, placeholder: 'Join thousands of satisfied customers' },
            { key: 'image', type: 'image', label: 'Image', condition: (c) => c.layout === 'with-image' },
            { key: 'divider1', type: 'divider', label: 'Buttons' },
            { key: 'buttonText', type: 'text', label: 'Primary Button', placeholder: 'Get Started' },
            { key: 'buttonLink', type: 'text', label: 'Button Link', placeholder: '#contact' },
            { key: 'secondaryButtonText', type: 'text', label: 'Secondary Button', placeholder: 'Learn More' },
            { key: 'secondaryButtonLink', type: 'text', label: 'Secondary Link', placeholder: '#about' },
        ],
        styleFields: [
            { key: 'backgroundColor', type: 'color', label: 'Background Color', presets: ['#2563eb', '#059669', '#7c3aed', '#dc2626'], tab: 'style' },
            { key: 'textColor', type: 'color', label: 'Text Color', presets: ['#ffffff', '#111827'], tab: 'style' },
            { key: 'gradient', type: 'checkbox', label: 'Use Gradient Background', tab: 'style' },
        ],
    },

    'logo-cloud': {
        type: 'logo-cloud',
        name: 'Logo Cloud',
        fields: [
            { key: 'layout', type: 'select', label: 'Layout', defaultValue: 'grid', options: [
                { label: 'Grid', value: 'grid' },
                { label: 'Marquee (Scrolling)', value: 'marquee' },
                { label: 'Centered Row', value: 'row' },
            ]},
            { key: 'title', type: 'text', label: 'Title', placeholder: 'Trusted by Leading Companies' },
            { key: 'subtitle', type: 'textarea', label: 'Subtitle', rows: 2 },
            textPositionField,
            { key: 'grayscale', type: 'checkbox', label: 'Grayscale Logos (color on hover)' },
            { key: 'divider1', type: 'divider', label: 'Logos' },
            { key: 'items', type: 'items', label: 'Logos', addLabel: 'Add Logo', itemFields: [
                { key: 'image', type: 'image', label: 'Logo Image' },
                { key: 'name', type: 'text', label: 'Company Name' },
                { key: 'link', type: 'text', label: 'Link (optional)', placeholder: 'https://...' },
            ]},
        ],
        styleFields: [backgroundColorField],
    },

    'video-hero': {
        type: 'video-hero',
        name: 'Video Hero',
        fields: [
            { key: 'layout', type: 'select', label: 'Layout', defaultValue: 'fullscreen', options: [
                { label: 'Fullscreen', value: 'fullscreen' },
                { label: 'With Content Overlay', value: 'overlay' },
                { label: 'Side by Side', value: 'split' },
            ]},
            { key: 'videoUrl', type: 'video', label: 'Video URL', helpText: 'YouTube, Vimeo, or direct video URL' },
            { key: 'posterImage', type: 'image', label: 'Poster Image', helpText: 'Shown before video plays' },
            { key: 'autoPlay', type: 'checkbox', label: 'Auto-play Video' },
            { key: 'muted', type: 'checkbox', label: 'Muted by Default' },
            { key: 'loop', type: 'checkbox', label: 'Loop Video' },
            { key: 'divider1', type: 'divider', label: 'Content', condition: (c) => c.layout !== 'fullscreen' },
            { key: 'title', type: 'text', label: 'Title', placeholder: 'Watch Our Story', condition: (c) => c.layout !== 'fullscreen' },
            { key: 'subtitle', type: 'textarea', label: 'Subtitle', rows: 2, condition: (c) => c.layout !== 'fullscreen' },
            { key: 'buttonText', type: 'text', label: 'Button Text', placeholder: 'Learn More', condition: (c) => c.layout !== 'fullscreen' },
            { key: 'buttonLink', type: 'text', label: 'Button Link', placeholder: '#about', condition: (c) => c.layout !== 'fullscreen' },
        ],
        styleFields: [
            { key: 'overlay', type: 'checkbox', label: 'Dark Overlay', condition: (c) => c.layout === 'overlay', tab: 'style' },
            minHeightField,
        ],
    },

    'whatsapp': {
        type: 'whatsapp',
        name: 'WhatsApp',
        fields: [
            { key: 'phoneNumber', type: 'text', label: 'Phone Number', placeholder: '+260 XXX XXX XXX', helpText: 'Include country code (e.g., +260 for Zambia)' },
            { key: 'message', type: 'textarea', label: 'Pre-filled Message', rows: 3, placeholder: 'Hi! I\'m interested in...' },
            { key: 'divider1', type: 'divider', label: 'Button Style' },
            { key: 'buttonText', type: 'text', label: 'Button Text', placeholder: 'Chat on WhatsApp' },
            { key: 'buttonStyle', type: 'buttonGroup', label: 'Button Style', options: [
                { label: 'Solid', value: 'solid' },
                { label: 'Outline', value: 'outline' },
                { label: 'Minimal', value: 'minimal' },
            ]},
            { key: 'buttonSize', type: 'buttonGroup', label: 'Button Size', options: [
                { label: 'Small', value: 'sm' },
                { label: 'Medium', value: 'md' },
                { label: 'Large', value: 'lg' },
            ]},
            { key: 'alignment', type: 'buttonGroup', label: 'Alignment', options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'right' },
            ]},
            { key: 'showIcon', type: 'checkbox', label: 'Show WhatsApp Icon' },
        ],
        styleFields: [
            { key: 'backgroundColor', type: 'color', label: 'Button Color', presets: ['#25D366', '#128C7E', '#075E54', '#2563eb', '#7c3aed'] },
            { key: 'textColor', type: 'color', label: 'Text Color', presets: ['#ffffff', '#111827'] },
        ],
    },
};

/**
 * Get schema for a section type
 */
export function getSectionSchema(type: string): SectionSchema | undefined {
    return sectionSchemas[type];
}

/**
 * Get all fields for a section (content + style + advanced)
 */
export function getAllFields(schema: SectionSchema): { content: SchemaField[]; style: SchemaField[]; advanced: SchemaField[] } {
    return {
        content: schema.fields || [],
        style: schema.styleFields || [],
        advanced: schema.advancedFields || [],
    };
}
