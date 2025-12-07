import { ref, computed, watch } from 'vue';

export interface WidgetConfig {
    id: string;
    type: 'stats' | 'activity' | 'quick-actions' | 'ai-credits' | 'upcoming-posts' | 'suggestions' | 'recent-posts' | 'performance-cta' | 'chart';
    title: string;
    visible: boolean;
    order: number;
    size: 'small' | 'medium' | 'large' | 'full';
    column?: 'main' | 'sidebar';
}

export interface DashboardLayout {
    widgets: WidgetConfig[];
    columns: number;
    compactMode: boolean;
}

const STORAGE_KEY = 'bizboost-dashboard-layout';

// Default widget configuration
const defaultWidgets: WidgetConfig[] = [
    { id: 'stats', type: 'stats', title: 'Statistics', visible: true, order: 0, size: 'full', column: 'main' },
    { id: 'quick-actions', type: 'quick-actions', title: 'Quick Actions', visible: true, order: 1, size: 'full', column: 'main' },
    { id: 'activity', type: 'activity', title: 'Activity Feed', visible: true, order: 2, size: 'large', column: 'main' },
    { id: 'ai-credits', type: 'ai-credits', title: 'AI Credits', visible: true, order: 0, size: 'medium', column: 'sidebar' },
    { id: 'upcoming-posts', type: 'upcoming-posts', title: 'Upcoming Posts', visible: true, order: 1, size: 'medium', column: 'sidebar' },
    { id: 'suggestions', type: 'suggestions', title: 'Smart Suggestions', visible: true, order: 2, size: 'medium', column: 'sidebar' },
    { id: 'recent-posts', type: 'recent-posts', title: 'Recent Posts', visible: true, order: 3, size: 'full', column: 'main' },
    { id: 'performance-cta', type: 'performance-cta', title: 'Performance Overview', visible: true, order: 4, size: 'full', column: 'main' },
];

// Global reactive state
const layout = ref<DashboardLayout>({
    widgets: [...defaultWidgets],
    columns: 3,
    compactMode: false,
});

const isCustomizing = ref(false);
const draggedWidget = ref<string | null>(null);

