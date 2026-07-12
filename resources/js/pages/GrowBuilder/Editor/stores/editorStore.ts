import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { Page, Section, Site, NavigationSettings, FooterSettings, NavItem } from '../types';

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

export const useEditorStore = defineStore('editor-editor', () => {
    // ============================================
    // UI State
    // ============================================
    const leftSidebarOpen = ref(true);
    const previewMode = ref<'desktop' | 'tablet' | 'mobile'>('desktop');
    const selectedSectionId = ref<string | null>(null);
    const saving = ref(false);
    const publishing = ref(false);
    const isPublished = ref(false);
    const isDragging = ref(false);
    const activeInspectorTab = ref<'content' | 'style' | 'advanced'>('content');
    const hoveredSectionId = ref<string | null>(null);
    const activeLeftTab = ref<'pages' | 'widgets' | 'inspector'>('widgets');
    const showNavSettings = ref(false);
    const showFooterSettings = ref(false);
    const showKeyboardShortcuts = ref(false);
    const darkMode = ref(false);
    const canvasZoom = ref(100);
    const lastSaved = ref<Date | null>(null);

    // Preview
    const isFullPreview = ref(false);
    const isIframePreview = ref(false);
    const previewWidth = ref(1024);
    const isResizingPreview = ref(false);
    const iframeKey = ref(0);

    // Onboarding
    const showOnboarding = ref(false);

    // ============================================
    // Page & Site State
    // ============================================
    const activePage = ref<Page | null>(null);
    const sections = ref<Section[]>([]);
    const pageTitle = ref('');
    const siteNavigation = ref<NavigationSettings>({
        logoText: '',
        logo: '',
        navItems: [],
        showCta: true,
        ctaText: 'Contact Us',
        ctaLink: '#contact',
        sticky: true,
        style: 'default',
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
        copyrightText: '',
        showSocialLinks: true,
        socialLinks: [],
        columns: [],
        showNewsletter: false,
        newsletterTitle: 'Subscribe to our newsletter',
        backgroundColor: '#1f2937',
        textColor: '#ffffff',
        layout: 'columns',
    });

    // Section resize state
    const resizingSection = ref<string | null>(null);
    const resizeStartY = ref(0);
    const resizeStartHeight = ref(0);

    // Context menu
    const contextMenu = ref({
        visible: false,
        x: 0,
        y: 0,
        sectionId: null as string | null,
        sectionType: undefined as string | undefined,
    });

    // ============================================
    // Computed
    // ============================================
    const selectedSection = computed(() => {
        if (!selectedSectionId.value) return null;
        return sections.value.find(s => s.id === selectedSectionId.value) || null;
    });

    const canvasWidth = computed(() => `max-w-[${previewWidth.value}px]`);

    const canvasTransform = computed(() => {
        if (canvasZoom.value === 100) return '';
        return `scale(${canvasZoom.value / 100})`;
    });

    const canvasTransformOrigin = computed(() => 'top center');

    const isMobilePreview = computed(() => previewWidth.value < 768);

    const textSize = computed(() =>
        isMobilePreview.value
            ? { h1: 'text-2xl', h2: 'text-xl', h3: 'text-lg', p: 'text-sm' }
            : { h1: 'text-4xl', h2: 'text-3xl', h3: 'text-xl', p: 'text-base' }
    );

    const spacing = computed(() =>
        isMobilePreview.value
            ? { section: 'py-10 px-4', gap: 'gap-4' }
            : { section: 'py-16 px-6', gap: 'gap-6' }
    );

    const gridCols2 = computed(() => isMobilePreview.value ? 'grid-cols-1' : 'grid-cols-1 md:grid-cols-2');
    const gridCols3 = computed(() => isMobilePreview.value ? 'grid-cols-1' : 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3');
    const gridCols4 = computed(() => isMobilePreview.value ? 'grid-cols-2' : 'grid-cols-2 md:grid-cols-4');

    // ============================================
    // Initialization
    // ============================================
    function initialize(
        site: Site,
        pages: Page[],
        currentPage?: Page,
    ) {
        activePage.value = currentPage || pages[0] || null;

        siteNavigation.value = {
            logoText: site.name,
            logo: site.settings?.navigation?.logo || '',
            navItems: [],
            showCta: true,
            ctaText: 'Contact Us',
            ctaLink: '#contact',
            sticky: true,
            style: 'default',
            showAuthButtons: false,
            showLoginButton: true,
            showRegisterButton: true,
            loginText: 'Login',
            registerText: 'Sign Up',
            loginStyle: 'link',
            registerStyle: 'solid',
        };

        siteFooter.value = {
            logo: '',
            copyrightText: `© ${new Date().getFullYear()} ${site.name}. All rights reserved.`,
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
        };

        isPublished.value = site.status === 'published';
        initializeSections();
        initializeSiteNavigation(site, pages);
        initializeSiteFooter(site);
    }

    function initializeSections() {
        if (activePage.value?.content?.sections) {
            sections.value = activePage.value.content.sections.map((s: any, i: number) => ({
                ...s,
                id: s.id || `section-${Date.now()}-${i}`,
                style: s.style || {},
            }));
        } else {
            sections.value = [];
        }
        pageTitle.value = activePage.value?.title || 'New Page';
    }

    function initializeSiteNavigation(site: Site, pages: Page[]) {
        if (site.settings?.navigation) {
            const navSettings = { ...siteNavigation.value, ...site.settings.navigation };
            if (navSettings.navItems && navSettings.navItems.length > 0) {
                navSettings.navItems = navSettings.navItems.map((navItem: NavItem) => {
                    const matchingPage = pages.find(p => {
                        const pageUrl = p.isHomepage ? '/' : `/${p.slug}`;
                        return pageUrl === navItem.url || p.title === navItem.label;
                    });
                    return { ...navItem, pageId: matchingPage?.id || navItem.pageId };
                });
            } else {
                navSettings.navItems = pages
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
            siteNavigation.value = navSettings;
        } else {
            siteNavigation.value.navItems = pages
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
    }

    function initializeSiteFooter(site: Site) {
        if (site.settings?.footer) {
            const footerSettings = { ...site.settings.footer };
            if (footerSettings.columns && Array.isArray(footerSettings.columns)) {
                footerSettings.columns = footerSettings.columns.map((column: any, colIndex: number) => ({
                    ...column,
                    id: column.id || `footer-col-${colIndex}`,
                    links: column.links?.map((link: any, linkIndex: number) => ({
                        ...link,
                        id: link.id || `footer-link-${colIndex}-${linkIndex}`,
                    })) || [],
                }));
            }
            siteFooter.value = { ...siteFooter.value, ...footerSettings };
        }
    }

    // ============================================
    // Section Actions
    // ============================================
    function selectSection(id: string) {
        selectedSectionId.value = id;
        activeLeftTab.value = 'inspector';
        activeInspectorTab.value = 'content';
        showNavSettings.value = false;
        showFooterSettings.value = false;
    }

    function addSection(type: string) {
        const newSection: Section = {
            id: `section-${Date.now()}`,
            type: type as any,
            content: getDefaultContent(type),
            style: { backgroundColor: '#ffffff', textColor: '#111827' },
        };
        sections.value.push(newSection);
        selectedSectionId.value = newSection.id;
    }

    function duplicateSection(id: string) {
        const index = sections.value.findIndex(s => s.id === id);
        if (index === -1) return;
        const original = sections.value[index];
        const duplicate: Section = {
            ...JSON.parse(JSON.stringify(original)),
            id: `section-${Date.now()}`,
        };
        sections.value.splice(index + 1, 0, duplicate);
        selectedSectionId.value = duplicate.id;
    }

    function deleteSection(id: string) {
        const index = sections.value.findIndex(s => s.id === id);
        if (index === -1) return;
        sections.value.splice(index, 1);
        if (selectedSectionId.value === id) selectedSectionId.value = null;
    }

    function moveSection(id: string, direction: 'up' | 'down') {
        const index = sections.value.findIndex(s => s.id === id);
        if (index === -1) return;
        const newIndex = direction === 'up' ? index - 1 : index + 1;
        if (newIndex < 0 || newIndex >= sections.value.length) return;
        const [section] = sections.value.splice(index, 1);
        sections.value.splice(newIndex, 0, section);
    }

    function updateSectionContent(key: string, value: any) {
        if (!selectedSection.value) return;
        selectedSection.value.content[key] = value;
    }

    function updateSectionStyle(key: string, value: any) {
        if (!selectedSection.value) return;
        if (!selectedSection.value.style) {
            selectedSection.value.style = {};
        }
        if (selectedSection.value.style[key] !== value) {
            selectedSection.value.style[key] = value;
        }
    }

    // ============================================
    // Section Resize
    // ============================================
    function startResize(e: MouseEvent, sectionId: string) {
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
    }

    function handleResize(e: MouseEvent) {
        if (!resizingSection.value) return;
        const deltaY = e.clientY - resizeStartY.value;
        const newHeight = Math.max(100, resizeStartHeight.value + deltaY);
        const section = sections.value.find(s => s.id === resizingSection.value);
        if (section) {
            if (!section.style) section.style = {};
            section.style.minHeight = newHeight;
        }
    }

    function stopResize() {
        resizingSection.value = null;
        document.removeEventListener('mousemove', handleResize);
        document.removeEventListener('mouseup', stopResize);
        document.body.style.cursor = '';
        document.body.style.userSelect = '';
    }

    // ============================================
    // Context Menu
    // ============================================
    function showContextMenu(e: MouseEvent, sectionId: string | null = null) {
        e.preventDefault();
        const section = sectionId ? sections.value.find(s => s.id === sectionId) : null;
        contextMenu.value = {
            visible: true,
            x: e.clientX,
            y: e.clientY,
            sectionId,
            sectionType: section?.type,
        };
    }

    function closeContextMenu() {
        contextMenu.value.visible = false;
    }

    // ============================================
    // Items Management
    // ============================================
    function addItem() {
        if (!selectedSection.value) return;
        const type = selectedSection.value.type;
        if (!selectedSection.value.content.items) selectedSection.value.content.items = [];
        if (type === 'services' || type === 'features') {
            selectedSection.value.content.items.push({ title: 'New Item', description: 'Description' });
        } else if (type === 'testimonials') {
            selectedSection.value.content.items.push({ name: 'Customer Name', text: 'Testimonial text', role: 'Role' });
        }
    }

    function removeItem(index: number) {
        if (!selectedSection.value?.content.items) return;
        selectedSection.value.content.items.splice(index, 1);
    }

    function addPlan() {
        if (!selectedSection.value) return;
        if (!selectedSection.value.content.plans) selectedSection.value.content.plans = [];
        selectedSection.value.content.plans.push({ name: 'New Plan', price: 'K0', features: ['Feature 1'] });
    }

    function removePlan(index: number) {
        if (!selectedSection.value?.content.plans) return;
        selectedSection.value.content.plans.splice(index, 1);
    }

    function addPlanFeature(planIndex: number) {
        if (!selectedSection.value?.content.plans?.[planIndex]) return;
        if (!selectedSection.value.content.plans[planIndex].features) selectedSection.value.content.plans[planIndex].features = [];
        selectedSection.value.content.plans[planIndex].features.push('New Feature');
    }

    function addFaqItem() {
        if (!selectedSection.value) return;
        if (!selectedSection.value.content.items) selectedSection.value.content.items = [];
        selectedSection.value.content.items.push({ question: 'New Question?', answer: 'Answer here...' });
    }

    function removeFaqItem(index: number) {
        if (!selectedSection.value?.content.items) return;
        selectedSection.value.content.items.splice(index, 1);
    }

    function addTeamMember() {
        if (!selectedSection.value) return;
        if (!selectedSection.value.content.items) selectedSection.value.content.items = [];
        selectedSection.value.content.items.push({ name: 'New Member', role: 'Role', image: '', bio: '' });
    }

    function removeTeamMember(index: number) {
        if (!selectedSection.value?.content.items) return;
        selectedSection.value.content.items.splice(index, 1);
    }

    function addBlogPost() {
        if (!selectedSection.value) return;
        if (!selectedSection.value.content.posts) selectedSection.value.content.posts = [];
        selectedSection.value.content.posts.push({ title: 'New Post', excerpt: 'Post excerpt...', date: new Date().toISOString().split('T')[0], image: '' });
    }

    function removeBlogPost(index: number) {
        if (!selectedSection.value?.content.posts) return;
        selectedSection.value.content.posts.splice(index, 1);
    }

    function addStatItem() {
        if (!selectedSection.value) return;
        if (!selectedSection.value.content.items) selectedSection.value.content.items = [];
        selectedSection.value.content.items.push({ value: '100+', label: 'New Stat' });
    }

    function removeStatItem(index: number) {
        if (!selectedSection.value?.content.items) return;
        selectedSection.value.content.items.splice(index, 1);
    }

    function removeGalleryImage(sectionId: string, imageIndex: number) {
        const section = sections.value.find(s => s.id === sectionId);
        if (section?.content.images) section.content.images.splice(imageIndex, 1);
    }

    // ============================================
    // Preview
    // ============================================
    function setPreviewMode(mode: 'desktop' | 'tablet' | 'mobile') {
        previewMode.value = mode;
        if (mode === 'mobile') previewWidth.value = 375;
        else if (mode === 'tablet') previewWidth.value = 768;
        else if (mode === 'desktop') previewWidth.value = 1024;
    }

    function switchPage(page: Page) {
        activePage.value = page;
        initializeSections();
        selectedSectionId.value = null;
    }

    return {
        // State
        leftSidebarOpen, previewMode, selectedSectionId, saving, publishing, isPublished,
        isDragging, activeInspectorTab, hoveredSectionId, activeLeftTab,
        showNavSettings, showFooterSettings, showKeyboardShortcuts, darkMode, canvasZoom, lastSaved,
        isFullPreview, isIframePreview, previewWidth, isResizingPreview, iframeKey,
        showOnboarding,
        activePage, sections, pageTitle, siteNavigation, siteFooter,
        resizingSection, contextMenu,

        // Computed
        selectedSection, canvasWidth, canvasTransform, canvasTransformOrigin,
        isMobilePreview, textSize, spacing, gridCols2, gridCols3, gridCols4,

        // Init
        initialize, initializeSections, initializeSiteNavigation, initializeSiteFooter,

        // Section CRUD
        selectSection, addSection, duplicateSection, deleteSection, moveSection,
        updateSectionContent, updateSectionStyle,

        // Resize
        startResize, handleResize, stopResize,

        // Context menu
        showContextMenu, closeContextMenu,

        // Items
        addItem, removeItem, addPlan, removePlan, addPlanFeature,
        addFaqItem, removeFaqItem,
        addTeamMember, removeTeamMember,
        addBlogPost, removeBlogPost,
        addStatItem, removeStatItem,
        removeGalleryImage,

        // Preview
        setPreviewMode, switchPage,
    };
});
