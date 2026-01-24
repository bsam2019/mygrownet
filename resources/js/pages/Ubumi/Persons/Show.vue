<template>
  <UbumiLayout :title="person.name">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <Link
          :href="route('ubumi.families.show', { family: person.family_slug })"
          class="inline-flex items-center text-sm text-emerald-600 hover:text-emerald-700 mb-4"
        >
          <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
          Back to Family Members
        </Link>
      </div>

      <!-- Person Card -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 px-6 py-8">
          <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
              <!-- Photo -->
              <div class="flex-shrink-0">
                <div
                  v-if="person.photo_url"
                  class="h-24 w-24 rounded-full bg-white p-1 shadow-lg"
                >
                  <img
                    :src="person.photo_url"
                    :alt="person.name"
                    class="h-full w-full rounded-full object-cover"
                  />
                </div>
                <div
                  v-else
                  class="h-24 w-24 rounded-full bg-white flex items-center justify-center shadow-lg"
                >
                  <UserIcon class="h-12 w-12 text-emerald-600" aria-hidden="true" />
                </div>
              </div>

              <!-- Name and Status -->
              <div>
                <h1 class="text-3xl font-bold text-white">{{ person.name }}</h1>
                <div class="mt-2 flex items-center gap-3">
                  <span
                    v-if="person.is_deceased"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-800 text-white"
                  >
                    Deceased
                  </span>
                  <span
                    v-if="person.gender"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/20 text-white"
                  >
                    {{ person.gender === 'male' ? 'Male' : 'Female' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-2">
              <Link
                :href="route('ubumi.persons.edit', { person: person.id })"
                class="inline-flex items-center px-4 py-2 bg-white text-emerald-600 text-sm font-medium rounded-lg hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white"
              >
                <PencilIcon class="h-4 w-4 mr-2" aria-hidden="true" />
                Edit
              </Link>
            </div>
          </div>
        </div>

        <!-- Details Section -->
        <div class="px-6 py-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h2>
          <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Date of Birth -->
            <div v-if="person.date_of_birth">
              <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
              <dd class="mt-1 text-sm text-gray-900">
                {{ formatDate(person.date_of_birth) }}
                <span class="text-gray-500">({{ calculateAge(person.date_of_birth) }} years old)</span>
              </dd>
            </div>

            <!-- Approximate Age -->
            <div v-else-if="person.approximate_age">
              <dt class="text-sm font-medium text-gray-500">Approximate Age</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ person.approximate_age }} years</dd>
            </div>

            <!-- Gender -->
            <div v-if="person.gender">
              <dt class="text-sm font-medium text-gray-500">Gender</dt>
              <dd class="mt-1 text-sm text-gray-900 capitalize">{{ person.gender }}</dd>
            </div>

            <!-- Created Date -->
            <div>
              <dt class="text-sm font-medium text-gray-500">Added to Family Tree</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ formatDate(person.created_at) }}</dd>
            </div>

            <!-- Last Updated -->
            <div v-if="person.updated_at">
              <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ formatDate(person.updated_at) }}</dd>
            </div>
          </dl>
        </div>

        <!-- Relationships Section -->
        <div class="px-6 py-6 bg-gray-50 border-t border-gray-200">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Relationships</h2>
            <button
              @click="showAddRelationshipModal = true"
              class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
            >
              <PlusIcon class="h-4 w-4 mr-2" aria-hidden="true" />
              Add Relationship
            </button>
          </div>

          <!-- Relationships List -->
          <div v-if="Object.keys(relationships).length > 0" class="space-y-6">
            <div
              v-for="(relationshipList, type) in relationships"
              :key="type"
              class="bg-white rounded-lg border border-gray-200 p-4"
            >
              <h3 class="text-sm font-semibold text-gray-700 mb-3 capitalize">
                {{ relationshipList[0].type }}
              </h3>
              <div class="space-y-2">
                <div
                  v-for="relationship in relationshipList"
                  :key="relationship.id"
                  class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                >
                  <div class="flex items-center gap-3">
                    <!-- Photo -->
                    <div class="flex-shrink-0">
                      <div
                        v-if="relationship.related_person.photo_url"
                        class="h-10 w-10 rounded-full overflow-hidden"
                      >
                        <img
                          :src="relationship.related_person.photo_url"
                          :alt="relationship.related_person.name"
                          class="h-full w-full object-cover"
                        />
                      </div>
                      <div
                        v-else
                        class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center"
                      >
                        <UserIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                      </div>
                    </div>

                    <!-- Name and Info -->
                    <div>
                      <Link
                        :href="route('ubumi.families.persons.show', { family: person.family_slug, person: relationship.related_person.slug })"
                        class="text-sm font-medium text-gray-900 hover:text-emerald-600"
                      >
                        {{ relationship.related_person.name }}
                      </Link>
                      <div class="flex items-center gap-2 mt-0.5">
                        <span
                          v-if="relationship.related_person.age"
                          class="text-xs text-gray-500"
                        >
                          {{ relationship.related_person.age }} years old
                        </span>
                        <span
                          v-if="relationship.related_person.is_deceased"
                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-200 text-gray-700"
                        >
                          Deceased
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Remove Button -->
                  <button
                    @click="confirmRemoveRelationship(relationship.id)"
                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    aria-label="Remove relationship"
                  >
                    <svg
                      class="h-5 w-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                      aria-hidden="true"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                      />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div
            v-else
            class="text-center py-8 bg-white rounded-lg border border-gray-200"
          >
            <UserIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No relationships yet</h3>
            <p class="mt-1 text-sm text-gray-500">
              Get started by adding a relationship to this person.
            </p>
          </div>
        </div>

        <!-- Health Check-Ins Section -->
        <div class="px-6 py-6 border-t border-gray-200">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Health Check-Ins</h2>
            <button
              v-if="!person.is_deceased"
              @click="showCheckInModal = true"
              class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-indigo-600 text-white text-sm font-medium rounded-lg hover:from-purple-600 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
            >
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Check In
            </button>
          </div>

          <!-- Latest Check-In -->
          <div v-if="latestCheckIn" class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-lg p-4 mb-4">
            <div class="flex items-start gap-3">
              <span class="text-3xl">{{ latestCheckIn.status_emoji }}</span>
              <div class="flex-1">
                <div class="flex items-center gap-2">
                  <span class="font-semibold text-gray-900">{{ latestCheckIn.status_label }}</span>
                  <span
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                    :class="{
                      'bg-green-100 text-green-800': latestCheckIn.status_color === 'green',
                      'bg-amber-100 text-amber-800': latestCheckIn.status_color === 'amber',
                      'bg-red-100 text-red-800': latestCheckIn.status_color === 'red',
                    }"
                  >
                    Latest
                  </span>
                </div>
                <p v-if="latestCheckIn.note" class="text-sm text-gray-600 mt-1">{{ latestCheckIn.note }}</p>
                <p class="text-xs text-gray-500 mt-2">{{ formatDateTime(latestCheckIn.checked_in_at) }}</p>
              </div>
            </div>
          </div>

          <!-- View History Link -->
          <Link
            :href="route('ubumi.families.persons.check-ins.index', { family: person.family_slug, person: person.slug })"
            class="inline-flex items-center text-sm text-purple-600 hover:text-purple-700"
          >
            View full check-in history
            <svg class="h-4 w-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </Link>

          <!-- Empty State -->
          <div v-if="!latestCheckIn" class="text-center py-8 bg-gray-50 rounded-lg">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No check-ins yet</h3>
            <p class="mt-1 text-sm text-gray-500">
              Start tracking wellness by recording the first check-in.
            </p>
          </div>
        </div>
      </div>

      <!-- Danger Zone -->
      <div class="mt-6 bg-white rounded-lg shadow-sm border border-red-200 overflow-hidden">
        <div class="px-6 py-4 bg-red-50 border-b border-red-200">
          <h3 class="text-sm font-semibold text-red-900">Danger Zone</h3>
        </div>
        <div class="px-6 py-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-900">Delete this person</p>
              <p class="text-sm text-gray-600">
                This action cannot be undone. All data will be permanently removed.
              </p>
            </div>
            <button
              @click="confirmDelete"
              class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            >
              Delete Person
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Relationship Modal -->
    <Teleport to="body">
      <div
        v-if="showAddRelationshipModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
      >
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <!-- Background overlay -->
          <div
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
            aria-hidden="true"
            @click="closeModal"
          ></div>

          <!-- Center modal -->
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

          <!-- Modal panel -->
          <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
          >
            <form @submit.prevent="submitRelationship">
              <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                  <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                      Add Relationship
                    </h3>
                    <div class="mt-4 space-y-4">
                      <!-- Select Person -->
                      <div>
                        <label for="related_person_id" class="block text-sm font-medium text-gray-700">
                          Select Person
                        </label>
                        <select
                          id="related_person_id"
                          v-model="relationshipForm.related_person_id"
                          class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md"
                          required
                        >
                          <option value="">Choose a family member...</option>
                          <option
                            v-for="person in availablePersons"
                            :key="person.id"
                            :value="person.id"
                          >
                            {{ person.name }}
                          </option>
                        </select>
                        <p v-if="relationshipForm.errors.related_person_id" class="mt-1 text-sm text-red-600">
                          {{ relationshipForm.errors.related_person_id }}
                        </p>
                      </div>

                      <!-- Select Relationship Type -->
                      <div>
                        <label for="relationship_type" class="block text-sm font-medium text-gray-700">
                          Relationship Type
                        </label>
                        <select
                          id="relationship_type"
                          v-model="relationshipForm.relationship_type"
                          class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md"
                          required
                        >
                          <option value="">Choose relationship type...</option>
                          <option
                            v-for="type in relationshipTypes"
                            :key="type.value"
                            :value="type.value"
                          >
                            {{ type.label }}
                          </option>
                        </select>
                        <p v-if="relationshipForm.errors.relationship_type" class="mt-1 text-sm text-red-600">
                          {{ relationshipForm.errors.relationship_type }}
                        </p>
                      </div>

                      <!-- Error Message -->
                      <div v-if="relationshipForm.errors.error" class="rounded-md bg-red-50 p-4">
                        <div class="flex">
                          <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                              {{ relationshipForm.errors.error }}
                            </h3>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button
                  type="submit"
                  :disabled="relationshipForm.processing"
                  class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span v-if="relationshipForm.processing">Adding...</span>
                  <span v-else>Add Relationship</span>
                </button>
                <button
                  type="button"
                  @click="closeModal"
                  :disabled="relationshipForm.processing"
                  class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Check-In Modal -->
    <CheckInModal
      :show="showCheckInModal"
      :person-name="person.name"
      :family-slug="person.family_slug"
      :person-slug="person.slug"
      @close="showCheckInModal = false"
    />
  </UbumiLayout>
