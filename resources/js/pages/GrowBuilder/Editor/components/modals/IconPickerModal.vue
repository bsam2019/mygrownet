<script setup lang="ts">
/**
 * Icon Picker Modal
 * Searchable icon library using Heroicons
 */
import { ref, computed, watch } from 'vue';
import { XMarkIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import * as OutlineIcons from '@heroicons/vue/24/outline';
import * as SolidIcons from '@heroicons/vue/24/solid';

const props = defineProps<{
    show: boolean;
    currentIcon?: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'select', iconName: string, iconStyle: 'outline' | 'solid'): void;
}>();

const searchQuery = ref('');
const iconStyle = ref<'outline' | 'solid'>('outline');
const selectedCategory = ref<string | null>(null);

// Icon categories for organization
const iconCategories: Record<string, string[]> = {
    'Actions': ['PlusIcon', 'MinusIcon', 'XMarkIcon', 'CheckIcon', 'ArrowPathIcon', 'TrashIcon', 'PencilIcon', 'DocumentDuplicateIcon'],
    'Arrows': ['ArrowUpIcon', 'ArrowDownIcon', 'ArrowLeftIcon', 'ArrowRightIcon', 'ChevronUpIcon', 'ChevronDownIcon', 'ChevronLeftIcon', 'ChevronRightIcon', 'ArrowsPointingOutIcon'],
    'Communication': ['EnvelopeIcon', 'PhoneIcon', 'ChatBubbleLeftIcon', 'ChatBubbleOvalLeftIcon', 'BellIcon', 'MegaphoneIcon', 'InboxIcon'],
    'Commerce': ['ShoppingCartIcon', 'ShoppingBagIcon', 'CreditCardIcon', 'BanknotesIcon', 'CurrencyDollarIcon', 'ReceiptPercentIcon', 'TagIcon', 'GiftIcon'],
    'Content': ['DocumentIcon', 'DocumentTextIcon', 'FolderIcon', 'PhotoIcon', 'FilmIcon', 'MusicalNoteIcon', 'BookOpenIcon', 'NewspaperIcon'],
    'Interface': ['Bars3Icon', 'EllipsisHorizontalIcon', 'EllipsisVerticalIcon', 'AdjustmentsHorizontalIcon', 'Cog6ToothIcon', 'WrenchIcon', 'FunnelIcon'],
    'Navigation': ['HomeIcon', 'MapPinIcon', 'MapIcon', 'GlobeAltIcon', 'BuildingOfficeIcon', 'BuildingStorefrontIcon'],
    'People': ['UserIcon', 'UserGroupIcon', 'UsersIcon', 'UserPlusIcon', 'UserCircleIcon', 'IdentificationIcon', 'AcademicCapIcon'],
    'Social': ['HeartIcon', 'StarIcon', 'HandThumbUpIcon', 'HandThumbDownIcon', 'ShareIcon', 'LinkIcon', 'HashtagIcon'],
    'Status': ['CheckCircleIcon', 'XCircleIcon', 'ExclamationCircleIcon', 'InformationCircleIcon', 'QuestionMarkCircleIcon', 'ShieldCheckIcon', 'LockClosedIcon'],
    'Technology': ['ComputerDesktopIcon', 'DevicePhoneMobileIcon', 'DeviceTabletIcon', 'ServerIcon', 'CloudIcon', 'WifiIcon', 'SignalIcon', 'CpuChipIcon'],
    'Time': ['ClockIcon', 'CalendarIcon', 'CalendarDaysIcon'],
    'Weather': ['SunIcon', 'MoonIcon', 'CloudIcon', 'BoltIcon', 'FireIcon', 'SparklesIcon'],
};

// Get all icon names from Heroicons
const allIconNames = computed(() => {
    const icons = iconStyle.value === 'outline' ? OutlineIcons : SolidIcons;
    return Object.keys(icons).filter(name => name.endsWith('Icon') && name !== 'default');
});

