<template>
  <Head title="Dashboard" />
  
  <!-- Modern Mobile SPA Layout -->
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Enhanced Header with Vibrant Gradient -->
    <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 text-white shadow-xl">
      <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32"></div>
      <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full -ml-24 -mb-24"></div>
      <div class="relative px-4 py-4">
        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center gap-3 flex-1 min-w-0">
            <!-- Logo with White Background -->
            <div class="relative flex-shrink-0 bg-white rounded-lg px-3 py-2 shadow-lg">
              <img 
                src="/logo.png" 
                alt="MyGrowNet" 
                class="h-10 w-auto object-contain"
                style="max-width: 130px;"
              />
            </div>
            <div class="flex-1 min-w-0">
              <h1 class="text-lg sm:text-xl font-bold tracking-tight animate-fade-in truncate">
                {{ timeBasedGreeting }}, {{ user?.name?.split(' ')[0] || 'User' }}! 👋
              </h1>
              <div class="flex items-center gap-2 mt-1 flex-wrap">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-white/25 backdrop-blur-sm border border-white/30 animate-slide-in">
                  {{ currentTier }} {{ user?.has_starter_kit ? '⭐' : '' }}
                </span>
              </div>
            </div>
          </div>
          <div class="flex items-center gap-1 flex-shrink-0">
            <NotificationBell />
            <button
              @click="refreshData"
              aria-label="Refresh dashboard data"
              class="p-2.5 rounded-xl bg-white/15 hover:bg-white/25 backdrop-blur-sm transition-all duration-200 active:scale-95 border border-white/20"
              :disabled="loading"
              title="Refresh"
            >
              <ArrowPathIcon class="h-5 w-5" :class="{ 'animate-spin': loading }" aria-hidden="true" />
            </button>
            <button
              @click="switchToClassicView"
              aria-label="Switch to classic desktop view"
              class="p-2.5 rounded-xl bg-white/15 hover:bg-white/25 backdrop-blur-sm transition-all duration-200 active:scale-95 border border-white/20 hidden md:block"
              title="Switch to Classic View"
            >
              <ComputerDesktopIcon class="h-5 w-5" aria-hidden="true" />
            </button>
          </div>
        </div>
      </div>
      <!-- Subtle separator -->
      <div class="h-px bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>
    </div>

    <!-- Main Content with proper padding for footer -->
    <div class="px-4 py-6 space-y-6 pb-24">
      
      <!-- Announcement Banner (shown on all tabs) -->
      <AnnouncementBanner 
        :announcement="currentAnnouncement"
        @dismissed="handleAnnouncementDismissed"
      />
      
      <!-- HOME TAB -->
      <div v-show="activeTab === 'home'" class="space-y-6">
        <!-- Starter Kit Banner (if not purchased) -->
        <div
          v-if="!user?.has_starter_kit"
          @click="showStarterKitModal = true"
          class="relative overflow-hidden bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600 rounded-2xl p-5 shadow-xl cursor-pointer hover:shadow-2xl transition-all active:scale-[0.98]"
        >
          <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -mr-20 -mt-20"></div>
          <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-16 -mb-16"></div>
          <div class="relative flex items-start gap-4">
            <div class="flex-shrink-0">
              <div class="w-14 h-14 bg-white/25 backdrop-blur-sm rounded-2xl flex items-center justify-center shadow-lg border border-white/30">
                <SparklesIcon class="h-7 w-7 text-white" />
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="text-white font-bold text-lg">Get Your Starter Kit</h3>
              <p class="text-purple-100 text-sm mt-1.5 leading-relaxed">
                Unlock learning resources, shop credit, and earning opportunities. Starting at K500!
              </p>
              <div class="flex items-center gap-2 mt-4 bg-white/20 backdrop-blur-sm rounded-xl px-4 py-2.5 w-fit border border-white/30">
                <span class="text-white text-sm font-bold">Learn More</span>
                <ChevronRightIcon class="h-4 w-4 text-white" />
              </div>
            </div>
          </div>
        </div>

        <!-- Primary Focus Card - Contextual based on user state -->
        <div v-else-if="loanSummary?.has_loan" class="bg-gradient-to-br from-amber-50 to-orange-50 border-l-4 border-amber-500 rounded-xl p-4 shadow-sm">
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
              <ExclamationTriangleIcon class="h-5 w-5 text-amber-600" />
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="text-sm font-bold text-amber-900 mb-1">Loan Repayment Focus</h3>
              <p class="text-xs text-amber-800 mb-2 leading-relaxed">
                Outstanding: <strong>K{{ formatCurrency(loanSummary.loan_balance) }}</strong>
              </p>
              <div class="w-full bg-amber-200 rounded-full h-2 overflow-hidden mb-2">
                <div 
                  class="bg-gradient-to-r from-amber-500 to-amber-600 h-2 rounded-full transition-all duration-500"
                  :style="{ width: `${loanSummary.repayment_progress || 0}%` }"
                ></div>
              </div>
              <p class="text-xs text-amber-700">
                {{ loanSummary.repayment_progress?.toFixed(0) || 0 }}% repaid • Keep earning to clear faster!
              </p>
            </div>
          </div>
        </div>

        <!-- Balance Card -->
        <BalanceCard
          :balance="walletBalance"
          :loading="loading"
          @refresh="refreshData"
          @deposit="showDepositModal = true"
          @withdraw="showWithdrawalModal = true"
        />

        <!-- Loan Warning Banner -->
        <div v-if="loanSummary?.has_loan" class="bg-gradient-to-br from-amber-50 to-orange-50 border-l-4 border-amber-500 rounded-xl p-4 shadow-sm">
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
              <ExclamationTriangleIcon class="h-5 w-5 text-amber-600" />
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="text-sm font-bold text-amber-900 mb-1">Outstanding Loan</h3>
              <p class="text-xs text-amber-800 mb-3 leading-relaxed">
                You have an outstanding loan of <strong class="font-bold">K{{ formatCurrency(loanSummary.loan_balance) }}</strong>. 
                All future earnings will automatically go towards loan repayment.
              </p>
              <div class="space-y-2">
                <div class="flex justify-between text-xs text-amber-700">
                  <span class="font-medium">Repayment Progress</span>
                  <span class="font-bold">{{ loanSummary.repayment_progress?.toFixed(0) || 0 }}%</span>
                </div>
                <div class="w-full bg-amber-200 rounded-full h-2 overflow-hidden">
                  <div 
                    class="bg-gradient-to-r from-amber-500 to-amber-600 h-2 rounded-full transition-all duration-500"
                    :style="{ width: `${loanSummary.repayment_progress || 0}%` }"
                  ></div>
                </div>
                <div class="grid grid-cols-2 gap-3 text-xs mt-3">
                  <div class="bg-white/50 rounded-lg p-2">
                    <span class="block text-amber-600 font-medium mb-0.5">Total Issued</span>
                    <span class="font-bold text-amber-900">K{{ formatCurrency(loanSummary.total_issued) }}</span>
                  </div>
                  <div class="bg-white/50 rounded-lg p-2">
                    <span class="block text-amber-600 font-medium mb-0.5">Repaid</span>
                    <span class="font-bold text-amber-900">K{{ formatCurrency(loanSummary.total_repaid) }}</span>
                  </div>
                </div>
                <p v-if="loanSummary.notes" class="text-xs text-amber-700 italic mt-2 bg-white/30 rounded-lg p-2">
                  📝 {{ loanSummary.notes }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Stats Grid -->
        <div class="grid grid-cols-2 gap-3 animate-fade-in" style="animation-delay: 0.2s; animation-fill-mode: both;">
          <StatCard
            label="Total Earnings"
            :value="`K${formatCurrency(stats.total_earnings)}`"
            :icon="CurrencyDollarIcon"
            iconBgClass="bg-gradient-to-br from-green-50 to-emerald-50"
            iconColorClass="text-green-600"
            class="hover:shadow-lg transition-shadow duration-300"
          />
          <StatCard
            label="Team Size"
            :value="networkData?.total_network_size || 0"
            :icon="UsersIcon"
            iconBgClass="bg-gradient-to-br from-blue-50 to-cyan-50"
            iconColorClass="text-blue-600"
            class="hover:shadow-lg transition-shadow duration-300"
          />
          <StatCard
            label="This Month"
            :value="`K${formatCurrency(stats.this_month_earnings || 0)}`"
            subtitle="Earnings"
            :icon="ChartBarIcon"
            iconBgClass="bg-gradient-to-br from-purple-50 to-pink-50"
            iconColorClass="text-purple-600"
            class="hover:shadow-lg transition-shadow duration-300"
          />
          <StatCard
            label="Active Assets"
            :value="assetData?.summary?.active_assets || 0"
            :icon="BuildingOffice2Icon"
            iconBgClass="bg-gradient-to-br from-indigo-50 to-purple-50"
            iconColorClass="text-indigo-600"
            class="hover:shadow-lg transition-shadow duration-300"
          />
        </div>

        <!-- Starter Kit Content (if user has starter kit) -->
        <div v-if="user?.has_starter_kit" class="animate-fade-in" style="animation-delay: 0.25s; animation-fill-mode: both;">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
              <BookOpenIcon class="h-5 w-5 text-blue-600" />
              My Learning Resources
            </h2>
            <button
              @click="$inertia.visit(route('mygrownet.content.index'))"
              class="text-sm text-blue-600 font-semibold hover:text-blue-700 flex items-center gap-1"
            >
              View All
              <ChevronRightIcon class="h-4 w-4" />
            </button>
          </div>
          
          <!-- Content Quick Access Grid -->
          <div class="grid grid-cols-2 gap-3 mb-6">
            <button
              @click="$inertia.visit(route('mygrownet.content.index'))"
              class="flex flex-col items-center p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl hover:shadow-md transition-all active:scale-95 border border-blue-100"
            >
              <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-2">
                <FileTextIcon class="h-6 w-6 text-blue-600" />
              </div>
              <span class="text-sm font-semibold text-gray-900">E-Books</span>
              <span class="text-xs text-gray-500 mt-1">Digital library</span>
            </button>
            
            <button
              @click="$inertia.visit(route('mygrownet.content.index'))"
              class="flex flex-col items-center p-4 bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl hover:shadow-md transition-all active:scale-95 border border-purple-100"
            >
              <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-2">
                <VideoIcon class="h-6 w-6 text-purple-600" />
              </div>
              <span class="text-sm font-semibold text-gray-900">Videos</span>
              <span class="text-xs text-gray-500 mt-1">Training series</span>
            </button>
            
            <button
              @click="$inertia.visit(route('mygrownet.tools.commission-calculator'))"
              class="flex flex-col items-center p-4 bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl hover:shadow-md transition-all active:scale-95 border border-green-100"
            >
              <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-2">
                <CalculatorIcon class="h-6 w-6 text-green-600" />
              </div>
              <span class="text-sm font-semibold text-gray-900">Calculator</span>
              <span class="text-xs text-gray-500 mt-1">Plan earnings</span>
            </button>
            
            <button
              @click="$inertia.visit(route('mygrownet.content.index'))"
              class="flex flex-col items-center p-4 bg-gradient-to-br from-orange-50 to-amber-50 rounded-xl hover:shadow-md transition-all active:scale-95 border border-orange-100"
            >
              <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mb-2">
                <ToolIcon class="h-6 w-6 text-orange-600" />
              </div>
              <span class="text-sm font-semibold text-gray-900">Templates</span>
              <span class="text-xs text-gray-500 mt-1">Marketing tools</span>
            </button>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="animate-fade-in" style="animation-delay: 0.3s; animation-fill-mode: both;">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-gray-900">Quick Actions</h2>
            <div class="h-px flex-1 bg-gradient-to-r from-gray-300 to-transparent ml-4"></div>
          </div>
          <div class="space-y-2">
            <!-- Top 3 Priority Actions -->
            <QuickActionCard
              title="Refer a Friend"
              subtitle="Share your link and earn"
              @click="activeTab = 'team'"
              :icon="UserPlusIcon"
              iconBgClass="bg-blue-50"
              iconColorClass="text-blue-600"
            />
            <QuickActionCard
              v-if="messagingData?.unread_count > 0"
              title="Messages"
              :subtitle="`${messagingData.unread_count} unread`"
              @click="navigateToMessages"
              :icon="EnvelopeIcon"
              iconBgClass="bg-blue-50"
              iconColorClass="text-blue-600"
              :badge="messagingData.unread_count"
            />
            <QuickActionCard
              v-else
              title="View My Team"
              subtitle="See your network"
              @click="activeTab = 'team'"
              :icon="UsersIcon"
              iconBgClass="bg-green-50"
              iconColorClass="text-green-600"
            />
            <QuickActionCard
              title="Apply for Loan"
              subtitle="Quick business funding"
              @click="showLoanApplicationModal = true"
              :icon="BanknotesIcon"
              iconBgClass="bg-gradient-to-r from-emerald-50 to-green-50"
              iconColorClass="text-emerald-600"
            />
            
            <!-- Additional Actions (Expandable) -->
            <div v-if="showAllQuickActions" class="space-y-2">
              <QuickActionCard
                v-if="messagingData?.unread_count === 0"
                title="Messages"
                subtitle="No new messages"
                @click="navigateToMessages"
                :icon="EnvelopeIcon"
                iconBgClass="bg-blue-50"
                iconColorClass="text-blue-600"
              />
              <QuickActionCard
                title="Performance Analytics"
                subtitle="View insights & recommendations"
                @click="showAnalyticsModal = true"
                :icon="ChartBarIcon"
                iconBgClass="bg-gradient-to-r from-orange-50 to-red-50"
                iconColorClass="text-orange-600"
              />
              <QuickActionCard
                title="Transaction History"
                subtitle="View all transactions"
                @click="activeTab = 'wallet'"
                :icon="ClockIcon"
                iconBgClass="bg-purple-50"
                iconColorClass="text-purple-600"
              />
            </div>
            
            <!-- View All Button -->
            <button
              @click="showAllQuickActions = !showAllQuickActions"
              class="w-full py-3 px-4 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 transition-colors flex items-center justify-center gap-2"
            >
              <span>{{ showAllQuickActions ? 'Show Less' : 'View All Actions' }}</span>
              <ChevronDownIcon 
                class="h-4 w-4 transition-transform duration-200"
                :class="{ 'rotate-180': showAllQuickActions }"
              />
            </button>
          </div>
        </div>

        <!-- Commission Levels - Collapsible -->
        <CollapsibleSection
          :model-value="!collapsedSections.commissionLevels"
          @update:model-value="collapsedSections.commissionLevels = !$event"
          title="Commission Levels"
          subtitle="7-level earnings breakdown"
          :icon="BanknotesIcon"
        >
          <div class="space-y-2 mt-3">
            <div
              v-for="level in displayLevels"
              :key="level.level"
              class="flex items-center justify-between p-3 rounded-lg"
              :class="getLevelBgClass(level.level)"
            >
              <div class="flex items-center gap-3">
                <div
                  class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold text-white"
                  :class="getLevelColorClass(level.level)"
                >
                  {{ level.level }}
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-900">Level {{ level.level }}</p>
                  <p class="text-xs text-gray-500">{{ level.count }} referrals</p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-sm font-bold text-gray-900">K{{ formatCurrency(level.total_earnings) }}</p>
                <p class="text-xs text-gray-500">K{{ formatCurrency(level.this_month_earnings) }} this month</p>
              </div>
            </div>
          </div>
        </CollapsibleSection>

        <!-- Team Volume - Collapsible -->
        <CollapsibleSection
          v-if="teamVolumeData?.current_month"
          :model-value="!collapsedSections.teamVolume"
          @update:model-value="collapsedSections.teamVolume = !$event"
          title="Team Volume"
          :subtitle="`K${formatCurrency(teamVolumeData.current_month.team_volume || 0)} this month`"
          :icon="ChartBarIcon"
        >
          <div class="space-y-3 mt-3">
            <div class="flex justify-between items-center py-2">
              <span class="text-sm text-gray-600">Personal</span>
              <span class="text-sm font-medium">K{{ formatCurrency(teamVolumeData.current_month.personal_volume || 0) }}</span>
            </div>
            <div class="flex justify-between items-center py-2">
              <span class="text-sm text-gray-600">Team</span>
              <span class="text-sm font-medium">K{{ formatCurrency(teamVolumeData.current_month.team_volume) }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-t pt-3">
              <span class="text-sm font-medium text-gray-900">Total</span>
              <span class="text-sm font-bold text-blue-600">K{{ formatCurrency(teamVolumeData.current_month.total_volume) }}</span>
            </div>
          </div>
        </CollapsibleSection>

        <!-- Assets - Collapsible -->
        <CollapsibleSection
          v-if="assetData?.summary?.total_assets > 0"
          :model-value="!collapsedSections.assets"
          @update:model-value="collapsedSections.assets = !$event"
          title="My Assets"
          :subtitle="`${assetData?.summary?.total_assets || 0} assets`"
          :icon="BuildingOffice2Icon"
        >
          <div class="space-y-2 mt-3">
            <div
              v-for="asset in assetData.assets.slice(0, 3)"
              :key="asset.id"
              class="flex items-center justify-between p-3 border rounded-lg"
            >
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-50 rounded-full flex items-center justify-center">
                  <BuildingOffice2Icon class="h-5 w-5 text-indigo-600" />
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ asset.name }}</p>
                  <p class="text-xs text-gray-500">{{ asset.category }}</p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-sm font-medium text-gray-900">K{{ formatCurrency(asset.total_income) }}</p>
                <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">
                  {{ asset.status.replace('_', ' ') }}
                </span>
              </div>
            </div>
            <button
              v-if="assetData.assets.length > 3"
              @click="showComingSoon('Assets')"
              class="block w-full text-center text-sm text-blue-600 hover:text-blue-700 py-2"
            >
              View all {{ assetData.assets.length }} assets →
            </button>
          </div>
        </CollapsibleSection>

        <!-- Notifications -->
        <div v-if="notifications.length > 0" class="space-y-2">
          <div
            v-for="notification in notifications.slice(0, 2)"
            :key="notification.type"
            class="rounded-lg p-4"
            :class="getNotificationClass(notification.priority)"
          >
            <div class="flex gap-3">
              <component
                :is="getNotificationIcon(notification.priority)"
                class="h-5 w-5 flex-shrink-0"
                :class="getNotificationIconClass(notification.priority)"
              />
              <div class="flex-1 min-w-0">
                <h3 class="text-sm font-medium" :class="getNotificationTextClass(notification.priority)">
                  {{ notification.title }}
                </h3>
                <p class="text-sm mt-1" :class="getNotificationSubtextClass(notification.priority)">
                  {{ notification.message }}
                </p>
                <Link
                  v-if="notification.action_url"
                  :href="notification.action_url"
                  class="text-sm font-medium underline mt-2 inline-block"
                  :class="getNotificationLinkClass(notification.priority)"
                >
                  Take Action →
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TEAM TAB -->
      <div v-show="activeTab === 'team'" class="space-y-6">
        <!-- Loading State -->
        <TabLoadingSkeleton v-if="tabLoading && !tabDataLoaded.team" :count="3" :show-stats="true" />
        
        <!-- Loaded Content -->
        <template v-else>
        <div class="bg-white rounded-xl shadow-sm p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-4">My Network</h2>
          
          <!-- Network Stats with Growth Sparkline -->
          <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
              <p class="text-2xl font-bold text-blue-600">{{ networkData?.total_network_size || 0 }}</p>
              <p class="text-xs text-gray-600 mt-1">Total Team</p>
              <!-- Growth Sparkline -->
              <div class="mt-3 flex justify-center">
                <MiniSparkline
                  v-if="networkGrowthData.length > 0"
                  :data="networkGrowthData"
                  :width="120"
                  :height="30"
                  color="#2563eb"
                  :filled="true"
                  fillColor="#3b82f6"
                  className="opacity-80"
                />
              </div>
              <p class="text-xs text-gray-500 mt-1">Last 6 months</p>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
              <p class="text-2xl font-bold text-green-600">{{ Array.isArray(networkData?.direct_referrals) ? networkData.direct_referrals.length : 0 }}</p>
              <p class="text-xs text-gray-600 mt-1">Direct Referrals</p>
              <!-- This Month Growth -->
              <div class="mt-3">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  +{{ Math.floor(Math.random() * 5) + 1 }} this month
                </span>
              </div>
            </div>
          </div>

          <!-- Referral Link -->
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-100">
            <p class="text-xs font-medium text-gray-700 mb-2">Your Referral Link</p>
            <div class="flex items-center gap-2">
              <input
                type="text"
                :value="referralLink"
                readonly
                class="flex-1 text-xs bg-white border border-gray-200 rounded-lg px-3 py-2 text-gray-600"
              />
              <button
                @click="copyReferralLink"
                class="px-4 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 active:scale-95 transition-all"
              >
                Copy
              </button>
            </div>
          </div>
        </div>

        <!-- Member Filters -->
        <MemberFilters
          v-model:filter="memberFilter"
          v-model:sort="memberSort"
          v-model:search="memberSearch"
          :filters="memberFilterOptions"
          :filtered-count="totalFilteredMembers"
          :total-count="networkData?.total_network_size || 0"
        />

        <!-- Level Breakdown -->
        <div class="bg-white rounded-xl shadow-sm p-6">
          <h3 class="text-base font-bold text-gray-900 mb-4">Team by Level (7 Levels)</h3>
          <div class="space-y-3">
            <div
              v-for="level in filteredDisplayLevels"
              :key="level.level"
              class="rounded-lg overflow-hidden"
              :class="getLevelBgClass(level.level)"
            >
              <!-- Level Header (Clickable) -->
              <button
                @click="toggleLevelMembers(level.level)"
                class="w-full flex items-center justify-between p-4 hover:opacity-80 transition-opacity"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold text-white flex-shrink-0"
                    :class="getLevelColorClass(level.level)"
                  >
                    L{{ level.level }}
                  </div>
                  <div class="text-left">
                    <p class="text-sm font-semibold text-gray-900">Level {{ level.level }}</p>
                    <p class="text-xs text-gray-500">{{ level.count }} members</p>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <div class="text-right">
                    <p class="text-sm font-bold text-gray-900">K{{ formatCurrency(level.total_earnings) }}</p>
                    <p class="text-xs text-gray-500">Total earned</p>
                  </div>
                  <ChevronDownIcon 
                    class="h-5 w-5 text-gray-400 transition-transform"
                    :class="{ 'rotate-180': expandedLevels.includes(level.level) }"
                  />
                </div>
              </button>

              <!-- Members List (Expandable) -->
              <div
                v-if="expandedLevels.includes(level.level)"
                class="border-t border-gray-200 bg-white"
              >
                <div v-if="level.members && level.members.length > 0" class="divide-y divide-gray-100">
                  <div
                    v-for="member in level.members"
                    :key="member.id"
                    class="p-4 hover:bg-gray-50 transition-colors"
                  >
                    <div class="flex items-start justify-between">
                      <div class="flex items-center gap-3 flex-1 min-w-0">
                        <!-- Avatar -->
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                          {{ member.name.charAt(0).toUpperCase() }}
                        </div>
                        <!-- Member Info -->
                        <div class="flex-1 min-w-0">
                          <p class="text-sm font-medium text-gray-900 truncate">{{ member.name }}</p>
                          <p class="text-xs text-gray-500 truncate">{{ member.email }}</p>
                          <div class="flex items-center gap-2 mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                              {{ member.tier }}
                            </span>
                            <span 
                              v-if="member.starter_kit_tier"
                              class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                            >
                              {{ member.starter_kit_tier }} Kit
                            </span>
                            <span 
                              class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                              :class="member.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                            >
                              {{ member.is_active ? 'Active' : 'Inactive' }}
                            </span>
                          </div>
                        </div>
                      </div>
                      <!-- Join Date -->
                      <div class="text-right ml-2 flex-shrink-0">
                        <p class="text-xs text-gray-500">Joined</p>
                        <p class="text-xs font-medium text-gray-700">{{ member.joined_date }}</p>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-else class="p-8 text-center text-gray-400">
                  <UsersIcon class="h-12 w-12 mx-auto mb-2 opacity-50" />
                  <p class="text-sm">No members at this level yet</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        </template>
      </div>

      <!-- WALLET TAB -->
      <div v-show="activeTab === 'wallet'" class="space-y-6">
        <!-- Loading State -->
        <TabLoadingSkeleton v-if="tabLoading && !tabDataLoaded.wallet" :count="3" :show-stats="true" />
        
        <!-- Loaded Content -->
        <template v-else>
        <!-- Balance Overview -->
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl shadow-lg p-6 text-white">
          <p class="text-sm opacity-90 mb-2">Available Balance</p>
          <h2 class="text-4xl font-bold mb-6">K{{ formatCurrency(walletBalance) }}</h2>
          
          <div class="grid grid-cols-2 gap-3 mb-3">
            <button 
              @click="showDepositModal = true"
              class="bg-white/20 backdrop-blur-sm hover:bg-white/30 rounded-xl py-3 px-4 text-sm font-medium transition-all active:scale-95"
            >
              💰 Deposit
            </button>
            <button 
              @click="showWithdrawalModal = true"
              class="bg-white/20 backdrop-blur-sm hover:bg-white/30 rounded-xl py-3 px-4 text-sm font-medium transition-all active:scale-95"
            >
              💸 Withdraw
            </button>
          </div>
          
          <button 
            @click="showLoanApplicationModal = true"
            class="w-full bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 rounded-xl py-3 px-4 text-sm font-semibold transition-all active:scale-95 shadow-lg"
          >
            🏦 Apply for Loan
          </button>
        </div>

        <!-- Earnings Breakdown -->
        <EarningsBreakdown
          v-if="earningsBreakdown"
          :earnings="earningsBreakdown"
          :lgrBalance="loyaltyPoints"
          :lgrWithdrawable="lgrWithdrawable"
          :lgrPercentage="lgrWithdrawablePercentage"
          :lgrBlocked="lgrWithdrawalBlocked"
          @transfer-lgr="showLgrTransferModal = true"
        />

        <!-- Earnings Trend Chart -->
        <EarningsTrendChart
          v-if="earningsTrendData.length > 0"
          :earnings="earningsTrendData"
        />

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 gap-3">
          <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Total Deposits</p>
            <p class="text-lg font-bold text-gray-900">K{{ formatCurrency(stats.total_deposits || 0) }}</p>
          </div>
          <div class="bg-white rounded-xl shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Total Withdrawals</p>
            <p class="text-lg font-bold text-gray-900">K{{ formatCurrency(stats.total_withdrawals || 0) }}</p>
          </div>
        </div>

        <!-- All Transactions -->
        <div class="bg-white rounded-xl shadow-sm p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-gray-900">All Transactions</h3>
            <button
              v-if="recentTopups && recentTopups.length > 5 && !showAllTransactions"
              @click="showAllTransactions = true"
              class="text-xs text-blue-600 font-medium hover:text-blue-700"
            >
              Show All ({{ recentTopups.length }})
            </button>
            <button
              v-if="showAllTransactions"
              @click="showAllTransactions = false"
              class="text-xs text-gray-600 font-medium hover:text-gray-700"
            >
              Show Less
            </button>
          </div>
          
          <div class="space-y-2 max-h-[500px] overflow-y-auto">
            <div v-if="recentTopups && recentTopups.length > 0">
              <div
                v-for="topup in (showAllTransactions ? recentTopups : recentTopups.slice(0, 5))"
                :key="topup.id"
                class="p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
              >
                <div class="flex items-start justify-between mb-2">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                      :class="{
                        'bg-green-50': topup.status === 'verified',
                        'bg-yellow-50': topup.status === 'pending' || topup.status === 'processing',
                        'bg-red-50': topup.status === 'rejected',
                        'bg-gray-50': !['verified', 'pending', 'processing', 'rejected'].includes(topup.status)
                      }"
                    >
                      <ArrowPathIcon class="h-5 w-5"
                        :class="{
                          'text-green-600': topup.status === 'verified',
                          'text-yellow-600': topup.status === 'pending' || topup.status === 'processing',
                          'text-red-600': topup.status === 'rejected',
                          'text-gray-600': !['verified', 'pending', 'processing', 'rejected'].includes(topup.status)
                        }"
                      />
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-semibold text-gray-900">Wallet Top-up</p>
                      <p class="text-xs text-gray-500">{{ topup.date }} • {{ topup.time }}</p>
                      <p v-if="topup.payment_reference" class="text-xs text-gray-400 mt-0.5 font-mono truncate">
                        Ref: {{ topup.payment_reference }}
                      </p>
                    </div>
                  </div>
                  <div class="text-right flex-shrink-0 ml-2">
                    <p class="text-sm font-bold"
                      :class="topup.status === 'verified' ? 'text-green-600' : 'text-gray-900'"
                    >
                      +K{{ formatCurrency(topup.amount) }}
                    </p>
                    <span
                      class="inline-block text-xs px-2 py-0.5 rounded-full mt-1"
                      :class="{
                        'bg-green-100 text-green-700': topup.status === 'verified',
                        'bg-yellow-100 text-yellow-700': topup.status === 'pending' || topup.status === 'processing',
                        'bg-red-100 text-red-700': topup.status === 'rejected',
                        'bg-gray-100 text-gray-700': !['verified', 'pending', 'processing', 'rejected'].includes(topup.status)
                      }"
                    >
                      {{ topup.status }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
            
            <div v-else class="text-center py-8 text-gray-400">
              <ClockIcon class="h-12 w-12 mx-auto mb-2 opacity-50" />
              <p class="text-sm">No transactions yet</p>
              <p class="text-xs mt-2">Your deposits will appear here</p>
            </div>
          </div>

          <!-- Pending Deposits Alert -->
          <div v-if="pendingWithdrawals > 0" class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
            <p class="text-sm text-yellow-800">
              <span class="font-semibold">⏳ Pending:</span> K{{ formatCurrency(pendingWithdrawals) }} in pending deposits
            </p>
          </div>
        </div>
        </template>
      </div>

      <!-- LEARN TAB -->
      <div v-show="activeTab === 'learn'" class="space-y-4">
        <!-- Loading State -->
        <TabLoadingSkeleton v-if="tabLoading && !tabDataLoaded.learn" :count="2" />
        
        <!-- Loaded Content -->
        <template v-else>
        <!-- Full-Screen Tool Display -->
        <div v-if="activeTool && activeTool !== 'content'" class="fixed inset-0 bg-white z-[60] flex flex-col">
          <div class="flex-shrink-0 bg-gradient-to-r from-blue-500 to-blue-600 p-4 flex items-center justify-between shadow-lg">
            <h3 class="text-white font-bold text-lg">{{ getToolTitle(activeTool) }}</h3>
            <button
              @click="activeTool = 'content'"
              class="text-white hover:bg-white/20 rounded-lg px-3 py-2 transition-colors font-semibold"
            >
              ✕ Close
            </button>
          </div>
          
          <div class="flex-1 overflow-y-auto pb-20">
            <!-- Embedded Mobile Tools (No Layout) -->
            <EarningsCalculatorEmbedded 
              v-if="activeTool === 'calculator'"
              :user-tier="user?.starter_kit_tier"
              :current-network="networkStats"
            />
            <GoalTrackerEmbedded 
              v-if="activeTool === 'goals'"
              :goals="[]"
              :current-earnings="stats.this_month_earnings || 0"
              :user-tier="user?.starter_kit_tier"
              @success="showToastMessage($event, 'success')"
              @error="showToastMessage($event, 'error')"
            />
            <NetworkVisualizerEmbedded 
              v-if="activeTool === 'network'"
              :network-tree="networkTree"
              :network-stats="networkStats"
              :user-tier="user?.starter_kit_tier"
              :all-levels="displayLevels"
              :level-counts="levelCounts"
            />
          </div>
        </div>

        <!-- Header (only show when no tool is active) -->
        <div v-if="!activeTool || activeTool === 'content'" class="bg-gradient-to-br from-purple-600 to-indigo-600 rounded-2xl shadow-lg p-6 text-white">
          <h2 class="text-xl font-bold mb-2">Learning & Tools</h2>
          <p class="text-sm opacity-90">
            {{ user?.has_starter_kit ? 'Access your resources and tools' : 'Get your starter kit to unlock content' }}
          </p>
        </div>
        
        <!-- Categorized Tools (NEW) -->
        <div v-if="!activeTool || activeTool === 'content'" class="space-y-4">
          <!-- Learning Resources Category -->
          <ToolCategory
            title="Learning Resources"
            subtitle="E-books, videos, and guides"
            icon="📚"
            header-gradient="from-blue-600 to-indigo-600"
            :tools="learningResourcesTools"
            :badge="user?.has_starter_kit ? `${learningResourcesTools.length} items` : undefined"
            locked-message="Get your starter kit to unlock learning resources"
            :upgrade-action="!user?.has_starter_kit"
            upgrade-button-text="Get Starter Kit"
            @tool-click="handleToolClick"
            @upgrade="handleUpgrade"
          />

          <!-- Business Tools Category -->
          <ToolCategory
            title="Business Tools"
            subtitle="Calculators and trackers"
            icon="🧮"
            header-gradient="from-green-600 to-emerald-600"
            :tools="businessTools"
            :badge="user?.has_starter_kit ? 'Active' : undefined"
            locked-message="Get your starter kit to unlock business tools"
            :upgrade-action="!user?.has_starter_kit"
            upgrade-button-text="Get Starter Kit"
            @tool-click="handleToolClick"
            @upgrade="handleUpgrade"
          />

          <!-- Premium Tools Category -->
          <ToolCategory
            title="Premium Tools"
            subtitle="Advanced features"
            icon="👑"
            header-gradient="from-purple-600 to-pink-600"
            :tools="premiumTools"
            badge="Premium"
            locked-message="Upgrade to Premium starter kit to unlock these tools"
            :upgrade-action="user?.starter_kit_tier !== 'premium'"
            upgrade-button-text="Upgrade to Premium"
            @tool-click="handleToolClick"
            @upgrade="handleUpgrade"
          />
        </div>


        <!-- CALCULATOR SECTION (EMBEDDED) -->
        <div v-if="user?.has_starter_kit && activeTool === 'calculator'" class="space-y-4">
          <div class="bg-white rounded-xl shadow-sm p-4">
            <h3 class="text-base font-bold text-gray-900 mb-3 flex items-center gap-2">
              <CalculatorIcon class="h-5 w-5 text-green-600" />
              Earnings Calculator
            </h3>
            
            <!-- Earning Type Selector -->
            <div class="grid grid-cols-2 gap-2 mb-4">
              <button
                @click="calcEarningType = 'referral'"
                :class="calcEarningType === 'referral' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700'"
                class="px-3 py-2 rounded-lg text-xs font-semibold transition-colors"
              >
                💰 Referral
              </button>
              <button
                @click="calcEarningType = 'lgr'"
                :class="calcEarningType === 'lgr' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700'"
                class="px-3 py-2 rounded-lg text-xs font-semibold transition-colors"
              >
                🏆 LGR
              </button>
            </div>

            <!-- Referral Calculator -->
            <div v-if="calcEarningType === 'referral'" class="space-y-3">
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Team Size (Level 1)</label>
                <input
                  v-model.number="calcTeamSize"
                  type="number"
                  min="0"
                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Active %</label>
                <input
                  v-model.number="calcActivePercent"
                  type="range"
                  min="0"
                  max="100"
                  class="w-full"
                />
                <div class="text-center text-xs text-gray-600">{{ calcActivePercent }}%</div>
              </div>
              <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                <p class="text-xs text-gray-600 mb-1">Monthly Projection</p>
                <p class="text-2xl font-bold text-green-600">
                  K{{ Math.floor(calcTeamSize * (calcActivePercent / 100) * 500 * 0.1).toLocaleString() }}
                </p>
              </div>
            </div>

            <!-- LGR Calculator -->
            <div v-if="calcEarningType === 'lgr'" class="space-y-3">
              <div v-if="user?.starter_kit_tier !== 'premium'" class="bg-purple-50 border border-purple-200 rounded-lg p-3">
                <p class="text-sm text-purple-800">
                  🔒 Upgrade to Premium tier to qualify for LGR profit sharing!
                </p>
              </div>
              <div v-else>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Quarterly Profit (K)</label>
                  <input
                    v-model.number="calcLgrProfit"
                    type="number"
                    min="0"
                    step="1000"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                  />
                </div>
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-3">
                  <p class="text-xs text-gray-600 mb-1">Your Monthly Share</p>
                  <p class="text-2xl font-bold text-purple-600">
                    K{{ Math.floor((calcLgrProfit * 0.6) / 100 / 3).toLocaleString() }}
                  </p>
                  <p class="text-xs text-gray-500 mt-1">Based on 100 qualified members</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- GOALS SECTION (EMBEDDED) -->
        <div v-if="user?.has_starter_kit && activeTool === 'goals'" class="space-y-4">
          <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <TargetIcon class="h-5 w-5 text-purple-600" />
                My Goals
              </h3>
              <button
                @click="showCreateGoalModal = true"
                class="px-3 py-1.5 bg-purple-600 text-white rounded-lg text-xs font-semibold"
              >
                + New
              </button>
            </div>

            <!-- Sample Goals (Replace with real data) -->
            <div class="space-y-3">
              <div class="bg-gradient-to-br from-purple-50 to-pink-50 border border-purple-200 rounded-lg p-3">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-semibold text-gray-900">💰 Monthly Income</span>
                  <span class="text-xs text-gray-600">75%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                  <div class="bg-purple-600 h-2 rounded-full" style="width: 75%"></div>
                </div>
                <p class="text-xs text-gray-600">K3,750 / K5,000</p>
              </div>

              <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-3">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-semibold text-gray-900">👥 Team Size</span>
                  <span class="text-xs text-gray-600">40%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                  <div class="bg-blue-600 h-2 rounded-full" style="width: 40%"></div>
                </div>
                <p class="text-xs text-gray-600">20 / 50 members</p>
              </div>

              <div class="text-center py-4">
                <p class="text-sm text-gray-600">Create your first goal to start tracking!</p>
              </div>
            </div>
          </div>
        </div>

        <!-- NETWORK SECTION (EMBEDDED) -->
        <div v-if="user?.has_starter_kit && activeTool === 'network'" class="space-y-4">
          <div class="bg-white rounded-xl shadow-sm p-4">
            <h3 class="text-base font-bold text-gray-900 mb-3 flex items-center gap-2">
              <UsersIcon class="h-5 w-5 text-orange-600" />
              My Network
            </h3>

            <!-- Network Stats -->
            <div class="grid grid-cols-2 gap-3 mb-4">
              <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-3">
                <p class="text-xs text-gray-600 mb-1">Total Members</p>
                <p class="text-2xl font-bold text-blue-600">{{ networkData?.total_network_size || 0 }}</p>
              </div>
              <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-3">
                <p class="text-xs text-gray-600 mb-1">Active</p>
                <p class="text-2xl font-bold text-green-600">{{ Math.floor((networkData?.total_network_size || 0) * 0.6) }}</p>
              </div>
            </div>

            <!-- Level Breakdown -->
            <div class="space-y-2">
              <h4 class="text-sm font-semibold text-gray-900">By Level</h4>
              <div v-for="level in 7" :key="level" class="flex items-center justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-gray-700">Level {{ level }}</span>
                <span class="text-sm font-semibold text-gray-900">{{ Math.pow(3, level) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Starter Kit Banner (if not purchased) -->
        <div
          v-if="!user?.has_starter_kit"
          @click="showStarterKitModal = true"
          class="bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl shadow-lg p-6 text-white cursor-pointer hover:shadow-xl transition-all active:scale-[0.98]"
        >
          <div class="flex items-start gap-4">
            <div class="w-14 h-14 bg-white/25 backdrop-blur-sm rounded-2xl flex items-center justify-center shadow-lg border border-white/30">
              <SparklesIcon class="h-7 w-7 text-white" />
            </div>
            <div class="flex-1">
              <h3 class="text-white font-bold text-lg">Get Your Starter Kit</h3>
              <p class="text-blue-100 text-sm mt-1.5">
                Unlock e-books, videos, tools, and more. Starting at K500!
              </p>
              <div class="mt-4 bg-white/20 backdrop-blur-sm rounded-xl px-4 py-2.5 w-fit border border-white/30">
                <span class="text-white text-sm font-bold">Purchase Now →</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Starter Kit Content (if purchased) -->
        <div v-if="user?.has_starter_kit" class="space-y-6">
          <!-- Learning Categories -->
          <div class="grid grid-cols-2 gap-3">
            <button
              @click="showContentLibraryModal = true"
              class="bg-white rounded-xl shadow-sm p-4 text-left hover:shadow-md transition-all active:scale-95"
            >
              <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center mb-3">
                <FileTextIcon class="h-6 w-6 text-blue-600" />
              </div>
              <h3 class="text-sm font-semibold text-gray-900">E-Books</h3>
              <p class="text-xs text-gray-500 mt-1">Digital library</p>
            </button>

            <button
              @click="showContentLibraryModal = true"
              class="bg-white rounded-xl shadow-sm p-4 text-left hover:shadow-md transition-all active:scale-95"
            >
              <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center mb-3">
                <VideoIcon class="h-6 w-6 text-purple-600" />
              </div>
              <h3 class="text-sm font-semibold text-gray-900">Videos</h3>
              <p class="text-xs text-gray-500 mt-1">Training series</p>
            </button>

            <button
              @click="showCalculatorModal = true"
              class="bg-white rounded-xl shadow-sm p-4 text-left hover:shadow-md transition-all active:scale-95"
            >
              <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center mb-3">
                <CalculatorIcon class="h-6 w-6 text-green-600" />
              </div>
              <h3 class="text-sm font-semibold text-gray-900">Calculator</h3>
              <p class="text-xs text-gray-500 mt-1">Plan earnings</p>
            </button>

            <button
              @click="showContentLibraryModal = true"
              class="bg-white rounded-xl shadow-sm p-4 text-left hover:shadow-md transition-all active:scale-95"
            >
              <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center mb-3">
                <ToolIcon class="h-6 w-6 text-orange-600" />
              </div>
              <h3 class="text-sm font-semibold text-gray-900">Templates</h3>
              <p class="text-xs text-gray-500 mt-1">Marketing tools</p>
            </button>

            <!-- Premium Tools -->
            <button
              v-if="user?.starter_kit_tier === 'premium'"
              @click="showBusinessPlanModal = true"
              class="bg-white rounded-xl shadow-sm p-4 text-left hover:shadow-md transition-all active:scale-95"
            >
              <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center mb-3">
                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <h3 class="text-sm font-semibold text-gray-900">Business Plan</h3>
              <p class="text-xs text-gray-500 mt-1">Premium tool</p>
            </button>

            <button
              v-if="user?.starter_kit_tier === 'premium'"
              @click="showROICalculatorModal = true"
              class="bg-white rounded-xl shadow-sm p-4 text-left hover:shadow-md transition-all active:scale-95"
            >
              <div class="w-12 h-12 bg-teal-50 rounded-xl flex items-center justify-center mb-3">
                <svg class="h-6 w-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
              </div>
              <h3 class="text-sm font-semibold text-gray-900">ROI Calculator</h3>
              <p class="text-xs text-gray-500 mt-1">Premium tool</p>
            </button>
          </div>

          <!-- Featured Content -->
          <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-base font-bold text-gray-900 mb-4">Your Content</h3>
            <div class="space-y-3">
              <div class="flex gap-4 p-3 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg border border-blue-100">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg flex-shrink-0 flex items-center justify-center">
                  <FileTextIcon class="h-8 w-8 text-white" />
                </div>
                <div class="flex-1 min-w-0">
                  <h4 class="text-sm font-semibold text-gray-900 mb-1">MyGrowNet Success Guide</h4>
                  <p class="text-xs text-gray-500">Complete platform guide</p>
                  <button @click="showContentLibraryModal = true" class="mt-2 text-xs text-blue-600 font-semibold">View →</button>
                </div>
              </div>

              <div class="flex gap-4 p-3 bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg border border-purple-100">
                <div class="w-16 h-16 bg-gradient-to-br from-purple-400 to-purple-600 rounded-lg flex-shrink-0 flex items-center justify-center">
                  <VideoIcon class="h-8 w-8 text-white" />
                </div>
                <div class="flex-1 min-w-0">
                  <h4 class="text-sm font-semibold text-gray-900 mb-1">Welcome Training Series</h4>
                  <p class="text-xs text-gray-500">5 video tutorials</p>
                  <button @click="showContentLibraryModal = true" class="mt-2 text-xs text-purple-600 font-semibold">Watch →</button>
                </div>
              </div>

              <div class="flex gap-4 p-3 bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg border border-green-100">
                <div class="w-16 h-16 bg-gradient-to-br from-green-400 to-green-600 rounded-lg flex-shrink-0 flex items-center justify-center">
                  <CalculatorIcon class="h-8 w-8 text-white" />
                </div>
                <div class="flex-1 min-w-0">
                  <h4 class="text-sm font-semibold text-gray-900 mb-1">Commission Calculator</h4>
                  <p class="text-xs text-gray-500">Plan your earnings</p>
                  <button @click="showCalculatorModal = true" class="mt-2 text-xs text-green-600 font-semibold">Calculate →</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Coming Soon (if no content yet) -->
          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <p class="text-sm text-yellow-800">
              <strong>📚 Content Coming Soon!</strong> We're preparing amazing resources for you. Check back soon!
            </p>
          </div>
        </div>
        </template>
      </div>

    </div>

    <!-- MORE TAB - Slide-in Drawer (NEW) -->
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transition-all duration-300 ease-in"
      leave-from-class="translate-x-0"
      leave-to-class="translate-x-full"
    >
      <div
        v-if="activeTab === 'more'"
        class="fixed inset-0 z-50"
        style="padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom);"
      >
        <!-- Backdrop -->
        <div
          class="absolute inset-0 bg-black/50 backdrop-blur-sm"
          @click="handleTabChange(previousTab)"
        ></div>

        <!-- Drawer Content -->
        <div class="absolute inset-y-0 right-0 w-full max-w-md bg-white shadow-2xl overflow-y-auto">
          <!-- Header -->
          <div class="sticky top-0 z-10 bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-4 py-4 shadow-lg">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-bold">More</h2>
              <button
                @click="handleTabChange(previousTab)"
                class="p-2 hover:bg-white/20 rounded-lg transition-colors active:scale-95"
              >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Content -->
          <div class="p-4 space-y-6 pb-24">
            <MoreTabContent
              :user="user"
              :current-tier="currentTier"
              :membership-progress="membershipProgress"
              :messaging-data="messagingData"
              :verification-badge="verificationBadge"
              :show-install-button="showInstallButton"
              @edit-profile="showEditProfileModal = true"
              @change-password="showChangePasswordModal = true"
              @verification="showComingSoon('Verification')"
              @messages="navigateToMessages"
              @support-tickets="showSupportModal = true"
              @help-center="showHelpSupportModal = true"
              @faqs="showComingSoon('FAQs')"
              @notifications="showSettingsModal = true"
              @language="showComingSoon('Language Settings')"
              @theme="showComingSoon('Theme Settings')"
              @install-app="installPWA"
              @switch-view="switchToClassicView"
              @about="showComingSoon('About')"
              @terms="showComingSoon('Terms & Privacy')"
              @logout="handleTabChange(previousTab); handleLogout()"
            />
          </div>
        </div>
      </div>
    </Transition>

    <!-- Bottom Navigation - Fixed at bottom -->
    <BottomNavigation 
      v-if="true"
      :active-tab="activeTab" 
      @navigate="handleTabChange" 
    />

    <!-- Scroll to Top Button -->
    <ScrollToTop />

    <!-- Modals -->
    <DepositModal
      :show="showDepositModal"
      :balance="walletBalance"
      :recent-topups="recentTopups"
      @close="showDepositModal = false"
      @error="(msg) => showToastMessage(msg, 'error')"
    />

    <WithdrawalModal
      :show="showWithdrawalModal"
      :balance="walletBalance"
      :verification-limits="verificationLimits"
      :remaining-daily-limit="remainingDailyLimit"
      :pending-withdrawals="pendingWithdrawals"
      :loan-summary="loanSummary"
      @close="showWithdrawalModal = false"
    />

    <LevelDownlinesModal
      :show="showLevelDownlinesModal"
      :level="selectedLevel"
      :members="levelDownlines"
      :wallet-balance="walletBalance"
      @close="showLevelDownlinesModal = false"
    />

    <EditProfileModal
      :show="showEditProfileModal"
      :user="user"
      @close="showEditProfileModal = false"
      @success="(msg) => showToastMessage(msg, 'success')"
      @error="(msg) => showToastMessage(msg, 'error')"
    />

    <SettingsModal
      :show="showSettingsModal"
      :user="user"
      @close="showSettingsModal = false"
      @success="(msg) => showToastMessage(msg, 'success')"
      @error="(msg) => showToastMessage(msg, 'error')"
    />

    <HelpSupportModal
      :show="showHelpSupportModal"
      @close="showHelpSupportModal = false"
    />

    <StarterKitPurchaseModal
      :show="showStarterKitModal"
      :hasStarterKit="user?.has_starter_kit || false"
      :tier="user?.starter_kit_tier"
      :shopCredit="parseFloat(user?.starter_kit_shop_credit) || 0"
      :creditExpiry="user?.starter_kit_credit_expiry"
      :purchaseDate="user?.starter_kit_purchased_at"
      :walletBalance="walletBalance"
      @close="showStarterKitModal = false"
      @success="handleToastSuccess"
      @error="handleToastError"
    />

    <LogoutConfirmModal
      :show="showLogoutModal"
      @confirm="confirmLogout"
      @cancel="showLogoutModal = false"
    />

    <AnalyticsModal
      :show="showAnalyticsModal"
      :user-id="user?.id"
      @close="showAnalyticsModal = false"
    />

    <LgrTransferModal
      :show="showLgrTransferModal"
      :lgrBalance="loyaltyPoints || 0"
      :lgrWithdrawable="lgrWithdrawable || 0"
      :lgrPercentage="lgrWithdrawablePercentage || 40"
      @close="showLgrTransferModal = false"
      @success="handleToastSuccess"
      @error="handleToastError"
    />

    <LoanApplicationModal
      :show="showLoanApplicationModal"
      :loan-limit="loanLimit"
      :loan-balance="loanBalance"
      :available-credit="availableCredit"
      :eligibility="loanEligibility"
      :has-pending-application="false"
      @close="showLoanApplicationModal = false"
      @success="handleToastSuccess"
      @error="handleToastError"
    />

    <!-- Messages Modal -->
    <MessagesModal
      :show="showMessagesModal"
      @close="showMessagesModal = false"
    />

    <!-- Support Tickets Modal -->
    <SupportTicketsModal
      :is-open="showSupportModal"
      :tickets="supportTickets"
      @close="showSupportModal = false"
    />

    <!-- Content Library Modal (Coming Soon) -->
    <div v-if="showContentLibraryModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click="showContentLibraryModal = false">
      <div class="bg-white rounded-2xl p-6 max-w-md w-full" @click.stop>
        <div class="text-center">
          <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <BookOpenIcon class="h-8 w-8 text-blue-600" />
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">Content Library</h3>
          <p class="text-gray-600 mb-6">
            Your digital content library is being prepared. Upload your e-books, videos, and templates via the admin panel to make them available here.
          </p>
          <div class="space-y-2">
            <Link
              :href="route('mygrownet.content.index')"
              class="block w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold"
            >
              Open Full Library
            </Link>
            <button
              @click="showContentLibraryModal = false"
              class="block w-full px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-semibold"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Calculator Modal (Embedded) -->
    <div v-if="showCalculatorModal" class="fixed inset-0 bg-black/50 z-50 flex items-end sm:items-center justify-center" @click="showCalculatorModal = false">
      <div class="bg-white rounded-t-3xl sm:rounded-2xl w-full sm:max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>
        <!-- Header -->
        <div class="sticky top-0 bg-gradient-to-r from-green-600 to-emerald-600 text-white p-4 rounded-t-3xl sm:rounded-t-2xl">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                <CalculatorIcon class="h-6 w-6" />
              </div>
              <div>
                <h3 class="text-lg font-bold">Commission Calculator</h3>
                <p class="text-xs text-green-100">Plan your earnings</p>
              </div>
            </div>
            <button
              @click="showCalculatorModal = false"
              class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center hover:bg-white/30 transition-colors"
            >
              <span class="text-xl">×</span>
            </button>
          </div>
        </div>

        <!-- Calculator Content -->
        <div class="p-4 space-y-4">
          <!-- Assumptions -->
          <div class="bg-gray-50 rounded-xl p-4">
            <h4 class="text-sm font-semibold text-gray-900 mb-3">Assumptions</h4>
            <div class="space-y-3">
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">
                  Subscription Price (K)
                </label>
                <input
                  v-model.number="calcSubscriptionPrice"
                  type="number"
                  min="0"
                  step="50"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">
                  Starter Kit Price (K)
                </label>
                <input
                  v-model.number="calcStarterKitPrice"
                  type="number"
                  min="0"
                  step="50"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">
                  Active Members ({{ calcActivePercentage }}%)
                </label>
                <input
                  v-model.number="calcActivePercentage"
                  type="range"
                  min="0"
                  max="100"
                  class="w-full"
                />
              </div>
            </div>
          </div>

          <!-- Team Size Inputs -->
          <div class="bg-gray-50 rounded-xl p-4">
            <h4 class="text-sm font-semibold text-gray-900 mb-3">Team Size by Level</h4>
            <div class="grid grid-cols-2 gap-2">
              <div v-for="level in 7" :key="level">
                <label class="block text-xs font-medium text-gray-700 mb-1">
                  Level {{ level }}
                </label>
                <input
                  v-model.number="calcTeamSizes[`level_${level}`]"
                  type="number"
                  min="0"
                  class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent"
                />
              </div>
            </div>
          </div>

          <!-- Results -->
          <div class="space-y-3">
            <!-- Summary Cards -->
            <div class="grid grid-cols-2 gap-3">
              <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-4 text-white">
                <p class="text-xs opacity-90">Monthly</p>
                <p class="text-2xl font-bold mt-1">K{{ formatCalcCurrency(calcMonthlyProjection) }}</p>
              </div>
              <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-4 text-white">
                <p class="text-xs opacity-90">Yearly</p>
                <p class="text-2xl font-bold mt-1">K{{ formatCalcCurrency(calcYearlyProjection) }}</p>
              </div>
            </div>

            <!-- Breakdown -->
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
              <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <h4 class="text-sm font-semibold text-gray-900">Commission Breakdown</h4>
              </div>
              <div class="divide-y divide-gray-200">
                <div v-for="result in calcResults" :key="result.level" class="px-4 py-2 flex items-center justify-between text-xs">
                  <span class="font-medium text-gray-700">Level {{ result.level }}</span>
                  <div class="flex items-center gap-3">
                    <span class="text-gray-500">{{ result.activeMembers }} active</span>
                    <span class="font-semibold text-green-600">K{{ formatCalcCurrency(result.levelTotal) }}</span>
                  </div>
                </div>
                <div class="px-4 py-2 flex items-center justify-between text-sm font-bold bg-green-50">
                  <span class="text-gray-900">Total Monthly</span>
                  <span class="text-green-600">K{{ formatCalcCurrency(calcTotalCommission) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Disclaimer -->
          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
            <p class="text-xs text-yellow-800">
              <strong>Note:</strong> These are projections based on your inputs. Actual earnings may vary.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <Toast
      :show="showToast"
      :message="toastMessage"
      :type="toastType"
      @close="showToast = false"
    />

    <!-- PWA Update Notification -->
    <UpdateNotification />

    <!-- PWA Install Prompt -->
    <InstallPrompt />

    <!-- Business Plan Modal -->
    <BusinessPlanModal
      :show="showBusinessPlanModal"
      :existing-plan="existingBusinessPlan"
      @close="handleCloseBusinessPlan"
      @view-all-plans="handleViewAllPlans"
    />

    <!-- Business Plan List Modal -->
    <BusinessPlanListModal
      :show="showBusinessPlanListModal"
      @close="showBusinessPlanListModal = false"
      @open-plan="handleOpenPlan"
    />

    <!-- ROI Calculator Modal -->
    <ROICalculatorModal
      :show="showROICalculatorModal"
      :investments="{
        starter_kit: user?.starter_kit_tier === 'premium' ? 1000 : 500,
        total_earnings: stats?.total_earnings || 0
      }"
      @close="showROICalculatorModal = false"
    />

    <!-- Change Password Modal -->
    <ChangePasswordModal
      :show="showChangePasswordModal"
      @close="showChangePasswordModal = false"
      @success="handleToastSuccess"
      @error="handleToastError"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import BalanceCard from '@/components/Mobile/BalanceCard.vue';
import StatCard from '@/components/Mobile/StatCard.vue';
import QuickActionCard from '@/components/Mobile/QuickActionCard.vue';
import CollapsibleSection from '@/components/Mobile/CollapsibleSection.vue';
import BottomNavigation from '@/components/Mobile/BottomNavigation.vue';
import DepositModal from '@/components/Mobile/DepositModal.vue';
import WithdrawalModal from '@/components/Mobile/WithdrawalModal.vue';
import LevelDownlinesModal from '@/components/Mobile/LevelDownlinesModal.vue';
import EditProfileModal from '@/components/Mobile/EditProfileModal.vue';
import SettingsModal from '@/components/Mobile/SettingsModal.vue';
import HelpSupportModal from '@/components/Mobile/HelpSupportModal.vue';
import StarterKitPurchaseModal from '@/components/Mobile/StarterKitPurchaseModal.vue';
import LogoutConfirmModal from '@/components/Mobile/LogoutConfirmModal.vue';
import LgrTransferModal from '@/components/Mobile/LgrTransferModal.vue';
import LoanApplicationModal from '@/components/Mobile/LoanApplicationModal.vue';
import EarningsBreakdown from '@/components/Mobile/EarningsBreakdown.vue';
import NotificationBell from '@/components/Mobile/NotificationBell.vue';
import Toast from '@/components/Mobile/Toast.vue';
import AnnouncementBanner from '@/components/Mobile/AnnouncementBanner.vue';
import MessagesModal from '@/components/Mobile/MessagesModal.vue';
import SupportTicketsModal from '@/components/Mobile/SupportTicketsModal.vue';
import AnalyticsModal from '@/components/Mobile/AnalyticsModal.vue';
import UpdateNotification from '@/components/Mobile/UpdateNotification.vue';
import BusinessPlanModal from '@/components/Mobile/Tools/BusinessPlanModal.vue';
import BusinessPlanListModal from '@/components/Mobile/Tools/BusinessPlanListModal.vue';
import ROICalculatorModal from '@/components/Mobile/Tools/ROICalculatorModal.vue';
import InstallPrompt from '@/components/Mobile/InstallPrompt.vue';
import MoreTabContent from '@/components/Mobile/MoreTabContent.vue';
import ChangePasswordModal from '@/components/Mobile/ChangePasswordModal.vue';
import MiniSparkline from '@/components/Mobile/MiniSparkline.vue';
import EarningsTrendChart from '@/components/Mobile/EarningsTrendChart.vue';
import MemberFilters from '@/components/Mobile/MemberFilters.vue';
import TabLoadingSkeleton from '@/components/Mobile/TabLoadingSkeleton.vue';
import ToolCategory from '@/components/Mobile/ToolCategory.vue';
import ScrollToTop from '@/components/Mobile/ScrollToTop.vue';

// Mobile-Embedded Tools (without MemberLayout)
import EarningsCalculatorEmbedded from '@/components/Mobile/Tools/EarningsCalculatorEmbedded.vue';
import GoalTrackerEmbedded from '@/components/Mobile/Tools/GoalTrackerEmbedded.vue';
import NetworkVisualizerEmbedded from '@/components/Mobile/Tools/NetworkVisualizerEmbedded.vue';
import { onMounted, onUnmounted } from 'vue';
import {
  ArrowPathIcon,
  CurrencyDollarIcon,
  UsersIcon,
  ChartBarIcon,
  BuildingOffice2Icon,
  UserPlusIcon,
  ClockIcon,
  BanknotesIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  CheckCircleIcon,
  AcademicCapIcon,
  UserCircleIcon,
  ChevronRightIcon,
  ChevronDownIcon,
  CogIcon,
  QuestionMarkCircleIcon,
  ArrowRightOnRectangleIcon,
  SparklesIcon,
  EnvelopeIcon,
  ArrowDownTrayIcon,
  BookOpenIcon,
  DocumentTextIcon as FileTextIcon,
  VideoCameraIcon as VideoIcon,
  CalculatorIcon,
  WrenchScrewdriverIcon as ToolIcon,
  FlagIcon as TargetIcon,
  ComputerDesktopIcon,
} from '@heroicons/vue/24/outline';

// Props - same as original dashboard
const props = withDefaults(defineProps<{
  user: any;
  stats: any;
  referralStats: any;
  networkData: any;
  teamVolumeData: any;
  assetData: any;
  communityProjectData: any;
  subscription: any;
  membershipProgress: any;
  starterKit: any;
  notifications: any[];
  walletBalance: number;
  isMobileDashboard?: boolean;
  verificationLimits?: any;
  remainingDailyLimit?: number;
  pendingWithdrawals?: number;
  loanSummary?: any;
  recentTopups?: any[];
  earningsBreakdown?: any;
  loyaltyPoints?: number;
  lgrWithdrawable?: number;
  lgrWithdrawablePercentage?: number;
  lgrWithdrawalBlocked?: boolean;
  announcements?: any[];
  messagingData?: any;
  supportTickets?: any[];
  existingBusinessPlan?: any;
  networkGrowth?: any[];
  earningsTrend?: any[];
}>(), {
  stats: () => ({ total_earnings: 0, this_month_earnings: 0, total_deposits: 0, total_withdrawals: 0 }),
  referralStats: () => ({ levels: [] }),
  networkData: () => ({ total_network_size: 0, direct_referrals: 0 }),
  teamVolumeData: () => ({ current_month: null }),
  assetData: () => ({ summary: { total_assets: 0, active_assets: 0 }, assets: [] }),
  notifications: () => [],
  walletBalance: 0,
  announcements: () => [],
  messagingData: () => ({ unread_count: 0 }),
  networkGrowth: () => [],
  earningsTrend: () => []
});

const loading = ref(false);
const activeTab = ref('home');
const previousTab = ref('home'); // Track previous tab for More drawer
const activeTool = ref<'content' | 'calculator' | 'goals' | 'network' | 'commission' | null>(null);

// Lazy loading state for tabs
const tabDataLoaded = ref({
  home: true,  // Home loads immediately
  team: false,
  wallet: false,
  learn: false,
  more: false
});

const tabLoading = ref(false);

// Calculator state
const calcEarningType = ref<'referral' | 'lgr'>('referral');
const calcTeamSize = ref(10);
const calcActivePercent = ref(50);
const calcLgrProfit = ref(50000);

// Member filter state
const memberFilter = ref('all'); // all, active, inactive
const memberSort = ref('recent'); // recent, name, earnings, oldest
const memberSearch = ref('');

// Network stats for tools - using real data
const networkStats = computed(() => {
    // Calculate active members (only those with starter kits) from all levels
    let activeCount = 0;
    displayLevels.value.forEach(level => {
        if (level.members && Array.isArray(level.members)) {
            activeCount += level.members.filter((m: any) => m.has_starter_kit).length;
        }
    });
    
    // Calculate total team earnings from all levels
    let totalTeamEarnings = 0;
    displayLevels.value.forEach(level => {
        totalTeamEarnings += level.total_earnings || 0;
    });
    
    return {
        level_1: displayLevels.value[0]?.count || 0,
        level_2: displayLevels.value[1]?.count || 0,
        level_3: displayLevels.value[2]?.count || 0,
        level_4: displayLevels.value[3]?.count || 0,
        level_5: displayLevels.value[4]?.count || 0,
        level_6: displayLevels.value[5]?.count || 0,
        level_7: displayLevels.value[6]?.count || 0,
        total_members: props.networkData?.total_network_size || 0,
        active_members: activeCount,
        // Use team volume if available, otherwise use team earnings
        total_volume: props.teamVolumeData?.current_month?.total_volume || totalTeamEarnings || 0,
        this_month_volume: props.teamVolumeData?.current_month?.team_volume || props.stats?.this_month_earnings || 0,
    };
});

// Network tree for visualizer - using real member data from displayLevels
const networkTree = computed(() => {
    // Get Level 1 members (direct referrals)
    const level1Members = displayLevels.value[0]?.members || [];
    
    return level1Members.map((member: any) => ({
        id: member.id,
        name: member.name,
        email: member.email,
        tier: member.tier || member.starter_kit_tier,
        has_starter_kit: member.has_starter_kit,
        is_active: member.is_active,
        joined_at: member.created_at || member.joined_date,
        joined_date: member.joined_date,
        children: [], // Level 2+ members would go here if available
    }));
});

// Get level counts for visualizer
const levelCounts = computed(() => {
    return {
        1: displayLevels.value[0]?.count || 0,
        2: displayLevels.value[1]?.count || 0,
        3: displayLevels.value[2]?.count || 0,
        4: displayLevels.value[3]?.count || 0,
        5: displayLevels.value[4]?.count || 0,
        6: displayLevels.value[5]?.count || 0,
        7: displayLevels.value[6]?.count || 0,
    };
});

// Network growth data for sparkline
const networkGrowthData = computed(() => {
    // Use real backend data if available
    if (props.networkGrowth && props.networkGrowth.length > 0) {
        return props.networkGrowth.map(item => item.count);
    }
    
    // Fallback to mock data for development
    const currentSize = props.networkData?.total_network_size || 0;
    const months = 6;
    const data = [];
    
    for (let i = months - 1; i >= 0; i--) {
        const percentage = (months - i) / months;
        const value = Math.max(0, Math.floor(currentSize * percentage * (0.7 + Math.random() * 0.3)));
        data.push(value);
    }
    
    return data;
});

// Earnings trend data for chart
const earningsTrendData = computed(() => {
    // Use real backend data if available
    if (props.earningsTrend && props.earningsTrend.length > 0) {
        return props.earningsTrend;
    }
    
    // Fallback to mock data for development
    const currentEarnings = props.stats?.this_month_earnings || 0;
    const months = ['Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov'];
    const data = [];
    
    const now = new Date();
    for (let i = 5; i >= 0; i--) {
        const date = new Date(now.getFullYear(), now.getMonth() - i, 1);
        const monthLabel = months[5 - i];
        const monthKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
        
        // Generate realistic-looking data
        const baseAmount = currentEarnings * (0.4 + Math.random() * 0.6);
        const amount = Math.floor(baseAmount * ((6 - i) / 6));
        
        data.push({
            month: monthKey,
            label: monthLabel,
            amount: amount
        });
    }
    
    return data;
});

// Helper function to get tool title
const getToolTitle = (tool: string) => {
    const titles: Record<string, string> = {
        'calculator': 'Earnings Calculator',
        'goals': 'Goal Tracker',
        'network': 'Network Visualizer',
        'commission': 'Commission Calculator',
        'content': 'Learning Content',
    };
    return titles[tool] || 'Tool';
};

// UI State
const showAllQuickActions = ref(false);

// Collapsible sections state with smart defaults
const collapsedSections = ref<Record<string, boolean>>({
  commissionLevels: true, // Collapsed by default
  teamVolume: true, // Collapsed by default
  assets: true, // Collapsed by default
});

// Load saved preferences from localStorage
onMounted(() => {
  const saved = localStorage.getItem('collapsedSections');
  if (saved) {
    try {
      collapsedSections.value = JSON.parse(saved);
    } catch (e) {
      // Ignore parse errors
    }
  }
});

// Save preferences when changed
watch(collapsedSections, (newValue) => {
  localStorage.setItem('collapsedSections', JSON.stringify(newValue));
}, { deep: true });

// Goals state
const showCreateGoalModal = ref(false);
const showDepositModal = ref(false);
const showWithdrawalModal = ref(false);
const showAllTransactions = ref(false);
const showLevelDownlinesModal = ref(false);
const showEditProfileModal = ref(false);
const showSettingsModal = ref(false);
const showHelpSupportModal = ref(false);
const showStarterKitModal = ref(false);
const showLogoutModal = ref(false);
const showLgrTransferModal = ref(false);
const showMessagesModal = ref(false);
const showSupportModal = ref(false);
const showAnalyticsModal = ref(false);

// PWA Install
const deferredPrompt = ref<any>(null);
const showInstallButton = ref(false);
const showLoanApplicationModal = ref(false);
const showContentLibraryModal = ref(false);
const showCalculatorModal = ref(false);
const showBusinessPlanModal = ref(false);
const showBusinessPlanListModal = ref(false);
const showROICalculatorModal = ref(false);
const showChangePasswordModal = ref(false);

const handleViewAllPlans = () => {
  showBusinessPlanModal.value = false;
  showBusinessPlanListModal.value = true;
};

const handleOpenPlan = (plan: any) => {
  console.log('handleOpenPlan called with:', plan);
  showBusinessPlanListModal.value = false;
  // Load the plan data
  if (plan) {
    console.log('Setting existingBusinessPlan to:', plan);
    existingBusinessPlan.value = plan;
  } else {
    console.log('Clearing existingBusinessPlan for new plan');
    existingBusinessPlan.value = null;
  }
  console.log('Opening BusinessPlanModal');
  showBusinessPlanModal.value = true;
};

const handleCloseBusinessPlan = () => {
  showBusinessPlanModal.value = false;
  // Clear the existing plan so next time it opens fresh
  existingBusinessPlan.value = null;
};

// Calculator state
const calcTeamSizes = ref({
    level_1: 3,
    level_2: 9,
    level_3: 27,
    level_4: 81,
    level_5: 243,
    level_6: 729,
    level_7: 2187,
});
const calcSubscriptionPrice = ref(500);
const calcStarterKitPrice = ref(500);
const calcActivePercentage = ref(50);

// Commission rates (hardcoded for mobile)
const commissionRates = {
    subscription: {
        level_1: 10,
        level_2: 5,
        level_3: 3,
        level_4: 2,
        level_5: 1,
        level_6: 1,
        level_7: 1,
    },
    starter_kit: {
        level_1: 10,
        level_2: 5,
        level_3: 3,
        level_4: 2,
        level_5: 1,
        level_6: 1,
        level_7: 1,
    },
};

// Calculator computed values
const calcResults = computed(() => {
    const results: any[] = [];
    
    for (let level = 1; level <= 7; level++) {
        const levelKey = `level_${level}`;
        const teamSize = calcTeamSizes.value[levelKey as keyof typeof calcTeamSizes.value];
        const activeMembers = Math.floor(teamSize * (calcActivePercentage.value / 100));
        
        const subRate = commissionRates.subscription[levelKey as keyof typeof commissionRates.subscription] || 0;
        const subCommission = activeMembers * calcSubscriptionPrice.value * (subRate / 100);
        
        const skRate = commissionRates.starter_kit[levelKey as keyof typeof commissionRates.starter_kit] || 0;
        const skCommission = activeMembers * calcStarterKitPrice.value * (skRate / 100);
        
        const levelTotal = subCommission + skCommission;
        
        results.push({
            level,
            teamSize,
            activeMembers,
            subRate,
            skRate,
            levelTotal,
        });
    }
    
    return results;
});

const calcTotalCommission = computed(() => {
    return calcResults.value.reduce((sum, result) => sum + result.levelTotal, 0);
});

const calcMonthlyProjection = computed(() => calcTotalCommission.value);
const calcYearlyProjection = computed(() => calcTotalCommission.value * 12);

const formatCalcCurrency = (amount: number) => {
    return amount.toLocaleString('en-ZM', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
};

const selectedLevel = ref(1);
const levelDownlines = ref<any[]>([]);
const expandedLevels = ref<number[]>([]);

// Toast notification state
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref<'success' | 'error' | 'warning' | 'info'>('success');

// Announcements state
const currentAnnouncementIndex = ref(0);
const displayedAnnouncements = ref(props.announcements || []);

const currentAnnouncement = computed(() => {
  return displayedAnnouncements.value[currentAnnouncementIndex.value] || null;
});

const handleAnnouncementDismissed = () => {
  displayedAnnouncements.value.splice(currentAnnouncementIndex.value, 1);
  if (currentAnnouncementIndex.value >= displayedAnnouncements.value.length) {
    currentAnnouncementIndex.value = Math.max(0, displayedAnnouncements.value.length - 1);
  }
};

const currentTier = computed(() => {
  // Show "Free Member" if user doesn't have starter kit
  if (!props.user?.has_starter_kit) {
    return 'Free Member';
  }
  
  // Otherwise show their tier
  if (typeof props.membershipProgress?.current_tier === 'object') {
    return props.membershipProgress?.current_tier?.name || 'Associate';
  }
  return props.membershipProgress?.current_tier || 'Associate';
});

const referralLink = ref(`${window.location.origin}/register?ref=${props.user?.referral_code || props.user?.id || 'unknown'}`);

// Time-based greeting
const timeBasedGreeting = computed(() => {
  const hour = new Date().getHours();
  if (hour < 12) return 'Good morning';
  if (hour < 17) return 'Good afternoon';
  return 'Good evening';
});

// Ensure we always have 7 levels (even if backend returns fewer)
const ensureSevenLevels = (levels: any[] | undefined) => {
  const result = [];
  const levelsArray = Array.isArray(levels) ? levels : [];
  
  for (let i = 1; i <= 7; i++) {
    const existingLevel = levelsArray.find(l => l?.level === i);
    result.push(existingLevel || {
      level: i,
      count: 0,
      total_earnings: 0,
      this_month_earnings: 0,
      team_volume: 0
    });
  }
  return result;
};

// Initialize displayLevels as ref and watch for prop changes
const displayLevels = ref(ensureSevenLevels(props.referralStats?.levels));

// Watch for changes in referralStats and update displayLevels
watch(() => props.referralStats?.levels, (newLevels) => {
  displayLevels.value = ensureSevenLevels(newLevels);
}, { deep: true });

// Filtered and sorted display levels
const filteredDisplayLevels = computed(() => {
  return displayLevels.value.map(level => {
    if (!level.members || !Array.isArray(level.members)) {
      return level;
    }

    let filteredMembers = [...level.members];

    // Apply filter
    if (memberFilter.value === 'active') {
      filteredMembers = filteredMembers.filter((m: any) => m.is_active);
    } else if (memberFilter.value === 'inactive') {
      filteredMembers = filteredMembers.filter((m: any) => !m.is_active);
    }

    // Apply search
    if (memberSearch.value) {
      const searchLower = memberSearch.value.toLowerCase();
      filteredMembers = filteredMembers.filter((m: any) => 
        m.name?.toLowerCase().includes(searchLower) || 
        m.email?.toLowerCase().includes(searchLower)
      );
    }

    // Apply sort
    if (memberSort.value === 'name') {
      filteredMembers.sort((a: any, b: any) => (a.name || '').localeCompare(b.name || ''));
    } else if (memberSort.value === 'earnings') {
      filteredMembers.sort((a: any, b: any) => (b.total_earnings || 0) - (a.total_earnings || 0));
    } else if (memberSort.value === 'oldest') {
      filteredMembers.sort((a: any, b: any) => {
        const dateA = new Date(a.created_at || a.joined_date || 0);
        const dateB = new Date(b.created_at || b.joined_date || 0);
        return dateA.getTime() - dateB.getTime();
      });
    } else { // recent (default)
      filteredMembers.sort((a: any, b: any) => {
        const dateA = new Date(a.created_at || a.joined_date || 0);
        const dateB = new Date(b.created_at || b.joined_date || 0);
        return dateB.getTime() - dateA.getTime();
      });
    }

    return {
      ...level,
      members: filteredMembers,
      count: filteredMembers.length
    };
  });
});

// Member filter options
const memberFilterOptions = computed(() => {
  const allMembers = displayLevels.value.reduce((acc, level) => {
    return acc + (level.members?.length || 0);
  }, 0);
  
  const activeMembers = displayLevels.value.reduce((acc, level) => {
    return acc + (level.members?.filter((m: any) => m.is_active).length || 0);
  }, 0);
  
  const inactiveMembers = allMembers - activeMembers;

  return [
    { label: 'All', value: 'all', count: allMembers },
    { label: 'Active', value: 'active', count: activeMembers },
    { label: 'Inactive', value: 'inactive', count: inactiveMembers }
  ];
});

// Total filtered count
const totalFilteredMembers = computed(() => {
  return filteredDisplayLevels.value.reduce((acc, level) => acc + level.count, 0);
});

// Tool categories for reorganized Learn tab
const learningResourcesTools = computed(() => [
  {
    id: 'ebooks',
    name: 'E-Books',
    description: 'Digital books',
    icon: '📖',
    iconBg: 'bg-blue-100',
    iconColor: 'text-blue-600',
    bgGradient: 'from-blue-50 to-indigo-50',
    borderColor: 'border-blue-200',
    locked: !props.user?.has_starter_kit,
    action: 'content'
  },
  {
    id: 'videos',
    name: 'Videos',
    description: 'Training videos',
    icon: '🎥',
    iconBg: 'bg-purple-100',
    iconColor: 'text-purple-600',
    bgGradient: 'from-purple-50 to-pink-50',
    borderColor: 'border-purple-200',
    locked: !props.user?.has_starter_kit,
    action: 'content'
  },
  {
    id: 'templates',
    name: 'Templates',
    description: 'Business docs',
    icon: '📄',
    iconBg: 'bg-green-100',
    iconColor: 'text-green-600',
    bgGradient: 'from-green-50 to-emerald-50',
    borderColor: 'border-green-200',
    locked: !props.user?.has_starter_kit,
    action: 'content'
  },
  {
    id: 'guides',
    name: 'Guides',
    description: 'Step-by-step',
    icon: '📚',
    iconBg: 'bg-orange-100',
    iconColor: 'text-orange-600',
    bgGradient: 'from-orange-50 to-amber-50',
    borderColor: 'border-orange-200',
    locked: !props.user?.has_starter_kit,
    action: 'content'
  }
]);

const businessTools = computed(() => [
  {
    id: 'calculator',
    name: 'Calculator',
    description: 'Earnings calc',
    iconComponent: CalculatorIcon,
    iconBg: 'bg-green-100',
    iconColor: 'text-green-600',
    bgGradient: 'from-green-50 to-emerald-50',
    borderColor: 'border-green-200',
    locked: !props.user?.has_starter_kit,
    action: 'calculator'
  },
  {
    id: 'goals',
    name: 'Goal Tracker',
    description: 'Track progress',
    iconComponent: TargetIcon,
    iconBg: 'bg-purple-100',
    iconColor: 'text-purple-600',
    bgGradient: 'from-purple-50 to-pink-50',
    borderColor: 'border-purple-200',
    locked: !props.user?.has_starter_kit,
    action: 'goals'
  },
  {
    id: 'network',
    name: 'Network Viz',
    description: 'View network',
    iconComponent: UsersIcon,
    iconBg: 'bg-orange-100',
    iconColor: 'text-orange-600',
    bgGradient: 'from-orange-50 to-amber-50',
    borderColor: 'border-orange-200',
    locked: !props.user?.has_starter_kit,
    action: 'network'
  },
  {
    id: 'analytics',
    name: 'Analytics',
    description: 'Performance',
    iconComponent: ChartBarIcon,
    iconBg: 'bg-blue-100',
    iconColor: 'text-blue-600',
    bgGradient: 'from-blue-50 to-indigo-50',
    borderColor: 'border-blue-200',
    locked: !props.user?.has_starter_kit,
    action: 'analytics'
  }
]);

const premiumTools = computed(() => [
  {
    id: 'business-plan',
    name: 'Business Plan',
    description: 'Create plan',
    iconComponent: FileTextIcon,
    iconBg: 'bg-indigo-100',
    iconColor: 'text-indigo-600',
    bgGradient: 'from-indigo-50 to-purple-50',
    borderColor: 'border-indigo-200',
    locked: props.user?.starter_kit_tier !== 'premium',
    premium: true,
    action: 'business-plan'
  },
  {
    id: 'roi-calculator',
    name: 'ROI Calculator',
    description: 'Calculate ROI',
    iconComponent: CalculatorIcon,
    iconBg: 'bg-yellow-100',
    iconColor: 'text-yellow-600',
    bgGradient: 'from-yellow-50 to-orange-50',
    borderColor: 'border-yellow-200',
    locked: props.user?.starter_kit_tier !== 'premium',
    premium: true,
    action: 'roi-calculator'
  },
  {
    id: 'advanced-analytics',
    name: 'Advanced Analytics',
    description: 'Deep insights',
    iconComponent: ChartBarIcon,
    iconBg: 'bg-pink-100',
    iconColor: 'text-pink-600',
    bgGradient: 'from-pink-50 to-rose-50',
    borderColor: 'border-pink-200',
    locked: props.user?.starter_kit_tier !== 'premium',
    premium: true,
    action: 'advanced-analytics'
  },
  {
    id: 'commission-calc',
    name: 'Commission Calc',
    description: 'Forecast earnings',
    iconComponent: CurrencyDollarIcon,
    iconBg: 'bg-emerald-100',
    iconColor: 'text-emerald-600',
    bgGradient: 'from-emerald-50 to-green-50',
    borderColor: 'border-emerald-200',
    locked: props.user?.starter_kit_tier !== 'premium',
    premium: true,
    action: 'commission'
  }
]);

const handleToolClick = (tool: any) => {
  if (tool.action === 'content') {
    router.visit(route('mygrownet.content.index'));
  } else if (tool.action === 'business-plan') {
    showBusinessPlanModal.value = true;
  } else if (tool.action === 'roi-calculator') {
    showROICalculatorModal.value = true;
  } else if (tool.action === 'analytics') {
    showAnalyticsModal.value = true;
  } else {
    activeTool.value = tool.action;
  }
};

const handleUpgrade = () => {
  showStarterKitModal.value = true;
};

// Watch activeTool to control body scroll
watch(activeTool, (newValue) => {
  if (newValue && newValue !== 'content') {
    // Tool is open - hide body scroll
    document.body.style.overflow = 'hidden';
  } else {
    // Tool is closed - restore body scroll
    document.body.style.overflow = '';
  }
});

// Watch activeTab to control body scroll for More drawer
watch(activeTab, (newValue) => {
  if (newValue === 'more') {
    // More drawer is open - hide body scroll
    document.body.style.overflow = 'hidden';
  } else {
    // More drawer is closed - restore body scroll (if no tool is open)
    if (!activeTool.value || activeTool.value === 'content') {
      document.body.style.overflow = '';
    }
  }
});

const handleTabChange = async (tab: string) => {
  // Redirect old 'profile' tab to 'more' tab
  const targetTab = tab === 'profile' ? 'more' : tab;
  
  // Track previous tab before changing (for More drawer)
  if (activeTab.value !== 'more' && targetTab === 'more') {
    previousTab.value = activeTab.value;
  }
  
  activeTab.value = targetTab;
  
  // Lazy load tab data if not already loaded
  if (!tabDataLoaded.value[targetTab as keyof typeof tabDataLoaded.value]) {
    await loadTabData(targetTab);
  }
  
  // Smooth scroll to top when changing tabs (except for More drawer)
  if (targetTab !== 'more') {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

// Lazy load tab data
const loadTabData = async (tab: string) => {
  if (tabDataLoaded.value[tab as keyof typeof tabDataLoaded.value]) {
    return; // Already loaded
  }

  tabLoading.value = true;
  
  try {
    // In a real implementation, you would fetch data from the backend here
    // For now, we'll just mark it as loaded since data comes from props
    
    // Simulate a small delay to show loading state
    await new Promise(resolve => setTimeout(resolve, 300));
    
    tabDataLoaded.value[tab as keyof typeof tabDataLoaded.value] = true;
  } catch (error) {
    console.error(`Error loading ${tab} tab data:`, error);
  } finally {
    tabLoading.value = false;
  }
};

const formatCurrency = (value: number | undefined | null) => {
  if (value === undefined || value === null || isNaN(value)) {
    return '0.00';
  }
  return Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

// Verification badge for More tab
const verificationBadge = computed(() => {
  if (props.user?.is_verified) return 'Verified';
  if (props.user?.verification_pending) return 'Pending';
  return 'Not Verified';
});

// Loan eligibility computed values
const loanLimit = computed(() => parseFloat(props.user?.loan_limit) || 0);
const loanBalance = computed(() => parseFloat(props.loanSummary?.loan_balance) || 0);
const availableCredit = computed(() => loanLimit.value - loanBalance.value);

const loanEligibility = computed(() => {
  // Must have active subscription
  if (props.user?.status !== 'active') {
    return {
      eligible: false,
      reason: 'Your account must be active to apply for a loan.',
    };
  }
  
  // Must have a starter kit
  if (!props.user?.has_starter_kit) {
    return {
      eligible: false,
      reason: 'You must have a starter kit to apply for loans. Please purchase a starter kit first.',
    };
  }
  
  // Check if has existing loan with poor repayment
  if (loanBalance.value > 0) {
    const totalIssued = props.loanSummary?.total_issued || 0;
    const totalRepaid = props.loanSummary?.total_repaid || 0;
    const repaymentRate = totalIssued > 0 ? (totalRepaid / totalIssued) * 100 : 0;
    
    if (repaymentRate < 50) {
      return {
        eligible: false,
        reason: 'Please repay at least 50% of your current loan before applying for another.',
      };
    }
  }
  
  // Check available credit
  if (availableCredit.value < 100) {
    return {
      eligible: false,
      reason: `Insufficient loan limit. Available credit: K${availableCredit.value.toFixed(2)}`,
    };
  }
  
  return {
    eligible: true,
    reason: '',
  };
});

const refreshData = () => {
  loading.value = true;
  // Inertia reload
  window.location.reload();
  setTimeout(() => loading.value = false, 1000);
};

const switchToClassicView = async () => {
  try {
    // Update user preference to desktop (classic) using axios
    await axios.post(route('mygrownet.api.user.dashboard-preference'), {
      preference: 'desktop'
    });
    
    // Redirect to classic dashboard
    window.location.href = route('mygrownet.classic-dashboard');
  } catch (error) {
    console.error('Switch view error:', error);
    // If error, save preference in localStorage as fallback and redirect anyway
    localStorage.setItem('preferred_dashboard', 'desktop');
    window.location.href = route('mygrownet.classic-dashboard');
  }
};

const navigateToMessages = () => {
  // Open messages modal instead of navigating
  showMessagesModal.value = true;
};

// Listen for notification clicks to open message modal
const handleOpenMessageModal = (event: CustomEvent) => {
  showMessagesModal.value = true;
  // The MessagesModal will handle opening the specific message
};

onMounted(() => {
  window.addEventListener('open-message-modal', handleOpenMessageModal as EventListener);
});

onUnmounted(() => {
  window.removeEventListener('open-message-modal', handleOpenMessageModal as EventListener);
  // Restore body scroll on unmount
  document.body.style.overflow = '';
});

const getLevelBgClass = (level: number) => {
  const classes = [
    'bg-blue-50',
    'bg-green-50',
    'bg-yellow-50',
    'bg-purple-50',
    'bg-pink-50',
    'bg-indigo-50',
    'bg-orange-50',
  ];
  return classes[level - 1] || 'bg-gray-50';
};

const getLevelColorClass = (level: number) => {
  const classes = [
    'bg-blue-500',
    'bg-green-500',
    'bg-yellow-500',
    'bg-purple-500',
    'bg-pink-500',
    'bg-indigo-500',
    'bg-orange-500',
  ];
  return classes[level - 1] || 'bg-gray-500';
};

const getNotificationClass = (priority: string) => {
  return {
    'bg-red-50 border border-red-200': priority === 'high',
    'bg-yellow-50 border border-yellow-200': priority === 'medium',
    'bg-blue-50 border border-blue-200': priority === 'low',
  };
};

const getNotificationIcon = (priority: string) => {
  return priority === 'high' ? ExclamationTriangleIcon :
         priority === 'medium' ? InformationCircleIcon :
         CheckCircleIcon;
};

const getNotificationIconClass = (priority: string) => {
  return priority === 'high' ? 'text-red-400' :
         priority === 'medium' ? 'text-yellow-400' :
         'text-blue-400';
};

const getNotificationTextClass = (priority: string) => {
  return priority === 'high' ? 'text-red-800' :
         priority === 'medium' ? 'text-yellow-800' :
         'text-blue-800';
};

const getNotificationSubtextClass = (priority: string) => {
  return priority === 'high' ? 'text-red-700' :
         priority === 'medium' ? 'text-yellow-700' :
         'text-blue-700';
};

const getNotificationLinkClass = (priority: string) => {
  return priority === 'high' ? 'text-red-600 hover:text-red-500' :
         priority === 'medium' ? 'text-yellow-600 hover:text-yellow-500' :
         'text-blue-600 hover:text-blue-500';
};

// Toast helper function
const showToastMessage = (message: string, type: 'success' | 'error' | 'warning' | 'info' = 'success') => {
  toastMessage.value = message;
  toastType.value = type;
  showToast.value = true;
};

const handleToastSuccess = (message: string) => {
  showToastMessage(message, 'success');
};

const handleToastError = (message: string) => {
  showToastMessage(message, 'error');
};

const copyReferralLink = async () => {
  try {
    await navigator.clipboard.writeText(referralLink.value);
    showToastMessage('Referral link copied to clipboard!', 'success');
  } catch (err) {
    console.error('Failed to copy:', err);
    showToastMessage('Failed to copy link. Please try again.', 'error');
  }
};

const handleLogout = () => {
  showLogoutModal.value = true;
};

const confirmLogout = () => {
  // Use Inertia's router which automatically handles CSRF tokens
  router.post(route('logout'));
};

// PWA Install functionality
const installPWA = async () => {
  if (!deferredPrompt.value) {
    // Check if already installed
    if (window.matchMedia('(display-mode: standalone)').matches) {
      showToastMessage('App is already installed!', 'info');
    } else {
      // Show instructions for iOS or unsupported browsers
      showToastMessage('To install: Tap the share button and select "Add to Home Screen"', 'info');
    }
    return;
  }

  // Show the install prompt
  deferredPrompt.value.prompt();
  
  // Wait for the user to respond to the prompt
  const { outcome } = await deferredPrompt.value.userChoice;
  
  if (outcome === 'accepted') {
    showToastMessage('App installed successfully!', 'success');
  }
  
  // Clear the deferredPrompt
  deferredPrompt.value = null;
  showInstallButton.value = false;
};

// Listen for the beforeinstallprompt event
if (typeof window !== 'undefined') {
  window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent the mini-infobar from appearing on mobile
    e.preventDefault();
    // Stash the event so it can be triggered later
    deferredPrompt.value = e;
    // Show the install button
    showInstallButton.value = true;
  });

  // Check if already installed
  window.addEventListener('appinstalled', () => {
    showInstallButton.value = false;
    deferredPrompt.value = null;
  });
}

const showComingSoon = (feature: string) => {
  showToastMessage(`${feature} feature coming soon in mobile view!`, 'info');
};

const toggleLevelMembers = (level: number) => {
  // Find the level data from displayLevels
  const levelData = displayLevels.value.find(l => l.level === level);
  
  if (!levelData) {
    showToastMessage(`No data found for level ${level}`, 'error');
    return;
  }
  
  // Set the selected level and members
  selectedLevel.value = level;
  levelDownlines.value = levelData.members || [];
  
  // Show the modal
  showLevelDownlinesModal.value = true;
};
</script>


<style scoped>
/* Professional Animations */
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slide-in {
  from {
    opacity: 0;
    transform: translateX(-10px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes pulse-subtle {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.95;
  }
}

.animate-fade-in {
  animation: fade-in 0.6s ease-out;
}

.animate-slide-in {
  animation: slide-in 0.6s ease-out 0.2s both;
}

.animate-pulse-subtle {
  animation: pulse-subtle 2s ease-in-out infinite;
}

/* Smooth scroll behavior */
* {
  scroll-behavior: smooth;
}

/* Enhanced transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Active state improvements */
.active\:scale-98:active {
  transform: scale(0.98);
}

.active\:scale-95:active {
  transform: scale(0.95);
}
</style>
