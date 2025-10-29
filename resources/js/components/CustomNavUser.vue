<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { type SharedData, type User } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { ChevronsUpDown } from 'lucide-vue-next';
import UserMenuContent from './UserMenuContent.vue';

const page = usePage<SharedData>();
const user = page.props.auth?.user as User | undefined;
</script>

<template>
    <div v-if="user" class="px-2">
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <button 
                    class="w-full flex items-center gap-2 rounded-lg px-3 py-2 text-left text-sm transition-colors hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <UserInfo :user="user" />
                    <ChevronsUpDown class="ml-auto size-4 text-gray-500" />
                </button>
            </DropdownMenuTrigger>
            <DropdownMenuContent 
                class="w-56 rounded-lg" 
                side="right"
                align="end" 
                :side-offset="8"
            >
                <UserMenuContent :user="user" />
            </DropdownMenuContent>
        </DropdownMenu>
    </div>
</template>
