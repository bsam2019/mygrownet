/**
 * Inline Edit Composable
 * Handles inline editing of section content
 */

import { ref, nextTick, type Ref } from 'vue';
import type { Section, EditingField } from '../types';

export interface UseInlineEditOptions {
    sections: Ref<Section[]>;
}

export function useInlineEdit(options: UseInlineEditOptions) {
    const { sections } = options;

    // ============================================
    // State
    // ============================================
    const editingField = ref<EditingField | null>(null);
    const editingValue = ref<string>('');

    // ============================================
    // Functions
    // ============================================
    const startInlineEdit = (
        sectionId: string,
        field: string,
        currentValue: string,
        itemIndex?: number
    ) => {
        editingField.value = { sectionId, field, itemIndex };
        editingValue.value = currentValue || '';
        
        nextTick(() => {
            const input = document.querySelector('.inline-edit-input') as HTMLInputElement | HTMLTextAreaElement;
            if (input) {
                input.focus();
                input.select();
            }
        });
    };

    const saveInlineEdit = () => {
        if (!editingField.value) return;
        
        const section = sections.value.find(s => s.id === editingField.value?.sectionId);
        if (!section) return;

        const { field, itemIndex } = editingField.value;

        if (itemIndex !== undefined && section.content.items) {
            // Editing an item in an array (services, features, testimonials, etc.)
            const [itemField] = field.split('.');
            if (section.content.items[itemIndex]) {
                section.content.items[itemIndex][itemField] = editingValue.value;
            }
        } else if (field.startsWith('plans.')) {
            // Editing pricing plans
            const parts = field.split('.');
            const planIndex = parseInt(parts[1]);
            const planField = parts[2];
            if (section.content.plans?.[planIndex]) {
                section.content.plans[planIndex][planField] = editingValue.value;
            }
        } else if (field.startsWith('navItems.')) {
            // Editing navigation items
            const parts = field.split('.');
            const navIndex = parseInt(parts[1]);
            const navField = parts[2];
            if (section.content.navItems?.[navIndex]) {
                section.content.navItems[navIndex][navField] = editingValue.value;
            }
        } else {
            // Direct field edit
            section.content[field] = editingValue.value;
        }

        cancelInlineEdit();
    };

    const cancelInlineEdit = () => {
        editingField.value = null;
        editingValue.value = '';
    };

    const isEditing = (sectionId: string, field: string, itemIndex?: number): boolean => {
        if (!editingField.value) return false;
        return (
            editingField.value.sectionId === sectionId &&
            editingField.value.field === field &&
            editingField.value.itemIndex === itemIndex
        );
    };

    const handleInlineKeydown = (e: KeyboardEvent) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            saveInlineEdit();
        } else if (e.key === 'Escape') {
            cancelInlineEdit();
        }
    };

    return {
        // State
        editingField,
        editingValue,

        // Functions
        startInlineEdit,
        saveInlineEdit,
        cancelInlineEdit,
        isEditing,
        handleInlineKeydown,
    };
}
