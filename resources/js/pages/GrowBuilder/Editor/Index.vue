<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import draggable from 'vuedraggable';
import axios from 'axios';

// Components
import SectionRenderer from './components/SectionRenderer.vue';
import NavigationRenderer from './components/NavigationRenderer.vue';
import FooterRenderer from './components/FooterRenderer.vue';
import ToastContainer from './components/ToastContainer.vue';
import ContextMenu from './components/common/ContextMenu.vue';
import { NavigationInspector, FooterInspector, SectionInspector } from './components/inspectors';
import { CreatePageModal, EditPageModal, ApplyTemplateModal, MediaLibraryModal } from './components/modals';
import { WidgetPalette, PagesList, EditorToolbar } from './components/sidebar';

// Config
import { sectionBlocks } from './config/sectionBlocks';
import { findTemplate } from './config/pageTemplates';

// Types
import type { Page, Section, Site, NavigationSettings, FooterSettings, NavItem, SectionBlock, NewPageForm } from './types';

// Icons
import {
    PlusIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    Squares2X2Icon,
    DocumentIcon,
    ArrowsUpDownIcon,
    ArrowUpIcon,
    ArrowDownIcon,
    DocumentDuplicateIcon,
    TrashIcon,
    Bars3BottomLeftIcon,
    XMarkIcon,
    DevicePhoneMobileIcon,
    DeviceTabletIcon,
    ComputerDesktopIcon,
    QuestionMarkCircleIcon,
    PhotoIcon,
} from '@heroicons/vue/24/outline';

// Composables
import { useInlineEdit } from './composables/useInlineEdit';
import { useElementDrag } from './composables/useElementDrag';
import { useToast } from './composables/useToast';
import { useHistory } from './composables/useHistory';
import { useAutoSave } from './composables/useAutoSave';
import { useDragUpload } from './composables/useDragUpload';
import { useClipboard } from './composables/useClipboard';

const props = defineProps<{
    site: Site;
    pages: Page[];
    currentPage?: Page;
    sectionTypes: SectionBlock[];
}>();

// ============================================
// UI State
// ============================================
const leftSidebarOpen = ref(true);
const rightSidebarOpen = ref(true);
const previewMode = ref<'desktop' | 'tablet' | 'mobile'>('desktop');
const selectedSectionId = ref<string | null>(null);
const saving = ref(false);
const isDragging = ref(false);
const activeInspectorTab = ref<'content' | 'style' | 'advanced'>('content');
const hoveredSectionId = ref<string | null>(null);
const activeLeftTab = ref<'pages' | 'widgets'>('widgets');
const showNavSettings = ref(false);
const showFooterSettings = ref(false);

// Full-Width Preview Mode
const isFullPreview = ref(false);
const isIframePreview = ref(false); // True = iframe (interactive), False = component preview
const previewWidth = ref(1024); // Default width in pixels
const isResizingPreview = ref(false);
const showKeyboardShortcuts = ref(false);
const iframeKey = ref(0); // Force iframe refresh

// Zoom and Canvas
const canvasZoom = ref(100);
const lastSaved = ref<Date | null>(null);
const darkMode = ref(false);

// Responsive breakpoints
const breakpoints = [
    { name: 'Mobile', width: 375, icon: 'phone' },
    { name: 'Tablet', width: 768, icon: 'tablet' },
    { name: 'Laptop', width: 1024, icon: 'laptop' },
    { name: 'Desktop', width: 1440, icon: 'desktop' },
];

// Page State
const activePage = ref<Page | null>(props.currentPage || props.pages[0] || null);
const sections = ref<Section[]>([]);
const pageTitle = ref('');

// Site Navigation & Footer
const siteNavigation = ref<NavigationSettings>({
    logoText: props.site.name,
    logo: '',
    navItems: [] as NavItem[],
    showCta: true,
    ctaText: 'Contact Us',
    ctaLink: '#contact',
    sticky: true,
    style: 'default',
    // Auth buttons
    showAuthButtons: false,
    showLoginButton: true,
    showRegisterButton: true,
    loginText: 'Login',
    registerText: 'Sign Up',
    loginStyle: 'link',
    registerStyle: 'solid',
});

const siteFooter = ref<FooterSettings>({
    logo: '',
    copyrightText: `© ${new Date().getFullYear()} ${props.site.name}. All rights reserved.`,
    showSocialLinks: true,
    socialLinks: [],
    columns: [
        { id: 'col-1', title: 'Quick Links', links: [] },
        { id: 'col-2', title: 'Contact', links: [] },
    ],
    showNewsletter: false,
    newsletterTitle: 'Subscribe to our newsletter',
    backgroundColor: '#1f2937',
    textColor: '#ffffff',
    layout: 'columns',
});

// Modal State
const showCreatePageModal = ref(false);
const showEditPageModal = ref(false);
const editingPage = ref<Page | null>(null);
const creatingPage = ref(false);
const pageError = ref<string | null>(null);
const showApplyTemplateModal = ref(false);
const applyingTemplate = ref(false);

// Media Library State
const uploadingImage = ref(false);
const imageUploadError = ref<string | null>(null);
const mediaLibrary = ref<any[]>([]);
const showMediaLibrary = ref(false);
const mediaLibraryTarget = ref<{
    sectionId?: string;
    field: string;
    itemIndex?: number;
    target?: 'navigation' | 'footer' | 'section';
} | null>(null);

// Section Resize State
const resizingSection = ref<string | null>(null);
const resizeStartY = ref(0);
const resizeStartHeight = ref(0);

// ============================================
// Composables
// ============================================
const { editingValue, startInlineEdit, saveInlineEdit, isEditing, handleInlineKeydown } = useInlineEdit({ sections });
const { draggingElement, draggingSectionContent, startElementDrag, startSectionContentDrag, getElementTransform, getSectionContentTransform, hasElementOffset, hasSectionContentOffset, resetAllElementOffsets, resetSectionContentOffset } = useElementDrag({ sections });
const toast = useToast();
const history = useHistory({ maxHistory: 50 });
const { copySection, cutSection, pasteSection, hasClipboard, clipboardType } = useClipboard();

// Context Menu State
const contextMenu = ref({
    visible: false,
    x: 0,
    y: 0,
    sectionId: null as string | null,
    sectionType: undefined as string | undefined,
});

// Auto-save setup
const autoSave = useAutoSave({
    delay: 30000, // 30 seconds
    onSave: async () => {
        await savePage(true); // silent save
    },
    onSuccess: () => {
        // Toast is shown by savePage
    },
    onError: (error) => {
        toast.error('Auto-save failed: ' + error.message);
    },
});