export function useBizBoostDashboard() {
    // Load saved layout from localStorage
    const loadLayout = () => {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                const parsed = JSON.parse(saved) as DashboardLayout;
                // Merge with defaults to handle new widgets
                const mergedWidgets = defaultWidgets.map(defaultWidget => {
                    const savedWidget = parsed.widgets.find(w => w.id === defaultWidget.id);
                    return savedWidget ? { ...defaultWidget, ...savedWidget } : defaultWidget;
                });
                layout.value = {
                    ...parsed,
                    widgets: mergedWidgets,
                };
            }
        } catch (e) {
            console.error('[BizBoost Dashboard] Failed to load layout:', e);
        }
    };

    // Save layout to localStorage
    const saveLayout = () => {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(layout.value));
        } catch (e) {
            console.error('[BizBoost Dashboard] Failed to save layout:', e);
        }
    };

    // Watch for changes and auto-save
    watch(layout, saveLayout, { deep: true });

    // Get visible widgets sorted by order
    const visibleWidgets = computed(() => {
        return layout.value.widgets
            .filter(w => w.visible)
            .sort((a, b) => a.order - b.order);
    });

    // Get widgets by column
    const mainColumnWidgets = computed(() => {
        return visibleWidgets.value.filter(w => w.column === 'main');
    });

    const sidebarWidgets = computed(() => {
        return visibleWidgets.value.filter(w => w.column === 'sidebar');
    });

    // Toggle widget visibility
    const toggleWidget = (widgetId: string) => {
        const widget = layout.value.widgets.find(w => w.id === widgetId);
        if (widget) {
            widget.visible = !widget.visible;
        }
    };

    // Update widget order
    const updateWidgetOrder = (widgetId: string, newOrder: number) => {
        const widget = layout.value.widgets.find(w => w.id === widgetId);
        if (widget) {
            widget.order = newOrder;
        }
    };

    // Move widget to different column
    const moveWidgetToColumn = (widgetId: string, column: 'main' | 'sidebar') => {
        const widget = layout.value.widgets.find(w => w.id === widgetId);
        if (widget) {
            widget.column = column;
            // Recalculate order within new column
            const columnWidgets = layout.value.widgets.filter(w => w.column === column && w.id !== widgetId);
            widget.order = columnWidgets.length;
        }
    };

    // Define widget sections for proper grouping
    const getWidgetSection = (widgetId: string): string => {
        const topWidgets = ['stats', 'quick-actions'];
        const sidebarWidgets = ['ai-credits', 'upcoming-posts', 'suggestions'];
        const bottomWidgets = ['recent-posts', 'performance-cta'];
        const mainGridWidgets = ['activity'];
        
        if (topWidgets.includes(widgetId)) return 'top';
        if (sidebarWidgets.includes(widgetId)) return 'sidebar';
        if (bottomWidgets.includes(widgetId)) return 'bottom';
        if (mainGridWidgets.includes(widgetId)) return 'main-grid';
        return 'unknown';
    };

    // Reorder widgets via drag and drop
    const reorderWidgets = (sourceId: string, targetId: string, position: 'before' | 'after') => {
        const sourceWidget = layout.value.widgets.find(w => w.id === sourceId);
        const targetWidget = layout.value.widgets.find(w => w.id === targetId);
        
        if (!sourceWidget || !targetWidget || sourceId === targetId) return;

        // Only allow reordering within the same section
        const sourceSection = getWidgetSection(sourceId);
        const targetSection = getWidgetSection(targetId);
        
        if (sourceSection !== targetSection) {
            console.log(`[Dashboard] Cannot move widget from ${sourceSection} to ${targetSection}`);
            return;
        }

        // Get all widgets in the same section (excluding source), sorted by order
        const sectionWidgetIds = layout.value.widgets
            .filter(w => getWidgetSection(w.id) === sourceSection)
            .map(w => w.id);
        
        const sectionWidgets = layout.value.widgets
            .filter(w => sectionWidgetIds.includes(w.id) && w.id !== sourceId)
            .sort((a, b) => a.order - b.order);

        // Find target index in the filtered list
        const targetIndex = sectionWidgets.findIndex(w => w.id === targetId);
        
        // Calculate insertion index
        const insertIndex = position === 'before' ? targetIndex : targetIndex + 1;

        // Insert source widget at the correct position
        sectionWidgets.splice(insertIndex, 0, sourceWidget);

        // Reassign orders for all widgets in this section
        sectionWidgets.forEach((widget, index) => {
            widget.order = index;
        });

        // Trigger reactivity by creating a new array reference
        layout.value = {
            ...layout.value,
            widgets: [...layout.value.widgets],
        };
    };

    // Reset to default layout
    const resetLayout = () => {
        layout.value = {
            widgets: [...defaultWidgets.map(w => ({ ...w }))],
            columns: 3,
            compactMode: false,
        };
    };

    // Toggle customization mode
    const toggleCustomizing = () => {
        isCustomizing.value = !isCustomizing.value;
    };

    // Drag and drop handlers
    const startDrag = (widgetId: string) => {
        draggedWidget.value = widgetId;
    };

    const endDrag = () => {
        draggedWidget.value = null;
    };

    // Initialize on first use
    loadLayout();

    return {
        layout,
        visibleWidgets,
        mainColumnWidgets,
        sidebarWidgets,
        isCustomizing,
        draggedWidget,
        toggleWidget,
        updateWidgetOrder,
        moveWidgetToColumn,
        reorderWidgets,
        resetLayout,
        toggleCustomizing,
        startDrag,
        endDrag,
        loadLayout,
        saveLayout,
    };
}
