<template>
    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-4xl mx-auto px-4">
            <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">Logo Size Demonstration</h1>
            
            <div class="space-y-8">
                <!-- Size Examples -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Logo Sizes</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="flex items-center space-x-4 p-4 border rounded">
                                <Logo size="xs" />
                                <span class="text-sm text-gray-600">Extra Small (xs)</span>
                            </div>
                            <div class="flex items-center space-x-4 p-4 border rounded">
                                <Logo size="sm" />
                                <span class="text-sm text-gray-600">Small (sm)</span>
                            </div>
                            <div class="flex items-center space-x-4 p-4 border rounded">
                                <Logo size="md" />
                                <span class="text-sm text-gray-600">Medium (md)</span>
                            </div>
                            <div class="flex items-center space-x-4 p-4 border rounded">
                                <Logo size="lg" />
                                <span class="text-sm text-gray-600">Large (lg)</span>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-4 p-4 border rounded">
                                <Logo size="xl" />
                                <span class="text-sm text-gray-600">Extra Large (xl)</span>
                            </div>
                            <div class="flex items-center space-x-4 p-4 border rounded">
                                <Logo size="2xl" />
                                <span class="text-sm text-gray-600">2X Large (2xl)</span>
                            </div>
                            <div class="flex items-center space-x-4 p-4 border rounded">
                                <Logo size="3xl" />
                                <span class="text-sm text-gray-600">3X Large (3xl)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- With Text Examples -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Logo with Text</h2>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4 p-4 border rounded">
                            <Logo size="md" :showText="true" />
                            <span class="text-sm text-gray-600">Medium with text</span>
                        </div>
                        <div class="flex items-center space-x-4 p-4 border rounded">
                            <Logo size="lg" :showText="true" />
                            <span class="text-sm text-gray-600">Large with text</span>
                        </div>
                        <div class="flex items-center space-x-4 p-4 border rounded">
                            <Logo size="xl" :showText="true" />
                            <span class="text-sm text-gray-600">Extra Large with text</span>
                        </div>
                    </div>
                </div>

                <!-- Dark Background Examples -->
                <div class="bg-slate-800 rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4 text-white">Logo on Dark Background</h2>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4 p-4 border border-slate-600 rounded">
                            <Logo size="lg" variant="white" />
                            <span class="text-sm text-slate-300">Large white variant</span>
                        </div>
                        <div class="flex items-center space-x-4 p-4 border border-slate-600 rounded">
                            <Logo size="xl" variant="white" :showText="true" />
                            <span class="text-sm text-slate-300">Extra Large with white text</span>
                        </div>
                    </div>
                </div>

                <!-- Navigation Preview -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Navigation Bar Preview</h2>
                    <div class="bg-slate-800 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <Logo size="lg" />
                            <div class="flex space-x-4">
                                <span class="text-white text-sm">Home</span>
                                <span class="text-white text-sm">About</span>
                                <span class="text-white text-sm">Contact</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import Logo from '../components/Logo.vue';
</script>