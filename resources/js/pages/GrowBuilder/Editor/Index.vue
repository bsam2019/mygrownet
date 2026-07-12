<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted, defineAsyncComponent } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import draggable from 'vuedraggable';
import axios from 'axios';

// Components — eagerly loaded (always visible)
import SectionRenderer from './components/SectionRenderer.vue';
import NavigationRenderer from './components/NavigationRenderer.vue';
import FooterRenderer from './components/FooterRenderer.vue';
import ToastContainer from './components/ToastContainer.vue';
import ContextMenu from './components/common/ContextMenu.vue';
import MobileWarningModal from './components/MobileWarningModal.vue';
import EditorLeftSidebar from './components/EditorLeftSidebar.vue';
import { EditorToolbar } from './components/sidebar';

// Lazy-loaded (only on user interaction)
const OnboardingTutorial = defineAsyncComponent(() => import('./components/OnboardingTutorial.vue'));
const EditorPreview = defineAsyncComponent(() => import('./components/EditorPreview.vue'));
const KeyboardShortcutsModal = defineAsyncComponent(() => import('./components/KeyboardShortcutsModal.vue'));
const CreatePageModal = defineAsyncComponent(() => import('./components/modals/CreatePageModal.vue'));
const EditPageModal = defineAsyncComponent(() => import('./components/modals/EditPageModal.vue'));
const ApplyTemplateModal = defineAsyncComponent(() => import('./components/modals/ApplyTemplateModal.vue'));
const MediaLibraryModal = defineAsyncComponent(() => import('./components/modals/MediaLibraryModal.vue'));
const AIAssistantModal = defineAsyncComponent(() => import('./components/modals/AIAssistantModal.vue'));
const AIFloatingButton = defineAsyncComponent(() => import('./components/ai/AIFloatingButton.vue'));

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
    ArrowsUpDownIcon,
    ArrowUpIcon,
    ArrowDownIcon,
    DocumentDuplicateIcon,
    TrashIcon,
    ComputerDesktopIcon,
    QuestionMarkCircleIcon,
    PhotoIcon,
} from '@heroicons/vue/24/outline';

// Composables
import { useInlineEdit } from './composables/useInlineEdit';
import { useElementDrag } from './composables/useElementDrag';
import { useToast } from './composables/useToast';
import { useEditorStore } from './stores/editorStore';
import { usePageStore } from './stores/pageStore';
import { useMediaStore } from './stores/mediaStore';
import { useHistoryStore } from './stores/historyStore';
import { storeToRefs } from 'pinia';
import { useAutoSave } from './composables/useAutoSave';
import { useDragUpload } from './composables/useDragUpload';
import { useClipboard } from './composables/useClipboard';
import { useAIContext } from './composables/useAIContext';
import { useImageOptimization } from './composables/useImageOptimization';
import { useAIActions } from './composables/useAIActions';

interface AIUsage {
    limit: number;
    used: number;
    remaining: number;
    is_unlimited: boolean;
    percentage: number;
    month: string;
    features: string[];
    has_priority: boolean;
}

interface TierRestrictions {
    tier: string;
    tier_name: string;
    sites_limit: number;
    storage_limit: number;
    storage_limit_formatted: string;
    products_limit: number;
    products_unlimited: boolean;
    ai_prompts_limit: number;
    ai_unlimited: boolean;
    features: Record<string, boolean>;
}

const props = defineProps<{
    site: Site;
    pages: Page[];
    currentPage?: Page;
    sectionTypes: SectionBlock[];
    aiUsage?: AIUsage;
    tierRestrictions?: TierRestrictions;
}>();

// Helper to check if feature is available based on tier
const hasFeature = (feature: string): boolean => {
    return props.tierRestrictions?.features?.[feature] ?? false;
};

// Check if user can use AI
const canUseAI = computed(() => {
    const usage = aiUsage.value;
    console.log('canUseAI computed:', { 
        aiUsage: usage,
        limit: usage?.limit,
        remaining: usage?.remaining,
        is_unlimited: usage?.is_unlimited
    });
    if (!usage) return true;
    const result = usage.is_unlimited || usage.remaining > 0;
    console.log('canUseAI result:', result);
    return result;
});

// Check if user has AI section generator access
const hasAISectionGenerator = computed(() => {
    return aiUsage.value?.features?.includes('section') ?? false;
});

// Check if user has AI SEO access
const hasAISEO = computed(() => {
    return aiUsage.value?.features?.includes('seo') ?? false;
});

// ============================================
// Non-Store State (kept locally)
// ============================================
const isMobileDevice = ref(false);
const showMobileWarning = ref(false);
const ONBOARDING_KEY = computed(() => `growbuilder_onboarding_completed_${props.site.id}`);
const aiUsage = ref<AIUsage | undefined>(props.aiUsage);
watch(() => props.aiUsage, (newUsage) => {
    if (newUsage) {
        aiUsage.value = newUsage;
    }
}, { deep: true });
const breakpoints = [
    { name: 'Mobile', width: 375, icon: 'phone' },
    { name: 'Tablet', width: 768, icon: 'tablet' },
    { name: 'Laptop', width: 1024, icon: 'laptop' },
    { name: 'Desktop', width: 1440, icon: 'desktop' },
];
const showAIModal = ref(false);

// ============================================
// Store Initialization
// ============================================
const editorStore = useEditorStore();
const pageStore = usePageStore();
const mediaStore = useMediaStore();
const historyStore = useHistoryStore();

const {
    leftSidebarOpen, previewMode, selectedSectionId, saving, publishing, isPublished,
    isDragging, activeInspectorTab, hoveredSectionId, activeLeftTab,
    showNavSettings, showFooterSettings, showKeyboardShortcuts, darkMode, canvasZoom, lastSaved,
    isFullPreview, isIframePreview, previewWidth, isResizingPreview, iframeKey,
    showOnboarding,
    activePage, sections, pageTitle, siteNavigation, siteFooter,
    resizingSection, contextMenu,
} = storeToRefs(editorStore);
const {
    showCreatePageModal, showEditPageModal, editingPage, creatingPage, pageError,
    showApplyTemplateModal, applyingTemplate,
} = storeToRefs(pageStore);
const {
    uploadingImage, imageUploadError, mediaLibrary, showMediaLibrary,
    mediaLibraryTarget, mediaLibraryRequirements, mediaLibrarySectionType, mediaLibraryFieldName,
} = storeToRefs(mediaStore);
const { canUndo, canRedo } = storeToRefs(historyStore);

