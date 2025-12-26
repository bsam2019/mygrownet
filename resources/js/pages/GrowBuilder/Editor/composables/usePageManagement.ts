/**
 * Page Management Composable
 * Handles page CRUD operations
 */

import { ref, type Ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import type { Page, Section, NavigationSettings, FooterSettings, NewPageForm } from '../types';
import { findTemplate } from '../config/pageTemplates';

export interface UsePageManagementOptions {
    siteId: number;
    pages: Page[];
    activePage: Ref<Page | null>;
    sections: Ref<Section[]>;
    pageTitle: Ref<string>;
    siteNavigation: Ref<NavigationSettings>;
    siteFooter: Ref<FooterSettings>;
}

export function usePageManagement(options: UsePageManagementOptions) {
    const { siteId, pages, activePage, sections, pageTitle, siteNavigation, siteFooter } = options;

    // State
    const showCreatePageModal = ref(false);
    const showEditPageModal = ref(false);
    const editingPage = ref<Page | null>(null);
    const creatingPage = ref(false);
    const pageError = ref<string | null>(null);
    const saving = ref(false);

    // Open create page modal
    const openCreatePageModal = () => {
        pageError.value = null;
        showCreatePageModal.value = true;
    };

    // Create page
    const createPage = async (form: NewPageForm) => {
        if (!form.title) {
            pageError.value = 'Please enter a page title';
            return;
        }

        const slug = form.slug || form.title.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');

        // Check if slug already exists
        const existingPage = pages.find(p => p.slug === slug);
        if (existingPage) {
            pageError.value = `A page with the URL "/${slug}" already exists ("${existingPage.title}"). Please choose a different title or URL slug.`;
            return;
        }

        creatingPage.value = true;
        pageError.value = null;

        // Get template sections
        const template = findTemplate(form.templateId);
        const templateSections = template?.sections.map((s, i) => ({
            id: `section-${Date.now()}-${i}`,
            type: s.type,
            content: { ...s.content },
            style: { ...s.style },
        })) || [];

        try {
            const response = await axios.post(`/growbuilder/editor/${siteId}/pages`, {
                title: form.title,
                slug: slug,
                sections: templateSections,
                show_in_nav: form.showInNav,
                is_homepage: false,
            });

            // Add to navigation if showInNav is true
            if (form.showInNav && response.data.page?.id) {
                siteNavigation.value.navItems.push({
                    id: `nav-${response.data.page.id}`,
                    label: form.title,
                    url: `/${slug}`,
                    pageId: response.data.page.id,
                    isExternal: false,
                    children: [],
                });
                // Save navigation settings
                await axios.post(`/growbuilder/editor/${siteId}/settings`, {
                    navigation: siteNavigation.value,
                    footer: siteFooter.value,
                });
            }

            showCreatePageModal.value = false;

            // Switch to the new page
            if (response.data.page?.id) {
                router.visit(`/growbuilder/editor/${siteId}?page=${response.data.page.id}`);
            } else {
                router.reload();
            }
        } catch (error: any) {
            console.error('Failed to create page:', error);
            pageError.value = error.response?.data?.error || error.response?.data?.message || 'Failed to create page. Please try again.';
        } finally {
            creatingPage.value = false;
        }
    };

    // Open edit page modal
    const openEditPageModal = (page: Page) => {
        editingPage.value = { ...page };
        showEditPageModal.value = true;
    };

    // Update page
    const updatePage = async (page: Page) => {
        try {
            // Get original page to check if showInNav changed
            const originalPage = pages.find(p => p.id === page.id);
            const showInNavChanged = originalPage?.showInNav !== page.showInNav;

            await axios.put(`/growbuilder/editor/${siteId}/pages/${page.id}`, {
                title: page.title,
                slug: page.slug,
                show_in_nav: page.showInNav,
            });

            // Sync navigation if showInNav changed
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

                await axios.post(`/growbuilder/editor/${siteId}/settings`, {
                    navigation: siteNavigation.value,
                    footer: siteFooter.value,
                });
            } else if (siteNavigation.value.navItems.some(item => item.pageId === page.id)) {
                const navItem = siteNavigation.value.navItems.find(item => item.pageId === page.id);
                if (navItem) {
                    navItem.label = page.title;
                    navItem.url = page.isHomepage ? '/' : `/${page.slug}`;

                    await axios.post(`/growbuilder/editor/${siteId}/settings`, {
                        navigation: siteNavigation.value,
                        footer: siteFooter.value,
                    });
                }
            }

            showEditPageModal.value = false;
            editingPage.value = null;
            router.reload({ only: ['pages'] });
        } catch (error) {
            console.error('Failed to update page:', error);
        }
    };

    // Delete page
    const deletePage = async (pageId: number) => {
        const page = pages.find(p => p.id === pageId);
        if (!page || page.isHomepage) return;

        if (!confirm(`Delete "${page.title}"? This cannot be undone.`)) return;

        try {
            await axios.delete(`/growbuilder/editor/${siteId}/pages/${pageId}`);

            // Remove from navigation
            const navIndex = siteNavigation.value.navItems.findIndex(item => item.pageId === pageId);
            if (navIndex !== -1) {
                siteNavigation.value.navItems.splice(navIndex, 1);
                await axios.post(`/growbuilder/editor/${siteId}/settings`, {
                    navigation: siteNavigation.value,
                    footer: siteFooter.value,
                });
            }

            router.reload({ only: ['pages'] });

            // If we deleted the active page, switch to homepage
            if (activePage.value?.id === pageId) {
                const homepage = pages.find(p => p.isHomepage);
                if (homepage) {
                    router.get(`/growbuilder/editor/${siteId}`, { page: homepage.id });
                }
            }
        } catch (error) {
            console.error('Failed to delete page:', error);
        }
    };

    // Save page
    const savePage = async () => {
        if (!activePage.value) return;
        saving.value = true;
        try {
            await axios.post(`/growbuilder/editor/${siteId}/pages/${activePage.value.id}/save`, {
                title: pageTitle.value,
                content: { sections: sections.value },
            });

            await axios.post(`/growbuilder/editor/${siteId}/settings`, {
                navigation: siteNavigation.value,
                footer: siteFooter.value,
            });
        } catch (error) {
            console.error('Save failed:', error);
        } finally {
            saving.value = false;
        }
    };

    // Switch page
    const switchPage = (page: Page) => {
        if (page.id === activePage.value?.id) return;
        router.visit(`/growbuilder/editor/${siteId}?page=${page.id}`);
    };

    return {
        // State
        showCreatePageModal,
        showEditPageModal,
        editingPage,
        creatingPage,
        pageError,
        saving,

        // Actions
        openCreatePageModal,
        createPage,
        openEditPageModal,
        updatePage,
        deletePage,
        savePage,
        switchPage,
    };
}