</template>

<script setup lang="ts">
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import UbumiLayout from '@/layouts/UbumiLayout.vue';
import CheckInModal from '@/components/Ubumi/CheckInModal.vue';
import { ArrowLeftIcon, UserIcon, PencilIcon, PlusIcon } from '@heroicons/vue/24/outline';

interface Person {
  id: string;
  slug: string;
  family_id: number;
  family_slug: string;
  name: string;
  photo_url: string | null;
  date_of_birth: string | null;
  approximate_age: number | null;
  gender: 'male' | 'female' | null;
  is_deceased: boolean;
  created_at: string;
  updated_at: string | null;
}

interface RelatedPerson {
  id: string;
  slug: string;
  name: string;
  photo_url: string | null;
  age: number | null;
  is_deceased: boolean;
}

interface Relationship {
  id: number;
  type: string;
  related_person: RelatedPerson;
}

interface AvailablePerson {
  id: string;
  name: string;
}

interface RelationshipType {
  value: string;
  label: string;
}

interface CheckIn {
  id: string;
  status: string;
  status_label: string;
  status_emoji: string;
  status_color: string;
  note: string | null;
  location: string | null;
  photo_url: string | null;
  checked_in_at: string;
  is_recent: boolean;
}

interface Props {
  person: Person;
  relationships: Record<string, Relationship[]>;
  availablePersons: AvailablePerson[];
  relationshipTypes: RelationshipType[];
  latestCheckIn?: CheckIn | null;
}

