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
    isEditing?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isEditing: false,
});

const whatsappUrl = computed(() => {
    const phone = props.section.data.phoneNumber?.replace(/\D/g, '');
    if (!phone) return '#';
    
    const message = props.section.data.defaultMessage || '';
    const encodedMessage = encodeURIComponent(message);
    
    return `https://wa.me/${phone}${message ? `?text=${encodedMessage}` : ''}`;
});

const buttonClasses = computed(() => {
    const size = props.section.data.buttonSize || 'md';
    const style = props.section.data.buttonStyle || 'solid';
    
    const sizeClasses = {
        sm: 'px-4 py-2 text-sm',
        md: 'px-6 py-3 text-base',
        lg: 'px-8 py-4 text-lg',
    };
    
    const styleClasses = {
        solid: 'bg-green-500 text-white hover:bg-green-600',
        outline: 'border-2 border-green-500 text-green-600 hover:bg-green-50',
        minimal: 'text-green-600 hover:bg-green-50',
    };
    
    return `${sizeClasses[size]} ${styleClasses[style]} rounded-lg font-medium transition-colors inline-flex items-center gap-2`;
});

const alignmentClass = computed(() => {
    const alignment = props.section.data.alignment || 'center';
    return {
        left: 'text-left',
        center: 'text-center',
        right: 'text-right',
    }[alignment];
});
</script>

<template>
    <section 
        :style="{ 
            backgroundColor: section.data.backgroundColor || 'transparent',
            color: section.data.textColor || 'inherit'
        }"
        class="py-16 px-4"
    >
        <div class="max-w-4xl mx-auto" :class="alignmentClass">
            <h2 
                v-if="section.data.heading" 
                class="text-3xl md:text-4xl font-bold mb-4"
            >
                {{ section.data.heading }}
            </h2>
            
            <p 
                v-if="section.data.subheading" 
                class="text-lg md:text-xl mb-8 opacity-90"
            >
                {{ section.data.subheading }}
            </p>
            
            <a
                v-if="!isEditing"
                :href="whatsappUrl"
                target="_blank"
                rel="noopener noreferrer"
                :class="buttonClasses"
                :style="section.data.buttonColor ? { backgroundColor: section.data.buttonColor } : {}"
            >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                {{ section.data.buttonText || 'Chat on WhatsApp' }}
            </a>
            
            <button
                v-else
                :class="buttonClasses"
                :style="section.data.buttonColor ? { backgroundColor: section.data.buttonColor } : {}"
                disabled
            >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                {{ section.data.buttonText || 'Chat on WhatsApp' }}
            </button>
        </div>
    </section>
</template>