// Filter icons based on search and category
const filteredIcons = computed(() => {
    let icons = allIconNames.value;
    
    // Filter by category
    if (selectedCategory.value && iconCategories[selectedCategory.value]) {
        icons = icons.filter(name => iconCategories[selectedCategory.value!].includes(name));
    }
    
    // Filter by search
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        icons = icons.filter(name => 
            name.toLowerCase().includes(query) ||
            name.replace(/Icon$/, '').toLowerCase().includes(query)
        );
    }
    
    return icons.slice(0, 100); // Limit for performance
});

// Get icon component
const getIconComponent = (name: string) => {
    const icons = iconStyle.value === 'outline' ? OutlineIcons : SolidIcons;
    return (icons as any)[name];
};

// Format icon name for display
const formatIconName = (name: string) => {
    return name
        .replace(/Icon$/, '')
        .replace(/([A-Z])/g, ' $1')
        .trim();
};

const selectIcon = (iconName: string) => {
    emit('select', iconName, iconStyle.value);
};

// Reset when modal opens
watch(() => props.show, (show) => {
    if (show) {
        searchQuery.value = '';
        selectedCategory.value = null;
    }
});
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60" @click="emit('close')">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[85vh] flex flex-col" @click.stop>
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Choose Icon</h2>
                    <button @click="emit('close')" class="p-1.5 hover:bg-gray-100 rounded-lg" aria-label="Close">
                        <XMarkIcon class="w-5 h-5 text-gray-500" />
                    </button>
                </div>

                <!-- Search & Filters -->
                <div class="p-4 border-b border-gray-100 bg-gray-50 space-y-3">
                    <!-- Search -->
                    <div class="relative">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search icons..."
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                    
                    <!-- Style Toggle -->
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-600">Style:</span>
                        <div class="flex gap-1 bg-gray-200 rounded-lg p-1">
                            <button
                                @click="iconStyle = 'outline'"
                                :class="[
                                    'px-3 py-1.5 text-sm font-medium rounded-md transition-colors',
                                    iconStyle === 'outline' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'
                                ]"
                            >
                                Outline
                            </button>
                            <button
                                @click="iconStyle = 'solid'"
                                :class="[
                                    'px-3 py-1.5 text-sm font-medium rounded-md transition-colors',
                                    iconStyle === 'solid' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'
                                ]"
                            >
                                Solid
                            </button>
                        </div>
                    </div>
                    
                    <!-- Categories -->
                    <div class="flex flex-wrap gap-2">
                        <button
                            @click="selectedCategory = null"
                            :class="[
                                'px-3 py-1 text-xs font-medium rounded-full transition-colors',
                                selectedCategory === null
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-100'
                            ]"
                        >
                            All
                        </button>
                        <button
                            v-for="cat in Object.keys(iconCategories)"
                            :key="cat"
                            @click="selectedCategory = cat"
                            :class="[
                                'px-3 py-1 text-xs font-medium rounded-full transition-colors',
                                selectedCategory === cat
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-100'
                            ]"
                        >
                            {{ cat }}
                        </button>
                    </div>
                </div>

                <!-- Icons Grid -->
                <div class="flex-1 overflow-y-auto p-4">
                    <div v-if="filteredIcons.length > 0" class="grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 gap-2">
                        <button
                            v-for="iconName in filteredIcons"
                            :key="iconName"
                            @click="selectIcon(iconName)"
                            :title="formatIconName(iconName)"
                            :class="[
                                'aspect-square flex items-center justify-center rounded-lg border-2 transition-all hover:scale-110',
                                currentIcon === iconName
                                    ? 'border-blue-500 bg-blue-50 text-blue-600'
                                    : 'border-transparent bg-gray-100 text-gray-700 hover:bg-gray-200 hover:border-gray-300'
                            ]"
                        >
                            <component :is="getIconComponent(iconName)" class="w-6 h-6" />
                        </button>
                    </div>
                    
                    <!-- Empty State -->
                    <div v-else class="text-center py-12 text-gray-500">
                        <MagnifyingGlassIcon class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                        <p>No icons found for "{{ searchQuery }}"</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                    <p class="text-xs text-gray-500 text-center">
                        {{ filteredIcons.length }} icons available • 
                        <a href="https://heroicons.com" target="_blank" class="text-blue-600 hover:underline">Heroicons</a> by Tailwind Labs
                    </p>
                </div>
            </div>
        </div>
    </Teleport>
</template>
