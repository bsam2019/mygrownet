<template>
    <div class="min-h-screen bg-gradient-to-br from-purple-50 via-indigo-50 to-blue-50 pb-20">
        <!-- Mobile Header (shown on all devices) -->
        <header class="bg-gradient-to-r from-purple-500 to-indigo-600 text-white shadow-lg sticky top-0 z-30">
            <div class="px-4 py-3 flex items-center justify-between">
                <!-- Logo and Brand -->
                <Link :href="route('ubumi.index')" class="flex items-center gap-2">
                    <div class="h-10 w-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold">Ubumi</span>
                </Link>

                <!-- User Avatar -->
                <button
                    @click="showUserMenu = !showUserMenu"
                    class="h-10 w-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-sm font-semibold hover:bg-white/30 transition-colors"
                >
                    {{ userInitials }}
                </button>
            </div>
        </header>

        <!-- Main Content -->
        <main class="md:max-w-7xl md:mx-auto md:px-4 md:py-6">
            <slot />
        </main>

        <!-- Mobile Bottom Navigation (shown on all devices) -->
        <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-40">
            <div class="grid grid-cols-4 h-16">
                <!-- Home/Dashboard -->
                <Link
                    :href="route('ubumi.index')"
                    class="flex flex-col items-center justify-center gap-1 transition-colors"
                    :class="route().current('ubumi.index') 
                        ? 'text-purple-600' 
                        : 'text-gray-500 active:bg-gray-50'"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-xs font-medium">Home</span>
                </Link>

                <!-- Persons -->
                <Link
                    :href="route('ubumi.persons.index')"
                    class="flex flex-col items-center justify-center gap-1 transition-colors"
                    :class="route().current('ubumi.persons.*') 
                        ? 'text-purple-600' 
                        : 'text-gray-500 active:bg-gray-50'"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="text-xs font-medium">People</span>
                </Link>

                <!-- Add -->
                <button
                    @click="showAddMenu = !showAddMenu"
                    class="flex flex-col items-center justify-center gap-1 text-white relative"
                >
                    <div class="absolute -top-6 h-14 w-14 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 shadow-lg flex items-center justify-center">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-gray-500 mt-6">Add</span>
                </button>

                <!-- More -->
                <button
                    @click="showMoreMenu = !showMoreMenu"
                    class="flex flex-col items-center justify-center gap-1 transition-colors"
                    :class="showMoreMenu 
                        ? 'text-purple-600' 
                        : 'text-gray-500 active:bg-gray-50'"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span class="text-xs font-medium">More</span>
                </button>
            </div>
        </nav>

        <!-- Add Menu Modal -->
        <Teleport to="body">
            <Transition name="slide-up">
                <div 
                    v-if="showAddMenu" 
                    class="fixed inset-0 z-50 flex items-end"
                    @click.self="showAddMenu = false"
                >
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
                    <div class="relative w-full bg-white rounded-t-3xl shadow-2xl p-6 pb-8">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-gray-900">Add New</h3>
                            <button 
                                @click="showAddMenu = false"
                                class="p-2 hover:bg-gray-100 rounded-full"
                            >
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <Link
                                :href="route('ubumi.families.create')"
                                class="flex flex-col items-center gap-3 p-6 bg-gradient-to-br from-purple-50 to-indigo-50 rounded-2xl hover:shadow-lg transition-all active:scale-95"
                                @click="showAddMenu = false"
                            >
                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                </div>
                                <span class="text-sm font-semibold text-gray-900">New Family</span>
                            </Link>
                            <Link
                                :href="route('ubumi.persons.create')"
                                class="flex flex-col items-center gap-3 p-6 bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl hover:shadow-lg transition-all active:scale-95"
                                @click="showAddMenu = false"
                            >
                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <span class="text-sm font-semibold text-gray-900">New Person</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- More Menu Modal -->
        <Teleport to="body">
            <Transition name="slide-up">
                <div 
                    v-if="showMoreMenu" 
                    class="fixed inset-0 z-50 flex items-end"
                    @click.self="showMoreMenu = false"
                >
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
                    <div class="relative w-full bg-white rounded-t-3xl shadow-2xl p-6 pb-8">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-gray-900">Menu</h3>
                            <button 
                                @click="showMoreMenu = false"
                                class="p-2 hover:bg-gray-100 rounded-full"
                            >
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="space-y-2">
                            <Link
                                :href="route('dashboard')"
                                class="flex items-center gap-4 p-4 hover:bg-gray-50 rounded-xl transition-colors"
                                @click="showMoreMenu = false"
                            >
                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                </div>
                                <span class="font-medium text-gray-900">Back to MyGrowNet</span>
                            </Link>
                            <Link
                                :href="route('profile.edit')"
                                class="flex items-center gap-4 p-4 hover:bg-gray-50 rounded-xl transition-colors"
                                @click="showMoreMenu = false"
                            >
                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-purple-400 to-indigo-500 flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">{{ userInitials }}</span>
                                </div>
                                <span class="font-medium text-gray-900">Profile Settings</span>
                            </Link>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="flex items-center gap-4 p-4 hover:bg-red-50 rounded-xl transition-colors w-full text-left"
                                @click="showMoreMenu = false"
                            >
                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-red-400 to-red-500 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </div>
                                <span class="font-medium text-red-600">Logout</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{
    title?: string;
}>();

const page = usePage();
const showUserMenu = ref(false);
const showAddMenu = ref(false);
const showMoreMenu = ref(false);

const userInitials = computed(() => {
    const name = page.props.auth?.user?.name || '';
    return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
});
</script>

<style scoped>
/* Slide up transition for modals */
.slide-up-enter-active,
.slide-up-leave-active {
  transition: opacity 0.3s ease;
}

.slide-up-enter-active > div:last-child,
.slide-up-leave-active > div:last-child {
  transition: transform 0.3s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
  opacity: 0;
}

.slide-up-enter-from > div:last-child {
  transform: translateY(100%);
}

.slide-up-leave-to > div:last-child {
  transform: translateY(100%);
}
</style>