// Drag-to-upload setup
const handleDragUpload = async (file: File) => {
    const formData = new FormData();
    formData.append('file', file);
    
    try {
        const response = await axios.post(`/growbuilder/media/${props.site.id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        
        if (response.data.success) {
            mediaLibrary.value.unshift(response.data.media);
            toast.success(`"${file.name}" uploaded successfully`);
            
            // If a section is selected and it supports images, offer to add it
            if (selectedSection.value) {
                const type = selectedSection.value.type;
                if (type === 'hero' || type === 'about' || type === 'page-header') {
                    const field = type === 'about' ? 'image' : 'backgroundImage';
                    selectedSection.value.content[field] = response.data.media.url;
                    toast.info('Image applied to selected section');
                } else if (type === 'gallery') {
                    if (!selectedSection.value.content.images) {
                        selectedSection.value.content.images = [];
                    }
                    selectedSection.value.content.images.push({
                        id: response.data.media.id,
                        url: response.data.media.url,
                        alt: response.data.media.originalName,
                    });
                    toast.info('Image added to gallery');
                }
            }
        }
    } catch (error: any) {
        toast.error(error.response?.data?.message || 'Failed to upload image');
        throw error;
    }
};

const dragUpload = useDragUpload({
    onUpload: handleDragUpload,
    acceptedTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    maxSize: 10 * 1024 * 1024, // 10MB
});

// ============================================
// Initialization
// ============================================
const initializeSections = () => {
    if (activePage.value?.content?.sections) {
        sections.value = activePage.value.content.sections.map((s, i) => ({
            ...s,
            id: s.id || `section-${Date.now()}-${i}`,
            style: s.style || {},
        }));
    } else {
        sections.value = [];
    }
    pageTitle.value = activePage.value?.title || 'New Page';
};

const initializeSiteNavigation = () => {
    if (props.site.settings?.navigation) {
        siteNavigation.value = { ...siteNavigation.value, ...props.site.settings.navigation };
    } else {
        siteNavigation.value.navItems = props.pages
            .filter(p => p.showInNav)
            .sort((a, b) => a.navOrder - b.navOrder)
            .map(p => ({
                id: `nav-${p.id}`,
                label: p.title,
                url: p.isHomepage ? '/' : `/${p.slug}`,
                pageId: p.id,
                isExternal: false,
                children: [],
            }));
    }
};

const initializeSiteFooter = () => {
    if (props.site.settings?.footer) {
        siteFooter.value = { ...siteFooter.value, ...props.site.settings.footer };
    }
};

initializeSections();
initializeSiteNavigation();
initializeSiteFooter();

// Initialize history after loading data
history.initHistory(sections.value, siteNavigation.value, siteFooter.value);

// ============================================
// History (Undo/Redo)
// ============================================
const pushToHistory = () => {
    history.pushState(sections.value, siteNavigation.value, siteFooter.value);
    autoSave.markDirty();
};

const handleUndo = () => {
    const state = history.undo();
    if (state) {
        sections.value = state.sections;
        siteNavigation.value = state.navigation;
        siteFooter.value = state.footer;
        toast.info('Undone');
    }
};

const handleRedo = () => {
    const state = history.redo();
    if (state) {
        sections.value = state.sections;
        siteNavigation.value = state.navigation;
        siteFooter.value = state.footer;
        toast.info('Redone');
    }
};

// ============================================
// Watchers
// ============================================
watch(() => props.currentPage, (newPage) => {
    if (newPage) {
        activePage.value = newPage;
        initializeSections();
        selectedSectionId.value = null;
    }
});

watch(() => props.pages, () => {
    if (!props.site.settings?.navigation) {
        initializeSiteNavigation();
    }
}, { deep: true });

// ============================================
// Computed
// ============================================
const selectedSection = computed(() => {
    if (!selectedSectionId.value) return null;
    return sections.value.find(s => s.id === selectedSectionId.value) || null;
});

const selectedSectionType = computed(() => {
    if (!selectedSection.value) return null;
    return sectionBlocks.find(b => b.type === selectedSection.value?.type) || null;
});

const canvasWidth = computed(() => {
    if (isFullPreview.value) {
        return `max-w-[${previewWidth.value}px]`;
    }
    return previewMode.value === 'mobile' ? 'max-w-sm' : previewMode.value === 'tablet' ? 'max-w-2xl' : 'max-w-4xl';
});

// Canvas zoom transform
const canvasTransform = computed(() => {
    if (canvasZoom.value === 100) return '';
    return `scale(${canvasZoom.value / 100})`;
});

const canvasTransformOrigin = computed(() => 'top center');

const isMobilePreview = computed(() => previewMode.value === 'mobile' || previewWidth.value < 640);

// Mobile preview responsive helpers
const textSize = computed(() => isMobilePreview.value ? { h1: 'text-2xl', h2: 'text-xl', h3: 'text-lg', p: 'text-sm' } : { h1: 'text-4xl', h2: 'text-3xl', h3: 'text-xl', p: 'text-base' });
const spacing = computed(() => isMobilePreview.value ? { section: 'py-10 px-4', gap: 'gap-4' } : { section: 'py-16 px-8', gap: 'gap-6' });
const gridCols2 = computed(() => isMobilePreview.value ? 'grid-cols-1' : 'grid-cols-1 md:grid-cols-2');
const gridCols3 = computed(() => isMobilePreview.value ? 'grid-cols-1' : 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3');
const gridCols4 = computed(() => isMobilePreview.value ? 'grid-cols-2' : 'grid-cols-2 md:grid-cols-4');

// ============================================
// Section Actions
// ============================================
const selectSection = (id: string) => {
    selectedSectionId.value = id;
    rightSidebarOpen.value = true;
    activeInspectorTab.value = 'content';
    showNavSettings.value = false;
    showFooterSettings.value = false;
};

const getDefaultContent = (type: string): Record<string, any> => {
    const defaults: Record<string, Record<string, any>> = {
        hero: { title: 'Welcome to Our Business', subtitle: 'We help you grow and succeed', buttonText: 'Get Started', buttonLink: '#contact', textPosition: 'center', backgroundImage: '' },
        'page-header': { title: 'Page Title', subtitle: 'A brief description of this page', backgroundImage: '', backgroundColor: '#1e40af', textColor: '#ffffff', textPosition: 'center' },
        about: { title: 'About Us', description: 'Tell your story here.', image: '', imagePosition: 'right' },
        services: { title: 'Our Services', items: [{ title: 'Service 1', description: 'Description' }, { title: 'Service 2', description: 'Description' }] },
        features: { title: 'Why Choose Us', items: [{ title: 'Feature 1', description: 'Description' }, { title: 'Feature 2', description: 'Description' }] },
        gallery: { title: 'Our Gallery', images: [] },
        testimonials: { title: 'What Our Clients Say', items: [{ name: 'John Doe', text: 'Great service!', role: 'Customer' }] },
        pricing: { title: 'Pricing Plans', plans: [{ name: 'Basic', price: 'K99', features: ['Feature 1', 'Feature 2'] }] },
        products: { title: 'Our Products', showAll: true, limit: 6 },
        contact: { title: 'Contact Us', description: 'Get in touch with us', showForm: true, email: '', phone: '', address: '' },
        cta: { title: 'Ready to Get Started?', description: 'Contact us today', buttonText: 'Contact Us', buttonLink: '#contact' },
        text: { content: '<p>Enter your text content here...</p>' },
        faq: { title: 'Frequently Asked Questions', items: [{ question: 'Question?', answer: 'Answer.' }] },
        team: { title: 'Meet Our Team', items: [{ name: 'John Smith', role: 'CEO', image: '', bio: '' }] },
        blog: { title: 'Latest News', showLatest: true, limit: 3, posts: [] },
        stats: { title: 'Our Impact', items: [{ value: '500+', label: 'Happy Clients' }] },
        map: { title: 'Find Us', address: '123 Business Street', embedUrl: '', showAddress: true },
        video: { title: 'Watch Our Story', videoUrl: '', videoType: 'youtube', autoplay: false, description: '' },
        divider: { style: 'line', height: 40, color: '#e5e7eb' },
    };
    return defaults[type] || {};
};

const addSection = (type: string) => {
    pushToHistory();
    const newSection: Section = {
        id: `section-${Date.now()}`,
        type: type as any,
        content: getDefaultContent(type),
        style: { backgroundColor: '#ffffff', textColor: '#111827' },
    };
    sections.value.push(newSection);
    selectedSectionId.value = newSection.id;
};

const duplicateSection = (id: string) => {
    pushToHistory();
    const index = sections.value.findIndex(s => s.id === id);
    if (index === -1) return;
    const original = sections.value[index];
    const duplicate: Section = { ...JSON.parse(JSON.stringify(original)), id: `section-${Date.now()}` };
    sections.value.splice(index + 1, 0, duplicate);
    selectedSectionId.value = duplicate.id;
};

const deleteSection = (id: string) => {
    pushToHistory();
    const index = sections.value.findIndex(s => s.id === id);
    if (index === -1) return;
    sections.value.splice(index, 1);
    if (selectedSectionId.value === id) selectedSectionId.value = null;
};

const moveSection = (id: string, direction: 'up' | 'down') => {
    pushToHistory();
    const index = sections.value.findIndex(s => s.id === id);
    if (index === -1) return;
    const newIndex = direction === 'up' ? index - 1 : index + 1;
    if (newIndex < 0 || newIndex >= sections.value.length) return;
    const [section] = sections.value.splice(index, 1);
    sections.value.splice(newIndex, 0, section);
};

// ============================================
// Copy/Paste Functions
// ============================================
const handleCopySection = (id: string) => {
    const section = sections.value.find(s => s.id === id);
    if (!section) return;
    copySection(section, activePage.value?.id?.toString());
    toast.success('Section copied to clipboard');
};

const handleCutSection = (id: string) => {
    const section = sections.value.find(s => s.id === id);
    if (!section) return;
    cutSection(section, activePage.value?.id?.toString());
    deleteSection(id);
    toast.success('Section cut to clipboard');
};

const handlePasteSection = (afterId?: string) => {
    const newSection = pasteSection();
    if (!newSection) {
        toast.error('Nothing to paste');
        return;
    }
    
    pushToHistory();
    
    if (afterId) {
        const index = sections.value.findIndex(s => s.id === afterId);
        if (index !== -1) {
            sections.value.splice(index + 1, 0, newSection);
        } else {
            sections.value.push(newSection);
        }
    } else {
        sections.value.push(newSection);
    }
    
    selectedSectionId.value = newSection.id;
    toast.success('Section pasted');
};

// ============================================
// Context Menu Functions
// ============================================
const showContextMenu = (e: MouseEvent, sectionId: string | null = null) => {
    e.preventDefault();
    
    const section = sectionId ? sections.value.find(s => s.id === sectionId) : null;
    
    contextMenu.value = {
        visible: true,
        x: e.clientX,
        y: e.clientY,
        sectionId,
        sectionType: section?.type,
    };
};

const closeContextMenu = () => {
    contextMenu.value.visible = false;
};

const handleContextMenuAction = (action: string, sectionId: string | null) => {
    switch (action) {
        case 'edit':
            if (sectionId) {
                selectedSectionId.value = sectionId;
                activeInspectorTab.value = 'content';
                rightSidebarOpen.value = true;
            }
            break;
        case 'style':
            if (sectionId) {
                selectedSectionId.value = sectionId;
                activeInspectorTab.value = 'style';
                rightSidebarOpen.value = true;
            }
            break;
        case 'copy':
            if (sectionId) handleCopySection(sectionId);
            break;
        case 'cut':
            if (sectionId) handleCutSection(sectionId);
            break;
        case 'paste':
            handlePasteSection(sectionId || undefined);
            break;
        case 'duplicate':
            if (sectionId) duplicateSection(sectionId);
            break;
        case 'moveUp':
            if (sectionId) moveSection(sectionId, 'up');
            break;
        case 'moveDown':
            if (sectionId) moveSection(sectionId, 'down');
            break;
        case 'delete':
            if (sectionId) deleteSection(sectionId);
            break;
        case 'addSection':
            // Could open add section modal
            break;
    }
};

const updateSectionContent = (key: string, value: any) => {
    if (!selectedSection.value) return;
    pushToHistory();
    selectedSection.value.content[key] = value;
};

const updateSectionStyle = (key: string, value: any) => {
    if (!selectedSection.value) return;
    // Only push to history on first change in a batch
    if (!selectedSection.value.style) {
        selectedSection.value.style = {};
    }
    // Check if value actually changed before pushing to history
    if (selectedSection.value.style[key] !== value) {
        pushToHistory();
        selectedSection.value.style[key] = value;
    }
};


// ============================================
// Section Resize
// ============================================
const startResize = (e: MouseEvent, sectionId: string) => {
    e.preventDefault();
    e.stopPropagation();
    resizingSection.value = sectionId;
    resizeStartY.value = e.clientY;
    const section = sections.value.find(s => s.id === sectionId);
    const sectionEl = document.querySelector(`[data-section-id="${sectionId}"]`) as HTMLElement;
    resizeStartHeight.value = section?.style?.minHeight || sectionEl?.offsetHeight || 200;
    document.addEventListener('mousemove', handleResize);
    document.addEventListener('mouseup', stopResize);
    document.body.style.cursor = 'ns-resize';
    document.body.style.userSelect = 'none';
};

const handleResize = (e: MouseEvent) => {
    if (!resizingSection.value) return;
    const deltaY = e.clientY - resizeStartY.value;
    const newHeight = Math.max(100, resizeStartHeight.value + deltaY);
    const section = sections.value.find(s => s.id === resizingSection.value);
    if (section) {
        if (!section.style) section.style = {};
        section.style.minHeight = newHeight;
    }
};

const stopResize = () => {
    resizingSection.value = null;
    document.removeEventListener('mousemove', handleResize);
    document.removeEventListener('mouseup', stopResize);
    document.body.style.cursor = '';
    document.body.style.userSelect = '';
};

// ============================================
// Section Click Handler
// ============================================
const handleSectionClick = (e: MouseEvent, sectionId: string) => {
    if (draggingElement.value || draggingSectionContent.value) return;
    selectSection(sectionId);
};

// ============================================
// Items Management
// ============================================
const addItem = () => {
    if (!selectedSection.value) return;
    const type = selectedSection.value.type;
    if (!selectedSection.value.content.items) selectedSection.value.content.items = [];
    if (type === 'services' || type === 'features') {
        selectedSection.value.content.items.push({ title: 'New Item', description: 'Description' });
    } else if (type === 'testimonials') {
        selectedSection.value.content.items.push({ name: 'Customer Name', text: 'Testimonial text', role: 'Role' });
    }
};

const removeItem = (index: number) => {
    if (!selectedSection.value?.content.items) return;
    selectedSection.value.content.items.splice(index, 1);
};

const addPlan = () => {
    if (!selectedSection.value) return;
    if (!selectedSection.value.content.plans) selectedSection.value.content.plans = [];
    selectedSection.value.content.plans.push({ name: 'New Plan', price: 'K0', features: ['Feature 1'] });
};

const removePlan = (index: number) => {
    if (!selectedSection.value?.content.plans) return;
    selectedSection.value.content.plans.splice(index, 1);
};

const addPlanFeature = (planIndex: number) => {
    if (!selectedSection.value?.content.plans?.[planIndex]) return;
    if (!selectedSection.value.content.plans[planIndex].features) selectedSection.value.content.plans[planIndex].features = [];
    selectedSection.value.content.plans[planIndex].features.push('New Feature');
};

const addFaqItem = () => {
    if (!selectedSection.value) return;
    if (!selectedSection.value.content.items) selectedSection.value.content.items = [];
    selectedSection.value.content.items.push({ question: 'New Question?', answer: 'Answer here...' });
};

const removeFaqItem = (index: number) => {
    if (!selectedSection.value?.content.items) return;
    selectedSection.value.content.items.splice(index, 1);
};

const addTeamMember = () => {
    if (!selectedSection.value) return;
    if (!selectedSection.value.content.items) selectedSection.value.content.items = [];
    selectedSection.value.content.items.push({ name: 'New Member', role: 'Role', image: '', bio: '' });
};

const removeTeamMember = (index: number) => {
    if (!selectedSection.value?.content.items) return;
    selectedSection.value.content.items.splice(index, 1);
};

const addBlogPost = () => {
    if (!selectedSection.value) return;
    if (!selectedSection.value.content.posts) selectedSection.value.content.posts = [];
    selectedSection.value.content.posts.push({ title: 'New Post', excerpt: 'Post excerpt...', date: new Date().toISOString().split('T')[0], image: '' });
};

const removeBlogPost = (index: number) => {
    if (!selectedSection.value?.content.posts) return;
    selectedSection.value.content.posts.splice(index, 1);
};

const addStatItem = () => {
    if (!selectedSection.value) return;
    if (!selectedSection.value.content.items) selectedSection.value.content.items = [];
    selectedSection.value.content.items.push({ value: '100+', label: 'New Stat' });
};

const removeStatItem = (index: number) => {
    if (!selectedSection.value?.content.items) return;
    selectedSection.value.content.items.splice(index, 1);
};

const removeGalleryImage = (sectionId: string, imageIndex: number) => {
    const section = sections.value.find(s => s.id === sectionId);
    if (section?.content.images) section.content.images.splice(imageIndex, 1);
};

// ============================================
// Page Management
// ============================================
const openCreatePageModal = () => {
    pageError.value = null;
    showCreatePageModal.value = true;
};

const createPage = async (form: NewPageForm) => {
    if (!form.title) {
        pageError.value = 'Please enter a page title';
        return;
    }
    const slug = form.slug || form.title.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
    const existingPage = props.pages.find(p => p.slug === slug);
    if (existingPage) {
        pageError.value = `A page with the URL "/${slug}" already exists.`;
        return;
    }
    creatingPage.value = true;
    pageError.value = null;
    const template = findTemplate(form.templateId);
    const templateSections = template?.sections.map((s, i) => ({
        id: `section-${Date.now()}-${i}`,
        type: s.type,
        content: { ...s.content },
        style: { ...s.style },
    })) || [];
    try {
        const response = await axios.post(`/growbuilder/editor/${props.site.id}/pages`, {
            title: form.title,
            slug: slug,
            sections: templateSections,
            show_in_nav: form.showInNav,
            is_homepage: false,
        });
        if (form.showInNav && response.data.page?.id) {
            siteNavigation.value.navItems.push({
                id: `nav-${response.data.page.id}`,
                label: form.title,
                url: `/${slug}`,
                pageId: response.data.page.id,
                isExternal: false,
                children: [],
            });
            await axios.post(`/growbuilder/editor/${props.site.id}/settings`, {
                navigation: siteNavigation.value,
                footer: siteFooter.value,
            });
        }
        showCreatePageModal.value = false;
        if (response.data.page?.id) {
            router.visit(`/growbuilder/editor/${props.site.id}?page=${response.data.page.id}`);
        } else {
            router.reload();
        }
    } catch (error: any) {
        pageError.value = error.response?.data?.error || 'Failed to create page.';
    } finally {
        creatingPage.value = false;
    }
};

const openEditPageModal = (page: Page) => {
    editingPage.value = { ...page };
    showEditPageModal.value = true;
};

const updatePage = async (page: Page) => {
    try {
        const originalPage = props.pages.find(p => p.id === page.id);
        const showInNavChanged = originalPage?.showInNav !== page.showInNav;
        await axios.put(`/growbuilder/editor/${props.site.id}/pages/${page.id}`, {
            title: page.title,
            slug: page.slug,
            show_in_nav: page.showInNav,
        });
        if (showInNavChanged) {
            const navIndex = siteNavigation.value.navItems.findIndex(item => item.pageId === page.id);
            if (page.showInNav && navIndex === -1) {
                siteNavigation.value.navItems.push({
                    id: `nav-${page.id}`,
                    label: page.title,
                    url: page.isHomepage ? '/' : `/${page.slug}`,
                    pageId: page.id,
                    isExternal: false,
                    children: [],
                });
            } else if (!page.showInNav && navIndex !== -1) {
                siteNavigation.value.navItems.splice(navIndex, 1);
            }
            await axios.post(`/growbuilder/editor/${props.site.id}/settings`, {
                navigation: siteNavigation.value,
                footer: siteFooter.value,
            });
        }
        showEditPageModal.value = false;
        editingPage.value = null;
        toast.success('Page updated successfully');
        router.reload({ only: ['pages'] });
    } catch (error) {
        console.error('Failed to update page:', error);
        toast.error('Failed to update page');
    }
};

const deletePage = async (pageId: number) => {
    const page = props.pages.find(p => p.id === pageId);
    if (!page || page.isHomepage) return;
    if (!confirm(`Delete "${page.title}"? This cannot be undone.`)) return;
    try {
        await axios.delete(`/growbuilder/editor/${props.site.id}/pages/${pageId}`);
        const navIndex = siteNavigation.value.navItems.findIndex(item => item.pageId === pageId);
        if (navIndex !== -1) {
            siteNavigation.value.navItems.splice(navIndex, 1);
            await axios.post(`/growbuilder/editor/${props.site.id}/settings`, {
                navigation: siteNavigation.value,
                footer: siteFooter.value,
            });
        }
        toast.success('Page deleted');
        router.reload({ only: ['pages'] });
        if (activePage.value?.id === pageId) {
            const homepage = props.pages.find(p => p.isHomepage);
            if (homepage) router.get(`/growbuilder/editor/${props.site.id}`, { page: homepage.id });
        }
    } catch (error) {
        console.error('Failed to delete page:', error);
        toast.error('Failed to delete page');
    }
};

const savePage = async (silent = false) => {
    if (!activePage.value) return;
    saving.value = true;
    try {
        await axios.post(`/growbuilder/editor/${props.site.id}/pages/${activePage.value.id}/save`, {
            title: pageTitle.value,
            content: { sections: sections.value },
        });
        await axios.post(`/growbuilder/editor/${props.site.id}/settings`, {
            navigation: siteNavigation.value,
            footer: siteFooter.value,
        });
        lastSaved.value = new Date();
        autoSave.reset(); // Reset auto-save timer
        if (!silent) {
            toast.success('Changes saved successfully');
        }
    } catch (error) {
        console.error('Save failed:', error);
        if (!silent) {
            toast.error('Failed to save changes');
        }
        throw error; // Re-throw for auto-save error handling
    } finally {
        saving.value = false;
    }
};

const openPreview = async () => {
    await savePage();
    window.open(`${props.site.url}?t=${Date.now()}`, '_blank');
};

const switchPage = (page: Page) => {
    if (page.id === activePage.value?.id) return;
    router.visit(`/growbuilder/editor/${props.site.id}?page=${page.id}`);
};


// ============================================
// Template Management
// ============================================
const openApplyTemplateModal = () => {
    showApplyTemplateModal.value = true;
};

const applyTemplate = async (templateId: string) => {
    if (!activePage.value) return;
    const template = findTemplate(templateId);
    if (!template) return;
    applyingTemplate.value = true;
    const newSections = template.sections.map((s, i) => ({
        id: `section-${Date.now()}-${i}`,
        type: s.type,
        content: { ...s.content },
        style: { ...s.style },
    }));
    sections.value = newSections as Section[];
    showApplyTemplateModal.value = false;
    applyingTemplate.value = false;
    await savePage();
};

// ============================================
// Media Library
// ============================================
const loadMediaLibrary = async () => {
    try {
        const response = await axios.get(`/growbuilder/media/${props.site.id}`);
        mediaLibrary.value = response.data.data || [];
    } catch (error) {
        console.error('Failed to load media library:', error);
    }
};

const openMediaLibrary = async (
    target: 'navigation' | 'footer' | 'section',
    fieldOrSectionId: string,
    field?: string,
    itemIndex?: number
) => {
    if (target === 'navigation' || target === 'footer') {
        mediaLibraryTarget.value = { target, field: fieldOrSectionId };
    } else {
        mediaLibraryTarget.value = { target: 'section', sectionId: fieldOrSectionId, field: field!, itemIndex };
    }
    showMediaLibrary.value = true;
    await loadMediaLibrary();
};

const uploadImage = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (!input.files?.length) return;
    const file = input.files[0];
    uploadingImage.value = true;
    imageUploadError.value = null;
    const formData = new FormData();
    formData.append('file', file);
    try {
        const response = await axios.post(`/growbuilder/media/${props.site.id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        if (response.data.success) {
            mediaLibrary.value.unshift(response.data.media);
            selectMediaImage(response.data.media);
        }
    } catch (error: any) {
        imageUploadError.value = error.response?.data?.message || 'Failed to upload image';
    } finally {
        uploadingImage.value = false;
        input.value = '';
    }
};

const selectMediaImage = (media: any) => {
    if (!mediaLibraryTarget.value) return;
    const { target, sectionId, field, itemIndex } = mediaLibraryTarget.value;
    if (target === 'navigation') {
        siteNavigation.value.logo = media.url;
    } else if (target === 'footer') {
        siteFooter.value.logo = media.url;
    } else if (target === 'section' && sectionId) {
        const section = sections.value.find(s => s.id === sectionId);
        if (section && field) {
            if (itemIndex !== undefined && section.content.items) {
                section.content.items[itemIndex][field] = media.url;
            } else if (field === 'images' && section.type === 'gallery') {
                if (!section.content.images) section.content.images = [];
                section.content.images.push({ id: media.id, url: media.url, alt: media.originalName });
            } else {
                section.content[field] = media.url;
            }
        }
    }
    showMediaLibrary.value = false;
    mediaLibraryTarget.value = null;
};

// Handle cropped image selection (data URL from canvas)
const handleCroppedImage = async (dataUrl: string, originalMedia: any) => {
    if (!mediaLibraryTarget.value) return;
    
    // For cropped images, we use the data URL directly
    // In production, you'd upload this to the server first
    const { target, sectionId, field, itemIndex } = mediaLibraryTarget.value;
    
    if (target === 'navigation') {
        siteNavigation.value.logo = dataUrl;
    } else if (target === 'footer') {
        siteFooter.value.logo = dataUrl;
    } else if (target === 'section' && sectionId) {
        const section = sections.value.find(s => s.id === sectionId);
        if (section && field) {
            if (itemIndex !== undefined && section.content.items) {
                section.content.items[itemIndex][field] = dataUrl;
            } else if (field === 'images' && section.type === 'gallery') {
                if (!section.content.images) section.content.images = [];
                section.content.images.push({ id: `cropped-${Date.now()}`, url: dataUrl, alt: 'Cropped image' });
            } else {
                section.content[field] = dataUrl;
            }
        }
    }
    
    toast.success('Cropped image applied');
    showMediaLibrary.value = false;
    mediaLibraryTarget.value = null;
};

// Delete media from library
const deleteMediaImage = async (media: any) => {
    try {
        await axios.delete(`/growbuilder/media/${props.site.id}/${media.id}`);
        mediaLibrary.value = mediaLibrary.value.filter(m => m.id !== media.id);
        toast.success('Image deleted');
    } catch (error) {
        console.error('Failed to delete image:', error);
        toast.error('Failed to delete image');
    }
};

// Handle stock photo selection
const handleStockPhotoSelect = (url: string, attribution: string) => {
    if (!mediaLibraryTarget.value) return;
    const { target, sectionId, field, itemIndex } = mediaLibraryTarget.value;
    
    if (target === 'navigation') {
        siteNavigation.value.logo = url;
    } else if (target === 'footer') {
        siteFooter.value.logo = url;
    } else if (target === 'section' && sectionId) {
        const section = sections.value.find(s => s.id === sectionId);
        if (section && field) {
            if (itemIndex !== undefined && section.content.items) {
                section.content.items[itemIndex][field] = url;
            } else if (field === 'images' && section.type === 'gallery') {
                if (!section.content.images) section.content.images = [];
                section.content.images.push({ id: `stock-${Date.now()}`, url, alt: attribution });
            } else {
                section.content[field] = url;
            }
        }
    }
    
    toast.success('Stock photo added');
    showMediaLibrary.value = false;
    mediaLibraryTarget.value = null;
};

// ============================================
// Drag Handlers
// ============================================
const onDragStart = () => { isDragging.value = true; };
const onDragEnd = () => { isDragging.value = false; };

const cloneSection = (type: string) => ({
    id: `section-${Date.now()}`,
    type,
    content: getDefaultContent(type),
    style: { backgroundColor: '#ffffff', textColor: '#111827' },
});

const onWidgetDrop = (evt: any) => {
    isDragging.value = false;
    if (evt.added) selectedSectionId.value = evt.added.element.id;
};

// ============================================
// Navigation Handlers
// ============================================
const handleNavClick = () => {
    showNavSettings.value = true;
    showFooterSettings.value = false;
    selectedSectionId.value = null;
};

const handleFooterClick = () => {
    showFooterSettings.value = true;
    showNavSettings.value = false;
    selectedSectionId.value = null;
};

const handleBack = () => {
    router.visit('/growbuilder');
};

// ============================================
// Keyboard Shortcuts
// ============================================
const handleKeyboardShortcuts = (e: KeyboardEvent) => {
    // Don't trigger shortcuts when typing in inputs
    if (e.target instanceof HTMLInputElement || e.target instanceof HTMLTextAreaElement) {
        return;
    }
    
    // Also skip if in contenteditable
    if ((e.target as HTMLElement)?.isContentEditable) {
        return;
    }
    
    // Escape to exit full preview or deselect
    if (e.key === 'Escape') {
        if (isFullPreview.value) {
            exitFullPreview();
        } else if (selectedSectionId.value) {
            selectedSectionId.value = null;
            showNavSettings.value = false;
            showFooterSettings.value = false;
        }
        return;
    }
    
    // Ctrl+Z to undo
    if ((e.ctrlKey || e.metaKey) && !e.shiftKey && e.key === 'z') {
        e.preventDefault();
        handleUndo();
        return;
    }
    // Ctrl+Shift+Z or Ctrl+Y to redo
    if ((e.ctrlKey || e.metaKey) && (e.shiftKey && e.key === 'z' || e.key === 'y')) {
        e.preventDefault();
        handleRedo();
        return;
    }
    // Ctrl+S or Cmd+S to save
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        savePage();
    }
    // Ctrl+P or Cmd+P to toggle full preview
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
        e.preventDefault();
        toggleFullPreview();
    }
    // Ctrl+Shift+P to open in new tab
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'P') {
        e.preventDefault();
        openPreview();
    }
    // Ctrl+\ to toggle left sidebar
    if ((e.ctrlKey || e.metaKey) && e.key === '\\') {
        e.preventDefault();
        leftSidebarOpen.value = !leftSidebarOpen.value;
    }
    // Ctrl+] to toggle right sidebar
    if ((e.ctrlKey || e.metaKey) && e.key === ']') {
        e.preventDefault();
        rightSidebarOpen.value = !rightSidebarOpen.value;
    }
    // Delete to delete selected section
    if (e.key === 'Delete' && selectedSectionId.value && !showNavSettings.value && !showFooterSettings.value) {
        e.preventDefault();
        deleteSection(selectedSectionId.value);
    }
    // Ctrl+D to duplicate selected section
    if ((e.ctrlKey || e.metaKey) && e.key === 'd' && selectedSectionId.value) {
        e.preventDefault();
        duplicateSection(selectedSectionId.value);
    }
    // Ctrl+C to copy selected section
    if ((e.ctrlKey || e.metaKey) && e.key === 'c' && selectedSectionId.value && !window.getSelection()?.toString()) {
        e.preventDefault();
        handleCopySection(selectedSectionId.value);
    }
    // Ctrl+X to cut selected section
    if ((e.ctrlKey || e.metaKey) && e.key === 'x' && selectedSectionId.value && !window.getSelection()?.toString()) {
        e.preventDefault();
        handleCutSection(selectedSectionId.value);
    }
    // Ctrl+V to paste section
    if ((e.ctrlKey || e.metaKey) && e.key === 'v' && hasClipboard.value && !isEditing(selectedSectionId.value || '', '')) {
        e.preventDefault();
        handlePasteSection(selectedSectionId.value || undefined);
    }
    // Ctrl+? to show keyboard shortcuts
    if ((e.ctrlKey || e.metaKey) && e.key === '/') {
        e.preventDefault();
        showKeyboardShortcuts.value = !showKeyboardShortcuts.value;
    }
};

