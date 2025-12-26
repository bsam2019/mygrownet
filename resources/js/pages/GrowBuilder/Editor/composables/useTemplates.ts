/**
 * Templates Composable
 * Handles applying templates to pages
 */

import { ref, type Ref } from 'vue';
import type { Page, Section } from '../types';
import { findTemplate } from '../config/pageTemplates';

export interface UseTemplatesOptions {
    activePage: Ref<Page | null>;
    sections: Ref<Section[]>;
    pageTitle: Ref<string>;
    savePage: () => Promise<void>;
}

export function useTemplates(options: UseTemplatesOptions) {
    const { activePage, sections, pageTitle, savePage } = options;

    // State
    const showApplyTemplateModal = ref(false);
    const applyingTemplate = ref(false);

    // Open apply template modal
    const openApplyTemplateModal = () => {
        showApplyTemplateModal.value = true;
    };

    // Apply template to current page
    const applyTemplate = async (templateId: string) => {
        if (!activePage.value) return;

        const template = findTemplate(templateId);
        if (!template) return;

        applyingTemplate.value = true;

        // Generate new sections from template
        const newSections = template.sections.map((s, i) => ({
            id: `section-${Date.now()}-${i}`,
            type: s.type,
            content: { ...s.content },
            style: { ...s.style },
        }));

        // Replace current sections with template sections
        sections.value = newSections as Section[];

        // Keep original title for homepage
        if (template.isHomepage && activePage.value.isHomepage) {
            pageTitle.value = activePage.value.title;
        }

        showApplyTemplateModal.value = false;
        applyingTemplate.value = false;

        // Auto-save after applying template
        await savePage();
    };

    return {
        // State
        showApplyTemplateModal,
        applyingTemplate,

        // Actions
        openApplyTemplateModal,
        applyTemplate,
    };
}
