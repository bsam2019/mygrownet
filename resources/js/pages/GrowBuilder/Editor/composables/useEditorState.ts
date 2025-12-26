/**
 * Editor State Composable
 * Centralized state management for the GrowBuilder editor
 */

import { ref, computed, watch, type Ref, type ComputedRef } from 'vue';
import type {
    Section,
    Page,
    Site,
    PreviewMode,
    InspectorTab,
    LeftSidebarTab,
    EditingField,
    DraggingElement,
    NavigationSettings,
    FooterSettings,
    NavItem,
} from '../types';

export interface EditorState {
    // UI State
    leftSidebarOpen: Ref<boolean>;
    rightSidebarOpen: Ref<boolean>;
    previewMode: Ref<PreviewMode>;
    activeInspectorTab: Ref<InspectorTab>;
    activeLeftTab: Ref<LeftSidebarTab>;
    showAddSectionPanel: Ref<boolean>;
    isDragging: Ref<boolean>;
    saving: Ref<boolean>;
    
    // Selection State
    selectedSectionId: Ref<string | null>;
    hoveredSectionId: Ref<string | null>;
    showNavSettings: Ref<boolean>;
    showFooterSettings: Ref<boolean>;
    
    // Page State
    activePage: Ref<Page | null>;
    sections: Ref<Section[]>;
    pageTitle: Ref<string>;
    
    // Site-wide Settings
    siteNavigation: Ref<NavigationSettings>;
    siteFooter: Ref<FooterSettings>;
    
    // Computed
    selectedSection: ComputedRef<Section | null>;
    isMobilePreview: ComputedRef<boolean>;
    canvasWidth: ComputedRef<string>;
}

export function useEditorState(site: Site, pages: Page[], currentPage?: Page): EditorState {
    // ============================================
    // UI State
    // ============================================
    const leftSidebarOpen = ref(true);
    const rightSidebarOpen = ref(true);
    const previewMode = ref<PreviewMode>('desktop');
    const activeInspectorTab = ref<InspectorTab>('content');
    const activeLeftTab = ref<LeftSidebarTab>('widgets');
    const showAddSectionPanel = ref(false);
    const isDragging = ref(false);
    const saving = ref(false);

    // ============================================
    // Selection State
    // ============================================
    const selectedSectionId = ref<string | null>(null);
    const hoveredSectionId = ref<string | null>(null);
    const showNavSettings = ref(false);
    const showFooterSettings = ref(false);

    // ============================================
    // Page State
    // ============================================
    const activePage = ref<Page | null>(currentPage || pages[0] || null);
    const sections = ref<Section[]>([]);
    const pageTitle = ref('');

    // ============================================
    // Site-wide Settings
    // ============================================
    const siteNavigation = ref<NavigationSettings>({
        logoText: site.name,
        logo: '',
        navItems: [],
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
        if (site.settings?.navigation) {
            siteNavigation.value = {
                ...siteNavigation.value,
                ...site.settings.navigation,
            };
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
    };

    const initializeSiteFooter = () => {
        if (site.settings?.footer) {
            siteFooter.value = {
                ...siteFooter.value,
                ...site.settings.footer,
            };
        }
    };

    // Initialize on creation
    initializeSections();
    initializeSiteNavigation();
    initializeSiteFooter();

    // ============================================
    // Computed Properties
    // ============================================
    const selectedSection = computed(() => {
        if (!selectedSectionId.value) return null;
        return sections.value.find(s => s.id === selectedSectionId.value) || null;
    });

    const isMobilePreview = computed(() => previewMode.value === 'mobile');
    
    const canvasWidth = computed(() => 
        previewMode.value === 'mobile' ? 'max-w-sm' : 'max-w-4xl'
    );

    return {
        // UI State
        leftSidebarOpen,
        rightSidebarOpen,
        previewMode,
        activeInspectorTab,
        activeLeftTab,
        showAddSectionPanel,
        isDragging,
        saving,
        
        // Selection State
        selectedSectionId,
        hoveredSectionId,
        showNavSettings,
        showFooterSettings,
        
        // Page State
        activePage,
        sections,
        pageTitle,
        
        // Site-wide Settings
        siteNavigation,
        siteFooter,
        
        // Computed
        selectedSection,
        isMobilePreview,
        canvasWidth,
    };
}
