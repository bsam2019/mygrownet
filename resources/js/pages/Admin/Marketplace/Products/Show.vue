<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MarketplaceAdminLayout from '@/layouts/MarketplaceAdminLayout.vue';
import { 
    ArrowLeftIcon, 
    CheckCircleIcon, 
    XCircleIcon,
    PencilSquareIcon,
    TagIcon,
    CurrencyDollarIcon,
    CubeIcon,
    UserIcon,
    CalendarIcon,
    ExclamationTriangleIcon,
    PlusIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline';

interface FieldFeedback {
    field: string;
    message: string;
}

interface RejectionCategory {
    label: string;
    description: string;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    description: string;
    price: number;
    compare_price: number | null;
    formatted_price: string;
    formatted_compare_price: string | null;
    discount_percentage: number;
    stock_quantity: number;
    status: 'pending' | 'active' | 'rejected' | 'inactive' | 'changes_requested';
    rejection_reason: string | null;
    rejection_category: string | null;
    field_feedback: FieldFeedback[] | null;
    appeal_message: string | null;
    appealed_at: string | null;
    images: string[];
    image_urls: string[];
    primary_image_url: string | null;
    is_featured: boolean;
    views: number;
    created_at: string;
    updated_at: string;
    seller: {
        id: number;
        business_name: string;
        user: {
            name: string;
            email: string;
        };
    };
    category: {
        id: number;
        name: string;
        slug: string;
    };
}

const props = defineProps<{ 
    product: Product;
    rejectionCategories: Record<string, RejectionCategory>;
}>();

// Modal states
const showRejectModal = ref(false);
const showRequestChangesModal = ref(false);
const processing = ref(false);
const selectedImage = ref(0);

// Form data for rejection/changes
const feedbackForm = ref({
    category: '',
    reason: '',
    fieldFeedback: [] as FieldFeedback[],
});

// Product fields for field-specific feedback
const productFields = [
    { value: 'name', label: 'Product Name' },
    { value: 'description', label: 'Description' },
    { value: 'price', label: 'Price' },
    { value: 'images', label: 'Images' },
    { value: 'category', label: 'Category' },
    { value: 'stock', label: 'Stock Quantity' },
];

const resetForm = () => {
    feedbackForm.value = {
        category: '',
        reason: '',
        fieldFeedback: [],
    };
};

const addFieldFeedback = () => {
    feedbackForm.value.fieldFeedback.push({ field: '', message: '' });
};

const removeFieldFeedback = (index: number) => {
    feedbackForm.value.fieldFeedback.splice(index, 1);
};

const approveProduct = () => {
    if (!confirm(`Approve product "${props.product.name}"?`)) return;
    
    processing.value = true;
    router.post(`/admin/marketplace/products/${props.product.id}/approve`, {}, {
        onFinish: () => processing.value = false,
    });
};

const submitReject = () => {
    if (!feedbackForm.value.category || !feedbackForm.value.reason.trim()) return;
    
    processing.value = true;
    router.post(`/admin/marketplace/products/${props.product.id}/reject`, {
        category: feedbackForm.value.category,
        reason: feedbackForm.value.reason,
        field_feedback: feedbackForm.value.fieldFeedback.filter(f => f.field && f.message),
    }, {
        onSuccess: () => {
            showRejectModal.value = false;
            resetForm();
        },
        onFinish: () => processing.value = false,
    });
};

const submitRequestChanges = () => {
    if (!feedbackForm.value.category || !feedbackForm.value.reason.trim()) return;
    
    processing.value = true;
    router.post(`/admin/marketplace/products/${props.product.id}/request-changes`, {
        category: feedbackForm.value.category,
        reason: feedbackForm.value.reason,
        field_feedback: feedbackForm.value.fieldFeedback.filter(f => f.field && f.message),
    }, {
        onSuccess: () => {
            showRequestChangesModal.value = false;
            resetForm();
        },
        onFinish: () => processing.value = false,
    });
};

const openRejectModal = () => {
    resetForm();
    showRejectModal.value = true;
};

const openRequestChangesModal = () => {
    resetForm();
    showRequestChangesModal.value = true;
};

const getStatusBadge = (status: string) => {
    const badges: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800',
        active: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
        inactive: 'bg-gray-100 text-gray-800',
        changes_requested: 'bg-orange-100 text-orange-800',
    };
    return badges[status] || badges.inactive;
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        pending: 'Pending Review',
        active: 'Active',
        rejected: 'Rejected',
        inactive: 'Inactive',
        changes_requested: 'Changes Requested',
    };
    return labels[status] || status;
};