// ============================================
// Full Preview Mode
// ============================================
const toggleFullPreview = async (useIframe = true) => {
    if (!isFullPreview.value) {
        await savePage();
        isFullPreview.value = true;
        isIframePreview.value = useIframe;
        iframeKey.value++; // Force iframe refresh
        leftSidebarOpen.value = false;
        rightSidebarOpen.value = false;
        selectedSectionId.value = null;
        showNavSettings.value = false;
        showFooterSettings.value = false;
    } else {
        exitFullPreview();
    }
};

const exitFullPreview = () => {
    isFullPreview.value = false;
    isIframePreview.value = false;
    leftSidebarOpen.value = true;
    rightSidebarOpen.value = true;
};

const togglePreviewMode = () => {
    isIframePreview.value = !isIframePreview.value;
    if (isIframePreview.value) {
        iframeKey.value++; // Refresh iframe when switching to it
    }
};

const setPreviewBreakpoint = (width: number) => {
    previewWidth.value = width;
};

// Computed preview URL
const previewUrl = computed(() => {
    return `${props.site.url}?t=${Date.now()}`;
});

// Preview width resize handlers
const startPreviewResize = (e: MouseEvent) => {
    isResizingPreview.value = true;
    document.addEventListener('mousemove', handlePreviewResize);
    document.addEventListener('mouseup', stopPreviewResize);
    document.body.style.cursor = 'ew-resize';
    document.body.style.userSelect = 'none';
};