// ============================================
// Composables
// ============================================
const { editingValue, startInlineEdit, saveInlineEdit, isEditing, handleInlineKeydown } = useInlineEdit({ sections });
const { draggingElement, draggingSectionContent, startElementDrag, startSectionContentDrag, getElementTransform, getSectionContentTransform, hasElementOffset, hasSectionContentOffset, resetAllElementOffsets, resetSectionContentOffset } = useElementDrag({ sections });
const toast = useToast();
const { copySection, cutSection, pasteSection, hasClipboard, clipboardType } = useClipboard();

// Destructure store actions (excluding functions with local wrappers)
const {
    selectSection,
    showContextMenu, closeContextMenu,
    startResize, handleResize, stopResize,
    addItem, removeItem, addPlan, removePlan, addPlanFeature,
    addFaqItem, removeFaqItem,
    addTeamMember, removeTeamMember,
    addBlogPost, removeBlogPost,
    addStatItem, removeStatItem,
    removeGalleryImage,
    initializeSections, initializeSiteNavigation, initializeSiteFooter,
    setPreviewMode,
} = editorStore;
const { optimizeImage, optimizeLogo, formatFileSize, isImage, needsOptimization } = useImageOptimization();

// AI Actions - Phase 1 Enhancement
const {
    context: aiSessionContext,
    conversation: aiConversation,
    loading: aiLoading,
    error: aiError,
    pendingActions: aiPendingActions,
    autoApplyActions,
    confirmationActions,
    hasPendingActions,
    isContextLoaded,
    loadContext: loadAIContext,
    updateContext: updateAIContext,
    sendMessage: sendAIMessage,
    applyAction: applyAIAction,
    applyAllActions: applyAllAIActions,
    dismissAction: dismissAIAction,
    clearConversation: clearAIConversation,
} = useAIActions(props.site.id);

// AI Context - provides site/page awareness to AI assistant
const siteRef = computed(() => props.site);
const pagesRef = computed(() => props.pages);
const currentPageRef = computed(() => activePage.value);
const sectionsRef = computed(() => sections.value);
const selectedSectionIdRef = computed(() => selectedSectionId.value);

