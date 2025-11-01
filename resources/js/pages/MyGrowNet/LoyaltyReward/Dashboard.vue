<template>
  <MemberLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-900">
        Loyalty Growth Reward
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="overflow-hidden bg-gradient-to-r from-blue-500 to-blue-600 shadow-sm sm:rounded-lg">
          <div class="p-6 text-white">
            <h3 class="text-2xl font-bold">Loyalty Growth Reward Program</h3>
            <p class="mt-2 text-blue-100">
              Earn up to K2,100 in Loyalty Credits over 70 days through active participation
            </p>
          </div>
        </div>

        <!-- Qualification Status -->
        <div v-if="!qualification.fully_qualified" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h4 class="text-lg font-semibold text-gray-900">Qualification Requirements</h4>
            <p class="mt-2 text-sm text-gray-600">
              Complete all requirements below to start your 70-day reward cycle
            </p>

            <div class="mt-6 space-y-4">
              <!-- Starter Package -->
              <div class="flex items-center justify-between rounded-lg border p-4">
                <div class="flex items-center space-x-3">
                  <div
                    :class="[
                      'flex h-10 w-10 items-center justify-center rounded-full',
                      qualification.starter_package_completed
                        ? 'bg-green-100 text-green-600'
                        : 'bg-gray-100 text-gray-400',
                    ]"
                  >
                    <svg
                      v-if="qualification.starter_package_completed"
                      class="h-6 w-6"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 13l4 4L19 7"
                      />
                    </svg>
                    <span v-else class="text-lg font-bold">1</span>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">Purchase K1,000 Starter Package</p>
                    <p class="text-sm text-gray-500">Access learning materials and platform tools</p>
                  </div>
                </div>
                <Link
                  v-if="!qualification.starter_package_completed"
                  :href="route('mygrownet.starter-kit.show')"
                  class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                >
                  Get Started
                </Link>
              </div>

              <!-- Training -->
              <div class="flex items-center justify-between rounded-lg border p-4">
                <div class="flex items-center space-x-3">
                  <div
                    :class="[
                      'flex h-10 w-10 items-center justify-center rounded-full',
                      qualification.training_completed
                        ? 'bg-green-100 text-green-600'
                        : 'bg-gray-100 text-gray-400',
                    ]"
                  >
                    <svg
                      v-if="qualification.training_completed"
                      class="h-6 w-6"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 13l4 4L19 7"
                      />
                    </svg>
                    <span v-else class="text-lg font-bold">2</span>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">Complete Business Fundamentals Training</p>
                    <p class="text-sm text-gray-500">Essential business knowledge course</p>
                  </div>
                </div>
              </div>

              <!-- Network -->
              <div class="flex items-center justify-between rounded-lg border p-4">
                <div class="flex items-center space-x-3">
                  <div
                    :class="[
                      'flex h-10 w-10 items-center justify-center rounded-full',
                      qualification.network_requirement_met
                        ? 'bg-green-100 text-green-600'
                        : 'bg-gray-100 text-gray-400',
                    ]"
                  >
                    <svg
                      v-if="qualification.network_requirement_met"
                      class="h-6 w-6"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 13l4 4L19 7"
                      />
                    </svg>
                    <span v-else class="text-lg font-bold">3</span>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">Build First-Level Team (3 Members)</p>
                    <p class="text-sm text-gray-500">
                      Current: {{ qualification.progress.network.current }} / 3 members
                    </p>
                  </div>
                </div>
              </div>

              <!-- Activities -->
              <div class="flex items-center justify-between rounded-lg border p-4">
                <div class="flex items-center space-x-3">
                  <div
                    :class="[
                      'flex h-10 w-10 items-center justify-center rounded-full',
                      qualification.activity_requirement_met
                        ? 'bg-green-100 text-green-600'
                        : 'bg-gray-100 text-gray-400',
                    ]"
                  >
                    <svg
                      v-if="qualification.activity_requirement_met"
                      class="h-6 w-6"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 13l4 4L19 7"
                      />
                    </svg>
                    <span v-else class="text-lg font-bold">4</span>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">Complete 2 Platform Activities</p>
                    <p class="text-sm text-gray-500">
                      Current: {{ qualification.progress.activities.current }} / 2 activities
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Active Cycle Dashboard -->
        <div v-if="qualification.fully_qualified && cycle.has_active_cycle" class="space-y-6">
          <!-- Stats Cards -->
          <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
              <div class="p-6">
                <p class="text-sm font-medium text-gray-500">Current Cycle Day</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">
                  {{ cycle.cycle.active_days }} / 70
                </p>
              </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
              <div class="p-6">
                <p class="text-sm font-medium text-gray-500">Total Earned</p>
                <p class="mt-2 text-3xl font-bold text-green-600">
                  K{{ cycle.cycle.total_earned_lgc.toFixed(2) }}
                </p>
              </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
              <div class="p-6">
                <p class="text-sm font-medium text-gray-500">Projected Earnings</p>
                <p class="mt-2 text-3xl font-bold text-blue-600">
                  K{{ cycle.cycle.projected_earnings.toFixed(2) }}
                </p>
              </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
              <div class="p-6">
                <p class="text-sm font-medium text-gray-500">Days Remaining</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">
                  {{ cycle.cycle.remaining_days }}
                </p>
              </div>
            </div>
          </div>

          <!-- Today's Activity -->
          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <h4 class="text-lg font-semibold text-gray-900">Today's Activity</h4>
              <div v-if="cycle.has_activity_today" class="mt-4 rounded-lg bg-green-50 p-4">
                <div class="flex items-center">
                  <svg
                    class="h-6 w-6 text-green-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                  <p class="ml-3 text-sm font-medium text-green-800">
                    Activity completed! You earned K30 today.
                  </p>
                </div>
              </div>
              <div v-else class="mt-4 rounded-lg bg-yellow-50 p-4">
                <p class="text-sm text-yellow-800">
                  Complete an activity today to earn K30 in Loyalty Credits
                </p>
                <div class="mt-4 space-y-2">
                  <p class="text-sm font-medium text-gray-900">Quick Activities:</p>
                  <ul class="list-inside list-disc space-y-1 text-sm text-gray-600">
                    <li>Complete a learning module</li>
                    <li>Make a marketplace purchase</li>
                    <li>Attend a platform event</li>
                    <li>Engage in community discussions</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Progress Bar -->
          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <h4 class="text-lg font-semibold text-gray-900">Cycle Progress</h4>
                <span class="text-sm text-gray-500">
                  {{ cycle.cycle.completion_rate.toFixed(1) }}% Complete
                </span>
              </div>
              <div class="mt-4 h-4 w-full overflow-hidden rounded-full bg-gray-200">
                <div
                  class="h-full bg-blue-600 transition-all duration-500"
                  :style="{ width: cycle.cycle.completion_rate + '%' }"
                ></div>
              </div>
            </div>
          </div>

          <!-- Recent Activities -->
          <div v-if="cycle.recent_activities.length > 0" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <h4 class="text-lg font-semibold text-gray-900">Recent Activities</h4>
              <div class="mt-4 space-y-3">
                <div
                  v-for="activity in cycle.recent_activities"
                  :key="activity.date"
                  class="flex items-center justify-between rounded-lg border p-3"
                >
                  <div>
                    <p class="text-sm font-medium text-gray-900">{{ activity.description }}</p>
                    <p class="text-xs text-gray-500">{{ activity.date }}</p>
                  </div>
                  <span class="text-sm font-semibold text-green-600">
                    +K{{ activity.lgc_earned }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Start Cycle Button -->
        <div v-if="qualification.fully_qualified && !cycle.has_active_cycle" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-center">
            <h4 class="text-lg font-semibold text-gray-900">You're Qualified!</h4>
            <p class="mt-2 text-sm text-gray-600">
              Start your 70-day Loyalty Growth Reward cycle now
            </p>
            <form @submit.prevent="startCycle" class="mt-6">
              <button
                type="submit"
                :disabled="processing"
                class="rounded-md bg-blue-600 px-6 py-3 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
              >
                {{ processing ? 'Starting...' : 'Start My Cycle' }}
              </button>
            </form>
          </div>
        </div>

        <!-- Wallet Balance -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h4 class="text-lg font-semibold text-gray-900">Loyalty Credits Balance</h4>
            <p class="mt-2 text-3xl font-bold text-blue-600">
              K{{ Number(user.loyalty_points || 0).toFixed(2) }}
            </p>
            <p class="mt-2 text-sm text-gray-600">
              Use for platform purchases, venture investments, or convert up to 40% to cash
            </p>
            <Link
              :href="route('loyalty-reward.policy')"
              class="mt-3 inline-flex items-center text-sm text-blue-600 hover:text-blue-800"
            >
              <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              View LGR Policy
            </Link>
          </div>
        </div>
      </div>
    </div>
  </MemberLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';

interface Props {
  qualification: any;
  cycle: any;
  user: any;
}

const props = defineProps<Props>();
const processing = ref(false);

const startCycle = () => {
  processing.value = true;
  router.post(
    route('mygrownet.loyalty-reward.start-cycle'),
    {},
    {
      onFinish: () => {
        processing.value = false;
      },
    }
  );
};
</script>