const getCategoryLabel = (category: string | null) => {
    if (!category || !props.rejectionCategories[category]) return category;
    return props.rejectionCategories[category].label;
};

const canReview = computed(() => 
    ['pending', 'changes_requested'].includes(props.product.status) || 
    (props.product.status === 'rejected' && props.product.appeal_message)
);
</script>

<template>
    <Head :title="`${product.name} - Product Review`" />
    
    <MarketplaceAdminLayout title="Product Review">
        <!-- Back Link -->
        <div class="mb-6">
            <Link href="/admin/marketplace/products" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
                <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                Back to Products
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Images -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Product Images</h3>
                    
                    <!-- Main Image -->
                    <div class="aspect-square rounded-lg overflow-hidden bg-gray-100 mb-3">
                        <img
                            v-if="product.image_urls && product.image_urls[selectedImage]"
                            :src="product.image_urls[selectedImage]"
                            :alt="product.name"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                            No image
                        </div>
                    </div>
                    
                    <!-- Thumbnails -->
                    <div v-if="product.image_urls && product.image_urls.length > 1" class="grid grid-cols-4 gap-2">
                        <button
                            v-for="(url, index) in product.image_urls"
                            :key="index"
                            @click="selectedImage = index"
                            :class="[
                                'aspect-square rounded-lg overflow-hidden border-2 transition-colors',
                                selectedImage === index ? 'border-orange-500' : 'border-transparent hover:border-gray-300'
                            ]"
                        >
                            <img :src="url" :alt="`Image ${index + 1}`" class="w-full h-full object-cover" />
                        </button>
                    </div>
                    
                    <p class="text-xs text-gray-500 mt-3">{{ product.image_urls?.length || 0 }} image(s)</p>
                </div>

                <!-- Appeal Notice -->
                <div v-if="product.appeal_message" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-blue-800 mb-2">📩 Seller Appeal</h4>
                    <p class="text-sm text-blue-700">{{ product.appeal_message }}</p>
                    <p class="text-xs text-blue-600 mt-2">
                        Submitted: {{ product.appealed_at ? new Date(product.appealed_at).toLocaleString() : 'N/A' }}
                    </p>
                </div>
            </div>

            <!-- Right Column: Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Product Info -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ product.name }}</h1>
                            <p class="text-sm text-gray-500 mt-1">ID: {{ product.id }}</p>
                        </div>
                        <span :class="getStatusBadge(product.status)" class="px-3 py-1 rounded-full text-sm font-medium">
                            {{ getStatusLabel(product.status) }}
                        </span>
                    </div>

                    <!-- Previous Feedback (if rejected or changes requested) -->
                    <div v-if="['rejected', 'changes_requested'].includes(product.status) && product.rejection_reason" 
                         :class="[
                             'mb-4 p-4 rounded-lg border',
                             product.status === 'rejected' ? 'bg-red-50 border-red-200' : 'bg-orange-50 border-orange-200'
                         ]">
                        <div class="flex items-start gap-3">
                            <ExclamationTriangleIcon :class="[
                                'h-5 w-5 mt-0.5',
                                product.status === 'rejected' ? 'text-red-600' : 'text-orange-600'
                            ]" aria-hidden="true" />
                            <div class="flex-1">
                                <p :class="[
                                    'text-sm font-medium',
                                    product.status === 'rejected' ? 'text-red-800' : 'text-orange-800'
                                ]">
                                    {{ product.status === 'rejected' ? 'Rejection' : 'Changes Requested' }} - {{ getCategoryLabel(product.rejection_category) }}
                                </p>
                                <p :class="[
                                    'text-sm mt-1',
                                    product.status === 'rejected' ? 'text-red-700' : 'text-orange-700'
                                ]">
                                    {{ product.rejection_reason }}
                                </p>
                                
                                <!-- Field-specific feedback -->
                                <div v-if="product.field_feedback && product.field_feedback.length > 0" class="mt-3 space-y-2">
                                    <p :class="[
                                        'text-xs font-medium',
                                        product.status === 'rejected' ? 'text-red-800' : 'text-orange-800'
                                    ]">Field-specific issues:</p>
                                    <div v-for="(feedback, idx) in product.field_feedback" :key="idx" 
                                         :class="[
                                             'text-sm p-2 rounded',
                                             product.status === 'rejected' ? 'bg-red-100' : 'bg-orange-100'
                                         ]">
                                        <span class="font-medium">{{ feedback.field }}:</span> {{ feedback.message }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Description</h3>
                        <p class="text-gray-600 whitespace-pre-wrap">{{ product.description }}</p>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-2 text-gray-500 mb-1">
                                <CurrencyDollarIcon class="h-4 w-4" aria-hidden="true" />
                                <span class="text-xs">Price</span>
                            </div>
                            <p class="font-semibold text-gray-900">{{ product.formatted_price }}</p>
                            <p v-if="product.formatted_compare_price" class="text-xs text-gray-500 line-through">
                                {{ product.formatted_compare_price }}
                            </p>
                        </div>
                        
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-2 text-gray-500 mb-1">
                                <CubeIcon class="h-4 w-4" aria-hidden="true" />
                                <span class="text-xs">Stock</span>
                            </div>
                            <p class="font-semibold text-gray-900">{{ product.stock_quantity }}</p>
                        </div>
                        
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-2 text-gray-500 mb-1">
                                <TagIcon class="h-4 w-4" aria-hidden="true" />
                                <span class="text-xs">Category</span>
                            </div>
                            <p class="font-semibold text-gray-900">{{ product.category?.name || 'N/A' }}</p>
                        </div>
                        
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-2 text-gray-500 mb-1">
                                <CalendarIcon class="h-4 w-4" aria-hidden="true" />
                                <span class="text-xs">Submitted</span>
                            </div>
                            <p class="font-semibold text-gray-900">{{ new Date(product.created_at).toLocaleDateString() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Seller Info -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-sm font-medium text-gray-700 mb-4 flex items-center gap-2">
                        <UserIcon class="h-4 w-4" aria-hidden="true" />
                        Seller Information
                    </h3>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900">{{ product.seller?.business_name }}</p>
                            <p class="text-sm text-gray-500">{{ product.seller?.user?.name }} • {{ product.seller?.user?.email }}</p>
                        </div>
                        <Link
                            :href="`/admin/marketplace/sellers/${product.seller?.id}`"
                            class="text-orange-600 hover:text-orange-700 text-sm font-medium"
                        >
                            View Seller
                        </Link>
                    </div>
                </div>

                <!-- Actions -->
                <div v-if="canReview" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-sm font-medium text-gray-700 mb-4">Review Actions</h3>
                    
                    <div class="flex flex-wrap gap-3">
                        <button
                            @click="approveProduct"
                            :disabled="processing"
                            class="flex-1 min-w-[140px] flex items-center justify-center gap-2 px-4 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 font-medium"
                        >
                            <CheckCircleIcon class="h-5 w-5" aria-hidden="true" />
                            Approve
                        </button>
                        <button
                            @click="openRequestChangesModal"
                            :disabled="processing"
                            class="flex-1 min-w-[140px] flex items-center justify-center gap-2 px-4 py-2.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 disabled:opacity-50 font-medium"
                        >
                            <PencilSquareIcon class="h-5 w-5" aria-hidden="true" />
                            Request Changes
                        </button>
                        <button
                            @click="openRejectModal"
                            :disabled="processing"
                            class="flex-1 min-w-[140px] flex items-center justify-center gap-2 px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 font-medium"
                        >
                            <XCircleIcon class="h-5 w-5" aria-hidden="true" />
                            Reject
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feedback Modal (shared for reject and request changes) -->
        <Teleport to="body">
            <div v-if="showRejectModal || showRequestChangesModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">
                            {{ showRejectModal ? 'Reject Product' : 'Request Changes' }}
                        </h3>
                        <p class="text-sm text-gray-500 mb-6">
                            {{ showRejectModal 
                                ? 'This will permanently reject the product. The seller can appeal.' 
                                : 'Ask the seller to make specific changes before approval.' 
                            }}
                        </p>

                        <!-- Category Selection -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Issue Category *</label>
                            <select
                                v-model="feedbackForm.category"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-gray-900 bg-white"
                            >
                                <option value="">Select a category</option>
                                <option v-for="(cat, key) in rejectionCategories" :key="key" :value="key">
                                    {{ cat.label }}
                                </option>
                            </select>
                            <p v-if="feedbackForm.category && rejectionCategories[feedbackForm.category]" class="mt-1 text-xs text-gray-500">
                                {{ rejectionCategories[feedbackForm.category].description }}
                            </p>
                        </div>

                        <!-- Reason -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ showRejectModal ? 'Rejection Reason' : 'What needs to change' }} *
                            </label>
                            <textarea
                                v-model="feedbackForm.reason"
                                rows="3"
                                placeholder="Provide clear feedback for the seller..."
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-gray-900 bg-white placeholder-gray-400"
                            ></textarea>
                        </div>

                        <!-- Field-specific Feedback -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700">Field-specific Feedback</label>
                                <button
                                    type="button"
                                    @click="addFieldFeedback"
                                    class="text-sm text-orange-600 hover:text-orange-700 flex items-center gap-1"
                                >
                                    <PlusIcon class="h-4 w-4" aria-hidden="true" />
                                    Add Field
                                </button>
                            </div>
                            
                            <div v-if="feedbackForm.fieldFeedback.length === 0" class="text-sm text-gray-500 italic">
                                No field-specific feedback added. Click "Add Field" to specify issues with particular fields.
                            </div>
                            
                            <div v-for="(feedback, index) in feedbackForm.fieldFeedback" :key="index" class="flex gap-2 mb-2">
                                <select
                                    v-model="feedback.field"
                                    class="w-1/3 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-gray-900 bg-white"
                                >
                                    <option value="">Field</option>
                                    <option v-for="field in productFields" :key="field.value" :value="field.value">
                                        {{ field.label }}
                                    </option>
                                </select>
                                <input
                                    v-model="feedback.message"
                                    type="text"
                                    placeholder="What's wrong with this field?"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-gray-900 bg-white placeholder-gray-400"
                                />
                                <button
                                    type="button"
                                    @click="removeFieldFeedback(index)"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg"
                                >
                                    <TrashIcon class="h-4 w-4" aria-hidden="true" />
                                </button>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3 pt-4 border-t border-gray-200">
                            <button
                                @click="showRejectModal = false; showRequestChangesModal = false; resetForm()"
                                :disabled="processing"
                                class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50 font-medium"
                            >
                                Cancel
                            </button>
                            <button
                                v-if="showRejectModal"
                                @click="submitReject"
                                :disabled="!feedbackForm.category || !feedbackForm.reason.trim() || processing"
                                class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                            >
                                {{ processing ? 'Rejecting...' : 'Reject Product' }}
                            </button>
                            <button
                                v-if="showRequestChangesModal"
                                @click="submitRequestChanges"
                                :disabled="!feedbackForm.category || !feedbackForm.reason.trim() || processing"
                                class="flex-1 px-4 py-2.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                            >
                                {{ processing ? 'Submitting...' : 'Request Changes' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </MarketplaceAdminLayout>
</template>
