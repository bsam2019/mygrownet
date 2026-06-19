import { ref } from 'vue';
import axios from 'axios';

export function useImages() {
    const generating = ref(false);
    const error = ref<string | null>(null);

    async function generateImage(prompt: string, options: { width?: number; height?: number; numOutputs?: number } = {}) {
        generating.value = true;
        error.value = null;

        try {
            const { data } = await axios.post('/growbuilder/ai/generate-image', {
                prompt,
                width: options.width ?? 1024,
                height: options.height ?? 1024,
                num_outputs: options.numOutputs ?? 1,
            });

            if (!data.success) throw new Error(data.message || 'Generation failed');
            return data.images as Array<{ url: string; path: string; filename: string }>;
        } catch (e: any) {
            error.value = e.response?.data?.message || e.message || 'Image generation failed';
            return null;
        } finally {
            generating.value = false;
        }
    }

    async function generateLogo(businessName: string, industry: string, style = 'minimalist') {
        generating.value = true;
        error.value = null;

        try {
            const { data } = await axios.post('/growbuilder/ai/generate-logo', {
                business_name: businessName,
                industry,
                style,
            });

            if (!data.success) throw new Error(data.message || 'Generation failed');
            return data.images as Array<{ url: string; path: string; filename: string }>;
        } catch (e: any) {
            error.value = e.response?.data?.message || e.message || 'Logo generation failed';
            return null;
        } finally {
            generating.value = false;
        }
    }

    async function analyzeReferenceSite(url: string) {
        generating.value = true;
        error.value = null;

        try {
            const { data } = await axios.post('/growbuilder/ai/analyze-reference', { url });
            if (!data.success) throw new Error(data.message || 'Analysis failed');
            return data.analysis;
        } catch (e: any) {
            error.value = e.response?.data?.message || e.message || 'Analysis failed';
            return null;
        } finally {
            generating.value = false;
        }
    }

    async function convertReferenceSite(analysis: any) {
        generating.value = true;
        error.value = null;

        try {
            const { data } = await axios.post('/growbuilder/ai/convert-reference', { analysis });
            if (!data.success) throw new Error(data.message || 'Conversion failed');
            return data.structure;
        } catch (e: any) {
            error.value = e.response?.data?.message || e.message || 'Conversion failed';
            return null;
        } finally {
            generating.value = false;
        }
    }

    return {
        generating,
        error,
        generateImage,
        generateLogo,
        analyzeReferenceSite,
        convertReferenceSite,
    };
}
