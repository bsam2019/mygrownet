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
              <h1 class="text-xl font-bold tracking-tight animate-fade-in">{{ timeBasedGreeting }}, {{ user?.name?.split(' ')[0] || 'User' }}! 👋</h1>
              <div class="flex items-center gap-2 mt-1">
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
              class="p-2.5 rounded-xl bg-white/15 hover:bg-white/25 backdrop-blur-sm transition-all duration-200 active:scale-95 border border-white/20"
              :disabled="loading"
            >
              <ArrowPathIcon class="h-5 w-5" :class="{ 'animate-spin': loading }" />
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

        <!-- Quick Actions -->
        <div class="animate-fade-in" style="animation-delay: 0.3s; animation-fill-mode: both;">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-gray-900">Quick Actions</h2>
            <div class="h-px flex-1 bg-gradient-to-r from-gray-300 to-transparent ml-4"></div>
          </div>
          <div class="space-y-2">
            <QuickActionCard
              title="Refer a Friend"
              subtitle="Share your link and earn"
              @click="activeTab = 'team'"
              :icon="UserPlusIcon"
              iconBgClass="bg-blue-50"
              iconColorClass="text-blue-600"
            />
            <QuickActionCard
              title="View My Team"
              subtitle="See your network"
              @click="activeTab = 'team'"
              :icon="UsersIcon"
              iconBgClass="bg-green-50"
              iconColorClass="text-green-600"
            />
            <QuickActionCard
              title="Messages"
              :subtitle="messagingData?.unread_count > 0 ? `${messagingData.unread_count} unread` : 'No new messages'"
              @click="navigateToMessages"
              :icon="EnvelopeIcon"
              iconBgClass="bg-blue-50"
              iconColorClass="text-blue-600"
              :badge="messagingData?.unread_count"
            />
            <QuickActionCard
              title="Transaction History"
              subtitle="View all transactions"
              @click="activeTab = 'wallet'"
              :icon="ClockIcon"
              iconBgClass="bg-purple-50"
              iconColorClass="text-purple-600"
            />
            <QuickActionCard
              v-if="!user?.has_starter_kit"
              title="Get Starter Kit"
              subtitle="Unlock learning & earning"
              @click="showStarterKitModal = true"
              :icon="SparklesIcon"
              iconBgClass="bg-gradient-to-r from-indigo-50 to-purple-50"
              iconColorClass="text-indigo-600"
            />
            <QuickActionCard
              title="Apply for Loan"
              subtitle="Quick business funding"
              @click="showLoanApplicationModal = true"
              :icon="BanknotesIcon"
              iconBgClass="bg-gradient-to-r from-emerald-50 to-green-50"
              iconColorClass="text-emerald-600"
            />
          </div>
        </div>

        <!-- Commission Levels - Collapsible -->
        <CollapsibleSection
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
        <div class="bg-white rounded-xl shadow-sm p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-4">My Network</h2>
          
          <!-- Network Stats -->
          <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
              <p class="text-2xl font-bold text-blue-600">{{ networkData?.total_network_size || 0 }}</p>
              <p class="text-xs text-gray-600 mt-1">Total Team</p>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
              <p class="text-2xl font-bold text-green-600">{{ Array.isArray(networkData?.direct_referrals) ? networkData.direct_referrals.length : 0 }}</p>
              <p class="text-xs text-gray-600 mt-1">Direct Referrals</p>
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

        <!-- Level Breakdown -->
        <div class="bg-white rounded-xl shadow-sm p-6">
          <h3 class="text-base font-bold text-gray-900 mb-4">Team by Level (7 Levels)</h3>
          <div class="space-y-3">
            <div
              v-for="level in displayLevels"
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
      </div>

      <!-- WALLET TAB -->
      <div v-show="activeTab === 'wallet'" class="space-y-6">
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
      </div>

      <!-- LEARN TAB -->
      <div v-show="activeTab === 'learn'" class="space-y-6">
        <div class="bg-gradient-to-br from-purple-600 to-indigo-600 rounded-2xl shadow-lg p-6 text-white">
          <h2 class="text-xl font-bold mb-2">Learning Center</h2>
          <p class="text-sm opacity-90">Grow your skills and knowledge</p>
        </div>

        <!-- Learning Categories -->
        <div class="grid grid-cols-2 gap-3">
          <button class="bg-white rounded-xl shadow-sm p-4 text-left hover:shadow-md transition-all active:scale-95">
            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center mb-3">
              <AcademicCapIcon class="h-6 w-6 text-blue-600" />
            </div>
            <h3 class="text-sm font-semibold text-gray-900">Courses</h3>
            <p class="text-xs text-gray-500 mt-1">12 available</p>
          </button>

          <button class="bg-white rounded-xl shadow-sm p-4 text-left hover:shadow-md transition-all active:scale-95">
            <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center mb-3">
              <BuildingOffice2Icon class="h-6 w-6 text-green-600" />
            </div>
            <h3 class="text-sm font-semibold text-gray-900">Resources</h3>
            <p class="text-xs text-gray-500 mt-1">25 items</p>
          </button>
        </div>

        <!-- Featured Content -->
        <div class="bg-white rounded-xl shadow-sm p-6">
          <h3 class="text-base font-bold text-gray-900 mb-4">Featured Content</h3>
          <div class="space-y-3">
            <div class="flex gap-4 p-3 bg-gray-50 rounded-lg">
              <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg flex-shrink-0"></div>
              <div class="flex-1 min-w-0">
                <h4 class="text-sm font-semibold text-gray-900 mb-1">Getting Started Guide</h4>
                <p class="text-xs text-gray-500">Learn the basics of MyGrowNet</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- PROFILE TAB -->
      <div v-show="activeTab === 'profile'" class="space-y-6">
        <!-- Profile Header -->
        <div class="bg-white rounded-xl shadow-sm p-6">
          <div class="flex items-center gap-4 mb-6">
            <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
              {{ user?.name?.charAt(0) || 'U' }}
            </div>
            <div class="flex-1 min-w-0">
              <h2 class="text-lg font-bold text-gray-900">{{ user?.name || 'User' }}</h2>
              <p class="text-sm text-gray-500">{{ user?.email || 'No email' }}</p>
              <div class="flex items-center gap-2 mt-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  {{ currentTier }}
                </span>
              </div>
            </div>
          </div>

          <!-- Membership Progress -->
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
              <p class="text-xs font-medium text-gray-700">Membership Progress</p>
              <p class="text-xs font-bold text-blue-600">{{ membershipProgress?.progress_percentage || 0 }}%</p>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div
                class="bg-gradient-to-r from-blue-500 to-indigo-600 h-2 rounded-full transition-all duration-500"
                :style="{ width: `${membershipProgress?.progress_percentage || 0}%` }"
              ></div>
            </div>
            <p class="text-xs text-gray-600 mt-2">
              {{ membershipProgress?.next_tier?.name || 'Max Level' }}
            </p>
          </div>
        </div>

        <!-- Profile Menu -->
        <div class="bg-white rounded-xl shadow-sm divide-y divide-gray-100">
          <button
            @click="showEditProfileModal = true"
            class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-center gap-3">
              <UserCircleIcon class="h-5 w-5 text-gray-400" />
              <span class="text-sm font-medium text-gray-900">Edit Profile</span>
            </div>
            <ChevronRightIcon class="h-5 w-5 text-gray-400" />
          </button>

          <button
            @click="showSettingsModal = true"
            class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-center gap-3">
              <CogIcon class="h-5 w-5 text-gray-400" />
              <span class="text-sm font-medium text-gray-900">Settings</span>
            </div>
            <ChevronRightIcon class="h-5 w-5 text-gray-400" />
          </button>

          <button
            @click="navigateToMessages"
            class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-center gap-3">
              <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              <span class="text-sm font-medium text-gray-900">Messages</span>
              <span 
                v-if="messagingData?.unread_count > 0"
                class="ml-auto inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full"
              >
                {{ messagingData.unread_count }}
              </span>
            </div>
            <ChevronRightIcon class="h-5 w-5 text-gray-400" />
          </button>

          <button
            @click="showSupportModal = true"
            class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-center gap-3">
              <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <span class="text-sm font-medium text-gray-900">Support Tickets</span>
            </div>
            <ChevronRightIcon class="h-5 w-5 text-gray-400" />
          </button>

          <button
            @click="showHelpSupportModal = true"
            class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-center gap-3">
              <QuestionMarkCircleIcon class="h-5 w-5 text-gray-400" />
              <span class="text-sm font-medium text-gray-900">Help & Support</span>
            </div>
            <ChevronRightIcon class="h-5 w-5 text-gray-400" />
          </button>

          <button
            v-if="showInstallButton"
            @click="installPWA"
            class="w-full flex items-center justify-between p-4 hover:bg-blue-50 transition-colors"
          >
            <div class="flex items-center gap-3">
              <ArrowDownTrayIcon class="h-5 w-5 text-blue-600" />
              <span class="text-sm font-medium text-blue-600">Install App</span>
            </div>
            <ChevronRightIcon class="h-5 w-5 text-gray-400" />
          </button>

          <button
            @click="handleLogout"
            class="w-full flex items-center justify-between p-4 hover:bg-red-50 transition-colors"
          >
            <div class="flex items-center gap-3">
              <ArrowRightOnRectangleIcon class="h-5 w-5 text-red-600" />
              <span class="text-sm font-medium text-red-600">Logout</span>
            </div>
          </button>
        </div>
      </div>
    </div>

    <!-- Bottom Navigation - Fixed at bottom -->
    <BottomNavigation 
      v-if="true"
      :active-tab="activeTab" 
      @navigate="handleTabChange" 
    />

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
      :shopCredit="user?.starter_kit_shop_credit"
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
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
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
import UpdateNotification from '@/components/Mobile/UpdateNotification.vue';
import InstallPrompt from '@/components/Mobile/InstallPrompt.vue';
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
});

const loading = ref(false);
const activeTab = ref('home');
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

// PWA Install
const deferredPrompt = ref<any>(null);
const showInstallButton = ref(false);
const showLoanApplicationModal = ref(false);
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

const handleTabChange = (tab: string) => {
  activeTab.value = tab;
  // Smooth scroll to top when changing tabs
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const formatCurrency = (value: number | undefined | null) => {
  if (value === undefined || value === null || isNaN(value)) {
    return '0.00';
  }
  return Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

// Loan eligibility computed values
const loanLimit = computed(() => props.user?.loan_limit || 0);
const loanBalance = computed(() => props.loanSummary?.loan_balance || 0);
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
  router.post(route('logout'), {}, {
    onStart: () => {
      showLogoutModal.value = false;
    },
    onSuccess: () => {
      // Logout successful, will redirect to login
    },
    onError: (errors) => {
      console.error('Logout failed:', errors);
      showToastMessage('Logout failed. Please try again.', 'error');
    },
  });
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