const { context: aiContext, contextSummary, smartSuggestions } = useAIContext(
    siteRef,
    pagesRef,
    currentPageRef,
    sectionsRef,
    selectedSectionIdRef
);

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
    // Validate file type
    if (!isImage(file)) {
        toast.error('Please drop an image file');
        return;
    }
    
    try {
        // Optimize image before upload
        let uploadFile = file;
        if (needsOptimization(file)) {
            const originalSize = file.size;
            toast.info('Optimizing image...');
            
            const result = await optimizeImage(file, {
                maxWidth: 1920,
                maxHeight: 1080,
                quality: 0.85,
                format: 'jpeg',
            });
            
            uploadFile = result.file;
            
            if (result.compressionRatio > 0) {
                toast.success(
                    `Image optimized: ${formatFileSize(originalSize)} → ${formatFileSize(result.optimizedSize)} (${result.compressionRatio}% smaller)`
                );
            }
        }
        
        const formData = new FormData();
        formData.append('file', uploadFile);
        
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
editorStore.initialize(props.site, props.pages, props.currentPage);
console.log('Site settings:', props.site.settings);
console.log('Site settings navigation:', props.site.settings?.navigation);
console.log('Site settings footer:', props.site.settings?.footer);

// Initialize history after loading data
historyStore.initHistory(sections.value, siteNavigation.value, siteFooter.value);

// ============================================
// History (Undo/Redo)
// ============================================
const pushToHistory = () => {
    historyStore.pushState(sections.value, siteNavigation.value, siteFooter.value);
    autoSave.markDirty();
};

const handleUndo = () => {
    const state = historyStore.undo();
    if (state) {
        sections.value = state.sections;
        siteNavigation.value = state.navigation;
        siteFooter.value = state.footer;
        toast.info('Undone');
    }
};

const handleRedo = () => {
    const state = historyStore.redo();
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
// Watchers
// ============================================
watch(() => props.currentPage, (newPage) => {
    if (newPage) {
        activePage.value = newPage;
        editorStore.initializeSections();
        selectedSectionId.value = null;
    }
});

watch(() => props.pages, () => {
    if (!props.site.settings?.navigation) {
        editorStore.initializeSiteNavigation(props.site, props.pages);
    }
}, { deep: true });

// Watch previewMode and update previewWidth accordingly
watch(previewMode, (newMode) => {
    if (newMode === 'mobile') {
        previewWidth.value = 375;
    } else if (newMode === 'tablet') {
        previewWidth.value = 768;
    } else if (newMode === 'desktop') {
        previewWidth.value = 1024;
    }
});

// Watch active page and update AI context (Phase 1)
watch(activePage, (newPage) => {
    if (newPage && isContextLoaded.value) {
        updateAIContext('current_page', {
            id: newPage.id,
            title: newPage.title,
            section_count: sections.value.length,
        }).catch(err => {
            console.warn('Failed to update AI context:', err);
        });
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
    // Always use the previewWidth value for consistent behavior
    return `max-w-[${previewWidth.value}px]`;
});

// Canvas zoom transform
const canvasTransform = computed(() => {
    if (canvasZoom.value === 100) return '';
    return `scale(${canvasZoom.value / 100})`;
});

const canvasTransformOrigin = computed(() => 'top center');

const isMobilePreview = computed(() => previewWidth.value < 768);

// Mobile preview responsive helpers
const textSize = computed(() => isMobilePreview.value ? { h1: 'text-2xl', h2: 'text-xl', h3: 'text-lg', p: 'text-sm' } : { h1: 'text-4xl', h2: 'text-3xl', h3: 'text-xl', p: 'text-base' });
const spacing = computed(() => isMobilePreview.value ? { section: 'py-10 px-4', gap: 'gap-4' } : { section: 'py-16 px-6', gap: 'gap-6' });
const gridCols2 = computed(() => isMobilePreview.value ? 'grid-cols-1' : 'grid-cols-1 md:grid-cols-2');
const gridCols3 = computed(() => isMobilePreview.value ? 'grid-cols-1' : 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3');
const gridCols4 = computed(() => isMobilePreview.value ? 'grid-cols-2' : 'grid-cols-2 md:grid-cols-4');

// ============================================
// Section Actions
// ============================================
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
        blog: { title: 'Latest News', description: 'Stay updated with our latest articles and insights', postsCount: 6, showViewAll: true },
        stats: { title: 'Our Impact', items: [{ value: '500+', label: 'Happy Clients' }] },
        map: { title: 'Find Us', address: '123 Business Street', embedUrl: '', showAddress: true },
        video: { title: 'Watch Our Story', videoUrl: '', videoType: 'youtube', autoplay: false, description: '' },
        divider: { style: 'line', height: 40, color: '#e5e7eb' },
    };
    return defaults[type] || {};
};

const addSection = (type: string) => {
    pushToHistory();
    editorStore.addSection(type);
};

const duplicateSection = (id: string) => {
    pushToHistory();
    editorStore.duplicateSection(id);
};

const deleteSection = (id: string) => {
    pushToHistory();
    editorStore.deleteSection(id);
};

const moveSection = (id: string, direction: 'up' | 'down') => {
    pushToHistory();
    editorStore.moveSection(id, direction);
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
const handleContextMenuAction = (action: string, sectionId: string | null) => {
    switch (action) {
        case 'edit':
            if (sectionId) {
                selectedSectionId.value = sectionId;
                activeInspectorTab.value = 'content';
                activeLeftTab.value = 'inspector';
            }
            break;
        case 'style':
            if (sectionId) {
                selectedSectionId.value = sectionId;
                activeInspectorTab.value = 'style';
                activeLeftTab.value = 'inspector';
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
    }
};

const updateSectionContent = (key: string, value: any) => {
    if (!selectedSection.value) return;
    pushToHistory();
    editorStore.updateSectionContent(key, value);
};

const updateSectionStyle = (key: string, value: any) => {
    if (!selectedSection.value) return;
    if (!selectedSection.value.style) {
        selectedSection.value.style = {};
    }
    if (selectedSection.value.style[key] !== value) {
        pushToHistory();
        editorStore.updateSectionStyle(key, value);
    }
};

// ============================================
// Section Click Handler
// ============================================
const handleSectionClick = (e: MouseEvent, sectionId: string) => {
    if (draggingElement.value || draggingSectionContent.value) return;
    selectSection(sectionId);
};

// Items management actions are destructured from editorStore above

// ============================================
// Page Management
// ============================================
const openCreatePageModal = () => {
    pageStore.openCreatePageModal();
};

const createPage = async (form: NewPageForm) => {
    await pageStore.createPage(form, props.site.id, props.pages);
};

const openEditPageModal = (page: Page) => {
    pageStore.openEditPageModal(page);
};

const updatePage = async (page: Page) => {
    try {
        await pageStore.updatePage(page, props.site.id, props.pages);
        toast.success('Page updated successfully');
    } catch (error) {
        console.error('Failed to update page:', error);
        toast.error('Failed to update page');
    }
};

const deletePage = async (pageId: number) => {
    try {
        await pageStore.deletePage(pageId, props.site.id, props.pages);
        toast.success('Page deleted');
    } catch (error) {
        console.error('Failed to delete page:', error);
        toast.error('Failed to delete page');
    }
};

const savePage = async (silent = false) => {
    if (!activePage.value) return;
    saving.value = true;
    try {
        await pageStore.savePage(props.site.id, silent);
        autoSave.reset();
        if (!silent) {
            toast.success('Changes saved successfully');
        }
    } catch (error) {
        console.error('Save failed:', error);
        if (!silent) {
            toast.error('Failed to save changes');
        }
        throw error;
    } finally {
        saving.value = false;
    }
};

// ============================================
// Publish/Unpublish
// ============================================
const publishSite = async () => {
    publishing.value = true;
    try {
        // Save first to ensure latest changes are published
        await savePage(true);
        
        await axios.post(`/growbuilder/sites/${props.site.id}/publish`);
        isPublished.value = true;
        toast.success('Site published! Your site is now live.');
    } catch (error: any) {
        const message = error.response?.data?.error || error.response?.data?.message || 'Failed to publish site';
        toast.error(message);
    } finally {
        publishing.value = false;
    }
};

const unpublishSite = async () => {
    publishing.value = true;
    try {
        await axios.post(`/growbuilder/sites/${props.site.id}/unpublish`);
        isPublished.value = false;
        toast.success('Site unpublished');
    } catch (error: any) {
        const message = error.response?.data?.error || 'Failed to unpublish site';
        toast.error(message);
    } finally {
        publishing.value = false;
    }
};

const openPreview = async () => {
    await savePage();
    window.open(`${props.site.url}?t=${Date.now()}`, '_blank');
};

const switchPage = (pageOrId: Page | number) => {
    pageStore.switchPage(pageOrId, props.site.id, props.pages);
};

// ============================================
// Template Management
// ============================================
const openApplyTemplateModal = () => {
    pageStore.openApplyTemplateModal();
};

const applyTemplate = async (templateId: string) => {
    await pageStore.applyTemplate(templateId, props.site.id);
};

// ============================================
// Media Library
// ============================================
const loadMediaLibrary = async () => {
    await mediaStore.loadMediaLibrary(props.site.id);
};

const openMediaLibrary = async (
    target: 'navigation' | 'footer' | 'section',
    fieldOrSectionId: string,
    field?: string,
    itemIndex?: number
) => {
    await mediaStore.openMediaLibrary(props.site.id, target, fieldOrSectionId, field, itemIndex);
};

const uploadImage = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (!input.files?.length) return;
    
    let file = input.files[0];
    
    // Validate file type
    if (!isImage(file)) {
        imageUploadError.value = 'Please select an image file';
        input.value = '';
        return;
    }
    
    uploadingImage.value = true;
    imageUploadError.value = null;
    
    try {
        // Optimize image before upload
        if (needsOptimization(file)) {
            const originalSize = file.size;
            toast.info('Optimizing image...');
            
            // Determine optimization type based on target
            const isLogoUpload = mediaLibraryTarget.value?.target === 'navigation' || 
                                 mediaLibraryTarget.value?.target === 'footer';
            
            const result = isLogoUpload 
                ? await optimizeLogo(file)
                : await optimizeImage(file, {
                    maxWidth: 1920,
                    maxHeight: 1080,
                    quality: 0.85,
                    format: 'jpeg',
                });
            
            file = result.file;
            
            // Show optimization results
            if (result.compressionRatio > 0) {
                toast.success(
                    `Image optimized: ${formatFileSize(originalSize)} → ${formatFileSize(result.optimizedSize)} (${result.compressionRatio}% smaller)`
                );
            }
        }
        
        const formData = new FormData();
        formData.append('file', file);
        
        const response = await axios.post(`/growbuilder/media/${props.site.id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        
        if (response.data.success) {
            mediaLibrary.value.unshift(response.data.media);
            mediaStore.selectMediaImage(response.data.media);
        }
    } catch (error: any) {
        imageUploadError.value = error.response?.data?.message || 'Failed to upload image';
        toast.error(imageUploadError.value);
    } finally {
        uploadingImage.value = false;
        input.value = '';
    }
};

// selectMediaImage is handled by mediaStore

// Handle cropped image selection (data URL from canvas)
const handleCroppedImage = async (dataUrl: string, originalMedia: any) => {
    console.log('handleCroppedImage called:', { 
        dataUrl: dataUrl.substring(0, 50) + '...', 
        originalMedia, 
        target: mediaLibraryTarget.value 
    });
    
    if (!mediaLibraryTarget.value) return;
    
    try {
        // Upload the cropped image to the server
        toast.info('Saving cropped image...');
        
        const response = await axios.post(`/growbuilder/media/${props.site.id}/base64`, {
            image: dataUrl,
            filename: `cropped-${originalMedia.originalName || 'image.jpg'}`,
            source_media_id: originalMedia.id, // Track the source for cleanup
        });
        
        if (!response.data.success) {
            throw new Error(response.data.message || 'Failed to save cropped image');
        }
        
        const savedMedia = response.data.media;
        const imageUrl = savedMedia.url;
        
        // Add to media library
        mediaLibrary.value.unshift(savedMedia);
        
        // Apply the saved image URL to the target
        const { target, sectionId, field, itemIndex } = mediaLibraryTarget.value;
        
        if (target === 'navigation') {
            siteNavigation.value.logo = imageUrl;
            console.log('Applied cropped image to navigation logo');
        } else if (target === 'footer') {
            siteFooter.value.logo = imageUrl;
            console.log('Applied cropped image to footer logo');
        } else if (target === 'section' && sectionId) {
            const section = sections.value.find(s => s.id === sectionId);
            console.log('Found section:', section?.type, 'field:', field);
            
            if (section && field) {
                // Get the old image URL to potentially delete it later
                let oldImageUrl = null;
                
                // Handle nested field paths like 'slides.0.backgroundImage'
                if (field.includes('.')) {
                    const fieldParts = field.split('.');
                    let target = section.content;
                    
                    // Navigate to the parent object
                    for (let i = 0; i < fieldParts.length - 1; i++) {
                        const part = fieldParts[i];
                        if (!target[part]) {
                            if (isNaN(Number(fieldParts[i + 1]))) {
                                target[part] = {};
                            } else {
                                target[part] = [];
                            }
                        }
                        target = target[part];
                    }
                    
                    // Get old value and set the new one
                    const finalField = fieldParts[fieldParts.length - 1];
                    oldImageUrl = target[finalField];
                    target[finalField] = imageUrl;
                    console.log('Applied cropped image to nested field:', { sectionId, field, finalField });
                } else if (itemIndex !== undefined && section.content.items) {
                    oldImageUrl = section.content.items[itemIndex][field];
                    section.content.items[itemIndex][field] = imageUrl;
                    console.log('Applied cropped image to section item:', { sectionId, field, itemIndex });
                } else if (field === 'images' && section.type === 'gallery') {
                    if (!section.content.images) section.content.images = [];
                    section.content.images.push({ id: savedMedia.id, url: imageUrl, alt: savedMedia.originalName });
                    console.log('Added cropped image to gallery');
                } else {
                    oldImageUrl = section.content[field];
                    section.content[field] = imageUrl;
                    console.log('Applied cropped image to section field:', { sectionId, field });
                }
                
                // If there was an old cropped image (starts with our S3 URL), delete it
                if (oldImageUrl && typeof oldImageUrl === 'string' && oldImageUrl.includes('digitaloceanspaces.com')) {
                    console.log('Checking for old cropped image to delete:', oldImageUrl);
                    
                    // Find the media record for the old image
                    const oldMedia = mediaLibrary.value.find(m => m.url === oldImageUrl);
                    console.log('Found old media record:', oldMedia);
                    
                    if (oldMedia) {
                        // Check if it's a cropped image from the same source
                        const isOldCropped = oldMedia.metadata?.source === 'cropped' || 
                                           oldMedia.filename?.includes('cropped-') ||
                                           oldMedia.source_media_id;
                        
                        const isSameSource = oldMedia.source_media_id === originalMedia.id;
                        
                        console.log('Old image analysis:', {
                            isOldCropped,
                            isSameSource,
                            oldSourceId: oldMedia.source_media_id,
                            newSourceId: originalMedia.id,
                            oldFilename: oldMedia.filename
                        });
                        
                        if (isOldCropped && isSameSource) {
                            // Delete the old cropped image
                            try {
                                console.log('Deleting old cropped image:', oldMedia.id);
                                await axios.delete(`/growbuilder/media/${props.site.id}/${oldMedia.id}`);
                                mediaLibrary.value = mediaLibrary.value.filter(m => m.id !== oldMedia.id);
                                console.log('Successfully deleted old cropped image:', oldMedia.id);
                            } catch (error) {
                                console.warn('Failed to delete old cropped image:', error);
                            }
                        } else {
                            console.log('Old image not deleted - not a cropped version from same source');
                        }
                    } else {
                        console.log('No media record found for old URL');
                    }
                }
            }
        }
        
        toast.success('Cropped image saved and applied');
        mediaStore.closeMediaLibrary();
    } catch (error: any) {
        console.error('Failed to save cropped image:', error);
        toast.error(error.response?.data?.message || 'Failed to process image. Please try again or contact support.');
    }
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

const selectMediaImage = (media: any) => {
    mediaStore.selectMediaImage(media);
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
    mediaStore.closeMediaLibrary();
};

// ============================================
// AI Content Handlers
// ============================================
const handleAIContent = (content: any) => {
    console.log('handleAIContent received:', {
        sectionType: content.sectionType,
        position: content.position,
        hasStyle: !!content.style,
        style: content.style
    });
    
    // Find the target section - either selected or by type from AI
    let targetSection = selectedSection.value;
    
    // If no section selected but AI specified a section type, find it
    // BUT: Don't auto-find for headers - always create new if position is 'first'
    if (!targetSection && content.sectionType) {
        // For headers with position 'first', always create new (don't update existing)
        const isHeaderWithFirstPosition = 
            (content.sectionType === 'page-header' || content.sectionType === 'hero') 
            && content.position === 'first';
        
        if (!isHeaderWithFirstPosition) {
            targetSection = sections.value.find(s => s.type === content.sectionType) || null;
            if (targetSection) {
                selectedSectionId.value = targetSection.id;
            }
        }
    }
    
    if (!targetSection) {
        // No section selected or found, create a new one based on the content type
        const sectionType = content.sectionType || 'hero';
        pushToHistory();
        
        // Get style from AI or use defaults that match existing page
        const existingStyle = sections.value[0]?.style || {};
        const newStyle = {
            backgroundColor: content.style?.backgroundColor || existingStyle.backgroundColor || '#ffffff',
            textColor: content.style?.textColor || existingStyle.textColor || '#111827',
            ...content.style
        };
        
        const newSection: Section = {
            id: `section-${Date.now()}`,
            type: sectionType as any,
            content: { ...getDefaultContent(sectionType), ...content },
            style: newStyle,
        };
        
        // Handle position-aware placement
        const position = content.position || 'auto';
        console.log('Creating new section with position:', position, 'type:', sectionType);
        
        if (position === 'first') {
            // Insert at the beginning of the page
            sections.value.unshift(newSection);
            toast.success('AI content added at the top of the page');
        } else if (position === 'last') {
            // Insert at the end
            sections.value.push(newSection);
            toast.success('AI content added at the bottom of the page');
        } else {
            // Auto placement based on section type
            if (sectionType === 'page-header' || sectionType === 'hero') {
                // Headers go at the top
                sections.value.unshift(newSection);
                toast.success('Header section added at the top');
            } else if (sectionType === 'cta' || sectionType === 'contact') {
                // CTA and contact typically go near the end
                sections.value.push(newSection);
                toast.success('AI content added as new section');
            } else {
                // Default: add at the end
                sections.value.push(newSection);
                toast.success('AI content added as new section');
            }
        }
        
        selectedSectionId.value = newSection.id;
    } else {
        // Apply to target section
        pushToHistory();
        Object.keys(content).forEach(key => {
            if (key !== 'sectionType' && key !== 'position' && key !== 'style') {
                targetSection!.content[key] = content[key];
            }
        });
        // Also apply style if provided
        if (content.style) {
            Object.keys(content.style).forEach(key => {
                targetSection!.style[key] = content.style[key];
            });
        }
        toast.success('AI content applied to section');
    }
};

const handleAIColors = (palette: any) => {
    if (selectedSection.value) {
        pushToHistory();
        if (palette.background) {
            selectedSection.value.style.backgroundColor = palette.background;
        }
        if (palette.text) {
            selectedSection.value.style.textColor = palette.text;
        }
        if (palette.primary) {
            selectedSection.value.style.primaryColor = palette.primary;
        }
        if (palette.accent) {
            selectedSection.value.style.accentColor = palette.accent;
        }
        toast.success('Color palette applied to section');
    } else {
        toast.info('Select a section to apply colors');
    }
};

// Handle AI usage updates
const handleAIUsageUpdate = (newUsage: AIUsage) => {
    console.log('handleAIUsageUpdate called with:', newUsage);
    console.log('Current aiUsage:', aiUsage.value);
    aiUsage.value = newUsage;
    console.log('Updated aiUsage:', aiUsage.value);
};

const handleAIStyle = (styleChange: any) => {
    // Find the target section - either selected or by type from AI
    let targetSection = selectedSection.value;
    
    // If no section selected but AI specified a section type, find it
    if (!targetSection && styleChange.sectionType) {
        targetSection = sections.value.find(s => s.type === styleChange.sectionType) || null;
        if (targetSection) {
            selectedSectionId.value = targetSection.id;
        }
    }
    
    if (!targetSection) {
        toast.info('Select a section to apply style changes');
        return;
    }
    
    pushToHistory();
    
    // Apply style changes to the section
    if (!targetSection.style) {
        targetSection.style = {};
    }
    if (!targetSection.content) {
        targetSection.content = {};
    }
    
    // Apply each style property
    if (styleChange.backgroundColor) {
        targetSection.style.backgroundColor = styleChange.backgroundColor;
    }
    if (styleChange.textColor) {
        targetSection.style.textColor = styleChange.textColor;
    }
    if (styleChange.paddingY) {
        targetSection.style.paddingY = styleChange.paddingY;
    }
    // Handle text alignment/position (multiple possible property names from AI)
    const textAlign = styleChange.textPosition || styleChange.textAlign || styleChange.alignment;
    if (textAlign) {
        targetSection.content.textPosition = textAlign;
        targetSection.style.textAlign = textAlign;
    }
    if (styleChange.titleSize) {
        targetSection.style.titleSize = styleChange.titleSize;
    }
    if (styleChange.fontWeight) {
        targetSection.style.fontWeight = styleChange.fontWeight;
    }
    if (styleChange.minHeight) {
        targetSection.style.minHeight = styleChange.minHeight;
    }
    // Handle additional common style properties
    if (styleChange.padding) {
        targetSection.style.padding = styleChange.padding;
    }
    if (styleChange.margin) {
        targetSection.style.margin = styleChange.margin;
    }
    if (styleChange.borderRadius) {
        targetSection.style.borderRadius = styleChange.borderRadius;
    }
    if (styleChange.gap) {
        targetSection.style.gap = styleChange.gap;
    }
    if (styleChange.layout) {
        targetSection.content.layout = styleChange.layout;
    }
    
    toast.success(`Style applied: ${styleChange.action || 'changes made'}`);
};

// Handle AI add section request
const handleAIAddSection = (type: string, content?: any) => {
    pushToHistory();
    const newSection: Section = {
        id: `section-${Date.now()}`,
        type: type as any,
        content: { ...getDefaultContent(type), ...content },
        style: { backgroundColor: '#ffffff', textColor: '#111827' },
    };
    sections.value.push(newSection);
    selectedSectionId.value = newSection.id;
    toast.success(`Added ${type} section`);
};

// Handle AI navigation changes
const handleAINavigation = (changes: any) => {
    pushToHistory();
    
    if (changes.style) {
        siteNavigation.value.style = changes.style;
    }
    if (changes.sticky !== undefined) {
        siteNavigation.value.sticky = changes.sticky;
    }
    if (changes.showCta !== undefined) {
        siteNavigation.value.showCta = changes.showCta;
    }
    if (changes.showAuthButtons !== undefined) {
        siteNavigation.value.showAuthButtons = changes.showAuthButtons;
    }
    if (changes.ctaText) {
        siteNavigation.value.ctaText = changes.ctaText;
    }
    if (changes.ctaLink) {
        siteNavigation.value.ctaLink = changes.ctaLink;
    }
    
    toast.success(changes.action || 'Navigation updated');
};

// Handle AI footer changes
const handleAIFooter = (changes: any) => {
    pushToHistory();
    
    if (changes.layout) {
        siteFooter.value.layout = changes.layout;
    }
    if (changes.backgroundColor) {
        siteFooter.value.backgroundColor = changes.backgroundColor;
    }
    if (changes.textColor) {
        siteFooter.value.textColor = changes.textColor;
    }
    if (changes.showSocialLinks !== undefined) {
        siteFooter.value.showSocialLinks = changes.showSocialLinks;
    }
    if (changes.showNewsletter !== undefined) {
        siteFooter.value.showNewsletter = changes.showNewsletter;
    }
    
    toast.success(changes.action || 'Footer updated');
};

// Handle AI create page request
const handleAICreatePage = async (template: string, title?: string) => {
    const pageTitle = title || template.charAt(0).toUpperCase() + template.slice(1);
    const slug = pageTitle.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
    
    // Check if page already exists
    const existingPage = props.pages.find(p => p.slug === slug);
    if (existingPage) {
        toast.error(`A page with URL "/${slug}" already exists`);
        return;
    }
    
    // Find template sections
    const templateData = findTemplate(template);
    const templateSections = templateData?.sections.map((s, i) => ({
        id: `section-${Date.now()}-${i}`,
        type: s.type,
        content: { ...s.content },
        style: { ...s.style },
    })) || [];
    
    try {
        const response = await axios.post(`/growbuilder/editor/${props.site.id}/pages`, {
            title: pageTitle,
            slug: slug,
            sections: templateSections,
            show_in_nav: true,
            is_homepage: false,
        });
        
        // Add to navigation
        if (response.data.page?.id) {
            siteNavigation.value.navItems.push({
                id: `nav-${response.data.page.id}`,
                label: pageTitle,
                url: `/${slug}`,
                pageId: response.data.page.id,
                isExternal: false,
                children: [],
            });
            
            // Save navigation
            await axios.post(`/growbuilder/editor/${props.site.id}/settings`, {
                navigation: siteNavigation.value,
                footer: siteFooter.value,
            });
        }
        
        toast.success(`Created ${pageTitle} page`);
        
        // Navigate to new page
        if (response.data.page?.id) {
            router.visit(`/growbuilder/editor/${props.site.id}?page=${response.data.page.id}`);
        }
    } catch (error: any) {
        toast.error(error.response?.data?.error || 'Failed to create page');
    }
};

// Handle AI-generated page with custom content
const handleAIGeneratedPage = async (pageType: string, pageData: any) => {
    const pageTitle = pageData.title || pageType.charAt(0).toUpperCase() + pageType.slice(1);
    const slug = pageTitle.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
    
    // Check if page already exists
    const existingPage = props.pages.find(p => p.slug === slug);
    if (existingPage) {
        toast.error(`A page with URL "/${slug}" already exists`);
        return;
    }
    
    // Transform AI-generated sections to our format
    const aiSections = (pageData.sections || []).map((s: any, i: number) => ({
        id: `section-${Date.now()}-${i}`,
        type: s.type,
        content: { ...s.content },
        style: { ...s.style },
    }));
    
    try {
        const response = await axios.post(`/growbuilder/editor/${props.site.id}/pages`, {
            title: pageTitle,
            slug: slug,
            sections: aiSections,
            show_in_nav: true,
            is_homepage: false,
        });
        
        // Add to navigation
        if (response.data.page?.id) {
            siteNavigation.value.navItems.push({
                id: `nav-${response.data.page.id}`,
                label: pageTitle,
                url: `/${slug}`,
                pageId: response.data.page.id,
                isExternal: false,
                children: [],
            });
            
            // Save navigation
            await axios.post(`/growbuilder/editor/${props.site.id}/settings`, {
                navigation: siteNavigation.value,
                footer: siteFooter.value,
            });
        }
        
        toast.success(`Created ${pageTitle} page with AI-generated content`);
        
        // Navigate to new page
        if (response.data.page?.id) {
            router.visit(`/growbuilder/editor/${props.site.id}?page=${response.data.page.id}`);
        }
    } catch (error: any) {
        toast.error(error.response?.data?.error || 'Failed to create AI page');
    }
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
    activeLeftTab.value = 'inspector';
};

const handleFooterClick = () => {
    showFooterSettings.value = true;
    showNavSettings.value = false;
    selectedSectionId.value = null;
    activeLeftTab.value = 'inspector';
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
    // Ctrl+] to cycle through sidebar tabs
    if ((e.ctrlKey || e.metaKey) && e.key === ']') {
        e.preventDefault();
        const tabs = ['widgets', 'pages', 'inspector'];
        const currentIndex = tabs.indexOf(activeLeftTab.value);
        activeLeftTab.value = tabs[(currentIndex + 1) % tabs.length] as 'widgets' | 'pages' | 'inspector';
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
const toggleFullPreview = async (useIframe = false) => {
    if (!isFullPreview.value) {
        // Only save if using iframe preview (which loads the actual site URL)
        if (useIframe) {
            await savePage();
            iframeKey.value++; // Force iframe refresh
        }
        isFullPreview.value = true;
        isIframePreview.value = useIframe;
        leftSidebarOpen.value = false;
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
};

const togglePreviewMode = async () => {
    if (!isIframePreview.value) {
        // Switching to iframe mode - need to save first
        await savePage();
        iframeKey.value++; // Refresh iframe when switching to it
    }
    isIframePreview.value = !isIframePreview.value;
};

const setPreviewBreakpoint = (width: number) => {
    previewWidth.value = width;
    // Update preview mode based on width
    if (width <= 375) {
        previewMode.value = 'mobile';
    } else if (width <= 768) {
        previewMode.value = 'tablet';
    } else {
        previewMode.value = 'desktop';
    }
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
    // Detect mobile device and set preview mode accordingly
    const checkMobile = () => {
        const userAgent = navigator.userAgent || navigator.vendor || (window as any).opera;
        const isMobile = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(userAgent.toLowerCase());
        const isSmallScreen = window.innerWidth < 768;
        return isMobile || isSmallScreen;
    };
    
    isMobileDevice.value = checkMobile();
    
    // Auto-set preview mode to mobile on mobile devices
    if (isMobileDevice.value) {
        previewMode.value = 'mobile';
        previewWidth.value = 375; // iPhone size
        leftSidebarOpen.value = false; // Start with sidebar closed on mobile
    }
    
    window.addEventListener('keydown', handleKeyboardShortcuts);
    // Attach drag-to-upload to the canvas area
    dragUpload.attachGlobal();
    
    // Initialize AI session context (Phase 1)
    loadAIContext().catch(err => {
        console.warn('Failed to load AI context:', err);
    });
    
    // Check if onboarding should be shown (check database first, fallback to localStorage)
    if (!props.site.onboarding_completed) {
        const onboardingCompleted = localStorage.getItem(ONBOARDING_KEY.value);
        if (!onboardingCompleted) {
            // Small delay to let the editor render first
            setTimeout(() => {
                showOnboarding.value = true;
            }, 500);
        }
    }
});

// Onboarding handlers
const closeOnboarding = () => {
    showOnboarding.value = false;
    // Save to database
    saveOnboardingCompletion();
};

const completeOnboarding = () => {
    showOnboarding.value = false;
    // Save to database
    saveOnboardingCompletion();
    toast.success('Tutorial complete! Start building your site.');
};

const saveOnboardingCompletion = async () => {
    // Save to localStorage as backup
    localStorage.setItem(ONBOARDING_KEY.value, 'true');
    
    // Save to database
    try {
        await axios.post(route('growbuilder.sites.complete-onboarding', props.site.id));
    } catch (error) {
        console.error('Failed to save onboarding completion:', error);
    }
};

// Allow restarting the tutorial from help menu
const restartOnboarding = () => {
    showOnboarding.value = true;
};

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyboardShortcuts);
    dragUpload.detachGlobal();
});
</script>

<template>
    <Head :title="`Edit - ${site.name}`" />

    <EditorPreview
        :is-full-preview="isFullPreview"
        :is-iframe-preview="isIframePreview"
        :preview-width="previewWidth"
        :iframe-key="iframeKey"
        :site-name="site.name"
        :site-url="site.url"
        :site-navigation="siteNavigation"
        :site-footer="siteFooter"
        :sections="sections"
        :is-mobile="isMobilePreview"
        :editing-value="''"
        :dragging-element="null"
        @close="exitFullPreview"
        @edit="exitFullPreview"
        @set-preview-breakpoint="previewWidth = $event"
        @set-iframe-preview="isIframePreview = $event"
        @refresh-iframe="iframeKey++"
        @switch-page="switchPage"
    />

    <KeyboardShortcutsModal
        :show="showKeyboardShortcuts"
        @close="showKeyboardShortcuts = false"
    />

    <MobileWarningModal
        :show="showMobileWarning"
        @close="showMobileWarning = false"
        @back="router.visit(route('growbuilder.index'))"
    />

    <div :class="['h-screen flex flex-col overflow-hidden transition-colors duration-200', darkMode ? 'bg-gray-900' : 'bg-gray-100']">
        <!-- Top Toolbar -->
        <EditorToolbar
            :siteName="site.name"
            :siteLogo="site.logo || siteNavigation.logo"
            :pageTitle="activePage?.title || ''"
            :previewMode="previewMode"
            :saving="saving"
            :publishing="publishing"
            :isPublished="isPublished"
            :siteUrl="site.url"
            :lastSaved="lastSaved"
            :canUndo="canUndo"
            :canRedo="canRedo"
            :zoom="canvasZoom"
            :darkMode="darkMode"
            :sections="sections"
            @update:darkMode="darkMode = $event"
            @update:previewMode="previewMode = $event"
            @update:zoom="canvasZoom = $event"
            @preview="toggleFullPreview"
            @save="savePage"
            @publish="publishSite"
            @unpublish="unpublishSite"
            @undo="handleUndo"
            @redo="handleRedo"
            @showShortcuts="showKeyboardShortcuts = true"
            @restartTutorial="restartOnboarding"
            @openAI="showAIModal = true"
            @back="router.visit(route('growbuilder.index'))"
        />

        <!-- Main Content Area -->
        <div class="flex-1 flex overflow-hidden relative">
            <EditorLeftSidebar
                :siteName="site.name"
                :siteSubdomain="site.subdomain || ''"
                :pages="pages"
                :selectedSectionType="selectedSectionType"
                @toggle="leftSidebarOpen = false"
                @dragStart="onDragStart"
                @dragEnd="onDragEnd"
                @switchPage="switchPage"
                @createPage="openCreatePageModal"
                @editPage="openEditPageModal"
                @deletePage="deletePage"
                @applyTemplate="openApplyTemplateModal"
                @deleteSection="deleteSection"
                @update:sections="sections = $event"
                @openMediaLibrary="openMediaLibrary"
                @updateContent="updateSectionContent"
                @updateStyle="updateSectionStyle"
            />

            <!-- Mobile Backdrop Overlay -->
            <div
                v-if="leftSidebarOpen"
                @click="leftSidebarOpen = false"
                class="fixed inset-0 bg-black/50 z-20 md:hidden top-14"
            ></div>

            <!-- Toggle Left Sidebar (Hidden on mobile) -->
            <button
                @click="leftSidebarOpen = !leftSidebarOpen"
                class="hidden md:block absolute left-0 top-1/2 -translate-y-1/2 z-10 p-1 bg-white border border-gray-200 rounded-r-lg shadow-sm hover:bg-gray-50 transition-colors"
                :class="leftSidebarOpen ? 'ml-72' : 'ml-0'"
                aria-label="Toggle sidebar"
            >
                <ChevronLeftIcon :class="['w-3.5 h-3.5 text-gray-500 transition-transform', leftSidebarOpen ? '' : 'rotate-180']" aria-hidden="true" />
            </button>

            <!-- Mobile Floating Button to Open Sidebar -->
            <button
                v-if="!leftSidebarOpen"
                @click="leftSidebarOpen = true"
                class="md:hidden fixed bottom-6 left-6 z-30 w-14 h-14 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 transition-colors flex items-center justify-center"
                aria-label="Open editor tools"
            >
                <Squares2X2Icon class="w-6 h-6" aria-hidden="true" />
            </button>


            <!-- Canvas Area -->
            <main class="flex-1 overflow-y-auto p-2 md:p-6 relative" :class="[darkMode ? 'canvas-background-dark custom-scrollbar-dark' : 'canvas-background custom-scrollbar']" style="height: calc(100vh - 3.5rem); -webkit-overflow-scrolling: touch;">
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
                            @switchPage="switchPage"
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
                            :delay="isMobileDevice ? 300 : 0"
                            :delay-on-touch-only="true"
                            :touch-start-threshold="5"
                            @start="onDragStart"
                            @end="onDragEnd"
                            @change="onWidgetDrop"
                            :class="['min-h-[calc(100vh-8rem)]', isDragging && sections.length > 0 ? 'bg-blue-50/30' : '']"
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
        :siteId="site.id"
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
        :imageRequirements="mediaLibraryRequirements"
        :sectionType="mediaLibrarySectionType"
        :fieldName="mediaLibraryFieldName"
        @close="showMediaLibrary = false"
        @upload="uploadImage"
        @select="selectMediaImage"
        @selectCropped="handleCroppedImage"
        @selectStockPhoto="handleStockPhotoSelect"
        @delete="deleteMediaImage"
    />

    <AIAssistantModal
        :isOpen="showAIModal"
        :siteId="site.id"
        :siteName="site.name"
        :darkMode="darkMode"
        :aiContext="aiContext"
        :contextSummary="contextSummary"
        :smartSuggestions="smartSuggestions"
        :initialText="selectedSection?.content?.title || selectedSection?.content?.text"
        :aiUsage="aiUsage"
        :tierRestrictions="tierRestrictions"
        :hasAISectionGenerator="hasAISectionGenerator"
        :hasAISEO="hasAISEO"
        @close="showAIModal = false"
        @applyContent="handleAIContent"
        @applyColors="handleAIColors"
        @applyStyle="handleAIStyle"
        @addSection="handleAIAddSection"
        @updateNavigation="handleAINavigation"
        @updateFooter="handleAIFooter"
        @createPage="handleAICreatePage"
        @createAIPage="handleAIGeneratedPage"
        @updateUsage="handleAIUsageUpdate"
    />

    <!-- AI Floating Button -->
    <AIFloatingButton
        :isOpen="showAIModal"
        :darkMode="darkMode"
        :currentSection="selectedSection ? { type: selectedSection.type } : null"
        :currentPage="activePage ? { title: activePage.title } : null"
        :aiUsage="aiUsage"
        :canUseAI="canUseAI"
        @toggle="showAIModal = !showAIModal"
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

    <!-- Onboarding Tutorial -->
    <OnboardingTutorial
        :show="showOnboarding"
        :siteName="site.name"
        @close="closeOnboarding"
        @complete="completeOnboarding"
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
