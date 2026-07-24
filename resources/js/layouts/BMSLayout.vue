<template>
  <div>
    <Head>
      <!-- CMS-specific meta tags removed - using main site PWA only -->
    </Head>

    <div class="flex h-screen overflow-hidden bg-gray-50">
    <!-- Desktop Sidebar -->
    <aside 
      :class="[
        'hidden md:flex md:flex-col transition-all duration-300 ease-in-out z-50 bg-white border-r border-gray-100 shadow-lg',
        sidebarCollapsed ? 'w-20' : 'w-64'
      ]"
    >
      <!-- Logo/Company - Fixed Header with Switcher -->
      <div class="flex-shrink-0 border-b border-gray-100 sticky top-0 bg-gradient-to-b from-white to-gray-50/50 z-10 backdrop-blur-sm">
        <div class="flex items-center gap-3 px-4 py-4 h-16">
          <!-- Company Logo or Icon -->
          <div v-if="company?.logo_url" class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden bg-white border border-gray-200">
            <img 
              :src="company.logo_url" 
              :alt="company.name"
              class="w-full h-full object-contain p-1"
            />
          </div>
          <div v-else class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-blue-500/30 ring-2 ring-blue-100">
            <span class="text-white font-bold text-sm">{{ company?.name?.charAt(0) || 'C' }}</span>
          </div>
          
          <div v-if="!sidebarCollapsed" class="flex-1 min-w-0">
            <p class="text-sm font-bold text-gray-900 truncate tracking-tight leading-tight">{{ company?.name || 'Company Name' }}</p>
            <p class="text-xs text-gray-500 truncate font-medium">{{ cmsUser?.role?.name || 'Role' }}</p>
          </div>
          <button
            v-if="!sidebarCollapsed && userCompanies && userCompanies.length > 1"
            @click="showCompanySwitcher = !showCompanySwitcher"
            class="flex-shrink-0 p-1 rounded-lg hover:bg-gray-100 transition"
            aria-label="Switch company"
          >
            <ChevronUpDownIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
          </button>
        </div>

        <!-- Company Switcher Dropdown -->
        <div v-if="showCompanySwitcher && userCompanies && userCompanies.length > 1"
          class="border-t border-gray-100 bg-white shadow-lg"
        >
          <p class="px-4 pt-2 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Switch Company</p>
          <div class="pb-2">
            <button
              v-for="uc in userCompanies"
              :key="uc.company_id"
              @click="switchCompany(uc.company_id)"
              class="w-full flex items-center gap-3 px-4 py-2.5 text-left hover:bg-blue-50 transition"
              :class="uc.is_active ? 'bg-blue-50' : ''"
            >
              <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                :class="uc.is_active ? 'bg-blue-600' : 'bg-gray-200'">
                <BuildingOfficeIcon class="h-4 w-4" :class="uc.is_active ? 'text-white' : 'text-gray-500'" aria-hidden="true" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ uc.company_name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ uc.role }}</p>
              </div>
              <CheckIcon v-if="uc.is_active" class="h-4 w-4 text-blue-600 flex-shrink-0" aria-hidden="true" />
            </button>
          </div>
        </div>
      </div>

      <!-- Quick Search -->
      <div v-if="!sidebarCollapsed" class="flex-shrink-0 px-3 pt-3 pb-2 border-b border-gray-100 bg-white sticky top-16 z-10">
        <div class="relative">
          <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" aria-hidden="true" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Quick find..."
            class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
            @input="handleSearch"
          />
          <button
            v-if="searchQuery"
            @click="clearSearch"
            class="absolute right-2 top-1/2 -translate-y-1/2 p-1 hover:bg-gray-100 rounded transition"
            aria-label="Clear search"
          >
            <XMarkIcon class="h-3 w-3 text-gray-400" aria-hidden="true" />
          </button>
        </div>
        <p v-if="searchQuery && filteredNavItems.length === 0" class="text-xs text-gray-500 mt-2 px-2">
          No results found
        </p>
      </div>

      <!-- Navigation - Scrollable -->
      <div class="flex flex-col flex-grow overflow-y-auto custom-scrollbar bg-gradient-to-b from-gray-50/50 to-white">
        <!-- No results message -->
        <div v-if="searchQuery && filteredNavItems.length === 0" class="px-6 py-12 text-center">
          <MagnifyingGlassIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
          <p class="text-sm font-medium text-gray-500">No pages found</p>
          <p class="text-xs text-gray-400 mt-1">Try a different search term</p>
        </div>

        <!-- Regular Navigation (filtered when searching) -->
        <nav v-else class="flex-1 px-3 py-4 space-y-1">
          <NavItem
            v-if="shouldShowNavItem('bms.dashboard')"
            icon="HomeIcon"
            label="Dashboard"
            route-name="bms.dashboard"
            :collapsed="sidebarCollapsed"
            :active="isActive('bms.dashboard')"
            @click="navigateTo('bms.dashboard')"
          />

          <!-- Core Business Section (Collapsible) -->
          <div v-if="isSectionVisible(['bms.jobs', 'bms.measurements', 'bms.customers', 'bms.invoices', 'bms.payments', 'bms.reports', 'bms.budgets'])">
            <button
              v-if="!sidebarCollapsed && !searchQuery"
              @click="toggleSection('business')"
              class="w-full px-3 mb-1 group"
            >
              <div class="flex items-center justify-between px-2 py-1.5 bg-gradient-to-r from-blue-50 to-transparent rounded-lg hover:from-blue-100 transition-colors">
                <div class="flex items-center gap-2">
                  <div class="w-1 h-4 bg-gradient-to-b from-blue-500 to-blue-600 rounded-full"></div>
                  <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Core Business</p>
                </div>
                <ChevronDownIcon 
                  :class="['h-4 w-4 text-gray-500 transition-transform duration-200', collapsedSections.business ? '-rotate-90' : '']"
                  aria-hidden="true"
                />
              </div>
            </button>

            <div v-show="sidebarCollapsed || !collapsedSections.business || searchQuery" class="space-y-1">
              <NavItem
                v-if="shouldShowNavItem('bms.jobs')"
                icon="BriefcaseIcon"
                label="Jobs"
                route-name="bms.jobs"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.jobs')"
                @click="navigateTo('bms.jobs.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.measurements') && hasFabrication"
                icon="ScissorsIcon"
                label="Measurements"
                route-name="bms.measurements"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.measurements')"
                @click="navigateTo('bms.measurements.index')"
              />
              
              <NavItem
                v-if="shouldShowNavItem('bms.customers')"
                icon="UsersIcon"
                label="Customers"
                route-name="bms.customers"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.customers')"
                @click="navigateTo('bms.customers.index')"
              />
              
              <NavItem
                v-if="shouldShowNavItem('bms.invoices')"
                icon="DocumentTextIcon"
                label="Invoices"
                route-name="bms.invoices"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.invoices')"
                @click="navigateTo('bms.invoices.index')"
              />
              
              <NavItem
                v-if="shouldShowNavItem('bms.payments')"
                icon="CreditCardIcon"
                label="Payments"
                route-name="bms.payments"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.payments')"
                @click="navigateTo('bms.payments.index')"
              />
              
              <NavItem
                v-if="shouldShowNavItem('bms.reports')"
                icon="ChartBarIcon"
                label="Reports"
                route-name="bms.reports"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.reports')"
                @click="navigateTo('bms.reports.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.budgets')"
                icon="CalculatorIcon"
                label="Budgets"
                route-name="bms.budgets"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.budgets')"
                @click="navigateTo('bms.budgets.index')"
              />
            </div>
          </div>

          <!-- Analytics Section (Collapsible) -->
          <div v-if="isSectionVisible(['bms.analytics.overview', 'bms.analytics.operations', 'bms.analytics.finance', 'bms.analytics.procurement'])">
            <button
              v-if="!sidebarCollapsed && !searchQuery"
              @click="toggleSection('analytics')"
              class="w-full px-3 mb-1 group"
            >
              <div class="flex items-center justify-between px-2 py-1.5 bg-gradient-to-r from-blue-50 to-transparent rounded-lg hover:from-blue-100 transition-colors">
                <div class="flex items-center gap-2">
                  <div class="w-1 h-4 bg-gradient-to-b from-blue-500 to-blue-600 rounded-full"></div>
                  <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Analytics</p>
                </div>
                <ChevronDownIcon 
                  :class="['h-4 w-4 text-gray-500 transition-transform duration-200', collapsedSections.analytics ? '-rotate-90' : '']"
                  aria-hidden="true"
                />
              </div>
            </button>
            
            <div v-show="sidebarCollapsed || !collapsedSections.analytics || searchQuery" class="space-y-1">
              <NavItem
                v-if="shouldShowNavItem('bms.analytics.overview')"
                icon="ChartBarIcon"
                label="CEO Overview"
                route-name="bms.analytics.overview"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.analytics.overview')"
                @click="navigateTo('bms.analytics.overview')"
              />
              <NavItem
                v-if="shouldShowNavItem('bms.analytics.operations')"
                icon="PresentationChartLineIcon"
                label="Operations"
                route-name="bms.analytics.operations"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.analytics.operations')"
                @click="navigateTo('bms.analytics.operations')"
              />
              
              <NavItem
                v-if="shouldShowNavItem('bms.analytics.finance')"
                icon="CurrencyDollarIcon"
                label="Finance"
                route-name="bms.analytics.finance"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.analytics.finance')"
                @click="navigateTo('bms.analytics.finance')"
              />
              <NavItem
                v-if="shouldShowNavItem('bms.analytics.procurement')"
                icon="TruckIcon"
                label="Procurement"
                route-name="bms.analytics.procurement"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.analytics.procurement')"
                @click="navigateTo('bms.analytics.procurement')"
              />
            </div>
          </div>

          <!-- Planning Section (Collapsible) -->
          <div v-if="isSectionVisible(['bms.plans', 'bms.plans.command-center', 'bms.kpis', 'bms.business-plans'])">
            <button
              v-if="!sidebarCollapsed && !searchQuery"
              @click="toggleSection('planning')"
              class="w-full px-3 mb-1 group"
            >
              <div class="flex items-center justify-between px-2 py-1.5 bg-gradient-to-r from-violet-50 to-transparent rounded-lg hover:from-violet-100 transition-colors">
                <div class="flex items-center gap-2">
                  <div class="w-1 h-4 bg-gradient-to-b from-violet-500 to-violet-600 rounded-full"></div>
                  <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Planning</p>
                </div>
                <ChevronDownIcon 
                  :class="['h-4 w-4 text-gray-500 transition-transform duration-200', collapsedSections.planning ? '-rotate-90' : '']"
                  aria-hidden="true"
                />
              </div>
            </button>

            <div v-show="sidebarCollapsed || !collapsedSections.planning || searchQuery" class="space-y-1">
              <NavItem
                v-if="shouldShowNavItem('bms.kpis')"
                icon="ChartBarIcon"
                label="KPIs"
                route-name="bms.kpis"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.kpis')"
                @click="navigateTo('bms.kpis.index')"
              />
              <NavItem
                v-if="shouldShowNavItem('bms.plans.command-center')"
                icon="PresentationChartLineIcon"
                label="Command Center"
                route-name="bms.plans.command-center"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.plans.command-center')"
                @click="navigateTo('bms.plans.command-center')"
              />
              <NavItem
                v-if="shouldShowNavItem('bms.plans')"
                icon="ClipboardDocumentListIcon"
                label="All Plans"
                route-name="bms.plans"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.plans')"
                @click="navigateTo('bms.plans.index')"
              />
              <NavItem
                v-if="shouldShowNavItem('bms.business-plans')"
                icon="DocumentTextIcon"
                label="Business Plans"
                route-name="bms.business-plans"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.business-plans')"
                @click="navigateTo('bms.business-plans.index')"
              />
            </div>
          </div>

          <!-- Operations Module Section (Unified Task Management) -->
          <div v-if="company?.has_operations_module && isSectionVisible(['bms.operations.dashboard', 'bms.operations.my-tasks', 'bms.operations.tasks', 'bms.operations.workflows', 'bms.operations.kanban', 'bms.operations.planning'])">
            <button
              v-if="!sidebarCollapsed && !searchQuery"
              @click="toggleSection('operationsModule')"
              class="w-full px-3 mb-1 group"
            >
              <div class="flex items-center justify-between px-2 py-1.5 bg-gradient-to-r from-indigo-50 to-transparent rounded-lg hover:from-indigo-100 transition-colors">
                <div class="flex items-center gap-2">
                  <div class="w-1 h-4 bg-gradient-to-b from-indigo-500 to-indigo-600 rounded-full"></div>
                  <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Operations</p>
                </div>
                <ChevronDownIcon 
                  :class="['h-4 w-4 text-gray-500 transition-transform duration-200', collapsedSections.operationsModule ? '-rotate-90' : '']"
                  aria-hidden="true"
                />
              </div>
            </button>
            
            <div v-show="sidebarCollapsed || !collapsedSections.operationsModule || searchQuery" class="space-y-1">
              <NavItem
                v-if="shouldShowNavItem('bms.operations.dashboard')"
                icon="ChartBarIcon"
                label="Dashboard"
                route-name="bms.operations.dashboard"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.operations.dashboard')"
                @click="navigateTo('bms.operations.dashboard')"
              />
              
              <NavItem
                v-if="shouldShowNavItem('bms.operations.my-tasks')"
                icon="ClipboardDocumentCheckIcon"
                label="My Tasks"
                route-name="bms.operations.my-tasks"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.operations.my-tasks')"
                @click="navigateTo('bms.operations.my-tasks')"
              />
              
              <NavItem
                v-if="shouldShowNavItem('bms.operations.tasks')"
                icon="DocumentTextIcon"
                label="All Tasks"
                route-name="bms.operations.tasks"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.operations.tasks')"
                @click="navigateTo('bms.operations.tasks.index')"
              />
              
              <NavItem
                v-if="shouldShowNavItem('bms.operations.workflows')"
                icon="ArrowPathIcon"
                label="Workflows"
                route-name="bms.operations.workflows"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.operations.workflows')"
                @click="navigateTo('bms.operations.workflows.index')"
              />
              
              <NavItem
                v-if="shouldShowNavItem('bms.operations.kanban')"
                icon="ViewColumnsIcon"
                label="Kanban Board"
                route-name="bms.operations.kanban"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.operations.kanban')"
                @click="navigateTo('bms.operations.kanban')"
              />
              
              <NavItem
                v-if="shouldShowNavItem('bms.operations.planning')"
                icon="CalendarDaysIcon"
                label="Planning"
                route-name="bms.operations.planning"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.operations.planning')"
                @click="navigateTo('bms.operations.planning')"
              />
              
              <NavItem
                v-if="shouldShowNavItem('bms.operations.templates')"
                icon="DocumentDuplicateIcon"
                label="Templates"
                route-name="bms.operations.templates"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.operations.templates')"
                @click="navigateTo('bms.operations.templates.index')"
              />
              
              <NavItem
                v-if="shouldShowNavItem('bms.operations.recurring-tasks')"
                icon="ArrowPathRoundedSquareIcon"
                label="Recurring Tasks"
                route-name="bms.operations.recurring-tasks"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.operations.recurring-tasks')"
                @click="navigateTo('bms.operations.recurring-tasks.index')"
              />
              
              <!-- Advanced Features -->
              <NavItem
                v-if="shouldShowNavItem('bms.operations.workload-balance')"
                icon="ScaleIcon"
                label="Workload Balance"
                route-name="bms.operations.workload-balance"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.operations.workload-balance')"
                @click="navigateTo('bms.operations.workload-balance')"
              />
              
              <NavItem
                v-if="shouldShowNavItem('bms.operations.capacity-forecast')"
                icon="CalendarDaysIcon"
                label="Capacity Forecast"
                route-name="bms.operations.capacity-forecast"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.operations.capacity-forecast')"
                @click="navigateTo('bms.operations.capacity-forecast')"
              />
              
              <NavItem
                v-if="shouldShowNavItem('bms.operations.scenarios')"
                icon="BeakerIcon"
                label="Scenarios"
                route-name="bms.operations.scenarios.index"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.operations.scenarios')"
                @click="navigateTo('bms.operations.scenarios.index')"
              />
              
              <NavItem
                v-if="shouldShowNavItem('bms.operations.analytics')"
                icon="ChartPieIcon"
                label="Analytics"
                route-name="bms.operations.analytics"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.operations.analytics')"
                @click="navigateTo('bms.operations.analytics')"
              />
              
              <NavItem
                v-if="shouldShowNavItem('bms.operations.gantt')"
                icon="Bars3BottomLeftIcon"
                label="Gantt Chart"
                route-name="bms.operations.gantt"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.operations.gantt')"
                @click="navigateTo('bms.operations.gantt')"
              />
            </div>
          </div>

          <!-- Financial Management Section (Collapsible) -->
          <div v-if="isSectionVisible(['bms.expenses', 'bms.quotations', 'bms.inventory', 'bms.materials', 'bms.assets', 'bms.assets.depreciation-register', 'bms.contracts', 'bms.purchase-orders', 'bms.vendors', 'bms.payroll', 'bms.payroll.workers'])">
            <button
              v-if="!sidebarCollapsed && !searchQuery"
              @click="toggleSection('financial')"
              class="w-full px-3 mb-1 group"
            >
              <div class="flex items-center justify-between px-2 py-1.5 bg-gradient-to-r from-green-50 to-transparent rounded-lg hover:from-green-100 transition-colors">
                <div class="flex items-center gap-2">
                  <div class="w-1 h-4 bg-gradient-to-b from-green-500 to-green-600 rounded-full"></div>
                  <p class="text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">Financial</p>
                </div>
                <ChevronDownIcon 
                  :class="['h-4 w-4 text-gray-500 transition-transform duration-200', collapsedSections.financial ? '-rotate-90' : '']"
                  aria-hidden="true"
                />
              </div>
            </button>

            <div v-show="sidebarCollapsed || !collapsedSections.financial || searchQuery" class="space-y-1">
              <NavItem
                v-if="shouldShowNavItem('bms.expenses')"
                icon="BanknotesIcon"
                label="Expenses"
                route-name="bms.expenses"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.expenses')"
                @click="navigateTo('bms.expenses.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.quotations')"
                icon="DocumentDuplicateIcon"
                label="Quotations"
                route-name="bms.quotations"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.quotations')"
                @click="navigateTo('bms.quotations.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.inventory')"
                icon="CubeIcon"
                label="Inventory"
                route-name="bms.inventory"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.inventory')"
                @click="navigateTo('bms.inventory.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.materials') && (hasFabrication || hasConstruction)"
                icon="CubeIcon"
                label="Materials"
                route-name="bms.materials"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.materials')"
                @click="navigateTo('bms.materials.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.assets')"
                icon="ComputerDesktopIcon"
                label="Assets"
                route-name="bms.assets"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.assets')"
                @click="navigateTo('bms.assets.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.assets.depreciation-register')"
                icon="ChartBarIcon"
                label="Depreciation Register"
                route-name="bms.assets.depreciation-register"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.assets.depreciation-register')"
                @click="navigateTo('bms.assets.depreciation-register')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.contracts')"
                icon="DocumentTextIcon"
                label="Contracts"
                route-name="bms.contracts"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.contracts')"
                @click="navigateTo('bms.contracts.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.purchase-orders')"
                icon="ClipboardDocumentListIcon"
                label="Purchase Orders"
                route-name="bms.purchase-orders"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.purchase-orders')"
                @click="navigateTo('bms.purchase-orders.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.vendors')"
                icon="BuildingStorefrontIcon"
                label="Vendors"
                route-name="bms.vendors"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.vendors')"
                @click="navigateTo('bms.vendors.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.payroll')"
                icon="UserGroupIcon"
                label="Payroll"
                route-name="bms.payroll"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.payroll')"
                @click="navigateTo('bms.payroll.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.payroll.workers')"
                icon="UsersIcon"
                label="Workers"
                route-name="bms.payroll.workers"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.payroll.workers')"
                @click="navigateTo('bms.payroll.workers.index')"
              />
            </div>
          </div>

          <!-- Inventory Management Section (Collapsible) -->
          <div v-if="isSectionVisible(['bms.inventory.locations', 'bms.inventory.stock-levels', 'bms.inventory.transfers', 'bms.inventory.adjustments', 'bms.inventory.counts', 'bms.inventory.valuation'])">
            <button
              v-if="!sidebarCollapsed && !searchQuery"
              @click="toggleSection('inventoryMgmt')"
              class="w-full px-3 pt-2 mb-1 group"
            >
              <div class="flex items-center justify-between px-2 py-1.5 bg-gradient-to-r from-cyan-50 to-transparent rounded-lg hover:from-cyan-100 transition-colors">
                <div class="flex items-center gap-2">
                  <div class="w-1 h-4 bg-gradient-to-b from-cyan-500 to-cyan-600 rounded-full"></div>
                  <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Inventory</p>
                </div>
                <ChevronDownIcon 
                  :class="['h-4 w-4 text-gray-500 transition-transform duration-200', collapsedSections.inventoryMgmt ? '-rotate-90' : '']"
                  aria-hidden="true"
                />
              </div>
            </button>

            <div v-show="sidebarCollapsed || !collapsedSections.inventoryMgmt || searchQuery" class="space-y-1">
              <NavItem
                v-if="shouldShowNavItem('bms.inventory')"
                icon="CubeIcon"
                label="All Items"
                route-name="bms.inventory"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.inventory')"
                @click="navigateTo('bms.inventory.index')"
              />
              <NavItem
                v-if="shouldShowNavItem('bms.inventory.locations')"
                icon="BuildingOffice2Icon"
                label="Locations"
                route-name="bms.inventory.locations"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.inventory.locations')"
                @click="navigateTo('bms.inventory.locations.index')"
              />
              <NavItem
                v-if="shouldShowNavItem('bms.inventory.stock-levels')"
                icon="ChartBarIcon"
                label="Stock Levels"
                route-name="bms.inventory.stock-levels"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.inventory.stock-levels')"
                @click="navigateTo('bms.inventory.stock-levels.index')"
              />
              <NavItem
                v-if="shouldShowNavItem('bms.inventory.transfers')"
                icon="ArrowPathIcon"
                label="Transfers"
                route-name="bms.inventory.transfers"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.inventory.transfers')"
                @click="navigateTo('bms.inventory.transfers.index')"
              />
              <NavItem
                v-if="shouldShowNavItem('bms.inventory.adjustments')"
                icon="CalculatorIcon"
                label="Adjustments"
                route-name="bms.inventory.adjustments"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.inventory.adjustments')"
                @click="navigateTo('bms.inventory.adjustments.index')"
              />
              <NavItem
                v-if="shouldShowNavItem('bms.inventory.counts')"
                icon="ClipboardDocumentCheckIcon"
                label="Stock Counts"
                route-name="bms.inventory.counts"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.inventory.counts')"
                @click="navigateTo('bms.inventory.counts.index')"
              />
              <NavItem
                v-if="shouldShowNavItem('bms.inventory.valuation')"
                icon="CurrencyDollarIcon"
                label="Valuation"
                route-name="bms.inventory.valuation"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.inventory.valuation')"
                @click="navigateTo('bms.inventory.valuation.index')"
              />
            </div>
          </div>

          <!-- Construction Section (Collapsible) -->
          <div v-if="hasConstruction && isSectionVisible(['bms.projects', 'bms.subcontractors', 'bms.equipment', 'bms.labour.crews', 'bms.labour.timesheets', 'bms.boq', 'bms.progress-billing'])">
            <button
              v-if="!sidebarCollapsed && !searchQuery"
              @click="toggleSection('construction')"
              class="w-full px-3 pt-2 mb-1 group"
            >
              <div class="flex items-center justify-between px-2 py-1.5 bg-gradient-to-r from-orange-50 to-transparent rounded-lg hover:from-orange-100 transition-colors">
                <div class="flex items-center gap-2">
                  <div class="w-1 h-4 bg-gradient-to-b from-orange-500 to-orange-600 rounded-full"></div>
                  <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Construction</p>
                </div>
                <ChevronDownIcon 
                  :class="['h-4 w-4 text-gray-500 transition-transform duration-200', collapsedSections.construction ? '-rotate-90' : '']"
                  aria-hidden="true"
                />
              </div>
            </button>

            <div v-show="sidebarCollapsed || !collapsedSections.construction || searchQuery" class="space-y-1">
              <NavItem
                v-if="shouldShowNavItem('bms.projects') && hasConstruction"
                icon="BuildingOffice2Icon"
                label="Projects"
                route-name="bms.projects"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.projects')"
                @click="navigateTo('bms.projects.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.subcontractors') && hasConstruction"
                icon="UserGroupIcon"
                label="Subcontractors"
                route-name="bms.subcontractors"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.subcontractors')"
                @click="navigateTo('bms.subcontractors.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.equipment') && hasConstruction"
                icon="WrenchScrewdriverIcon"
                label="Equipment"
                route-name="bms.equipment"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.equipment')"
                @click="navigateTo('bms.equipment.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.labour.crews') && hasConstruction"
                icon="UserGroupIcon"
                label="Labour Crews"
                route-name="bms.labour.crews"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.labour.crews')"
                @click="navigateTo('bms.labour.crews.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.labour.timesheets') && hasConstruction"
                icon="ClockIcon"
                label="Timesheets"
                route-name="bms.labour.timesheets"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.labour.timesheets')"
                @click="navigateTo('bms.labour.timesheets.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.boq') && hasConstruction"
                icon="DocumentTextIcon"
                label="BOQ"
                route-name="bms.boq"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.boq')"
                @click="navigateTo('bms.boq.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.progress-billing') && hasConstruction"
                icon="DocumentCheckIcon"
                label="Progress Billing"
                route-name="bms.progress-billing"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.progress-billing')"
                @click="navigateTo('bms.progress-billing.certificates.index')"
              />
            </div>
          </div>

          <!-- Field Operations Section (Collapsible) -->
          <div v-if="(hasFabrication || hasConstruction) && isSectionVisible(['bms.production', 'bms.installation', 'bms.inventory', 'bms.fleet', 'bms.documents', 'bms.safety', 'bms.quality'])">
            <button
              v-if="!sidebarCollapsed && !searchQuery"
              @click="toggleSection('fieldOperations')"
              class="w-full px-3 pt-2 mb-1 group"
            >
              <div class="flex items-center justify-between px-2 py-1.5 bg-gradient-to-r from-blue-50 to-transparent rounded-lg hover:from-blue-100 transition-colors">
                <div class="flex items-center gap-2">
                  <div class="w-1 h-4 bg-gradient-to-b from-blue-500 to-blue-600 rounded-full"></div>
                  <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Field Operations</p>
                </div>
                <ChevronDownIcon 
                  :class="['h-4 w-4 text-gray-500 transition-transform duration-200', collapsedSections.fieldOperations ? '-rotate-90' : '']"
                  aria-hidden="true"
                />
              </div>
            </button>

            <div v-show="sidebarCollapsed || !collapsedSections.fieldOperations || searchQuery" class="space-y-1">
              <NavItem
                v-if="shouldShowNavItem('bms.production') && hasFabrication"
                icon="CogIcon"
                label="Production"
                route-name="bms.production"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.production')"
                @click="navigateTo('bms.production.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.installation') && (hasFabrication || hasConstruction)"
                icon="WrenchScrewdriverIcon"
                label="Installation"
                route-name="bms.installation"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.installation')"
                @click="navigateTo('bms.installation.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.inventory') && (hasFabrication || hasConstruction)"
                icon="CubeIcon"
                label="Stock Management"
                route-name="bms.inventory"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.inventory')"
                @click="navigateTo('bms.inventory.stock-levels.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.fleet')"
                icon="TruckIcon"
                label="Fleet"
                route-name="bms.fleet"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.fleet')"
                @click="navigateTo('bms.fleet.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.documents')"
                icon="FolderIcon"
                label="Documents"
                route-name="bms.documents"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.documents')"
                @click="navigateTo('bms.documents.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.safety') && hasConstruction"
                icon="ShieldCheckIcon"
                label="Safety"
                route-name="bms.safety"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.safety')"
                @click="navigateTo('bms.safety.incidents.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.quality') && (hasFabrication || hasConstruction)"
                icon="CheckBadgeIcon"
                label="Quality"
                route-name="bms.quality"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.quality')"
                @click="navigateTo('bms.quality.inspections.index')"
              />
            </div>
          </div>

          <!-- Payroll Configuration Section (Collapsible) -->
          <div v-if="isSectionVisible(['bms.payroll.configuration.allowance-types', 'bms.payroll.configuration.deduction-types'])">
            <button
              v-if="!sidebarCollapsed && !searchQuery"
              @click="toggleSection('payroll')"
              class="w-full px-3 pt-2 mb-1 group"
            >
              <div class="flex items-center justify-between px-2 py-1.5 bg-gradient-to-r from-emerald-50 to-transparent rounded-lg hover:from-emerald-100 transition-colors">
                <div class="flex items-center gap-2">
                  <div class="w-1 h-4 bg-gradient-to-b from-emerald-500 to-emerald-600 rounded-full"></div>
                  <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Payroll Config</p>
                </div>
                <ChevronDownIcon 
                  :class="['h-4 w-4 text-gray-500 transition-transform duration-200', collapsedSections.payroll ? '-rotate-90' : '']"
                  aria-hidden="true"
                />
              </div>
            </button>

            <div v-show="sidebarCollapsed || !collapsedSections.payroll || searchQuery" class="space-y-1">
              <NavItem
                v-if="shouldShowNavItem('bms.payroll.configuration.allowance-types')"
                icon="BanknotesIcon"
                label="Allowance Types"
                route-name="bms.payroll.configuration.allowance-types"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.payroll.configuration.allowance-types')"
                @click="navigateTo('bms.payroll.configuration.allowance-types.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.payroll.configuration.deduction-types')"
                icon="MinusCircleIcon"
                label="Deduction Types"
                route-name="bms.payroll.configuration.deduction-types"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.payroll.configuration.deduction-types')"
                @click="navigateTo('bms.payroll.configuration.deduction-types.index')"
              />
            </div>
          </div>

          <!-- HR Management Section (Collapsible) -->
          <div v-if="isSectionVisible(['bms.branches', 'bms.departments', 'bms.org-chart', 'bms.leave', 'bms.shifts', 'bms.attendance', 'bms.overtime', 'bms.recruitment', 'bms.hrms-onboarding', 'bms.performance', 'bms.training', 'bms.hr-reports'])">
            <button
              v-if="!sidebarCollapsed && !searchQuery"
              @click="toggleSection('hr')"
              class="w-full px-3 pt-4 mb-1 group"
            >
              <div class="flex items-center justify-between px-2 py-1.5 bg-gradient-to-r from-purple-50 to-transparent rounded-lg hover:from-purple-100 transition-colors">
                <div class="flex items-center gap-2">
                  <div class="w-1 h-4 bg-gradient-to-b from-purple-500 to-purple-600 rounded-full"></div>
                  <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">HR Management</p>
                </div>
                <ChevronDownIcon 
                  :class="['h-4 w-4 text-gray-500 transition-transform duration-200', collapsedSections.hr ? '-rotate-90' : '']"
                  aria-hidden="true"
                />
              </div>
            </button>

            <div v-show="sidebarCollapsed || !collapsedSections.hr || searchQuery" class="space-y-1">
              <NavItem
                v-if="shouldShowNavItem('bms.branches')"
                icon="BuildingOffice2Icon"
                label="Branches"
                route-name="bms.branches"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.branches')"
                @click="navigateTo('bms.branches.index')"
              />
              <NavItem
                v-if="shouldShowNavItem('bms.departments')"
                icon="BuildingOffice2Icon"
                label="Departments"
                route-name="bms.departments"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.departments')"
                @click="navigateTo('bms.departments.index')"
              />
              <NavItem
                v-if="shouldShowNavItem('bms.org-chart')"
                icon="UserGroupIcon"
                label="Org Chart"
                route-name="bms.org-chart"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.org-chart')"
                @click="navigateTo('bms.org-chart.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.leave')"
                icon="CalendarDaysIcon"
                label="Leave Management"
                route-name="bms.leave"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.leave')"
                @click="navigateTo('bms.leave.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.shifts')"
                icon="ClockIcon"
                label="Shifts"
                route-name="bms.shifts"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.shifts')"
                @click="navigateTo('bms.shifts.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.attendance')"
                icon="ClipboardDocumentCheckIcon"
                label="Attendance"
                route-name="bms.attendance"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.attendance')"
                @click="navigateTo('bms.attendance.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.overtime')"
                icon="ClockIcon"
                label="Overtime"
                route-name="bms.overtime"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.overtime')"
                @click="navigateTo('bms.overtime.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.recruitment')"
                icon="BriefcaseIcon"
                label="Recruitment"
                route-name="bms.recruitment"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.recruitment')"
                @click="navigateTo('bms.recruitment.job-postings.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.hrms-onboarding')"
                icon="AcademicCapIcon"
                label="Onboarding"
                route-name="bms.hrms-onboarding"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.hrms-onboarding')"
                @click="navigateTo('bms.hrms-onboarding.templates.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.performance')"
                icon="ChartBarIcon"
                label="Performance"
                route-name="bms.performance"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.performance')"
                @click="navigateTo('bms.performance.goals.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.training')"
                icon="AcademicCapIcon"
                label="Training"
                route-name="bms.training"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.training')"
                @click="navigateTo('bms.training.programs')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.hr-reports')"
                icon="DocumentChartBarIcon"
                label="HR Reports"
                route-name="bms.hr-reports"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.hr-reports')"
                @click="navigateTo('bms.hr-reports.index')"
              />
            </div>
          </div>

          <!-- Administration Section (Collapsible) -->
          <div v-if="isSectionVisible(['bms.time-tracking', 'bms.recurring-invoices', 'bms.approvals', 'bms.accounting'])">
            <button
              v-if="!sidebarCollapsed && !searchQuery"
              @click="toggleSection('administration')"
              class="w-full px-3 mb-1 group"
            >
              <div class="flex items-center justify-between px-2 py-1.5 bg-gradient-to-r from-indigo-50 to-transparent rounded-lg hover:from-indigo-100 transition-colors">
                <div class="flex items-center gap-2">
                  <div class="w-1 h-4 bg-gradient-to-b from-indigo-500 to-indigo-600 rounded-full"></div>
                  <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Administration</p>
                </div>
                <ChevronDownIcon 
                  :class="['h-4 w-4 text-gray-500 transition-transform duration-200', collapsedSections.administration ? '-rotate-90' : '']"
                  aria-hidden="true"
                />
              </div>
            </button>

            <div v-show="sidebarCollapsed || !collapsedSections.administration || searchQuery" class="space-y-1">
              <NavItem
                v-if="shouldShowNavItem('bms.time-tracking')"
                icon="ClockIcon"
                label="Time Tracking"
                route-name="bms.time-tracking"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.time-tracking')"
                @click="navigateTo('bms.time-tracking.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.recurring-invoices')"
                icon="ArrowPathIcon"
                label="Recurring Invoices"
                route-name="bms.recurring-invoices"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.recurring-invoices')"
                @click="navigateTo('bms.recurring-invoices.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.approvals')"
                icon="CheckCircleIcon"
                label="Approvals"
                route-name="bms.approvals"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.approvals')"
                @click="navigateTo('bms.approvals.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.accounting')"
                icon="CalculatorIcon"
                label="Chart of Accounts"
                route-name="bms.accounting"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.accounting')"
                @click="navigateTo('bms.accounting.index')"
              />
            </div>
          </div>

          <!-- Settings Section (Collapsible) -->
          <div v-if="isSectionVisible(['bms.settings.index', 'bms.settings.document-templates', 'bms.settings.pricing-rules', 'bms.settings.industry-presets.index', 'bms.settings.email.index', 'bms.settings.sms.index', 'bms.settings.currency.index', 'bms.security.settings', 'bms.portal-users.index'])">
            <button
              v-if="!sidebarCollapsed && !searchQuery"
              @click="toggleSection('settings')"
              class="w-full px-3 mb-1 group"
            >
              <div class="flex items-center justify-between px-2 py-1.5 bg-gradient-to-r from-gray-100 to-transparent rounded-lg hover:from-gray-200 transition-colors">
                <div class="flex items-center gap-2">
                  <div class="w-1 h-4 bg-gradient-to-b from-gray-500 to-gray-600 rounded-full"></div>
                  <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Settings</p>
                </div>
                <ChevronDownIcon 
                  :class="['h-4 w-4 text-gray-500 transition-transform duration-200', collapsedSections.settings ? '-rotate-90' : '']"
                  aria-hidden="true"
                />
              </div>
            </button>

            <div v-show="sidebarCollapsed || !collapsedSections.settings || searchQuery" class="space-y-1">
              <NavItem
                v-if="shouldShowNavItem('bms.settings.index')"
                icon="Cog6ToothIcon"
                label="Company Settings"
                route-name="bms.settings"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.settings.index')"
                @click="navigateTo('bms.settings.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.settings.document-templates') && company?.has_bizdocs_module"
                icon="DocumentTextIcon"
                label="Document Templates"
                route-name="bms.settings.document-templates"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.settings.document-templates')"
                @click="navigateTo('bms.settings.document-templates.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.settings.pricing-rules') && hasFabrication"
                icon="CalculatorIcon"
                label="Pricing Rules"
                route-name="bms.settings.pricing-rules"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.settings.pricing-rules')"
                @click="navigateTo('bms.settings.pricing-rules')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.settings.industry-presets.index')"
                icon="SwatchIcon"
                label="Industry Presets"
                route-name="bms.settings.industry-presets"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.settings.industry-presets')"
                @click="navigateTo('bms.settings.industry-presets.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.settings.email.index')"
                icon="EnvelopeIcon"
                label="Email Settings"
                route-name="bms.settings.email"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.settings.email')"
                @click="navigateTo('bms.settings.email.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.settings.sms.index')"
                icon="DevicePhoneMobileIcon"
                label="SMS Settings"
                route-name="bms.settings.sms"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.settings.sms')"
                @click="navigateTo('bms.settings.sms.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.settings.currency.index')"
                icon="BanknotesIcon"
                label="Currency"
                route-name="bms.settings.currency"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.settings.currency')"
                @click="navigateTo('bms.settings.currency.index')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.security.settings')"
                icon="ShieldCheckIcon"
                label="Security"
                route-name="bms.security"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.security')"
                @click="navigateTo('bms.security.settings')"
              />

              <NavItem
                v-if="shouldShowNavItem('bms.portal-users.index')"
                icon="UserGroupIcon"
                label="Portal Users"
                route-name="bms.portal-users"
                :collapsed="sidebarCollapsed"
                :active="isActive('bms.portal-users')"
                @click="navigateTo('bms.portal-users.index')"
              />
            </div>
          </div>
        </nav>
      </div>

      <!-- User Profile - Fixed at Bottom -->
      <div class="flex-shrink-0 border-t border-gray-100 px-3 py-2 bg-gradient-to-t from-gray-50 to-white mt-auto">
        <div v-if="!sidebarCollapsed" class="flex items-center gap-3 p-1.5 rounded-xl hover:bg-white/80 transition-all duration-200 cursor-pointer group">
          <div class="w-10 h-10 bg-gradient-to-br from-gray-200 to-gray-300 rounded-xl flex items-center justify-center flex-shrink-0 ring-2 ring-gray-100 group-hover:ring-blue-100 transition-all">
            <UserCircleIcon class="h-7 w-7 text-gray-600 group-hover:text-blue-600 transition-colors" aria-hidden="true" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-900 truncate">{{ user?.name }}</p>
            <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
          </div>
          <GlobalAppSwitcher />
          <Menu as="div" class="relative">
            <MenuButton class="p-1 rounded-lg hover:bg-gray-100">
              <EllipsisVerticalIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
            </MenuButton>
            <MenuItems class="absolute bottom-full right-0 mb-2 w-48 origin-bottom-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
              <div class="py-1">
                <MenuItem v-if="isAdmin" v-slot="{ active }">
                  <Link
                    :href="route('admin.dashboard')"
                    :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-gray-700 flex items-center gap-2']"
                  >
                    <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                    Back to Admin Dashboard
                  </Link>
                </MenuItem>
                <MenuItem v-slot="{ active }">
                  <button
                    @click="navigateTo('profile')"
                    :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-gray-700']"
                  >
                    Profile Settings
                  </button>
                </MenuItem>
                <MenuItem v-slot="{ active }">
                  <button
                    @click="logout"
                    :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-gray-700']"
                  >
                    Sign Out
                  </button>
                </MenuItem>
              </div>
            </MenuItems>
          </Menu>
        </div>
        <div v-else class="flex flex-col items-center gap-2 py-2">
          <GlobalAppSwitcher />
          <Menu as="div" class="relative">
            <MenuButton class="w-11 h-11 bg-gradient-to-br from-gray-200 to-gray-300 rounded-xl flex items-center justify-center hover:from-blue-100 hover:to-blue-200 transition-all duration-200 ring-2 ring-gray-100 hover:ring-blue-200">
              <UserCircleIcon class="h-7 w-7 text-gray-600 hover:text-blue-600 transition-colors" aria-hidden="true" />
            </MenuButton>
            <MenuItems class="absolute bottom-full left-0 mb-2 w-48 origin-bottom-left rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
              <div class="py-1">
                <MenuItem v-if="isAdmin" v-slot="{ active }">
                  <Link
                    :href="route('admin.dashboard')"
                    :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-gray-700 flex items-center gap-2']"
                  >
                    <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                    Back to Admin Dashboard
                  </Link>
                </MenuItem>
                <MenuItem v-slot="{ active }">
                  <button
                    @click="navigateTo('profile')"
                    :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-gray-700']"
                  >
                    Profile Settings
                  </button>
                </MenuItem>
                <MenuItem v-slot="{ active }">
                  <button
                    @click="logout"
                    :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-gray-700']"
                  >
                    Sign Out
                  </button>
                </MenuItem>
              </div>
            </MenuItems>
          </Menu>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex flex-col flex-1 overflow-hidden">
      <!-- Top Header -->
      <header class="flex-shrink-0 bg-white border-b border-gray-200 shadow-sm z-30">
        <div class="px-4 sm:px-6 lg:px-8">
          <div class="flex h-16 items-center justify-between">
            <!-- Left side: Hamburger + Breadcrumbs -->
            <div class="flex items-center gap-4">
              <!-- Desktop Sidebar Toggle -->
              <button
                @click="sidebarCollapsed = !sidebarCollapsed"
                class="hidden md:block p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
              >
                <Bars3Icon v-if="sidebarCollapsed" class="h-6 w-6" aria-hidden="true" />
                <Bars3BottomLeftIcon v-else class="h-6 w-6" aria-hidden="true" />
              </button>

              <!-- Mobile menu button -->
              <button
                @click="mobileMenuOpen = true"
                class="md:hidden p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100"
              >
                <Bars3Icon class="h-6 w-6" aria-hidden="true" />
              </button>

              <!-- Breadcrumbs -->
              <nav class="hidden md:flex items-center space-x-2 text-sm">
                <button @click="navigateTo('bms.dashboard')" class="text-gray-500 hover:text-gray-700 transition">
                  CMS
                </button>
                <ChevronRightIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                <span class="text-gray-900 font-medium">{{ pageTitle }}</span>
              </nav>
            </div>

            <!-- Right side actions -->
            <div class="flex items-center gap-3">
              <!-- Search -->
              <div class="hidden lg:block">
                <div class="relative">
                  <MagnifyingGlassIcon
                    class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"
                    aria-hidden="true"
                  />
                  <input
                    type="text"
                    placeholder="Search..."
                    class="block w-64 px-4 py-2 pl-10 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-500 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                  />
                </div>
              </div>

              <!-- Notifications Dropdown -->
              <Menu as="div" class="relative">
                <MenuButton class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 relative transition">
                  <BellIcon class="h-6 w-6" aria-hidden="true" />
                  <span v-if="unreadNotificationsCount > 0" class="absolute top-1 right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-medium text-white ring-2 ring-white">
                    {{ unreadNotificationsCount > 9 ? '9+' : unreadNotificationsCount }}
                  </span>
                </MenuButton>
                <MenuItems class="absolute right-0 mt-2 w-80 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                  <div class="p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                      <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                      <Link :href="route('bms.notifications.index')" class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                        View all
                      </Link>
                    </div>
                  </div>
                  <div class="max-h-96 overflow-y-auto">
                    <MenuItem v-for="notification in recentNotifications" :key="notification.id" v-slot="{ active }">
                      <button
                        @click="handleNotificationClick(notification)"
                        :class="[
                          active ? 'bg-gray-50' : '',
                          !notification.read_at ? 'bg-blue-50' : '',
                          'block w-full text-left px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition'
                        ]"
                      >
                        <div class="flex gap-3">
                          <div class="flex-shrink-0">
                            <div :class="[
                              'w-2 h-2 rounded-full mt-2',
                              !notification.read_at ? 'bg-blue-600' : 'bg-gray-300'
                            ]"></div>
                          </div>
                          <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ notification.title }}</p>
                            <p class="text-xs text-gray-600 mt-1 line-clamp-2">{{ notification.message }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ formatNotificationTime(notification.created_at) }}</p>
                          </div>
                        </div>
                      </button>
                    </MenuItem>
                    <div v-if="recentNotifications.length === 0" class="px-4 py-8 text-center">
                      <BellIcon class="h-12 w-12 text-gray-300 mx-auto mb-2" aria-hidden="true" />
                      <p class="text-sm text-gray-500">No notifications</p>
                    </div>
                  </div>
                </MenuItems>
              </Menu>

              <!-- User Profile Dropdown -->
              <Menu as="div" class="relative">
                <MenuButton class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 transition">
                  <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                    <UserCircleIcon class="h-7 w-7 text-gray-500" aria-hidden="true" />
                  </div>
                  <ChevronDownIcon class="hidden md:block h-4 w-4 text-gray-400" aria-hidden="true" />
                </MenuButton>
                <MenuItems class="absolute right-0 mt-2 w-56 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                  <div class="px-4 py-3 border-b border-gray-200">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ user?.name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
                  </div>
                  <div class="py-1">
                    <MenuItem v-if="isAdmin" v-slot="{ active }">
                      <Link
                        :href="route('admin.dashboard')"
                        :class="[active ? 'bg-gray-100' : '', 'flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-blue-700 font-medium']"
                      >
                        <ArrowLeftIcon class="h-5 w-5 text-blue-500" aria-hidden="true" />
                        Back to Admin Dashboard
                      </Link>
                    </MenuItem>
                    <div v-if="isAdmin" class="border-t border-gray-200 my-1"></div>
                    <MenuItem v-slot="{ active }">
                      <Link
                        :href="route('profile.edit')"
                        :class="[active ? 'bg-gray-100' : '', 'flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-gray-700']"
                      >
                        <UserCircleIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        Profile Settings
                      </Link>
                    </MenuItem>
                    <MenuItem v-slot="{ active }">
                      <Link
                        :href="route('bms.settings.index')"
                        :class="[active ? 'bg-gray-100' : '', 'flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-gray-700']"
                      >
                        <Cog6ToothIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        Company Settings
                      </Link>
                    </MenuItem>
                    <div class="border-t border-gray-200 my-1"></div>
                    <MenuItem v-slot="{ active }">
                      <button
                        @click="logout"
                        :class="[active ? 'bg-gray-100' : '', 'flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-red-600']"
                      >
                        <ArrowRightOnRectangleIcon class="h-5 w-5 text-red-500" aria-hidden="true" />
                        Sign Out
                      </button>
                    </MenuItem>
                  </div>
                </MenuItems>
              </Menu>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content (scrollable) -->
      <main class="flex-1 overflow-y-auto custom-scrollbar p-6">
        <slot />
      </main>
    </div>

    <!-- Slide-Over Panel -->
    <SlideOver
      :open="slideOver.isOpen.value"
      :title="slideOver.config.value.title"
      :subtitle="slideOver.config.value.subtitle"
      :size="slideOver.config.value.size"
      @close="slideOver.close()"
    >
      <JobForm
        v-if="slideOver.currentType.value === 'job'"
        :customers="customers"
        @cancel="slideOver.close()"
        @success="handleFormSuccess"
      />
      
      <CustomerForm
        v-else-if="slideOver.currentType.value === 'customer'"
        @cancel="slideOver.close()"
        @success="handleFormSuccess"
      />
      
      <InvoiceForm
        v-else-if="slideOver.currentType.value === 'invoice'"
        :customers="customers"
        @cancel="slideOver.close()"
        @success="handleFormSuccess"
      />
      
      <ExpenseForm
        v-else-if="slideOver.currentType.value === 'expense'"
        :categories="props.expenseCategories || []"
        :jobs="props.jobs || []"
        @cancel="slideOver.close()"
        @success="handleFormSuccess"
      />
    </SlideOver>

    <!-- Mobile Sidebar (same as before) -->
    <TransitionRoot as="template" :show="mobileMenuOpen">
      <Dialog as="div" class="relative z-50 md:hidden" @close="mobileMenuOpen = false">
        <TransitionChild
          as="template"
          enter="transition-opacity ease-linear duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="transition-opacity ease-linear duration-300"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-gray-900/80" />
        </TransitionChild>

        <div class="fixed inset-0 flex">
          <TransitionChild
            as="template"
            enter="transition ease-in-out duration-300 transform"
            enter-from="-translate-x-full"
            enter-to="translate-x-0"
            leave="transition ease-in-out duration-300 transform"
            leave-from="translate-x-0"
            leave-to="-translate-x-full"
          >
            <DialogPanel class="relative mr-16 flex w-full max-w-xs flex-1 bg-white">
              <TransitionChild
                as="template"
                enter="ease-in-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in-out duration-300"
                leave-from="opacity-100"
                leave-to="opacity-0"
              >
                <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                  <button type="button" class="-m-2.5 p-2.5" @click="mobileMenuOpen = false">
                    <XMarkIcon class="h-6 w-6 text-white" aria-hidden="true" />
                  </button>
                </div>
              </TransitionChild>
              
              <!-- Mobile sidebar content -->
              <div class="flex flex-col flex-grow overflow-y-auto">
                <div class="flex items-center flex-shrink-0 px-4 py-4 border-b border-gray-200">
                  <div class="flex items-center gap-3">
                    <!-- Company Logo or Icon -->
                    <div v-if="company?.logo_url" class="w-10 h-10 rounded-lg flex items-center justify-center overflow-hidden bg-white border border-gray-200">
                      <img 
                        :src="company.logo_url" 
                        :alt="company.name"
                        class="w-full h-full object-contain p-1"
                      />
                    </div>
                    <div v-else class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                      <span class="text-white font-bold text-sm">{{ company?.name?.charAt(0) || 'C' }}</span>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-semibold text-gray-900 truncate">{{ company?.name }}</p>
                      <p class="text-xs text-gray-500 truncate">{{ cmsUser?.role?.name }}</p>
                    </div>
                  </div>
                </div>

                <nav class="flex-1 px-3 py-4 space-y-1">
                  <button
                    @click="navigateTo('bms.dashboard'); mobileMenuOpen = false"
                    :class="[
                      isActive('bms.dashboard') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50',
                      'group flex items-center w-full px-3 py-2 text-sm font-medium rounded-lg transition'
                    ]"
                  >
                    <HomeIcon class="mr-3 h-5 w-5 flex-shrink-0" aria-hidden="true" />
                    Dashboard
                  </button>
                  <!-- Add other mobile nav items -->
                </nav>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </Dialog>
    </TransitionRoot>
    </div>
  </div>
  <ToastContainer />