const handlePreviewResize = (e: MouseEvent) => {
    if (!isResizingPreview.value) return;
    const container = document.querySelector('.preview-container') as HTMLElement;
    if (container) {
        const rect = container.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const newWidth = Math.abs(e.clientX - centerX) * 2;
        previewWidth.value = Math.max(320, Math.min(1920, newWidth));
    }
};

const stopPreviewResize = () => {
    isResizingPreview.value = false;
    document.removeEventListener('mousemove', handlePreviewResize);
    document.removeEventListener('mouseup', stopPreviewResize);
    document.body.style.cursor = '';
    document.body.style.userSelect = '';
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyboardShortcuts);
    // Attach drag-to-upload to the canvas area
    dragUpload.attachGlobal();
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyboardShortcuts);
    dragUpload.detachGlobal();
});
</script>

<template>
    <Head :title="`Edit - ${site.name}`" />

    <!-- Full Preview Mode Overlay -->
    <div v-if="isFullPreview" class="fixed inset-0 z-50 bg-gray-900 flex flex-col">
        <!-- Full Preview Toolbar -->
        <div class="bg-gray-800 border-b border-gray-700 px-4 py-2 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button
                    @click="exitFullPreview"
                    class="flex items-center gap-2 px-3 py-1.5 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    <XMarkIcon class="w-4 h-4" aria-hidden="true" />
                    Exit Preview
                </button>
                <span class="text-gray-400 text-sm">Press <kbd class="px-1.5 py-0.5 bg-gray-700 rounded text-xs">Esc</kbd> to exit</span>
            </div>
            
            <!-- Preview Mode Toggle -->
            <div class="flex items-center gap-4">
                <!-- Interactive/Static Toggle -->
                <div class="flex items-center bg-gray-700 rounded-lg p-1">
                    <button
                        @click="isIframePreview = true; iframeKey++"
                        :class="[
                            'px-3 py-1.5 text-sm rounded transition-colors',
                            isIframePreview ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'
                        ]"
                        title="Interactive preview with clickable links"
                    >
                        Interactive
                    </button>
                    <button
                        @click="isIframePreview = false"
                        :class="[
                            'px-3 py-1.5 text-sm rounded transition-colors',
                            !isIframePreview ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'
                        ]"
                        title="Static preview (faster)"
                    >
                        Static
                    </button>
                </div>
                
                <!-- Responsive Breakpoints -->
                <div class="flex items-center gap-2">
                    <span class="text-gray-400 text-sm">{{ previewWidth }}px</span>
                    <div class="flex items-center bg-gray-700 rounded-lg p-1">
                        <button
                            v-for="bp in breakpoints"
                            :key="bp.width"
                            @click="setPreviewBreakpoint(bp.width)"
                            :class="[
                                'p-2 rounded transition-colors',
                                previewWidth === bp.width ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-600'
                            ]"
                            :title="`${bp.name} (${bp.width}px)`"
                            :aria-label="`Set preview to ${bp.name}`"
                        >
                            <DevicePhoneMobileIcon v-if="bp.icon === 'phone'" class="w-5 h-5" aria-hidden="true" />
                            <DeviceTabletIcon v-else-if="bp.icon === 'tablet'" class="w-5 h-5" aria-hidden="true" />
                            <ComputerDesktopIcon v-else class="w-5 h-5" aria-hidden="true" />
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex items-center gap-2">
                <button
                    @click="openPreview"
                    class="px-3 py-1.5 text-gray-300 hover:text-white text-sm transition-colors"
                >
                    Open in New Tab
                </button>
            </div>
        </div>
        
        <!-- Preview Container -->
        <div class="flex-1 overflow-auto bg-gray-900 p-6 preview-container relative">
            <!-- Interactive Preview (iframe) -->
            <div 
                v-if="isIframePreview"
                class="mx-auto bg-white rounded-lg shadow-2xl overflow-hidden transition-all duration-300"
                :style="{ width: `${previewWidth}px`, maxWidth: '100%', height: 'calc(100vh - 120px)' }"
            >
                <iframe
                    :key="iframeKey"
                    :src="site.url"
                    class="w-full h-full border-0"
                    title="Site Preview"
                ></iframe>
            </div>
            
            <!-- Static Preview (component-based) -->
            <div 
                v-else
                class="mx-auto bg-white rounded-lg shadow-2xl overflow-hidden transition-all duration-300"
                :style="{ width: `${previewWidth}px`, maxWidth: '100%' }"
            >
                <!-- Navigation Preview -->
                <NavigationRenderer
                    :navigation="siteNavigation"
                    :siteName="site.name"
                    :isMobile="previewWidth < 768"
                    :isEditing="false"
                />
                
                <!-- Sections Preview -->
                <div v-for="section in sections" :key="section.id">
                    <SectionRenderer
                        :section="section"
                        :isMobile="previewWidth < 640"
                        :textSize="previewWidth < 640 ? { h1: 'text-2xl', h2: 'text-xl', h3: 'text-lg', p: 'text-sm' } : { h1: 'text-4xl', h2: 'text-3xl', h3: 'text-xl', p: 'text-base' }"
                        :spacing="previewWidth < 640 ? { section: 'py-10 px-4', gap: 'gap-4' } : { section: 'py-16 px-8', gap: 'gap-6' }"
                        :gridCols2="previewWidth < 640 ? 'grid-cols-1' : 'grid-cols-1 md:grid-cols-2'"
                        :gridCols3="previewWidth < 640 ? 'grid-cols-1' : 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3'"
                        :gridCols4="previewWidth < 640 ? 'grid-cols-2' : 'grid-cols-2 md:grid-cols-4'"
                        :getSectionContentTransform="() => ''"
                        :getElementTransform="() => ''"
                        :hasElementOffset="() => false"
                        :isEditing="() => false"
                        :editingValue="''"
                        :startInlineEdit="() => {}"
                        :saveInlineEdit="() => {}"
                        :handleInlineKeydown="() => {}"
                        :startElementDrag="() => {}"
                        :resetAllElementOffsets="() => {}"
                        :selectedSectionId="null"
                        :draggingElement="null"
                    />
                </div>
                
                <!-- Footer Preview -->
                <FooterRenderer
                    :footer="siteFooter"
                    :siteName="site.name"
                    :logoText="siteNavigation.logoText"
                    :isEditing="false"
                />
            </div>
            
            <!-- Resize Handles (only for static mode) -->
            <template v-if="!isIframePreview">
                <div 
                    class="absolute top-1/2 -translate-y-1/2 w-2 h-24 bg-gray-600 hover:bg-blue-500 rounded cursor-ew-resize transition-colors"
                    :style="{ left: `calc(50% - ${previewWidth / 2}px - 16px)` }"
                    @mousedown="startPreviewResize"
                ></div>
                <div 
                    class="absolute top-1/2 -translate-y-1/2 w-2 h-24 bg-gray-600 hover:bg-blue-500 rounded cursor-ew-resize transition-colors"
                    :style="{ left: `calc(50% + ${previewWidth / 2}px + 8px)` }"
                    @mousedown="startPreviewResize"
                ></div>
            </template>
        </div>
    </div>

    <!-- Keyboard Shortcuts Modal -->
    <Teleport to="body">
        <div v-if="showKeyboardShortcuts" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="showKeyboardShortcuts = false">
            <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-md w-full mx-4 animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-semibold text-gray-900">Keyboard Shortcuts</h3>
                    <button @click="showKeyboardShortcuts = false" class="p-1.5 hover:bg-gray-100 rounded-lg transition-colors" aria-label="Close">
                        <XMarkIcon class="w-5 h-5 text-gray-500" aria-hidden="true" />
                    </button>
                </div>
                <div class="space-y-1">
                    <div class="flex justify-between items-center py-2.5 px-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <span class="text-gray-700 text-sm">Save</span>
                        <kbd class="px-2.5 py-1 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-700 shadow-sm">Ctrl+S</kbd>
                    </div>
                    <div class="flex justify-between items-center py-2.5 px-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <span class="text-gray-700 text-sm">Undo</span>
                        <kbd class="px-2.5 py-1 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-700 shadow-sm">Ctrl+Z</kbd>
                    </div>
                    <div class="flex justify-between items-center py-2.5 px-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <span class="text-gray-700 text-sm">Redo</span>
                        <kbd class="px-2.5 py-1 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-700 shadow-sm">Ctrl+Shift+Z</kbd>
                    </div>
                    <div class="flex justify-between items-center py-2.5 px-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <span class="text-gray-700 text-sm">Preview Mode</span>
                        <kbd class="px-2.5 py-1 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-700 shadow-sm">Ctrl+P</kbd>
                    </div>
                    <div class="flex justify-between items-center py-2.5 px-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <span class="text-gray-700 text-sm">Open in New Tab</span>
                        <kbd class="px-2.5 py-1 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-700 shadow-sm">Ctrl+Shift+P</kbd>
                    </div>
                    <div class="flex justify-between items-center py-2.5 px-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <span class="text-gray-700 text-sm">Toggle Left Sidebar</span>
                        <kbd class="px-2.5 py-1 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-700 shadow-sm">Ctrl+\</kbd>
                    </div>
                    <div class="flex justify-between items-center py-2.5 px-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <span class="text-gray-700 text-sm">Toggle Right Sidebar</span>
                        <kbd class="px-2.5 py-1 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-700 shadow-sm">Ctrl+]</kbd>
                    </div>
                    <div class="flex justify-between items-center py-2.5 px-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <span class="text-gray-700 text-sm">Duplicate Section</span>
                        <kbd class="px-2.5 py-1 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-700 shadow-sm">Ctrl+D</kbd>
                    </div>
                    <div class="flex justify-between items-center py-2.5 px-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <span class="text-gray-700 text-sm">Delete Section</span>
                        <kbd class="px-2.5 py-1 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-700 shadow-sm">Delete</kbd>
                    </div>
                    <div class="flex justify-between items-center py-2.5 px-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <span class="text-gray-700 text-sm">Deselect / Exit Preview</span>
                        <kbd class="px-2.5 py-1 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-700 shadow-sm">Esc</kbd>
                    </div>
                    <div class="flex justify-between items-center py-2.5 px-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <span class="text-gray-700 text-sm">Show Shortcuts</span>
                        <kbd class="px-2.5 py-1 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-700 shadow-sm">Ctrl+/</kbd>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>

    <div :class="['h-screen flex flex-col overflow-hidden transition-colors duration-200', darkMode ? 'bg-gray-900' : 'bg-gray-100']">
        <!-- Top Toolbar -->
        <EditorToolbar
            :siteName="site.name"
            :siteLogo="site.logo || siteNavigation.logo"
            :pageTitle="activePage?.title || ''"
            :previewMode="previewMode"
            :saving="saving"
            :lastSaved="lastSaved"
            :canUndo="history.canUndo.value"
            :canRedo="history.canRedo.value"
            :zoom="canvasZoom"
            :darkMode="darkMode"
            @back="handleBack"
            @update:previewMode="previewMode = $event"
            @update:zoom="canvasZoom = $event"
            @update:darkMode="darkMode = $event"
            @preview="toggleFullPreview"
            @save="savePage"
            @undo="handleUndo"
            @redo="handleRedo"
            @showShortcuts="showKeyboardShortcuts = true"
        />

        <!-- Main Content Area -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Left Sidebar -->
            <aside :class="[
                'flex flex-col transition-all duration-300 flex-shrink-0 border-r',
                leftSidebarOpen ? 'w-72' : 'w-0',
                darkMode ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-200'
            ]">
                <div v-if="leftSidebarOpen" class="flex flex-col h-full overflow-hidden">
                    <!-- Sidebar Tabs -->
                    <div :class="['flex border-b', darkMode ? 'border-gray-700' : 'border-gray-200']">
                        <button
                            @click="activeLeftTab = 'widgets'"
                            :class="[
                                'flex-1 flex items-center justify-center gap-2 py-3 text-sm font-medium transition-colors border-b-2',
                                activeLeftTab === 'widgets' 
                                    ? (darkMode ? 'text-blue-400 border-blue-400 bg-blue-900/20' : 'text-blue-600 border-blue-600 bg-blue-50/50')
                                    : (darkMode ? 'text-gray-400 border-transparent hover:text-gray-200 hover:bg-gray-700' : 'text-gray-500 border-transparent hover:text-gray-700 hover:bg-gray-50')
                            ]"
                        >
                            <Squares2X2Icon class="w-4 h-4" aria-hidden="true" />
                            Widgets
                        </button>
                        <button
                            @click="activeLeftTab = 'pages'"
                            :class="[
                                'flex-1 flex items-center justify-center gap-2 py-3 text-sm font-medium transition-colors border-b-2',
                                activeLeftTab === 'pages' 
                                    ? (darkMode ? 'text-blue-400 border-blue-400 bg-blue-900/20' : 'text-blue-600 border-blue-600 bg-blue-50/50')
                                    : (darkMode ? 'text-gray-400 border-transparent hover:text-gray-200 hover:bg-gray-700' : 'text-gray-500 border-transparent hover:text-gray-700 hover:bg-gray-50')
                            ]"
                        >
                            <DocumentIcon class="w-4 h-4" aria-hidden="true" />
                            Pages
                        </button>
                    </div>

                    <!-- Widget Palette Tab -->
                    <WidgetPalette
                        v-if="activeLeftTab === 'widgets'"
                        :siteName="site.name"
                        :pages="pages"
                        :darkMode="darkMode"
                        @dragStart="onDragStart"
                        @dragEnd="onDragEnd"
                    />

                    <!-- Pages Tab -->
                    <PagesList
                        v-if="activeLeftTab === 'pages'"
                        :pages="pages"
                        :activePage="activePage"
                        :sections="sections"
                        :selectedSectionId="selectedSectionId"
                        :darkMode="darkMode"
                        @switchPage="switchPage"
                        @createPage="openCreatePageModal"
                        @editPage="openEditPageModal"
                        @deletePage="deletePage"
                        @applyTemplate="openApplyTemplateModal"
                        @selectSection="selectSection"
                        @deleteSection="deleteSection"
                        @dragStart="onDragStart"
                        @dragEnd="onDragEnd"
                        @update:sections="sections = $event"
                    />
                </div>
            </aside>

            <!-- Toggle Left Sidebar -->
            <button
                @click="leftSidebarOpen = !leftSidebarOpen"
                class="absolute left-0 top-1/2 -translate-y-1/2 z-10 p-1.5 bg-white border border-gray-200 rounded-r-lg shadow-sm hover:bg-gray-50 transition-colors"
                :class="leftSidebarOpen ? 'ml-72' : 'ml-0'"
                aria-label="Toggle left sidebar"
            >
                <ChevronLeftIcon :class="['w-4 h-4 text-gray-500 transition-transform', leftSidebarOpen ? '' : 'rotate-180']" aria-hidden="true" />
            </button>


            <!-- Canvas Area -->
            <main :class="['flex-1 overflow-auto p-6 relative', darkMode ? 'canvas-background-dark custom-scrollbar-dark' : 'canvas-background custom-scrollbar']">
                <!-- Drag-to-Upload Overlay -->
                <Transition
                    enter-active-class="transition-opacity duration-200"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition-opacity duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div 
                        v-if="dragUpload.isDraggingFile.value && !isFullPreview"
                        class="absolute inset-0 z-50 flex items-center justify-center bg-blue-600/90 backdrop-blur-sm"
                    >
                        <div class="text-center">
                            <div class="w-20 h-20 mx-auto mb-4 bg-white/20 rounded-2xl flex items-center justify-center">
                                <PhotoIcon class="w-10 h-10 text-white" aria-hidden="true" />
                            </div>
                            <h3 class="text-xl font-semibold text-white mb-2">Drop image to upload</h3>
                            <p class="text-blue-100 text-sm">
                                {{ selectedSection ? 'Image will be added to selected section' : 'Image will be added to media library' }}
                            </p>
                            <p class="text-blue-200 text-xs mt-2">JPG, PNG, GIF, WebP • Max 10MB</p>
                        </div>
                    </div>
                </Transition>
                
                <!-- Upload Progress Indicator -->
                <div 
                    v-if="dragUpload.isUploading.value"
                    class="absolute top-4 right-4 z-50 flex items-center gap-3 px-4 py-3 bg-white rounded-lg shadow-lg border border-gray-200"
                >
                    <div class="w-5 h-5 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                    <span class="text-sm text-gray-700">Uploading...</span>
                </div>
                
                <div 
                    :class="['mx-auto transition-all duration-300', canvasWidth]"
                    :style="{ transform: canvasTransform, transformOrigin: canvasTransformOrigin }"
                >
                    <div :class="['bg-white rounded-xl shadow-lg overflow-hidden transition-all ring-1', previewMode === 'mobile' ? 'max-w-sm mx-auto' : '', isDragging ? 'ring-2 ring-blue-400 ring-dashed' : 'ring-gray-200']">
                        
                        <!-- Site-Wide Navigation -->
                        <NavigationRenderer
                            :navigation="siteNavigation"
                            :siteName="site.name"
                            :isMobile="isMobilePreview"
                            :isEditing="showNavSettings"
                            @click="handleNavClick"
                        />

                        <!-- Drop Zone when empty -->
                        <div v-if="isDragging && sections.length === 0" class="min-h-[400px] flex flex-col items-center justify-center p-12 bg-blue-50/50 border-2 border-dashed border-blue-300 rounded-xl m-4">
                            <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mb-4">
                                <PlusIcon class="w-8 h-8 text-blue-500" aria-hidden="true" />
                            </div>
                            <p class="text-blue-600 font-medium">Drop widget here</p>
                        </div>

                        <!-- Sections -->
                        <draggable
                            v-model="sections"
                            :group="{ name: 'sections', pull: true, put: true }"
                            item-key="id"
                            ghost-class="opacity-30"
                            drag-class="shadow-2xl"
                            @start="onDragStart"
                            @end="onDragEnd"
                            @change="onWidgetDrop"
                            :class="['min-h-[300px]', isDragging && sections.length > 0 ? 'bg-blue-50/30' : '']"
                        >
                            <template #item="{ element }">
                                <div
                                    :data-section-id="element.id"
                                    :class="['relative group transition-all', selectedSectionId === element.id ? 'ring-2 ring-blue-500 ring-inset' : '', hoveredSectionId === element.id && selectedSectionId !== element.id ? 'ring-1 ring-blue-300 ring-inset' : '', resizingSection === element.id ? 'ring-2 ring-blue-500' : '']"
                                    :style="{ minHeight: element.style?.minHeight ? `${element.style.minHeight}px` : undefined }"
                                    @click="handleSectionClick($event, element.id)"
                                    @contextmenu="showContextMenu($event, element.id)"
                                    @mouseenter="hoveredSectionId = element.id"
                                    @mouseleave="hoveredSectionId = null"
                                >
                                    <!-- Section Actions Toolbar -->
                                    <div v-if="hoveredSectionId === element.id || selectedSectionId === element.id" class="absolute top-2 right-2 z-20 flex items-center gap-1 bg-white rounded-lg shadow-lg border border-gray-200 p-1">
                                        <button 
                                            @click.stop
                                            @mousedown.stop.prevent="startSectionContentDrag($event, element.id)"
                                            class="p-1.5 hover:bg-blue-100 rounded cursor-ns-resize" 
                                            :class="hasSectionContentOffset(element) ? 'bg-blue-50 text-blue-600' : ''"
                                            aria-label="Drag content vertically"
                                            title="Drag to reposition content vertically"
                                        >
                                            <ArrowsUpDownIcon class="w-4 h-4" :class="hasSectionContentOffset(element) ? 'text-blue-600' : 'text-gray-600'" aria-hidden="true" />
                                        </button>
                                        <div class="w-px h-4 bg-gray-200"></div>
                                        <button @click.stop="moveSection(element.id, 'up')" class="p-1.5 hover:bg-gray-100 rounded" aria-label="Move up">
                                            <ArrowUpIcon class="w-4 h-4 text-gray-600" aria-hidden="true" />
                                        </button>
                                        <button @click.stop="moveSection(element.id, 'down')" class="p-1.5 hover:bg-gray-100 rounded" aria-label="Move down">
                                            <ArrowDownIcon class="w-4 h-4 text-gray-600" aria-hidden="true" />
                                        </button>
                                        <button @click.stop="duplicateSection(element.id)" class="p-1.5 hover:bg-gray-100 rounded" aria-label="Duplicate">
                                            <DocumentDuplicateIcon class="w-4 h-4 text-gray-600" aria-hidden="true" />
                                        </button>
                                        <button @click.stop="deleteSection(element.id)" class="p-1.5 hover:bg-red-100 rounded" aria-label="Delete">
                                            <TrashIcon class="w-4 h-4 text-red-500" aria-hidden="true" />
                                        </button>
                                    </div>
                                    
                                    <!-- Section Type Label -->
                                    <div 
                                        v-if="hoveredSectionId === element.id || selectedSectionId === element.id" 
                                        class="absolute top-2 left-2 z-20 px-2 py-1 bg-gray-900/80 text-white text-xs font-medium rounded-md capitalize backdrop-blur-sm"
                                    >
                                        {{ element.type.replace('-', ' ') }}
                                    </div>

                                    <!-- Section Preview Content -->
                                    <SectionRenderer
                                        :section="element"
                                        :isMobile="isMobilePreview"
                                        :textSize="textSize"
                                        :spacing="spacing"
                                        :gridCols2="gridCols2"
                                        :gridCols3="gridCols3"
                                        :gridCols4="gridCols4"
                                        :getSectionContentTransform="getSectionContentTransform"
                                        :getElementTransform="getElementTransform"
                                        :hasElementOffset="hasElementOffset"
                                        :isEditing="isEditing"
                                        :editingValue="editingValue"
                                        :startInlineEdit="startInlineEdit"
                                        :saveInlineEdit="saveInlineEdit"
                                        :handleInlineKeydown="handleInlineKeydown"
                                        :startElementDrag="startElementDrag"
                                        :resetAllElementOffsets="resetAllElementOffsets"
                                        :selectedSectionId="selectedSectionId"
                                        :draggingElement="draggingElement"
                                        @update:editingValue="editingValue = $event"
                                    />

                                    <!-- Resize Handle -->
                                    <div
                                        v-if="hoveredSectionId === element.id || selectedSectionId === element.id || resizingSection === element.id"
                                        class="absolute bottom-0 left-0 right-0 h-3 flex items-center justify-center cursor-ns-resize group/resize z-20 hover:bg-blue-100/50 transition-colors"
                                        @mousedown="startResize($event, element.id)"
                                    >
                                        <div class="w-12 h-1 bg-gray-300 rounded-full group-hover/resize:bg-blue-500 transition-colors"></div>
                                    </div>
                                </div>
                            </template>
                        </draggable>

                        <!-- Empty State -->
                        <div v-if="sections.length === 0 && !isDragging" class="min-h-[400px] flex flex-col items-center justify-center p-12">
                            <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mb-6">
                                <Squares2X2Icon class="w-10 h-10 text-gray-400" aria-hidden="true" />
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Start building your page</h3>
                            <p class="text-gray-500 text-center mb-6">Drag widgets from the left panel or click below to add sections</p>
                            <button @click="addSection('hero')" class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                <PlusIcon class="w-5 h-5" aria-hidden="true" />
                                Add Section
                            </button>
                        </div>

                        <!-- Site-Wide Footer -->
                        <FooterRenderer
                            :footer="siteFooter"
                            :siteName="site.name"
                            :logoText="siteNavigation.logoText"
                            :isEditing="showFooterSettings"
                            @click="handleFooterClick"
                        />
                    </div>
                </div>
            </main>

            <!-- Right Sidebar - Inspector -->
            <aside :class="[
                'flex flex-col transition-all duration-300 flex-shrink-0 border-l',
                rightSidebarOpen ? 'w-80' : 'w-0',
                darkMode ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-200'
            ]">
                <div v-if="rightSidebarOpen" class="flex flex-col h-full overflow-hidden">
                    <!-- Inspector Header -->
                    <div :class="['p-4 border-b', darkMode ? 'border-gray-700' : 'border-gray-200']">
                        <div v-if="showNavSettings" class="flex items-center gap-3">
                            <div :class="['w-10 h-10 rounded-lg flex items-center justify-center', darkMode ? 'bg-indigo-900/50' : 'bg-indigo-100']">
                                <Bars3BottomLeftIcon :class="['w-5 h-5', darkMode ? 'text-indigo-400' : 'text-indigo-600']" aria-hidden="true" />
                            </div>
                            <div>
                                <h3 :class="['font-semibold', darkMode ? 'text-white' : 'text-gray-900']">Site Navigation</h3>
                                <p :class="['text-xs', darkMode ? 'text-gray-400' : 'text-gray-500']">Appears on all pages</p>
                            </div>
                        </div>
                        <div v-else-if="showFooterSettings" class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gray-700 rounded-lg flex items-center justify-center">
                                <Bars3BottomLeftIcon class="w-5 h-5 text-white" aria-hidden="true" />
                            </div>
                            <div>
                                <h3 :class="['font-semibold', darkMode ? 'text-white' : 'text-gray-900']">Site Footer</h3>
                                <p :class="['text-xs', darkMode ? 'text-gray-400' : 'text-gray-500']">Appears on all pages</p>
                            </div>
                        </div>
                        <div v-else-if="selectedSection" class="flex items-center gap-3">
                            <div :class="['w-10 h-10 rounded-lg flex items-center justify-center', darkMode ? 'bg-blue-900/50' : 'bg-blue-100']">
                                <component :is="selectedSectionType?.icon || Squares2X2Icon" :class="['w-5 h-5', darkMode ? 'text-blue-400' : 'text-blue-600']" aria-hidden="true" />
                            </div>
                            <div>
                                <h3 :class="['font-semibold capitalize', darkMode ? 'text-white' : 'text-gray-900']">{{ selectedSection.type }}</h3>
                                <p :class="['text-xs', darkMode ? 'text-gray-400' : 'text-gray-500']">Edit section properties</p>
                            </div>
                        </div>
                        <div v-else class="text-center py-4">
                            <p :class="['text-sm', darkMode ? 'text-gray-400' : 'text-gray-500']">Select a section to edit</p>
                        </div>
                    </div>

                    <!-- Navigation Settings Panel -->
                    <NavigationInspector
                        v-if="showNavSettings"
                        :navigation="siteNavigation"
                        :pages="pages"
                        :darkMode="darkMode"
                        @openMediaLibrary="(target, field) => openMediaLibrary(target, field)"
                    />

                    <!-- Footer Settings Panel -->
                    <FooterInspector
                        v-else-if="showFooterSettings"
                        :footer="siteFooter"
                        :pages="pages"
                        :darkMode="darkMode"
                        @openMediaLibrary="(target, field) => openMediaLibrary(target, field)"
                    />

                    <!-- Section Inspector -->
                    <SectionInspector
                        v-else-if="selectedSection"
                        :section="selectedSection"
                        :sectionType="selectedSectionType"
                        :activeTab="activeInspectorTab"
                        :darkMode="darkMode"
                        @update:activeTab="activeInspectorTab = $event"
                        @updateContent="updateSectionContent"
                        @updateStyle="updateSectionStyle"
                        @openMediaLibrary="(sectionId, field) => openMediaLibrary('section', sectionId, field)"
                        @addItem="addItem"
                        @removeItem="removeItem"
                        @addPlan="addPlan"
                        @removePlan="removePlan"
                        @addPlanFeature="addPlanFeature"
                        @addFaqItem="addFaqItem"
                        @removeFaqItem="removeFaqItem"
                        @addTeamMember="addTeamMember"
                        @removeTeamMember="removeTeamMember"
                        @addBlogPost="addBlogPost"
                        @removeBlogPost="removeBlogPost"
                        @addStatItem="addStatItem"
                        @removeStatItem="removeStatItem"
                        @removeGalleryImage="(idx) => removeGalleryImage(selectedSection?.id || '', idx)"
                    />
                </div>
            </aside>

            <!-- Toggle Right Sidebar -->
            <button
                @click="rightSidebarOpen = !rightSidebarOpen"
                class="absolute right-0 top-1/2 -translate-y-1/2 z-10 p-1.5 bg-white border border-gray-200 rounded-l-lg shadow-sm hover:bg-gray-50 transition-colors"
                :class="rightSidebarOpen ? 'mr-80' : 'mr-0'"
                aria-label="Toggle right sidebar"
            >
                <ChevronRightIcon :class="['w-4 h-4 text-gray-500 transition-transform', rightSidebarOpen ? '' : 'rotate-180']" aria-hidden="true" />
            </button>
        </div>
    </div>

    <!-- Modals -->
    <CreatePageModal
        :show="showCreatePageModal"
        :creating="creatingPage"
        :error="pageError"
        @close="showCreatePageModal = false"
        @create="createPage"
    />

    <EditPageModal
        :show="showEditPageModal"
        :page="editingPage"
        @close="showEditPageModal = false; editingPage = null"
        @update="updatePage"
    />

    <ApplyTemplateModal
        :show="showApplyTemplateModal"
        :activePage="activePage"
        :applying="applyingTemplate"
        @close="showApplyTemplateModal = false"
        @apply="applyTemplate"
    />

    <MediaLibraryModal
        :show="showMediaLibrary"
        :mediaLibrary="mediaLibrary"
        :uploading="uploadingImage"
        :uploadError="imageUploadError"
        :allowCrop="true"
        @close="showMediaLibrary = false"
        @upload="uploadImage"
        @select="selectMediaImage"
        @selectCropped="handleCroppedImage"
        @selectStockPhoto="handleStockPhotoSelect"
        @delete="deleteMediaImage"
    />

    <!-- Context Menu -->
    <ContextMenu
        :visible="contextMenu.visible"
        :x="contextMenu.x"
        :y="contextMenu.y"
        :section-id="contextMenu.sectionId"
        :section-type="contextMenu.sectionType"
        :can-move-up="contextMenu.sectionId ? sections.findIndex(s => s.id === contextMenu.sectionId) > 0 : false"
        :can-move-down="contextMenu.sectionId ? sections.findIndex(s => s.id === contextMenu.sectionId) < sections.length - 1 : false"
        :has-clipboard="hasClipboard"
        :clipboard-type="clipboardType"
        @close="closeContextMenu"
        @action="handleContextMenuAction"
    />

    <!-- Toast Notifications -->
    <ToastContainer />