const props = defineProps<Props>();

const showAddRelationshipModal = ref(false);
const showCheckInModal = ref(false);

const relationshipForm = useForm({
  related_person_id: '',
  relationship_type: '',
});

const submitRelationship = () => {
  relationshipForm.post(
    route('ubumi.families.persons.relationships.store', {
      family: props.person.family_slug,
      person: props.person.slug,
    }),
    {
      preserveScroll: true,
      onSuccess: () => {
        showAddRelationshipModal.value = false;
        relationshipForm.reset();
      },
    }
  );
};

const closeModal = () => {
  showAddRelationshipModal.value = false;
  relationshipForm.reset();
};

const confirmRemoveRelationship = (relationshipId: number) => {
  if (confirm('Are you sure you want to remove this relationship?')) {
    router.delete(
      route('ubumi.persons.relationships.destroy', {
        family: props.person.family_id,
        person: props.person.id,
        relationship: relationshipId,
      }),
      {
        preserveScroll: true,
      }
    );
  }
};

const formatDate = (dateString: string): string => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};

const formatDateTime = (dateString: string): string => {
  const date = new Date(dateString);
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  });
};

const calculateAge = (dateOfBirth: string): number => {
  const today = new Date();
  const birthDate = new Date(dateOfBirth);
  let age = today.getFullYear() - birthDate.getFullYear();
  const monthDiff = today.getMonth() - birthDate.getMonth();

  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
    age--;
  }

  return age;
};

const confirmDelete = () => {
  if (confirm('Are you sure you want to delete this person? This action cannot be undone.')) {
    router.delete(route('ubumi.persons.destroy', { person: props.person.slug }), {
      preserveScroll: true,
      onSuccess: () => {
        router.visit(route('ubumi.families.show', { family: props.person.family_slug }));
      },
    });
  }
};
</script>