</template>

<script setup lang="ts">
import { ref, provide, computed, watch, onMounted, onUnmounted } from 'vue'
import { router, usePage, Link, Head } from '@inertiajs/vue3'
import { Menu, MenuButton, MenuItem, MenuItems, Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue'
import {
  HomeIcon,
  BriefcaseIcon,
  UsersIcon,
  DocumentTextIcon,
  CreditCardIcon,
  ChartBarIcon,
  Cog6ToothIcon,
  BuildingOfficeIcon,
  UserCircleIcon,
  BellIcon,  MagnifyingGlassIcon,
  Bars3Icon,
  Bars3BottomLeftIcon,
  XMarkIcon,
  EllipsisVerticalIcon,
  ChevronRightIcon,
  PresentationChartLineIcon,
  CurrencyDollarIcon,
  ChevronDownIcon,
  ArrowRightOnRectangleIcon,
  BanknotesIcon,
  DocumentDuplicateIcon,
  CubeIcon,
  ComputerDesktopIcon,
  UserGroupIcon,
  SwatchIcon,
  ClockIcon,
  ArrowPathIcon,
  CheckCircleIcon,
  CalculatorIcon,
  EnvelopeIcon,
  DevicePhoneMobileIcon,
  ShieldCheckIcon,
  DocumentChartBarIcon,
  ArrowLeftIcon,
  ChevronUpDownIcon,
  CheckIcon,
  ClipboardDocumentCheckIcon,
  CalendarDaysIcon,
  ViewColumnsIcon,
  DocumentDuplicateIcon as DocumentDuplicateIconOutline,
  ArrowPathRoundedSquareIcon,
  ScaleIcon,
  ChartPieIcon,
  BeakerIcon,
} from '@heroicons/vue/24/outline'
import SlideOver from '@/components/BMS/SlideOver.vue'
import JobForm from '@/components/BMS/Forms/JobForm.vue'
import CustomerForm from '@/components/BMS/Forms/CustomerForm.vue'
import InvoiceForm from '@/components/BMS/Forms/InvoiceForm.vue'
import ExpenseForm from '@/components/BMS/Forms/ExpenseForm.vue'
import NavItem from '@/components/BMS/NavItem.vue'
import { useBMSSlideOver } from '@/composables/useBMSSlideOver'
import { useToast } from '@/composables/useToast'
import ToastContainer from '@/components/Shared/ToastContainer.vue'
import GlobalAppSwitcher from '@/Components/Workspace/GlobalAppSwitcher.vue'

interface Notification {
  id: number
  title: string
  message: string
  type: string
  read_at: string | null
  created_at: string
  data?: any
}

interface Props {
  pageTitle?: string
  customers?: any  // Can be array or pagination object
  expenseCategories?: any[]
  jobs?: any  // Can be array or pagination object
}

const props = defineProps<Props>()

const page = usePage()
const mobileMenuOpen = ref(false)
const sidebarCollapsed = ref(false)
const slideOver = useBMSSlideOver()

const { toast } = useToast()
const flash = computed(() => (page.props as any).flash)
watch(flash, (f) => {
    if (f?.success) toast.success(f.success)
    if (f?.error) toast.error(f.error)
    if (f?.warning) toast.warning(f.warning)
    if (f?.info) toast.info(f.info)
}, { immediate: true, deep: true })

// Collapsible sections state - persisted in localStorage
const STORAGE_KEY = 'cms_sidebar_sections'
const collapsedSections = ref<Record<string, boolean>>({
  business: true,
  financial: true,
  analytics: true,
  construction: true,
  operations: true,
  fieldOperations: true,
  operationsModule: true,
  hr: true,
  administration: true,
  payroll: true,
  inventoryMgmt: true,
  settings: true,
})

// Load collapsed state from localStorage
onMounted(() => {
  const saved = localStorage.getItem(STORAGE_KEY)
  if (saved) {
    try {
      collapsedSections.value = { ...collapsedSections.value, ...JSON.parse(saved) }
    } catch (e) {
      console.error('Failed to parse sidebar state:', e)
    }
  }
})

// Toggle section collapse
const toggleSection = (section: string) => {
  collapsedSections.value[section] = !collapsedSections.value[section]
  localStorage.setItem(STORAGE_KEY, JSON.stringify(collapsedSections.value))
}

// Check if section should be shown (for search functionality)
const isSectionVisible = (sectionRoutes: string[]) => {
  if (!searchQuery.value.trim()) return true
  return sectionRoutes.some(route => shouldShowNavItem(route))
}

// Quick search functionality
const searchQuery = ref('')
const allNavItems = ref([
  { label: 'Dashboard', route: 'bms.dashboard', keywords: ['home', 'overview', 'main'] },
  { label: 'Jobs', route: 'bms.jobs', keywords: ['work', 'projects', 'tasks'] },
  // Operations Module
  { label: 'Operations Dashboard', route: 'bms.operations.dashboard', keywords: ['operations', 'tasks', 'workflow', 'planning'] },
  { label: 'My Tasks', route: 'bms.operations.my-tasks', keywords: ['my tasks', 'assigned', 'todo'] },
  { label: 'All Tasks', route: 'bms.operations.tasks', keywords: ['tasks', 'work items', 'operations'] },
  { label: 'Workflows', route: 'bms.operations.workflows', keywords: ['workflows', 'processes', 'stages'] },
  { label: 'Kanban Board', route: 'bms.operations.kanban', keywords: ['kanban', 'board', 'visual', 'drag drop'] },
  { label: 'Planning', route: 'bms.operations.planning', keywords: ['planning', 'schedule', 'capacity', 'workload'] },
  { label: 'Task Templates', route: 'bms.operations.templates', keywords: ['templates', 'reusable', 'task templates'] },
  { label: 'Recurring Tasks', route: 'bms.operations.recurring-tasks', keywords: ['recurring', 'automated', 'scheduled tasks'] },
  { label: 'Measurements', route: 'bms.measurements', keywords: ['measure', 'dimensions', 'fabrication'] },
  { label: 'Customers', route: 'bms.customers', keywords: ['clients', 'contacts'] },
  { label: 'Invoices', route: 'bms.invoices', keywords: ['bills', 'billing', 'payments'] },
  { label: 'Payments', route: 'bms.payments', keywords: ['transactions', 'money'] },
  { label: 'Reports', route: 'bms.reports', keywords: ['analytics', 'statistics'] },
  { label: 'Budgets', route: 'bms.budgets', keywords: ['budget', 'planning', 'forecast'] },
  { label: 'Business Plans', route: 'bms.business-plans', keywords: ['strategy', 'planning', 'forecast', 'growth', 'business plan'] },
  // Analytics
  { label: 'CEO Overview', route: 'bms.analytics.overview', keywords: ['analytics', 'overview', 'dashboard', 'kpi', 'executive'] },
  { label: 'Operations Analytics', route: 'bms.analytics.operations', keywords: ['analytics', 'performance', 'operations'] },
  { label: 'Finance Analytics', route: 'bms.analytics.finance', keywords: ['analytics', 'financial', 'money'] },
  { label: 'Procurement Analytics', route: 'bms.analytics.procurement', keywords: ['analytics', 'procurement', 'vendors', 'purchase orders', 'contracts', 'assets'] },
  // Financial
  { label: 'Expenses', route: 'bms.expenses', keywords: ['costs', 'spending'] },
  { label: 'Quotations', route: 'bms.quotations', keywords: ['quotes', 'estimates'] },
  { label: 'Inventory', route: 'bms.inventory', keywords: ['stock', 'products', 'items'] },
  { label: 'Locations', route: 'bms.inventory.locations', keywords: ['warehouse', 'workshop', 'site', 'storage'] },
  { label: 'Stock Levels', route: 'bms.inventory.stock-levels', keywords: ['stock levels', 'quantities', 'on hand'] },
  { label: 'Transfers', route: 'bms.inventory.transfers', keywords: ['transfer', 'move', 'relocate stock'] },
  { label: 'Adjustments', route: 'bms.inventory.adjustments', keywords: ['adjust', 'correct', 'increase', 'decrease'] },
  { label: 'Stock Counts', route: 'bms.inventory.counts', keywords: ['count', 'physical inventory', 'cycle count'] },
  { label: 'Valuation', route: 'bms.inventory.valuation', keywords: ['fifo', 'lifo', 'average', 'value', 'worth'] },
  { label: 'Materials', route: 'bms.materials', keywords: ['materials', 'supplies', 'stock', 'aluminium', 'glass', 'profiles'] },
  { label: 'Assets', route: 'bms.assets', keywords: ['equipment', 'property'] },
  { label: 'Depreciation Register', route: 'bms.assets.depreciation-register', keywords: ['depreciation', 'asset value', 'amortization'] },
  { label: 'Payroll', route: 'bms.payroll', keywords: ['salary', 'wages', 'pay'] },
  { label: 'Workers', route: 'bms.payroll.workers', keywords: ['employees', 'staff', 'team'] },
  // Construction Modules
  { label: 'Projects', route: 'bms.projects', keywords: ['construction', 'sites', 'building'] },
  { label: 'Subcontractors', route: 'bms.subcontractors', keywords: ['contractors', 'vendors', 'suppliers'] },
  { label: 'Equipment', route: 'bms.equipment', keywords: ['machinery', 'tools', 'assets'] },
  { label: 'Labour Crews', route: 'bms.labour.crews', keywords: ['teams', 'workers', 'crew'] },
  { label: 'Timesheets', route: 'bms.labour.timesheets', keywords: ['hours', 'time tracking', 'attendance'] },
  { label: 'BOQ', route: 'bms.boq', keywords: ['bill of quantities', 'estimates', 'quantities'] },
  { label: 'Progress Billing', route: 'bms.progress-billing', keywords: ['certificates', 'billing', 'retention'] },
  // Operations
  { label: 'Production', route: 'bms.production', keywords: ['manufacturing', 'fabrication', 'production'] },
  { label: 'Installation', route: 'bms.installation', keywords: ['install', 'fitting', 'setup'] },
  { label: 'Stock Management', route: 'bms.inventory', keywords: ['stock levels', 'inventory', 'warehouse'] },
  { label: 'Fleet', route: 'bms.fleet', keywords: ['vehicles', 'transport', 'trucks'] },
  { label: 'Documents', route: 'bms.documents', keywords: ['files', 'storage', 'uploads'] },
  { label: 'Safety', route: 'bms.safety', keywords: ['health', 'safety', 'compliance'] },
  { label: 'Contracts', route: 'bms.contracts', keywords: ['agreements', 'legal', 'signed'] },
  { label: 'Purchase Orders', route: 'bms.purchase-orders', keywords: ['po', 'orders', 'procurement'] },
  { label: 'Vendors', route: 'bms.vendors', keywords: ['suppliers', 'contractors', 'providers'] },
  // HR Management
  { label: 'Branches', route: 'bms.branches', keywords: ['locations', 'offices', 'branches', 'sites'] },
  { label: 'Departments', route: 'bms.departments', keywords: ['divisions', 'units'] },
  { label: 'Org Chart', route: 'bms.org-chart', keywords: ['organizational', 'hierarchy', 'reporting', 'structure', 'tree'] },
  { label: 'Leave Management', route: 'bms.leave', keywords: ['vacation', 'time off', 'absence'] },
  { label: 'Shifts', route: 'bms.shifts', keywords: ['schedule', 'roster', 'timing'] },
  { label: 'Attendance', route: 'bms.attendance', keywords: ['clock in', 'presence', 'tracking'] },
  { label: 'Overtime', route: 'bms.overtime', keywords: ['extra hours', 'ot'] },
  { label: 'Recruitment', route: 'bms.recruitment', keywords: ['hiring', 'jobs', 'candidates'] },
  { label: 'Onboarding', route: 'bms.hrms-onboarding', keywords: ['new hire', 'orientation'] },
  { label: 'Performance', route: 'bms.performance', keywords: ['reviews', 'goals', 'appraisal'] },
  { label: 'Training', route: 'bms.training', keywords: ['learning', 'development', 'courses'] },
  { label: 'HR Reports', route: 'bms.hr-reports', keywords: ['human resources', 'analytics'] },
  { label: 'Loans', route: 'bms.loans', keywords: ['advances', 'lending', 'credit'] },
  // Other
  { label: 'Time Tracking', route: 'bms.time-tracking', keywords: ['hours', 'timesheet'] },
  { label: 'Recurring Invoices', route: 'bms.recurring-invoices', keywords: ['subscription', 'repeat'] },
  { label: 'Approvals', route: 'bms.approvals', keywords: ['pending', 'review', 'authorize'] },
  { label: 'Chart of Accounts', route: 'bms.accounting', keywords: ['accounting', 'ledger', 'books'] },
  // Payroll Configuration
  { label: 'Allowance Types', route: 'bms.payroll.configuration.allowance-types', keywords: ['benefits', 'perks', 'allowances'] },
  { label: 'Deduction Types', route: 'bms.payroll.configuration.deduction-types', keywords: ['taxes', 'withholding', 'deductions'] },
  // Settings
  { label: 'Company Settings', route: 'bms.settings.index', keywords: ['configuration', 'preferences', 'setup', 'company'] },
  { label: 'Document Templates', route: 'bms.settings.document-templates', keywords: ['templates', 'documents', 'formats'] },
  { label: 'Pricing Rules', route: 'bms.settings.pricing-rules', keywords: ['rates', 'pricing', 'fabrication', 'costs'] },
  { label: 'Industry Presets', route: 'bms.settings.industry-presets.index', keywords: ['templates', 'industry', 'setup'] },
  { label: 'Email Settings', route: 'bms.settings.email.index', keywords: ['mail', 'smtp', 'notifications'] },
  { label: 'SMS Settings', route: 'bms.settings.sms.index', keywords: ['text', 'messages', 'notifications'] },
  { label: 'Currency', route: 'bms.settings.currency.index', keywords: ['money', 'exchange', 'rates'] },
  { label: 'Security', route: 'bms.security.settings', keywords: ['password', 'authentication', 'access'] },
  { label: 'Portal Users', route: 'bms.portal-users.index', keywords: ['portal', 'customer portal', 'access', 'customer access', 'users'] },
])

const filteredNavItems = computed(() => {
  if (!searchQuery.value.trim()) {
    return allNavItems.value
  }
  
  const query = searchQuery.value.toLowerCase().trim()
  return allNavItems.value.filter(item => {
    const labelMatch = item.label.toLowerCase().includes(query)
    const keywordMatch = item.keywords.some(keyword => keyword.includes(query))
    return labelMatch || keywordMatch
  })
})

const handleSearch = () => {
  // Search is reactive through computed property
}

const clearSearch = () => {
  searchQuery.value = ''
}

const navigateToSearchResult = (route: string) => {
  searchQuery.value = ''
  navigateTo(route)
}

// Helper to determine if a nav item should be shown based on search filter
const shouldShowNavItem = (route: string) => {
  if (!searchQuery.value.trim()) {
    return true // Show all when not searching
  }
  return filteredNavItems.value.some(item => item.route === route)
}

// Provide slideOver to child components
provide('slideOver', slideOver)

// Access global shared data from Inertia
const company = computed(() => page.props.company)
const user = computed(() => page.props.auth?.user)
const cmsUser = computed(() => page.props.cmsUser)
const userCompanies = computed(() => (page.props as any).userCompanies ?? [])

// Company switcher state
const showCompanySwitcher = ref(false)

function switchCompany(companyId: number) {
  showCompanySwitcher.value = false
  router.post(route('bms.switch-company'), { company_id: companyId })
}

// Fabrication module — driven by company settings shared from middleware
const hasFabrication = computed(() => {
  const c = company.value as any
  if (!c) return false
  // Explicit toggle in settings takes precedence
  if (c.settings?.fabrication_module !== undefined) return !!c.settings.fabrication_module
  // Fall back: show if industry_type suggests fabrication
  const fabricationTypes = ['aluminium', 'fabrication', 'construction', 'manufacturing']
  return fabricationTypes.includes((c.industry_type ?? '').toLowerCase())
})

// Construction modules — driven by company settings
const hasConstruction = computed(() => {
  const c = company.value as any
  if (!c) return false
  // Check if construction modules are explicitly enabled
  if (c.settings?.construction_modules !== undefined) return !!c.settings.construction_modules
  // Fall back: show if industry_type suggests construction
  const constructionTypes = ['construction', 'building', 'contractor']
  return constructionTypes.includes((c.industry_type ?? '').toLowerCase())
})

// Check if user is admin (has 'admin' or 'administrator' role)
const isAdmin = computed(() => {
  const roles = user.value?.roles || []
  return roles.includes('admin') || roles.includes('administrator')
})

// Notifications from backend (no hardcoded data)
const recentNotifications = ref<Notification[]>(page.props.notifications || [])

const unreadNotificationsCount = computed(() => {
  return recentNotifications.value.filter(n => !n.read_at).length
})

const handleNotificationClick = (notification: Notification) => {
  // Mark as read
  if (!notification.read_at) {
    router.post(route('bms.notifications.mark-read', notification.id), {}, {
      preserveScroll: true,
      onSuccess: () => {
        notification.read_at = new Date().toISOString()
      }
    })
  }
  
  // Navigate to relevant page based on notification type
  if (notification.data?.url) {
    router.visit(notification.data.url)
  }
}

const formatNotificationTime = (timestamp: string) => {
  const date = new Date(timestamp)
  const now = new Date()
  const diffMs = now.getTime() - date.getTime()
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)
  
  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  if (diffDays < 7) return `${diffDays}d ago`
  
  return date.toLocaleDateString()
}

