import { defineStore } from 'pinia';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import type { Page, NewPageForm } from '../types';
import { findTemplate } from '../config/pageTemplates';
import { useEditorStore } from './editorStore';

export const usePageStore = defineStore('editor-pages', () => {
    const showCreatePageModal = ref(false);
    const showEditPageModal = ref(false);
    const editingPage = ref<Page | null>(null);
    const creatingPage = ref(false);
    const pageError = ref<string | null>(null);
    const saving = ref(false);
    const showApplyTemplateModal = ref(false);
    const applyingTemplate = ref(false);

    // Modal actions
    function openCreatePageModal() {
        pageError.value = null;
        showCreatePageModal.value = true;
    }

    function closeCreatePageModal() {
        showCreatePageModal.value = false;
        pageError.value = null;
    }

    const createPage = async (form: NewPageForm, siteId: number, pages: Page[]) => {
        if (!form.title) {
            pageError.value = 'Please enter a page title';
            return;
        }
        const slug = form.slug || form.title.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
        const existingPage = pages.find(p => p.slug === slug);
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
            const response = await axios.post(`/growbuilder/editor/${siteId}/pages`, {
                title: form.title,
                slug,
                sections: templateSections,
                show_in_nav: form.showInNav,
                is_homepage: false,
            });

            const editor = useEditorStore();
            if (form.showInNav && response.data.page?.id) {
                editor.siteNavigation.navItems.push({
                    id: `nav-${response.data.page.id}`,
                    label: form.title,
                    url: `/${slug}`,
                    pageId: response.data.page.id,
                    isExternal: false,
                    children: [],
                });
                await axios.post(`/growbuilder/editor/${siteId}/settings`, {
                    navigation: editor.siteNavigation,
                    footer: editor.siteFooter,
                });
            }

            showCreatePageModal.value = false;
            if (response.data.page?.id) {
                router.visit(`/growbuilder/editor/${siteId}?page=${response.data.page.id}`);
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

    const closeEditPageModal = () => {
        showEditPageModal.value = false;
        editingPage.value = null;
    };

    const updatePage = async (page: Page, siteId: number, pages: Page[]) => {
        try {
            const originalPage = pages.find(p => p.id === page.id);
            const showInNavChanged = originalPage?.showInNav !== page.showInNav;

            await axios.put(`/growbuilder/editor/${siteId}/pages/${page.id}`, {
                title: page.title,
                slug: page.slug,
                show_in_nav: page.showInNav,
            });

            const editor = useEditorStore();
            if (showInNavChanged) {
                const navIndex = editor.siteNavigation.navItems.findIndex(item => item.pageId === page.id);
                if (page.showInNav && navIndex === -1) {
                    editor.siteNavigation.navItems.push({
                        id: `nav-${page.id}`,
                        label: page.title,
                        url: page.isHomepage ? '/' : `/${page.slug}`,
                        pageId: page.id,
                        isExternal: false,
                        children: [],
                    });
                } else if (!page.showInNav && navIndex !== -1) {
                    editor.siteNavigation.navItems.splice(navIndex, 1);
                }
                await axios.post(`/growbuilder/editor/${siteId}/settings`, {
                    navigation: editor.siteNavigation,
                    footer: editor.siteFooter,
                });
            }

            showEditPageModal.value = false;
            editingPage.value = null;
            router.reload({ only: ['pages'] });
        } catch (error) {
            console.error('Failed to update page:', error);
        }
    };

    const deletePage = async (pageId: number, siteId: number, pages: Page[]) => {
        const page = pages.find(p => p.id === pageId);
        if (!page || page.isHomepage) return;
        if (!confirm(`Delete "${page.title}"? This cannot be undone.`)) return;

        try {
            await axios.delete(`/growbuilder/editor/${siteId}/pages/${pageId}`);

            const editor = useEditorStore();
            const navIndex = editor.siteNavigation.navItems.findIndex(item => item.pageId === pageId);
            if (navIndex !== -1) {
                editor.siteNavigation.navItems.splice(navIndex, 1);
                await axios.post(`/growbuilder/editor/${siteId}/settings`, {
                    navigation: editor.siteNavigation,
                    footer: editor.siteFooter,
                });
            }

            router.reload({ only: ['pages'] });

            if (editor.activePage?.id === pageId) {
                const homepage = pages.find(p => p.isHomepage);
                if (homepage) router.get(`/growbuilder/editor/${siteId}`, { page: homepage.id });
            }
        } catch (error) {
            console.error('Failed to delete page:', error);
        }
    };

    const savePage = async (siteId: number, silent = false) => {
        const editor = useEditorStore();
        if (!editor.activePage) return false;
        saving.value = true;
        try {
            await axios.post(`/growbuilder/editor/${siteId}/pages/${editor.activePage.id}/save`, {
                title: editor.pageTitle,
                content: { sections: editor.sections },
            });
            await axios.post(`/growbuilder/editor/${siteId}/settings`, {
                navigation: editor.siteNavigation,
                footer: editor.siteFooter,
            });
            editor.lastSaved = new Date();
            return true;
        } catch (error) {
            console.error('Save failed:', error);
            throw error;
        } finally {
            saving.value = false;
        }
    };

    const switchPage = (pageOrId: Page | number, siteId: number, pages: Page[]) => {
        const editor = useEditorStore();
        const targetPage = typeof pageOrId === 'number'
            ? pages.find(p => p.id === pageOrId)
            : pageOrId;

        if (!targetPage) return;
        if (targetPage.id === editor.activePage?.id) return;

        if (typeof pageOrId === 'number') {
            editor.switchPage(targetPage);
            return;
        }
        router.visit(`/growbuilder/editor/${siteId}?page=${targetPage.id}`);
    };

    const openApplyTemplateModal = () => {
        showApplyTemplateModal.value = true;
    };

    const closeApplyTemplateModal = () => {
        showApplyTemplateModal.value = false;
    };

    const applyTemplate = async (templateId: string, siteId: number) => {
        const editor = useEditorStore();
        if (!editor.activePage) return;

        const template = findTemplate(templateId);
        if (!template) return;

        applyingTemplate.value = true;
        const newSections = template.sections.map((s, i) => ({
            id: `section-${Date.now()}-${i}`,
            type: s.type,
            content: { ...s.content },
            style: { ...s.style },
        }));
        editor.sections = newSections as any;
        showApplyTemplateModal.value = false;
        applyingTemplate.value = false;
        await savePage(siteId, true);
    };

    return {
        showCreatePageModal, showEditPageModal, editingPage, creatingPage, pageError,
        saving, showApplyTemplateModal, applyingTemplate,
        openCreatePageModal, closeCreatePageModal, createPage,
        openEditPageModal, closeEditPageModal, updatePage, deletePage,
        savePage, switchPage,
        openApplyTemplateModal, closeApplyTemplateModal, applyTemplate,
    };
});
