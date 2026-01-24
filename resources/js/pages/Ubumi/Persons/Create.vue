<template>
    <UbumiLayout :title="`Add Member - ${family.name}`">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('ubumi.families.show', family.slug)"
                        class="inline-flex items-center text-gray-600 hover:text-gray-900 mb-4"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Members
                    </Link>
                    <h1 class="text-3xl font-bold text-gray-900">Add Family Member</h1>
                    <p class="mt-2 text-gray-600">{{ family.name }}</p>
                </div>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <form @submit.prevent="submit">
                        <!-- Name -->
                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-500': form.errors.name }"
                                placeholder="e.g., John Mwansa"
                                required
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Date of Birth or Approximate Age -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Age Information
                            </label>
                            <div class="space-y-4">
                                <!-- Date of Birth -->
                                <div>
                                    <label for="date_of_birth" class="block text-sm text-gray-600 mb-1">
                                        Date of Birth (if known)
                                    </label>
                                    <input
                                        id="date_of_birth"
                                        v-model="form.date_of_birth"
                                        type="date"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': form.errors.date_of_birth }"
                                    />
                                    <p v-if="form.errors.date_of_birth" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.date_of_birth }}
                                    </p>
                                </div>

                                <!-- OR -->
                                <div class="text-center text-sm text-gray-500">OR</div>

                                <!-- Approximate Age -->
                                <div>
                                    <label for="approximate_age" class="block text-sm text-gray-600 mb-1">
                                        Approximate Age (if date unknown)
                                    </label>
                                    <input
                                        id="approximate_age"
                                        v-model.number="form.approximate_age"
                                        type="number"
                                        min="0"
                                        max="150"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': form.errors.approximate_age }"
                                        placeholder="e.g., 45"
                                    />
                                    <p v-if="form.errors.approximate_age" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.approximate_age }}
                                    </p>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                Provide either exact date of birth or approximate age
                            </p>
                        </div>

                        <!-- Gender -->
                        <div class="mb-6">
                            <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">
                                Gender
                            </label>
                            <select
                                id="gender"
                                v-model="form.gender"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-500': form.errors.gender }"
                            >
                                <option value="">Select gender (optional)</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                                <option value="prefer_not_to_say">Prefer not to say</option>
                            </select>
                            <p v-if="form.errors.gender" class="mt-1 text-sm text-red-600">
                                {{ form.errors.gender }}
                            </p>
                        </div>

                        <!-- Relationship (Optional) -->
                        <div v-if="availablePersons.length > 0" class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Relationship (Optional)
                            </label>
                            <p class="text-sm text-gray-500 mb-3">
                                If this person is related to an existing family member, you can specify the relationship now.
                            </p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Related Person -->
                                <div>
                                    <label for="related_person_id" class="block text-xs text-gray-600 mb-1">
                                        Related to
                                    </label>
                                    <select
                                        id="related_person_id"
                                        v-model="form.related_person_id"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500"
                                        :class="{ 'border-red-500': form.errors.related_person_id }"
                                    >
                                        <option value="">Select a person (optional)</option>
                                        <option
                                            v-for="person in availablePersons"
                                            :key="person.id"
                                            :value="person.id"
                                        >
                                            {{ person.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.related_person_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.related_person_id }}
                                    </p>
                                </div>

                                <!-- Relationship Type -->
                                <div>
                                    <label for="relationship_type" class="block text-xs text-gray-600 mb-1">
                                        Relationship type
                                    </label>
                                    <select
                                        id="relationship_type"
                                        v-model="form.relationship_type"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500"
                                        :class="{ 'border-red-500': form.errors.relationship_type }"
                                        :disabled="!form.related_person_id"
                                    >
                                        <option value="">Select relationship type</option>
                                        <option
                                            v-for="type in relationshipTypes"
                                            :key="type.value"
                                            :value="type.value"
                                        >
                                            {{ type.label }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.relationship_type" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.relationship_type }}
                                    </p>
                                </div>
                            </div>

                            <p class="mt-2 text-xs text-gray-500">
                                Example: If adding a child, select the parent and choose "Child" as the relationship type.
                            </p>
                        </div>

                        <!-- Photo Upload -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Photo
                            </label>
                            
                            <!-- Photo Preview or Upload Area -->
                            <div v-if="photoPreview || form.photo_url" class="mb-4">
                                <div class="relative inline-block">
                                    <img
                                        :src="photoPreview || form.photo_url"
                                        alt="Person photo"
                                        class="h-32 w-32 rounded-full object-cover border-4 border-emerald-100"
                                    />
                                    <button
                                        type="button"
                                        @click="removePhoto"
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1.5 hover:bg-red-600 shadow-lg"
                                        aria-label="Remove photo"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Upload Button -->
                            <div v-else class="flex items-center justify-center w-full">
                                <div class="w-full border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-emerald-400 transition-colors">
                                    <UserIcon class="mx-auto h-12 w-12 text-gray-400 mb-3" aria-hidden="true" />
                                    <MediaUploadButton
                                        :endpoint="route('ubumi.photos.upload')"
                                        variant="dashed"
                                        size="md"
                                        @success="handlePhotoUpload"
                                        @error="handlePhotoError"
                                    >
                                        <template #default="{ uploading }">
                                            {{ uploading ? 'Uploading...' : 'Upload Photo' }}
                                        </template>
                                    </MediaUploadButton>
                                    <p class="mt-2 text-xs text-gray-500">
                                        PNG, JPG, GIF up to 5MB
                                    </p>
                                </div>
                            </div>

                            <p v-if="form.errors.photo_url" class="mt-2 text-sm text-red-600">
                                {{ form.errors.photo_url }}
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                            <Link
                                :href="route('ubumi.families.show', family.slug)"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="form.processing">Adding...</span>
                                <span v-else>Add Member</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Duplicate Warning Modal -->
        <Teleport to="body">
            <div
                v-if="showDuplicateWarning && duplicates.length > 0"
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="duplicate-warning-title"
                role="dialog"
                aria-modal="true"
            >
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Background overlay -->
                    <div
                        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                        aria-hidden="true"
                    ></div>

                    <!-- Center modal -->
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <!-- Modal panel -->
                    <div
                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
                    >
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <ExclamationTriangleIcon class="h-6 w-6 text-yellow-600" aria-hidden="true" />
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="duplicate-warning-title">
                                        Possible Duplicate Found
                                    </h3>
                                    <div class="mt-4">
                                        <p class="text-sm text-gray-500 mb-4">
                                            We found {{ duplicates.length }} similar {{ duplicates.length === 1 ? 'person' : 'people' }} in this family. 
                                            Please review to avoid creating duplicates:
                                        </p>
                                        
                                        <!-- Duplicate List -->
                                        <div class="space-y-3 max-h-96 overflow-y-auto">
                                            <div
                                                v-for="duplicate in duplicates"
                                                :key="duplicate.id"
                                                class="flex items-center gap-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg"
                                            >
                                                <!-- Photo -->
                                                <div class="flex-shrink-0">
                                                    <div
                                                        v-if="duplicate.photo_url"
                                                        class="h-12 w-12 rounded-full overflow-hidden"
                                                    >
                                                        <img
                                                            :src="duplicate.photo_url"
                                                            :alt="duplicate.name"
                                                            class="h-full w-full object-cover"
                                                        />
                                                    </div>
                                                    <div
                                                        v-else
                                                        class="h-12 w-12 rounded-full bg-yellow-200 flex items-center justify-center"
                                                    >
                                                        <UserIcon class="h-6 w-6 text-yellow-700" aria-hidden="true" />
                                                    </div>
                                                </div>

                                                <!-- Info -->
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900">
                                                        {{ duplicate.name }}
                                                    </p>
                                                    <p class="text-xs text-gray-600">
                                                        <span v-if="duplicate.age">{{ duplicate.age }} years old</span>
                                                        <span v-else-if="duplicate.date_of_birth">Born {{ duplicate.date_of_birth }}</span>
                                                    </p>
                                                </div>

                                                <!-- Similarity Score -->
                                                <div class="flex-shrink-0">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        {{ duplicate.similarity_score }}% match
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button
                                type="button"
                                @click="proceedAnyway"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                Add Anyway
                            </button>
                            <button
                                type="button"
                                @click="cancelSubmit"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </UbumiLayout>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import UbumiLayout from '@/layouts/UbumiLayout.vue';
import MediaUploadButton from '@/components/MediaUploadButton.vue';
import { UserIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import { useDuplicateDetection } from '@/composables/useDuplicateDetection';
import { toast } from '@/utils/bizboost-toast';

interface Family {
    id: string;
    slug: string;
    name: string;
}

interface Person {
    id: string;
    slug: string;
    name: string;
}

interface RelationshipType {
    value: string;
    label: string;
}

const props = defineProps<{
    family: Family;
    availablePersons: Person[];
    relationshipTypes: RelationshipType[];
}>();

const form = useForm({
    name: '',
    photo_url: '',
    date_of_birth: '',
    approximate_age: null as number | null,
    gender: '',
    // Optional relationship fields
    related_person_id: '',
    relationship_type: '',
});

const photoPreview = ref<string | null>(null);
const showDuplicateWarning = ref(false);
const { checking, duplicates, checkForDuplicates } = useDuplicateDetection(props.family.id);

// Debounced duplicate check when name changes
let checkTimeout: NodeJS.Timeout;
watch([() => form.name, () => form.date_of_birth, () => form.approximate_age], () => {
    clearTimeout(checkTimeout);
    if (form.name.length >= 3) {
        checkTimeout = setTimeout(() => {
            checkForDuplicates({
                name: form.name,
                date_of_birth: form.date_of_birth || undefined,
                approximate_age: form.approximate_age || undefined,
            });
        }, 500);
    }
});

// Show warning modal when duplicates are found
watch(duplicates, (newDuplicates) => {
    if (newDuplicates.length > 0) {
        showDuplicateWarning.value = true;
    }
});

const handlePhotoUpload = (url: string) => {
    form.photo_url = url;
    photoPreview.value = url;
    toast.success('Photo uploaded', 'Profile photo added successfully');
};

const handlePhotoError = (error: string) => {
    console.error('Photo upload error:', error);
    toast.error('Upload failed', error);
};

const removePhoto = () => {
    form.photo_url = '';
    photoPreview.value = null;
};

const proceedAnyway = () => {
    showDuplicateWarning.value = false;
    form.post(route('ubumi.families.persons.store', props.family.slug), {
        onSuccess: () => {
            toast.success('Member added', 'Family member added successfully');
        },
        onError: () => {
            toast.error('Failed to add member', 'Please check the form and try again');
        },
    });
};

const cancelSubmit = () => {
    showDuplicateWarning.value = false;
};

const submit = () => {
    // Check for duplicates before submitting
    if (duplicates.value.length > 0 && showDuplicateWarning.value === false) {
        showDuplicateWarning.value = true;
        return;
    }
    
    form.post(route('ubumi.families.persons.store', props.family.slug), {
        onSuccess: () => {
            toast.success('Member added', 'Family member added successfully');
        },
        onError: () => {
            toast.error('Failed to add member', 'Please check the form and try again');
        },
    });
};
</script>