const isActive = (routeName: string) => {
  const currentRoute = page.url
  if (!currentRoute) return false
  
  // Extract the base route from the full URL
  const routePath = currentRoute.split('?')[0] // Remove query params
  
  // Check if current route matches the given route name
  // Handle both exact matches and child routes
  if (routeName === 'bms.dashboard') {
    return routePath === '/cms' || routePath === '/cms/dashboard'
  }
  
  // Convert route name to path pattern (e.g., 'bms.jobs' -> '/cms/jobs')
  const routePattern = routeName.replace(/\./g, '/').replace('/index', '')
  const fullPattern = `/${routePattern}`
  
  // Exact match or child route (with trailing slash or additional segments)
  return routePath === fullPattern || 
         routePath.startsWith(`${fullPattern}/`) ||
         routePath.startsWith(`${fullPattern}?`)
}

const navigateTo = (routeName: string) => {
  router.visit(route(routeName), {
    preserveState: true,
    preserveScroll: false,
    only: ['pageContent', 'stats', 'recentJobs', 'recentInvoices'], // Only fetch what changed
  })
}

const handleFormSuccess = () => {
  slideOver.close()
  // Reload only necessary data
  router.reload({
    only: ['stats', 'recentJobs', 'recentInvoices', 'customers'],
    preserveScroll: true,
    preserveState: true,
  })
}

const logout = () => {
  router.post(route('logout'))
}
</script>

<style scoped>
/* Custom Scrollbar Styling */
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: #CBD5E1 #F8FAFC;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #F8FAFC;
  border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #CBD5E1;
  border-radius: 4px;
  transition: background 0.2s ease;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94A3B8;
}

.custom-scrollbar::-webkit-scrollbar-thumb:active {
  background: #64748B;
}

/* Firefox scrollbar styling */
@supports (scrollbar-color: auto) {
  .custom-scrollbar {
    scrollbar-color: #CBD5E1 #F8FAFC;
  }
  
  .custom-scrollbar:hover {
    scrollbar-color: #94A3B8 #F8FAFC;
  }
}
</style>
