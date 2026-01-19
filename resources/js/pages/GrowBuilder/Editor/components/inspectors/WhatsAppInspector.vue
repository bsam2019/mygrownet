<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    section: {
        id: string;
        type: string;
        data: {
            heading?: string;
            subheading?: string;
            buttonText?: string;
            phoneNumber?: string;
            defaultMessage?: string;
            buttonStyle?: 'solid' | 'outline' | 'minimal';
            buttonSize?: 'sm' | 'md' | 'lg';
            alignment?: 'left' | 'center' | 'right';
            backgroundColor?: string;
            textColor?: string;
            buttonColor?: string;
        };
    };
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'update', data: any): void;
}>();

const updateField = (field: string, value: any) => {
    emit('update', { ...props.section.data, [field]: value });
};

const formatPhoneNumber = (value: string) => {
    // Remove all non-digits
    const digits = value.replace(/\D/g, '');
    
    // Format as +260 XX XXX XXXX for Zambian numbers
    if (digits.startsWith('260')) {
        const formatted = digits.replace(/^260(\d{2})(\d{3})(\d{4})/, '+260 $1 $2 $3');
        return formatted;
    }
    
    return value;
};

const handlePhoneInput = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const formatted = formatPhoneNumber(input.value);
    updateField('phoneNumber', formatted);
};
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-sm font-medium text-gray-900 mb-4">Content</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Heading
                    </label>
                    <input
                        type="text"
                        :value="section.data.heading"
                        @input="updateField('heading', ($event.target as HTMLInputElement).value)"
                        placeholder="Get in Touch on WhatsApp"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Subheading
                    </label>
                    <textarea
                        :value="section.data.subheading"
                        @input="updateField('subheading', ($event.target as HTMLTextAreaElement).value)"
                        placeholder="We're here to help! Chat with us instantly."
                        rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        WhatsApp Number
                    </label>
                    <input
                        type="tel"
                        :value="section.data.phoneNumber"
                        @input="handlePhoneInput"
                        placeholder="+260 97 123 4567"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        Include country code (e.g., +260 for Zambia)
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Default Message
                    </label>
                    <textarea
                        :value="section.data.defaultMessage"
                        @input="updateField('defaultMessage', ($event.target as HTMLTextAreaElement).value)"
                        placeholder="Hi, I'm interested in..."
                        rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        This message will be pre-filled when users click the button
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Button Text
                    </label>
                    <input
                        type="text"
                        :value="section.data.buttonText"
                        @input="updateField('buttonText', ($event.target as HTMLInputElement).value)"
                        placeholder="Chat on WhatsApp"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-900 mb-4">Button Style</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Style
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            @click="updateField('buttonStyle', 'solid')"
                            :class="[
                                'px-3 py-2 text-sm rounded-lg border-2 transition-colors',
                                section.data.buttonStyle === 'solid' || !section.data.buttonStyle
                                    ? 'border-blue-500 bg-blue-50 text-blue-700'
                                    : 'border-gray-200 hover:border-gray-300'
                            ]"
                        >
                            Solid
                        </button>
                        <button
                            @click="updateField('buttonStyle', 'outline')"
                            :class="[
                                'px-3 py-2 text-sm rounded-lg border-2 transition-colors',
                                section.data.buttonStyle === 'outline'
                                    ? 'border-blue-500 bg-blue-50 text-blue-700'
                                    : 'border-gray-200 hover:border-gray-300'
                            ]"
                        >
                            Outline
                        </button>
                        <button
                            @click="updateField('buttonStyle', 'minimal')"
                            :class="[
                                'px-3 py-2 text-sm rounded-lg border-2 transition-colors',
                                section.data.buttonStyle === 'minimal'
                                    ? 'border-blue-500 bg-blue-50 text-blue-700'
                                    : 'border-gray-200 hover:border-gray-300'
                            ]"
                        >
                            Minimal
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Size
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            @click="updateField('buttonSize', 'sm')"
                            :class="[
                                'px-3 py-2 text-sm rounded-lg border-2 transition-colors',
                                section.data.buttonSize === 'sm'
                                    ? 'border-blue-500 bg-blue-50 text-blue-700'
                                    : 'border-gray-200 hover:border-gray-300'
                            ]"
                        >
                            Small
                        </button>
                        <button
                            @click="updateField('buttonSize', 'md')"
                            :class="[
                                'px-3 py-2 text-sm rounded-lg border-2 transition-colors',
                                section.data.buttonSize === 'md' || !section.data.buttonSize
                                    ? 'border-blue-500 bg-blue-50 text-blue-700'
                                    : 'border-gray-200 hover:border-gray-300'
                            ]"
                        >
                            Medium
                        </button>
                        <button
                            @click="updateField('buttonSize', 'lg')"
                            :class="[
                                'px-3 py-2 text-sm rounded-lg border-2 transition-colors',
                                section.data.buttonSize === 'lg'
                                    ? 'border-blue-500 bg-blue-50 text-blue-700'
                                    : 'border-gray-200 hover:border-gray-300'
                            ]"
                        >
                            Large
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Alignment
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            @click="updateField('alignment', 'left')"
                            :class="[
                                'px-3 py-2 text-sm rounded-lg border-2 transition-colors',
                                section.data.alignment === 'left'
                                    ? 'border-blue-500 bg-blue-50 text-blue-700'
                                    : 'border-gray-200 hover:border-gray-300'
                            ]"
                        >
                            Left
                        </button>
                        <button
                            @click="updateField('alignment', 'center')"
                            :class="[
                                'px-3 py-2 text-sm rounded-lg border-2 transition-colors',
                                section.data.alignment === 'center' || !section.data.alignment
                                    ? 'border-blue-500 bg-blue-50 text-blue-700'
                                    : 'border-gray-200 hover:border-gray-300'
                            ]"
                        >
                            Center
                        </button>
                        <button
                            @click="updateField('alignment', 'right')"
                            :class="[
                                'px-3 py-2 text-sm rounded-lg border-2 transition-colors',
                                section.data.alignment === 'right'
                                    ? 'border-blue-500 bg-blue-50 text-blue-700'
                                    : 'border-gray-200 hover:border-gray-300'
                            ]"
                        >
                            Right
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Button Color
                    </label>
                    <input
                        type="color"
                        :value="section.data.buttonColor || '#10b981'"
                        @input="updateField('buttonColor', ($event.target as HTMLInputElement).value)"
                        class="w-full h-10 rounded-lg border border-gray-300 cursor-pointer"
                    />
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-900 mb-4">Colors</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Background Color
                    </label>
                    <input
                        type="color"
                        :value="section.data.backgroundColor || '#ffffff'"
                        @input="updateField('backgroundColor', ($event.target as HTMLInputElement).value)"
                        class="w-full h-10 rounded-lg border border-gray-300 cursor-pointer"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Text Color
                    </label>
                    <input
                        type="color"
                        :value="section.data.textColor || '#000000'"
                        @input="updateField('textColor', ($event.target as HTMLInputElement).value)"
                        class="w-full h-10 rounded-lg border border-gray-300 cursor-pointer"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
