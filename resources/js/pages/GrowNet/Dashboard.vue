<template>
  <AppLayout title="MyGrowNet Dashboard">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="mb-8">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ user.name }}!</h1>
              <p class="text-gray-600 mt-1">
                {{ currentTier }} Member • {{ subscription?.package?.name || 'No Active Subscription' }}
              </p>
            </div>
            <div class="flex items-center space-x-4">
              <Link
                :href="route('home')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                title="Back to Home Hub"
              >
                <Squares2X2Icon class="h-4 w-4 mr-2" aria-hidden="true" />
                Home Hub
              </Link>
              <button
                @click="switchToMobileView"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                title="Switch to Mobile View"
              >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Mobile View
              </button>
              <button
                @click="refreshData"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                :disabled="loading"
              >
                <ArrowPathIcon class="h-4 w-4 mr-2" :class="{ 'animate-spin': loading }" />
                Refresh
              </button>
              <Link
                :href="route('mygrownet.membership.upgrade')"
                v-if="membershipProgress.eligibility"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
              >
                <ArrowUpIcon class="h-4 w-4 mr-2" />
                Upgrade Tier
              </Link>
            </div>
          </div>
        </div>

        <!-- Starter Kit Welcome Card -->
        <div v-if="starterKit && starterKit.received" class="mb-6 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg shadow-lg p-6 text-white">
          <div class="flex items-start justify-between flex-wrap gap-4">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-2">
                <GiftIcon class="h-6 w-6 flex-shrink-0" />
                <h3 class="text-lg font-semibold">{{ starterKit.package_name }}</h3>
              </div>
              <p class="text-sm text-purple-100 mb-3">
                Received on {{ starterKit.received_date }} • Status: <span class="font-semibold">{{ starterKit.status }}</span>
              </p>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div v-for="(feature, index) in starterKit.features.slice(0, 4)" :key="index" class="flex items-start gap-2">
                  <span class="text-purple-200 mt-0.5 flex-shrink-0">✓</span>
                  <span class="text-sm text-purple-50">{{ feature }}</span>
                </div>
              </div>
            </div>
            <div class="flex-shrink-0">
              <div class="bg-white/20 backdrop-blur-sm rounded-lg p-4 text-center min-w-[100px]">
                <svg class="h-8 w-8 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <p class="text-2xl font-bold">100</p>
                <p class="text-xs text-purple-100">LP Bonus</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Notifications -->
        <div v-if="notifications.length > 0" class="mb-6">
          <div class="space-y-3">
            <div
              v-for="notification in notifications"
              :key="notification.type"
              class="rounded-md p-4"
              :class="{
                'bg-red-50 border border-red-200': notification.priority === 'high',
                'bg-yellow-50 border border-yellow-200': notification.priority === 'medium',
                'bg-blue-50 border border-blue-200': notification.priority === 'low'
              }"
            >
              <div class="flex">
                <div class="flex-shrink-0">
                  <ExclamationTriangleIcon
                    v-if="notification.priority === 'high'"
                    class="h-5 w-5 text-red-400"
                  />
                  <InformationCircleIcon
                    v-else-if="notification.priority === 'medium'"
                    class="h-5 w-5 text-yellow-400"
                  />
                  <CheckCircleIcon v-else class="h-5 w-5 text-blue-400" />
                </div>
                <div class="ml-3 flex-1">
                  <h3 class="text-sm font-medium" :class="{
                    'text-red-800': notification.priority === 'high',
                    'text-yellow-800': notification.priority === 'medium',
                    'text-blue-800': notification.priority === 'low'
                  }">
                    {{ notification.title }}
                  </h3>
                  <div class="mt-1 text-sm" :class="{
                    'text-red-700': notification.priority === 'high',
                    'text-yellow-700': notification.priority === 'medium',
                    'text-blue-700': notification.priority === 'low'
                  }">
                    {{ notification.message }}
                  </div>
                  <div v-if="notification.action_url" class="mt-2">
                    <Link
                      :href="notification.action_url"
                      class="text-sm font-medium underline"
                      :class="{
                        'text-red-600 hover:text-red-500': notification.priority === 'high',
                        'text-yellow-600 hover:text-yellow-500': notification.priority === 'medium',
                        'text-blue-600 hover:text-blue-500': notification.priority === 'low'
                      }"
                    >
                      Take Action →
                    </Link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Key Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <CurrencyDollarIcon class="h-6 w-6 text-green-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Earnings</dt>
                    <dd class="text-lg font-medium text-gray-900">
                      K{{ formatCurrency(stats.total_earnings) }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <UsersIcon class="h-6 w-6 text-blue-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Team Size</dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ networkData?.total_network_size || 0 }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <ChartBarIcon class="h-6 w-6 text-purple-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Team Volume</dt>
                    <dd class="text-lg font-medium text-gray-900">
                      K{{ formatCurrency(teamVolumeData.current_month.team_volume) }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <BuildingOffice2Icon class="h-6 w-6 text-indigo-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Active Assets</dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ assetData.summary.active_assets }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Left Column -->
          <div class="lg:col-span-2 space-y-8">
            <!-- Five-Level Commission Tracking -->
            <div class="bg-white shadow rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Commission Levels</h3>
                <p class="text-sm text-gray-500">Track your five-level commission structure</p>
              </div>
              <div class="p-6">
                <div class="space-y-4">
                  <div
                    v-for="(level, index) in referralStats.levels"
                    :key="level.level"
                    class="flex items-center justify-between p-4 border rounded-lg"
                    :class="{
                      'border-blue-200 bg-blue-50': level.level === 1,
                      'border-green-200 bg-green-50': level.level === 2,
                      'border-yellow-200 bg-yellow-50': level.level === 3,
                      'border-purple-200 bg-purple-50': level.level === 4,
                      'border-red-200 bg-red-50': level.level === 5
                    }"
                  >
                    <div class="flex items-center">
                      <div
                        class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium text-white"
                        :class="{
                          'bg-blue-500': level.level === 1,
                          'bg-green-500': level.level === 2,
                          'bg-yellow-500': level.level === 3,
                          'bg-purple-500': level.level === 4,
                          'bg-red-500': level.level === 5
                        }"
                      >
                        {{ level.level }}
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">
                          Level {{ level.level }}
                        </div>
                        <div class="text-sm text-gray-500">
                          {{ level.count }} referrals
                        </div>
                      </div>
                    </div>
                    <div class="text-right">
                      <div class="text-sm font-medium text-gray-900">
                        K{{ formatCurrency(level.total_earnings) }}
                      </div>
                      <div class="text-sm text-gray-500">
                        K{{ formatCurrency(level.this_month_earnings) }} this month
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Team Volume Visualization -->
            <div class="bg-white shadow rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Team Volume</h3>
                <p class="text-sm text-gray-500">Monitor your team's performance and volume</p>
              </div>
              <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Volume Breakdown -->
                  <div>
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Volume Breakdown</h4>
                    <div class="space-y-3">
                      <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Personal Volume</span>
                        <span class="text-sm font-medium">
                          K{{ formatCurrency(teamVolumeData.current_month.personal_volume) }}
                        </span>
                      </div>
                      <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Team Volume</span>
                        <span class="text-sm font-medium">
                          K{{ formatCurrency(teamVolumeData.current_month.team_volume) }}
                        </span>
                      </div>
                      <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Left Leg Volume</span>
                        <span class="text-sm font-medium">
                          K{{ formatCurrency(teamVolumeData.current_month.left_leg_volume) }}
                        </span>
                      </div>
                      <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Right Leg Volume</span>
                        <span class="text-sm font-medium">
                          K{{ formatCurrency(teamVolumeData.current_month.right_leg_volume) }}
                        </span>
                      </div>
                      <div class="border-t pt-3">
                        <div class="flex justify-between items-center">
                          <span class="text-sm font-medium text-gray-900">Total Volume</span>
                          <span class="text-sm font-bold text-blue-600">
                            K{{ formatCurrency(teamVolumeData.current_month.total_volume) }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Volume Trend Chart -->
                  <div>
                    <h4 class="text-sm font-medium text-gray-900 mb-3">6-Month Trend</h4>
                    <div class="h-32 flex items-end space-x-2">
                      <div
                        v-for="(month, index) in teamVolumeData.monthly_trend"
                        :key="index"
                        class="flex-1 bg-blue-200 rounded-t"
                        :style="{
                          height: `${Math.max((month.volume / maxVolume) * 100, 5)}%`
                        }"
                        :title="`${month.month}: K${formatCurrency(month.volume)}`"
                      ></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mt-2">
                      <span>{{ teamVolumeData.monthly_trend[0]?.month }}</span>
                      <span>{{ teamVolumeData.monthly_trend[teamVolumeData.monthly_trend.length - 1]?.month }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Asset Tracking -->
            <div class="bg-white shadow rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Asset Portfolio</h3>
                <p class="text-sm text-gray-500">Track your physical rewards and income generation</p>
              </div>
              <div class="p-6">
                <div v-if="assetData.summary.total_assets > 0">
                  <!-- Asset Summary -->
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                      <div class="text-2xl font-bold text-indigo-600">{{ assetData.summary.total_assets }}</div>
                      <div class="text-sm text-gray-500">Total Assets</div>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                      <div class="text-2xl font-bold text-green-600">K{{ formatCurrency(assetData.summary.total_income_generated) }}</div>
                      <div class="text-sm text-gray-500">Total Income</div>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                      <div class="text-2xl font-bold text-blue-600">K{{ formatCurrency(assetData.summary.monthly_income_average) }}</div>
                      <div class="text-sm text-gray-500">Monthly Average</div>
                    </div>
                  </div>

                  <!-- Asset List -->
                  <div class="space-y-3">
                    <h4 class="text-sm font-medium text-gray-900">Your Assets</h4>
                    <div
                      v-for="asset in assetData.assets.slice(0, 3)"
                      :key="asset.id"
                      class="flex items-center justify-between p-4 border rounded-lg"
                    >
                      <div class="flex items-center">
                        <div class="flex-shrink-0">
                          <div
                            class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-medium"
                            :class="{
                              'bg-green-500': asset.status === 'ownership_transferred',
                              'bg-blue-500': asset.status === 'delivered',
                              'bg-yellow-500': asset.status === 'allocated',
                              'bg-gray-500': asset.status === 'pending'
                            }"
                          >
                            <BuildingOffice2Icon class="h-5 w-5" />
                          </div>
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">{{ asset.name }}</div>
                          <div class="text-sm text-gray-500">
                            {{ asset.category }} • {{ asset.allocated_at }}
                          </div>
                        </div>
                      </div>
                      <div class="text-right">
                        <div class="text-sm font-medium text-gray-900">
                          K{{ formatCurrency(asset.total_income) }}
                        </div>
                        <div
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="{
                            'bg-green-100 text-green-800': asset.status === 'ownership_transferred',
                            'bg-blue-100 text-blue-800': asset.status === 'delivered',
                            'bg-yellow-100 text-yellow-800': asset.status === 'allocated',
                            'bg-gray-100 text-gray-800': asset.status === 'pending'
                          }"
                        >
                          {{ asset.status.replace('_', ' ') }}
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Asset Income Trends -->
                  <div v-if="assetData.income_trends.length > 0" class="mt-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Income Trends (6 Months)</h4>
                    <div class="h-32 flex items-end space-x-2">
                      <div
                        v-for="(trend, index) in assetData.income_trends"
                        :key="index"
                        class="flex-1 bg-indigo-200 rounded-t"
                        :style="{
                          height: `${Math.max((trend.income / maxAssetIncome) * 100, 5)}%`
                        }"
                        :title="`${trend.month}: K${formatCurrency(trend.income)}`"
                      ></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mt-2">
                      <span>{{ assetData.income_trends[0]?.month }}</span>
                      <span>{{ assetData.income_trends[assetData.income_trends.length - 1]?.month }}</span>
                    </div>
                  </div>

                  <!-- View All Assets Link -->
                  <div v-if="assetData.assets.length > 3" class="mt-4 text-center">
                    <Link
                      :href="route('mygrownet.assets.index')"
                      class="text-sm text-indigo-600 hover:text-indigo-500"
                    >
                      View all {{ assetData.assets.length }} assets →
                    </Link>
                  </div>
                </div>
                <div v-else class="text-center py-8">
                  <BuildingOffice2Icon class="h-12 w-12 text-gray-300 mx-auto mb-4" />
                  <h3 class="text-sm font-medium text-gray-900 mb-2">No Assets Yet</h3>
                  <p class="text-sm text-gray-500 mb-4">
                    Build your team and maintain performance to qualify for physical rewards
                  </p>
                  <Link
                    :href="route('mygrownet.rewards.index')"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                  >
                    View Available Rewards
                  </Link>
                </div>
              </div>
            </div>

            <!-- Community Investment Portfolio -->
            <div class="bg-white shadow rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Community Investment Portfolio</h3>
                <p class="text-sm text-gray-500">Your community project investments and returns</p>
              </div>
              <div class="p-6">
                <div v-if="communityProjectData.portfolio_summary.total_projects > 0">
                  <!-- Portfolio Summary -->
                  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                      <div class="text-2xl font-bold text-blue-600">{{ communityProjectData.portfolio_summary.total_projects }}</div>
                      <div class="text-sm text-gray-500">Total Projects</div>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                      <div class="text-2xl font-bold text-green-600">K{{ formatCurrency(communityProjectData.portfolio_summary.total_contributed) }}</div>
                      <div class="text-sm text-gray-500">Total Invested</div>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                      <div class="text-2xl font-bold text-purple-600">K{{ formatCurrency(communityProjectData.portfolio_summary.total_returns_received) }}</div>
                      <div class="text-sm text-gray-500">Returns Received</div>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                      <div class="text-2xl font-bold" :class="{
                        'text-green-600': communityProjectData.portfolio_summary.net_roi >= 0,
                        'text-red-600': communityProjectData.portfolio_summary.net_roi < 0
                      }">
                        {{ communityProjectData.portfolio_summary.net_roi.toFixed(1) }}%
                      </div>
                      <div class="text-sm text-gray-500">Net ROI</div>
                    </div>
                  </div>

                  <!-- Recent Contributions -->
                  <div v-if="communityProjectData.recent_contributions.length > 0" class="mb-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Recent Contributions</h4>
                    <div class="space-y-3">
                      <div
                        v-for="contribution in communityProjectData.recent_contributions.slice(0, 3)"
                        :key="contribution.id"
                        class="flex items-center justify-between p-3 border rounded-lg"
                      >
                        <div class="flex items-center">
                          <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                              <BuildingOfficeIcon class="h-4 w-4 text-blue-600" />
                            </div>
                          </div>
                          <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900">{{ contribution.project_name }}</div>
                            <div class="text-sm text-gray-500">{{ contribution.contributed_at }}</div>
                          </div>
                        </div>
                        <div class="text-right">
                          <div class="text-sm font-medium text-gray-900">
                            K{{ formatCurrency(contribution.amount) }}
                          </div>
                          <div
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                            :class="{
                              'bg-green-100 text-green-800': contribution.status === 'confirmed',
                              'bg-yellow-100 text-yellow-800': contribution.status === 'pending',
                              'bg-gray-100 text-gray-800': contribution.status === 'cancelled'
                            }"
                          >
                            {{ contribution.status }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Investment Trends -->
                  <div v-if="communityProjectData.investment_trends.length > 0" class="mb-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Investment Trends (6 Months)</h4>
                    <div class="h-32 flex items-end space-x-2">
                      <div
                        v-for="(trend, index) in communityProjectData.investment_trends"
                        :key="index"
                        class="flex-1 space-y-1"
                      >
                        <!-- Returns bar -->
                        <div
                          class="bg-green-200 rounded-t"
                          :style="{
                            height: `${Math.max((trend.returns / maxInvestmentAmount) * 60, 2)}px`
                          }"
                          :title="`${trend.month} Returns: K${formatCurrency(trend.returns)}`"
                        ></div>
                        <!-- Contributions bar -->
                        <div
                          class="bg-blue-200 rounded-b"
                          :style="{
                            height: `${Math.max((trend.contributions / maxInvestmentAmount) * 60, 2)}px`
                          }"
                          :title="`${trend.month} Contributions: K${formatCurrency(trend.contributions)}`"
                        ></div>
                      </div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mt-2">
                      <span>{{ communityProjectData.investment_trends[0]?.month }}</span>
                      <span>{{ communityProjectData.investment_trends[communityProjectData.investment_trends.length - 1]?.month }}</span>
                    </div>
                    <div class="flex items-center justify-center space-x-4 mt-2 text-xs">
                      <div class="flex items-center">
                        <div class="w-3 h-3 bg-blue-200 rounded mr-1"></div>
                        <span>Contributions</span>
                      </div>
                      <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-200 rounded mr-1"></div>
                        <span>Returns</span>
                      </div>
                    </div>
                  </div>

                  <!-- Available Projects -->
                  <div v-if="communityProjectData.available_projects.length > 0">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Available Projects</h4>
                    <div class="space-y-3">
                      <div
                        v-for="project in communityProjectData.available_projects.slice(0, 2)"
                        :key="project.id"
                        class="p-4 border rounded-lg hover:bg-gray-50 transition-colors"
                      >
                        <div class="flex items-center justify-between mb-2">
                          <div class="flex items-center">
                            <h5 class="text-sm font-medium text-gray-900">{{ project.name }}</h5>
                            <span v-if="project.is_featured" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                              Featured
                            </span>
                          </div>
                          <div class="text-sm text-gray-500">{{ project.expected_annual_return }}% return</div>
                        </div>
                        <div class="mb-3">
                          <div class="flex justify-between text-sm text-gray-600 mb-1">
                            <span>Funding Progress</span>
                            <span>{{ project.funding_progress.toFixed(1) }}%</span>
                          </div>
                          <div class="w-full bg-gray-200 rounded-full h-2">
                            <div
                              class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                              :style="{ width: `${project.funding_progress}%` }"
                            ></div>
                          </div>
                        </div>
                        <div class="flex items-center justify-between">
                          <div class="text-sm text-gray-500">
                            Min: K{{ formatCurrency(project.minimum_contribution) }} • 
                            {{ project.days_remaining }} days left
                          </div>
                          <button
                            v-if="project.user_can_contribute"
                            class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                            @click="showContributeModal(project)"
                          >
                            Contribute
                          </button>
                        </div>
                      </div>
                    </div>
                    <div class="mt-4 text-center">
                      <Link
                        :href="route('mygrownet.projects.index')"
                        class="text-sm text-blue-600 hover:text-blue-500"
                      >
                        View all {{ communityProjectData.available_projects.length }} projects →
                      </Link>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-8">
                  <BuildingOfficeIcon class="h-12 w-12 text-gray-300 mx-auto mb-4" />
                  <h3 class="text-sm font-medium text-gray-900 mb-2">No Community Investments Yet</h3>
                  <p class="text-sm text-gray-500 mb-4">
                    Start investing in community projects to diversify your portfolio
                  </p>
                  <Link
                    :href="route('mygrownet.projects.index')"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
                  >
                    Explore Projects
                  </Link>
                </div>
              </div>
            </div>

            <!-- Network Structure -->
            <div class="bg-white shadow rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Network Structure</h3>
                <p class="text-sm text-gray-500">Your multilevel network overview</p>
              </div>
              <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                  <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ networkData.network_depth }}</div>
                    <div class="text-sm text-gray-500">Network Depth</div>
                  </div>
                  <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ networkData.active_members }}</div>
                    <div class="text-sm text-gray-500">Active Members</div>
                  </div>
                  <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ networkData.total_network_size }}</div>
                    <div class="text-sm text-gray-500">Total Network</div>
                  </div>
                </div>

                <!-- Direct Referrals -->
                <div v-if="networkData.direct_referrals.length > 0">
                  <h4 class="text-sm font-medium text-gray-900 mb-3">Direct Referrals</h4>
                  <div class="space-y-3">
                    <div
                      v-for="referral in networkData.direct_referrals.slice(0, 5)"
                      :key="referral.id"
                      class="flex items-center justify-between p-3 border rounded-lg"
                    >
                      <div class="flex items-center">
                        <div class="flex-shrink-0">
                          <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                            <span class="text-xs font-medium text-gray-700">
                              {{ referral.name.charAt(0).toUpperCase() }}
                            </span>
                          </div>
                        </div>
                        <div class="ml-3">
                          <div class="text-sm font-medium text-gray-900">{{ referral.name }}</div>
                          <div class="text-sm text-gray-500">
                            {{ referral.tier }} • {{ referral.joined_date }}
                          </div>
                        </div>
                      </div>
                      <div class="text-right">
                        <div
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="{
                            'bg-green-100 text-green-800': referral.status === 'active',
                            'bg-gray-100 text-gray-800': referral.status === 'inactive'
                          }"
                        >
                          {{ referral.status }}
                        </div>
                        <div class="text-sm text-gray-500 mt-1">
                          Team: {{ referral.team_size }}
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-if="networkData.direct_referrals.length > 5" class="mt-3 text-center">
                    <Link
                      :href="route('mygrownet.network.index')"
                      class="text-sm text-blue-600 hover:text-blue-500"
                    >
                      View all {{ networkData.direct_referrals.length }} referrals →
                    </Link>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column -->
          <div class="space-y-8">
            <!-- Membership Progress -->
            <div class="bg-white shadow rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Membership Progress</h3>
              </div>
              <div class="p-6">
                <div v-if="membershipProgress.current_tier" class="mb-4">
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-900">
                      {{ membershipProgress.current_tier.name }}
                    </span>
                    <span v-if="membershipProgress.next_tier" class="text-sm text-gray-500">
                      {{ membershipProgress.progress_percentage }}%
                    </span>
                  </div>
                  <div v-if="membershipProgress.next_tier" class="w-full bg-gray-200 rounded-full h-2">
                    <div
                      class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                      :style="{ width: `${membershipProgress.progress_percentage}%` }"
                    ></div>
                  </div>
                  <div v-if="membershipProgress.next_tier" class="text-sm text-gray-500 mt-2">
                    Next: {{ membershipProgress.next_tier.name }}
                  </div>
                  <div v-else class="text-sm text-green-600 mt-2">
                    {{ membershipProgress.message }}
                  </div>
                </div>
                <div v-else class="text-center py-4">
                  <p class="text-sm text-gray-500 mb-4">No active membership tier</p>
                  <Link
                    :href="route('mygrownet.membership.upgrade')"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
                  >
                    Get Started
                  </Link>
                </div>
              </div>
            </div>

            <!-- Project Voting & Alerts -->
            <div class="bg-white shadow rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Project Governance</h3>
              </div>
              <div class="p-6">
                <!-- Pending Votes -->
                <div v-if="communityProjectData.pending_votes.length > 0" class="space-y-3 mb-4">
                  <h4 class="text-sm font-medium text-gray-900">Pending Votes</h4>
                  <div
                    v-for="vote in communityProjectData.pending_votes"
                    :key="vote.vote_session_id"
                    class="p-3 bg-blue-50 border border-blue-200 rounded-lg"
                  >
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <CheckCircleIcon class="h-5 w-5 text-blue-400" />
                      </div>
                      <div class="ml-3 flex-1">
                        <div class="text-sm font-medium text-blue-800">
                          {{ vote.project_name }}
                        </div>
                        <div class="text-sm text-blue-700">
                          {{ vote.vote_subject }}
                        </div>
                        <div class="text-xs text-blue-600 mt-1">
                          {{ vote.days_remaining }} days remaining • Power: {{ vote.voting_power.toFixed(1) }}
                        </div>
                        <div class="mt-2">
                          <button
                            @click="showVotingModal(vote)"
                            class="text-xs text-blue-600 hover:text-blue-500 underline"
                          >
                            Cast Vote →
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Project Alerts -->
                <div v-if="communityProjectData.project_alerts.length > 0" class="space-y-3 mb-4">
                  <h4 class="text-sm font-medium text-gray-900">Project Alerts</h4>
                  <div
                    v-for="alert in communityProjectData.project_alerts"
                    :key="alert.project_id + alert.type"
                    class="p-3 rounded-lg"
                    :class="{
                      'bg-red-50 border border-red-200': alert.priority === 'high',
                      'bg-yellow-50 border border-yellow-200': alert.priority === 'medium'
                    }"
                  >
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <ExclamationTriangleIcon
                          class="h-5 w-5"
                          :class="{
                            'text-red-400': alert.priority === 'high',
                            'text-yellow-400': alert.priority === 'medium'
                          }"
                        />
                      </div>
                      <div class="ml-3">
                        <div class="text-sm font-medium" :class="{
                          'text-red-800': alert.priority === 'high',
                          'text-yellow-800': alert.priority === 'medium'
                        }">
                          {{ alert.project_name }}
                        </div>
                        <div class="text-sm" :class="{
                          'text-red-700': alert.priority === 'high',
                          'text-yellow-700': alert.priority === 'medium'
                        }">
                          {{ alert.message }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- No Governance Items -->
                <div v-if="communityProjectData.pending_votes.length === 0 && communityProjectData.project_alerts.length === 0" class="text-center py-4">
                  <CheckCircleIcon class="h-12 w-12 text-green-300 mx-auto mb-2" />
                  <p class="text-sm text-gray-500">No pending governance items</p>
                </div>
              </div>
            </div>

            <!-- Asset Alerts & Opportunities -->
            <div class="bg-white shadow rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Asset Alerts</h3>
              </div>
              <div class="p-6">
                <!-- Maintenance Alerts -->
                <div v-if="assetData.maintenance_alerts.length > 0" class="space-y-3 mb-4">
                  <div
                    v-for="alert in assetData.maintenance_alerts"
                    :key="alert.allocation_id"
                    class="p-3 rounded-lg"
                    :class="{
                      'bg-red-50 border border-red-200': alert.priority === 'critical',
                      'bg-yellow-50 border border-yellow-200': alert.priority === 'high'
                    }"
                  >
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <ExclamationTriangleIcon
                          class="h-5 w-5"
                          :class="{
                            'text-red-400': alert.priority === 'critical',
                            'text-yellow-400': alert.priority === 'high'
                          }"
                        />
                      </div>
                      <div class="ml-3">
                        <div class="text-sm font-medium" :class="{
                          'text-red-800': alert.priority === 'critical',
                          'text-yellow-800': alert.priority === 'high'
                        }">
                          {{ alert.asset_name }}
                        </div>
                        <div class="text-sm" :class="{
                          'text-red-700': alert.priority === 'critical',
                          'text-yellow-700': alert.priority === 'high'
                        }">
                          {{ alert.message }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Ownership Opportunities -->
                <div v-if="assetData.ownership_opportunities.length > 0" class="space-y-3">
                  <h4 class="text-sm font-medium text-gray-900">Ownership Opportunities</h4>
                  <div
                    v-for="opportunity in assetData.ownership_opportunities"
                    :key="opportunity.allocation_id"
                    class="p-3 bg-green-50 border border-green-200 rounded-lg"
                  >
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <CheckCircleIcon class="h-5 w-5 text-green-400" />
                      </div>
                      <div class="ml-3">
                        <div class="text-sm font-medium text-green-800">
                          {{ opportunity.asset_name }}
                        </div>
                        <div class="text-sm text-green-700">
                          Ready for ownership transfer
                        </div>
                        <div class="text-xs text-green-600 mt-1">
                          Value: K{{ formatCurrency(opportunity.estimated_value) }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- No Alerts -->
                <div v-if="assetData.maintenance_alerts.length === 0 && assetData.ownership_opportunities.length === 0" class="text-center py-4">
                  <CheckCircleIcon class="h-12 w-12 text-green-300 mx-auto mb-2" />
                  <p class="text-sm text-gray-500">All assets in good standing</p>
                </div>
              </div>
            </div>

            <!-- Recent Achievements -->
            <div class="bg-white shadow rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Achievements</h3>
              </div>
              <div class="p-6">
                <div v-if="recentAchievements.length > 0" class="space-y-3">
                  <div
                    v-for="achievement in recentAchievements"
                    :key="achievement.id"
                    class="flex items-center p-3 border rounded-lg"
                  >
                    <div class="flex-shrink-0">
                      <TrophyIcon class="h-6 w-6 text-yellow-500" />
                    </div>
                    <div class="ml-3">
                      <div class="text-sm font-medium text-gray-900">
                        {{ achievement.achievement.name }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ formatDate(achievement.created_at) }}
                      </div>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-4">
                  <TrophyIcon class="h-12 w-12 text-gray-300 mx-auto mb-2" />
                  <p class="text-sm text-gray-500">No achievements yet</p>
                </div>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
              </div>
              <div class="p-6">
                <div class="space-y-3">
                  <Link
                    :href="route('mygrownet.referrals.index')"
                    class="flex items-center p-3 border rounded-lg hover:bg-gray-50 transition-colors"
                  >
                    <UsersIcon class="h-5 w-5 text-blue-500 mr-3" />
                    <span class="text-sm font-medium">Manage Referrals</span>
                  </Link>
                  <Link
                    :href="route('mygrownet.commissions.index')"
                    class="flex items-center p-3 border rounded-lg hover:bg-gray-50 transition-colors"
                  >
                    <CurrencyDollarIcon class="h-5 w-5 text-green-500 mr-3" />
                    <span class="text-sm font-medium">View Commissions</span>
                  </Link>
                  <Link
                    :href="route('mygrownet.projects.index')"
                    class="flex items-center p-3 border rounded-lg hover:bg-gray-50 transition-colors"
                  >
                    <BuildingOfficeIcon class="h-5 w-5 text-purple-500 mr-3" />
                    <span class="text-sm font-medium">Community Projects</span>
                  </Link>
                  <Link
                    :href="route('mygrownet.learning.index')"
                    class="flex items-center p-3 border rounded-lg hover:bg-gray-50 transition-colors"
                  >
                    <AcademicCapIcon class="h-5 w-5 text-indigo-500 mr-3" />
                    <span class="text-sm font-medium">Learning Center</span>
                  </Link>
                  <Link
                    :href="route('mygrownet.assets.index')"
                    class="flex items-center p-3 border rounded-lg hover:bg-gray-50 transition-colors"
                  >
                    <BuildingOffice2Icon class="h-5 w-5 text-orange-500 mr-3" />
                    <span class="text-sm font-medium">Manage Assets</span>
                  </Link>
                  <Link
                    :href="route('mygrownet.tools.index')"
                    class="flex items-center p-3 border rounded-lg hover:bg-gray-50 transition-colors"
                  >
                    <DocumentTextIcon class="h-5 w-5 text-amber-500 mr-3" />
                    <span class="text-sm font-medium">Business Tools</span>
                  </Link>
                  <a
                    href="/quick-invoice"
                    class="flex items-center p-3 border rounded-lg hover:bg-gray-50 transition-colors"
                  >
                    <DocumentDuplicateIcon class="h-5 w-5 text-emerald-500 mr-3" />
                    <span class="text-sm font-medium">Quick Invoice</span>
                    <span class="ml-auto text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">Free</span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed, ref, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/layouts/AppLayout.vue'
import {
  ArrowPathIcon,
  ArrowUpIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  CheckCircleIcon,
  CurrencyDollarIcon,
  UsersIcon,
  ChartBarIcon,
  TrophyIcon,
  BuildingOfficeIcon,
  BuildingOffice2Icon,
  AcademicCapIcon,
  GiftIcon,
  Squares2X2Icon,
  DocumentTextIcon,
  DocumentDuplicateIcon
} from '@heroicons/vue/24/outline'

interface Props {
  user: any
  subscription: any
  starterKit?: {
    received: boolean
    package_name: string
    received_date: string
    features: string[]
    status: string
  } | null
  membershipProgress: any
  learningProgress: any
  referralStats: any
  leaderboardPosition: number | null
  recentAchievements: any[]
  availableProjects: any[]
  userInvestments: any[]
  monthlyStats: any[]
  notifications: any[]
  teamVolumeData: any
  networkData: any
  assetData: any
  communityProjectData: any
  stats: any
}

const props = defineProps<Props>()

const loading = ref(false)

const currentTier = computed(() => {
  return props.membershipProgress.current_tier?.name || 'No Tier'
})

const maxVolume = computed(() => {
  return Math.max(...props.teamVolumeData.monthly_trend.map((m: any) => m.volume), 1)
})

const maxAssetIncome = computed(() => {
  if (!props.assetData.income_trends || props.assetData.income_trends.length === 0) {
    return 1
  }
  return Math.max(...props.assetData.income_trends.map((t: any) => t.income), 1)
})

const maxInvestmentAmount = computed(() => {
  if (!props.communityProjectData.investment_trends || props.communityProjectData.investment_trends.length === 0) {
    return 1
  }
  const maxContributions = Math.max(...props.communityProjectData.investment_trends.map((t: any) => t.contributions))
  const maxReturns = Math.max(...props.communityProjectData.investment_trends.map((t: any) => t.returns))
  return Math.max(maxContributions, maxReturns, 1)
})

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount || 0)
}

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

const refreshData = async () => {
  loading.value = true
  try {
    router.reload({ only: ['stats', 'referralStats', 'teamVolumeData', 'networkData', 'assetData', 'communityProjectData'] })
  } finally {
    loading.value = false
  }
}

const switchToMobileView = async () => {
  try {
    // Update user preference to mobile using axios
    await axios.post(route('mygrownet.api.user.dashboard-preference'), {
      preference: 'mobile'
    });
    
    // Redirect to mobile dashboard
    window.location.href = route('dashboard');
  } catch (error) {
    console.error('Switch view error:', error);
    // If error, save preference in localStorage as fallback and redirect anyway
    localStorage.setItem('preferred_dashboard', 'mobile');
    window.location.href = route('dashboard');
  }
}

const showContributeModal = (project: any) => {
  // This would open a contribution modal
  console.log('Show contribute modal for project:', project.name)
}

const showVotingModal = (vote: any) => {
  // This would open a voting modal
  console.log('Show voting modal for:', vote.vote_subject)
}

onMounted(() => {
  // Any initialization logic
})
</script>