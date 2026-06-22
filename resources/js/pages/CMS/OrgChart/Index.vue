<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref, computed } from 'vue';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { ChevronDownIcon, ChevronRightIcon, MagnifyingGlassIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';
import { toast } from '@/utils/bizboost-toast';
import OrgTreeNode from './OrgTreeNode.vue';

interface WorkerNode {
    id: number;
    name: string;
    job_title: string | null;
    department: string | null;
    photo: string | null;
    children: WorkerNode[];
}

interface WorkerFlat {
    id: number;
    name: string;
    job_title: string | null;
    department_name: string | null;
    manager_id: number | null;
    photo: string | null;
}

interface DeptNode {
    id: number;
    name: string;
    children: DeptNode[];
}

const props = defineProps<{
    orgTree: WorkerNode[];
    workers: WorkerFlat[];
    departmentTree: DeptNode[];
}>();

const searchQuery = ref('');
const expanded = ref<Set<number>>(new Set());
const exporting = ref(false);

const filteredWorkers = computed(() => {
    const q = searchQuery.value.toLowerCase();
    if (!q) return [];
    return props.workers.filter(w =>
        w.name.toLowerCase().includes(q) ||
        (w.job_title || '').toLowerCase().includes(q) ||
        (w.department_name || '').toLowerCase().includes(q)
    );
});

function toggle(id: number) {
    if (expanded.value.has(id)) expanded.value.delete(id);
    else expanded.value.add(id);
}

function findPath(id: number): string[] {
    const path: string[] = [];
    const map = new Map<number, WorkerFlat>();
    props.workers.forEach(w => map.set(w.id, w));
    let cur = map.get(id);
    while (cur) {
        path.unshift(cur.name);
        cur = cur.manager_id ? map.get(cur.manager_id) : undefined;
    }
    return path;
}

async function downloadPdf() {
    exporting.value = true;
    try {
        const el = document.getElementById('org-chart-tree');
        if (!el) return;
        const html2canvas = (await import('html2canvas-pro')).default;
        const jsPDF = (await import('jspdf')).default;
        const canvas = await html2canvas(el, {
            scale: 2, backgroundColor: '#ffffff', useCORS: true,
            logging: false, width: el.scrollWidth, height: el.scrollHeight,
        });
        const imgData = canvas.toDataURL('image/png');
        const pdf = new jsPDF('l', 'mm', 'a4');
        const pdfW = pdf.internal.pageSize.getWidth();
        const pdfH = (canvas.height / canvas.width) * pdfW;
        pdf.addImage(imgData, 'PNG', 0, 0, pdfW, pdfH);
        pdf.save('org-chart.pdf');
        toast.success('Exported', 'Org chart downloaded as PDF');
    } catch {
        toast.error('Export failed', 'Could not generate PDF');
    } finally {
        exporting.value = false;
    }
}

const viewMode = ref<'tree' | 'cards'>('tree');
</script>

<template>
    <Head title="Org Chart" />

    <CMSLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Organization Chart</h1>
                    <p class="text-sm text-gray-500 mt-1">Visualize your team's reporting structure</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                        <input v-model="searchQuery" type="text" placeholder="Find someone..."
                            class="pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-64" />
                    </div>
                    <div class="flex rounded-lg border border-gray-300 overflow-hidden">
                        <button @click="viewMode = 'tree'"
                            :class="viewMode === 'tree' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                            class="px-3 py-2 text-sm font-medium transition">Tree</button>
                        <button @click="viewMode = 'cards'"
                            :class="viewMode === 'cards' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                            class="px-3 py-2 text-sm font-medium transition">Cards</button>
                    </div>
                    <button @click="downloadPdf" :disabled="exporting"
                        class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition text-sm font-medium">
                        <ArrowDownTrayIcon class="h-4 w-4" />
                        {{ exporting ? 'Exporting...' : 'Download PDF' }}
                    </button>
                </div>
            </div>

            <!-- Search results -->
            <div v-if="searchQuery && filteredWorkers.length" class="bg-white rounded-lg shadow p-4">
                <p class="text-xs text-gray-500 mb-2">Search results ({{ filteredWorkers.length }})</p>
                <div class="space-y-1">
                    <div v-for="w in filteredWorkers" :key="w.id"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 cursor-pointer text-sm"
                        @click="searchQuery = ''; expanded.add(w.id)">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-xs font-bold">
                            {{ w.name.charAt(0) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ w.name }}</p>
                            <p class="text-xs text-gray-500">{{ w.job_title || '—' }} <span v-if="w.department_name">· {{ w.department_name }}</span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else-if="searchQuery && !filteredWorkers.length" class="bg-white rounded-lg shadow p-6 text-center text-sm text-gray-500">
                No workers match "{{ searchQuery }}"
            </div>

            <!-- Tree View -->
            <div v-if="viewMode === 'tree'" id="org-chart-tree" class="bg-white rounded-lg shadow p-8 overflow-x-auto">
                <div v-if="!orgTree.length" class="text-center py-12 text-gray-500 text-sm">
                    No workers found. Add workers and assign managers to build your org chart.
                </div>
                <div v-else class="inline-flex flex-col items-center min-w-full">
                    <div v-for="root in orgTree" :key="root.id" class="flex flex-col items-center">
                        <OrgTreeNode :node="root" :expanded="expanded" :depth="0" @toggle="toggle" />
                    </div>
                </div>
            </div>

            <!-- Cards View -->
            <div v-if="viewMode === 'cards'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <div v-for="w in workers" :key="w.id"
                    class="bg-white rounded-lg shadow p-4 border border-gray-100 hover:shadow-md transition">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-sm">
                            {{ w.name.charAt(0) }}{{ w.name.split(' ').pop()?.charAt(0) }}
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900 text-sm truncate">{{ w.name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ w.job_title || '—' }}</p>
                        </div>
                    </div>
                    <div v-if="w.department_name" class="mt-2 text-xs text-gray-400">
                        {{ w.department_name }}
                    </div>
                    <div v-if="w.manager_id" class="mt-1 text-xs text-gray-400">
                        Reports to: <span class="font-medium text-gray-600">{{ workers.find(x => x.id === w.manager_id)?.name || 'Unknown' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </CMSLayout>
</template>
