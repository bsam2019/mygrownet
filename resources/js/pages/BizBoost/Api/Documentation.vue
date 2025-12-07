<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    ArrowLeftIcon,
    CodeBracketIcon,
    KeyIcon,
    ShieldCheckIcon,
    CubeIcon,
    UserGroupIcon,
    DocumentTextIcon,
} from '@heroicons/vue/24/outline';

const endpoints = [
    {
        category: 'Products',
        icon: CubeIcon,
        items: [
            { method: 'GET', path: '/api/v1/products', description: 'List all products' },
            { method: 'GET', path: '/api/v1/products/{id}', description: 'Get product details' },
            { method: 'POST', path: '/api/v1/products', description: 'Create a product' },
            { method: 'PUT', path: '/api/v1/products/{id}', description: 'Update a product' },
            { method: 'DELETE', path: '/api/v1/products/{id}', description: 'Delete a product' },
        ],
    },
    {
        category: 'Customers',
        icon: UserGroupIcon,
        items: [
            { method: 'GET', path: '/api/v1/customers', description: 'List all customers' },
            { method: 'GET', path: '/api/v1/customers/{id}', description: 'Get customer details' },
            { method: 'POST', path: '/api/v1/customers', description: 'Create a customer' },
            { method: 'PUT', path: '/api/v1/customers/{id}', description: 'Update a customer' },
            { method: 'DELETE', path: '/api/v1/customers/{id}', description: 'Delete a customer' },
        ],
    },
    {
        category: 'Sales',
        icon: DocumentTextIcon,
        items: [
            { method: 'GET', path: '/api/v1/sales', description: 'List all sales' },
            { method: 'GET', path: '/api/v1/sales/{id}', description: 'Get sale details' },
            { method: 'POST', path: '/api/v1/sales', description: 'Record a sale' },
        ],
    },
];

const methodColors: Record<string, string> = {
    GET: 'bg-emerald-100 text-emerald-700',
    POST: 'bg-blue-100 text-blue-700',
    PUT: 'bg-amber-100 text-amber-700',
    DELETE: 'bg-red-100 text-red-700',
};
</script>

<template>
    <Head title="API Documentation - BizBoost" />
    <BizBoostLayout title="API Documentation">
        <div class="space-y-6">
            <!-- Back Link -->
            <Link
                href="/bizboost/api-tokens"
                class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-violet-600"
            >
                <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                Back to API Tokens
            </Link>

            <!-- Header -->
            <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="rounded-lg bg-violet-100 p-2">
                        <CodeBracketIcon class="h-6 w-6 text-violet-600" aria-hidden="true" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">BizBoost API</h1>
                        <p class="text-sm text-gray-500">Version 1.0</p>
                    </div>
                </div>
                <p class="text-gray-600">
                    The BizBoost API allows you to integrate your business data with external applications.
                    Use your API token to authenticate requests.
                </p>
            </div>

            <!-- Authentication -->
            <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6">
                <div class="flex items-center gap-2 mb-4">
                    <KeyIcon class="h-5 w-5 text-violet-600" aria-hidden="true" />
                    <h2 class="text-lg font-semibold text-gray-900">Authentication</h2>
                </div>
                <p class="text-gray-600 mb-4">
                    Include your API token in the Authorization header of all requests:
                </p>
                <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                    <code class="text-sm text-emerald-400">
                        Authorization: Bearer YOUR_API_TOKEN
                    </code>
                </div>
            </div>

            <!-- Rate Limits -->
            <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6">
                <div class="flex items-center gap-2 mb-4">
                    <ShieldCheckIcon class="h-5 w-5 text-violet-600" aria-hidden="true" />
                    <h2 class="text-lg font-semibold text-gray-900">Rate Limits</h2>
                </div>
                <p class="text-gray-600">
                    API requests are limited to <strong>1000 requests per hour</strong> per token.
                    Rate limit headers are included in all responses.
                </p>
            </div>

            <!-- Endpoints -->
            <div class="space-y-6">
                <h2 class="text-lg font-semibold text-gray-900">Endpoints</h2>
                
                <div
                    v-for="section in endpoints"
                    :key="section.category"
                    class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 overflow-hidden"
                >
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center gap-2">
                        <component :is="section.icon" class="h-5 w-5 text-gray-500" aria-hidden="true" />
                        <h3 class="font-semibold text-gray-900">{{ section.category }}</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div
                            v-for="endpoint in section.items"
                            :key="endpoint.path"
                            class="px-6 py-3 flex items-center gap-4"
                        >
                            <span
                                :class="[methodColors[endpoint.method], 'px-2 py-1 rounded text-xs font-mono font-medium w-16 text-center']"
                            >
                                {{ endpoint.method }}
                            </span>
                            <code class="text-sm text-gray-700 font-mono flex-1">{{ endpoint.path }}</code>
                            <span class="text-sm text-gray-500">{{ endpoint.description }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Example Request -->
            <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Example Request</h2>
                <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                    <pre class="text-sm text-gray-300"><code>curl -X GET "https://api.mygrownet.com/bizboost/v1/products" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Accept: application/json"</code></pre>
                </div>
            </div>

            <!-- Example Response -->
            <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Example Response</h2>
                <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                    <pre class="text-sm text-gray-300"><code>{
  "data": [
    {
      "id": 1,
      "name": "Product Name",
      "price": 99.99,
      "category": "Electronics",
      "in_stock": true
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 25
  }
}</code></pre>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
