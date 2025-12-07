<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import Card from '@/Components/BizBoost/UI/Card.vue';
import FormInput from '@/Components/BizBoost/Form/FormInput.vue';
import FormTextarea from '@/Components/BizBoost/Form/FormTextarea.vue';
import FormSelect from '@/Components/BizBoost/Form/FormSelect.vue';
import FormCheckbox from '@/Components/BizBoost/Form/FormCheckbox.vue';
import FormImageUpload from '@/Components/BizBoost/Form/FormImageUpload.vue';
import FormSection from '@/Components/BizBoost/Form/FormSection.vue';
import FormActions from '@/Components/BizBoost/Form/FormActions.vue';
import {
    ArrowLeftIcon,
    PhotoIcon,
    CubeIcon,
    CurrencyDollarIcon,
    TagIcon,
} from '@heroicons/vue/24/outline';

interface Category {
    id: number;
    name: string;
    color: string | null;
}

interface Product {
    id: number;
    name: string;
    description: string | null;
    price: number;
    sku: string | null;
    stock_quantity: number | null;
    category: string | null;
    category_id: number | null;
    image_url: string | null;
    is_active: boolean;
}

interface Props {
    product: Product;
    categories: Category[];
    legacyCategories: string[];
}

const props = defineProps<Props>();

const getInitialCategoryValue = (): number | string | null => {
    if (props.product.category_id) return props.product.category_id;
    if (props.product.category) return `legacy:${props.product.category}`;
    return null;
};

const form = useForm({
    name: props.product.name,
    description: props.product.description || '',
    price: props.product.price.toString(),
    sku: props.product.sku || '',
    stock_quantity: props.product.stock_quantity?.toString() || '',
    category_id: getInitialCategoryValue(),
    category: '',
    image: null as File | null,
    is_active: props.product.is_active,
});

const useNewCategory = ref(false);

const categoryOptions = [
    { value: null, label: 'No Category' },
    ...props.categories.map(cat => ({ value: cat.id, label: cat.name })),
    ...props.legacyCategories.map(cat => ({ value: `legacy:${cat}`, label: `${cat} (legacy)` })),
];

const toggleCategoryMode = () => {
    useNewCategory.value = !useNewCategory.value;
    form.category_id = null;
    form.category = '';
};

const submit = () => {
    if (typeof form.category_id === 'string' && form.category_id.startsWith('legacy:')) {
        form.category = form.category_id.replace('legacy:', '');
        form.category_id = null;
    }
    
    form.post(`/bizboost/products/${props.product.id}`, {
        forceFormData: true,
        _method: 'PUT',
    });
};
</script>

<template>
    <Head title="Edit Product - BizBoost" />
    <BizBoostLayout title="Edit Product">
        <div class="max-w-2xl mx-auto">
            <Link
                href="/bizboost/products"
                class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mb-6 transition-colors"
            >
                <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                Back to Products
            </Link>

            <Card>
                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Product Image -->
                    <FormSection title="Product Image" description="Add a photo to showcase your product" :icon="PhotoIcon">
                        <FormImageUpload
                            v-model="form.image"
                            :current-image="product.image_url"
                            hint="Recommended: 800x800px, max 2MB"
                            :error="form.errors.image"
                        />
                    </FormSection>

                    <!-- Basic Info -->
                    <FormSection title="Product Details" description="Name and description of your product" :icon="CubeIcon">
                        <FormInput
                            v-model="form.name"
                            label="Product Name"
                            placeholder="Enter product name"
                            :error="form.errors.name"
                            required
                        />
                        <FormTextarea
                            v-model="form.description"
                            label="Description"
                            placeholder="Describe your product..."
                            :rows="3"
                        />
                    </FormSection>

                    <!-- Pricing -->
                    <FormSection title="Pricing & Inventory" description="Set your price and track stock" :icon="CurrencyDollarIcon">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <FormInput
                                v-model="form.price"
                                label="Price (K)"
                                type="number"
                                placeholder="0.00"
                                :error="form.errors.price"
                                required
                            />
                            <FormInput
                                v-model="form.sku"
                                label="SKU"
                                placeholder="Optional product code"
                            />
                        </div>
                        <FormInput
                            v-model="form.stock_quantity"
                            label="Stock Quantity"
                            type="number"
                            placeholder="Leave empty for unlimited"
                            hint="Track inventory levels"
                        />
                    </FormSection>

                    <!-- Category -->
                    <FormSection title="Category" description="Organize your products" :icon="TagIcon">
                        <div v-if="!useNewCategory">
                            <FormSelect
                                v-model="form.category_id"
                                label="Select Category"
                                :options="categoryOptions"
                                placeholder="Choose a category"
                            />
                        </div>
                        <div v-else>
                            <FormInput
                                v-model="form.category"
                                label="New Category Name"
                                placeholder="Enter category name"
                            />
                        </div>
                        <button
                            type="button"
                            @click="toggleCategoryMode"
                            class="text-sm text-violet-600 hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300 transition-colors"
                        >
                            {{ useNewCategory ? '← Select existing category' : '+ Create new category' }}
                        </button>
                    </FormSection>

                    <!-- Status -->
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <FormCheckbox
                            v-model="form.is_active"
                            label="Product is active"
                            description="Active products are visible in your catalog and can be sold"
                        />
                    </div>

                    <FormActions
                        submit-label="Update Product"
                        cancel-href="/bizboost/products"
                        :processing="form.processing"
                    />
                </form>
            </Card>
        </div>
    </BizBoostLayout>
</template>
