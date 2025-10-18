<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, MoreHorizontal } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
}>();

const pages = computed(() => {
  return props.links.filter((link, index) => index !== 0 && index !== props.links.length - 1);
});

const hasPages = computed(() => {
  return props.links.length > 3;
});
</script>

<template>
  <div v-if="hasPages" class="flex items-center justify-between px-2 py-3 sm:px-6">
    <div class="flex justify-between flex-1 sm:hidden">
      <Link
        v-if="links[0].url"
        :href="links[0].url"
        class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-muted-foreground ring-1 ring-inset ring-muted hover:bg-muted rounded-md"
      >
        Previous
      </Link>
      <span
        v-else
        class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-muted-foreground ring-1 ring-inset ring-muted rounded-md opacity-50 cursor-not-allowed"
      >
        Previous
      </span>

      <Link
        v-if="links[links.length - 1].url"
        :href="links[links.length - 1].url"
        class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-muted-foreground ring-1 ring-inset ring-muted hover:bg-muted rounded-md"
      >
        Next
      </Link>
      <span
        v-else
        class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-muted-foreground ring-1 ring-inset ring-muted rounded-md opacity-50 cursor-not-allowed"
      >
        Next
      </span>
    </div>

    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
          <Link
            v-if="links[0].url"
            :href="links[0].url"
            class="relative inline-flex items-center rounded-l-md px-2 py-2 text-muted-foreground ring-1 ring-inset ring-muted hover:bg-muted focus:z-20 focus:outline-offset-0"
          >
            <span class="sr-only">Previous</span>
            <ChevronLeft class="h-5 w-5" aria-hidden="true" />
          </Link>
          <span
            v-else
            class="relative inline-flex items-center rounded-l-md px-2 py-2 text-muted-foreground ring-1 ring-inset ring-muted opacity-50 cursor-not-allowed"
          >
            <span class="sr-only">Previous</span>
            <ChevronLeft class="h-5 w-5" aria-hidden="true" />
          </span>

          <template v-for="(link, index) in pages" :key="index">
            <div v-if="link.url === null" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-muted-foreground ring-1 ring-inset ring-muted">
              <MoreHorizontal class="h-5 w-5" />
            </div>
            <Link
              v-else
              :href="link.url"
              :class="[
                link.active
                  ? 'z-10 bg-primary text-primary-foreground focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary'
                  : 'text-muted-foreground ring-1 ring-inset ring-muted hover:bg-muted focus:outline-offset-0',
                'relative inline-flex items-center px-4 py-2 text-sm font-medium focus:z-20',
              ]"
            >
              {{ link.label }}
            </Link>
          </template>

          <Link
            v-if="links[links.length - 1].url"
            :href="links[links.length - 1].url"
            class="relative inline-flex items-center rounded-r-md px-2 py-2 text-muted-foreground ring-1 ring-inset ring-muted hover:bg-muted focus:z-20 focus:outline-offset-0"
          >
            <span class="sr-only">Next</span>
            <ChevronRight class="h-5 w-5" aria-hidden="true" />
          </Link>
          <span
            v-else
            class="relative inline-flex items-center rounded-r-md px-2 py-2 text-muted-foreground ring-1 ring-inset ring-muted opacity-50 cursor-not-allowed"
          >
            <span class="sr-only">Next</span>
            <ChevronRight class="h-5 w-5" aria-hidden="true" />
          </span>
        </nav>
      </div>
    </div>
  </div>
</template>