</template>

<style scoped>
.canvas-background {
    background-color: #f3f4f6;
    background-image: radial-gradient(circle, #d1d5db 1px, transparent 1px);
    background-size: 20px 20px;
}

.canvas-background-dark {
    background-color: #111827;
    background-image: radial-gradient(circle, #374151 1px, transparent 1px);
    background-size: 20px 20px;
}
</style>

<style>
/* Custom Scrollbar Styling - Global for this page */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}

.custom-scrollbar:hover {
    scrollbar-color: #94a3b8 transparent;
}

/* Webkit browsers (Chrome, Safari, Edge) */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 3px;
    transition: background-color 0.2s;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: #94a3b8;
}

/* Dark mode scrollbars */
.custom-scrollbar-dark {
    scrollbar-width: thin;
    scrollbar-color: #4b5563 transparent;
}

.custom-scrollbar-dark:hover {
    scrollbar-color: #6b7280 transparent;
}

.custom-scrollbar-dark::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.custom-scrollbar-dark::-webkit-scrollbar-track {
    background: transparent;
    border-radius: 3px;
}

.custom-scrollbar-dark::-webkit-scrollbar-thumb {
    background-color: #4b5563;
    border-radius: 3px;
}

.custom-scrollbar-dark::-webkit-scrollbar-thumb:hover {
    background-color: #6b7280;
}
</style>
