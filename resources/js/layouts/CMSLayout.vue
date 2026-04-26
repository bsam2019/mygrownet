<template>
  <div>
    <Head>
      <link rel="manifest" href="/manifest.json" />
      <meta name="theme-color" content="#2563eb" />
      <meta name="apple-mobile-web-app-capable" content="yes" />
      <meta name="apple-mobile-web-app-status-bar-style" content="default" />
      <meta name="apple-mobile-web-app-title" content="CMS" />
    </Head>

    <!-- PWA Install Prompt -->
    <PwaInstallPrompt />

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
            v-if="shouldShowNavItem('cms.dashboard')"
            icon="HomeIcon"
            label="Dashboard"
            route-name="cms.dashboard"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.dashboard')"
            @click="navigateTo('cms.dashboard')"
          />
          
          <NavItem
            v-if="shouldShowNavItem('cms.jobs')"
            icon="BriefcaseIcon"
            label="Jobs"
            route-name="cms.jobs"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.jobs')"
            @click="navigateTo('cms.jobs.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.measurements') && hasFabrication"
            icon="ScissorsIcon"
            label="Measurements"
            route-name="cms.measurements"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.measurements')"
            @click="navigateTo('cms.measurements.index')"
          />
          
          <NavItem
            v-if="shouldShowNavItem('cms.customers')"
            icon="UsersIcon"
            label="Customers"
            route-name="cms.customers"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.customers')"
            @click="navigateTo('cms.customers.index')"
          />
          
          <NavItem
            v-if="shouldShowNavItem('cms.invoices')"
            icon="DocumentTextIcon"
            label="Invoices"
            route-name="cms.invoices"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.invoices')"
            @click="navigateTo('cms.invoices.index')"
          />
          
          <NavItem
            v-if="shouldShowNavItem('cms.payments')"
            icon="CreditCardIcon"
            label="Payments"
            route-name="cms.payments"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.payments')"
            @click="navigateTo('cms.payments.index')"
          />
          
          <NavItem
            v-if="shouldShowNavItem('cms.reports')"
            icon="ChartBarIcon"
            label="Reports"
            route-name="cms.reports"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.reports')"
            @click="navigateTo('cms.reports.index')"
          />

          <div v-if="!searchQuery" class="pt-4 pb-2">
            <div class="border-t border-gray-100"></div>
          </div>

          <!-- Analytics Submenu -->
          <div v-if="!sidebarCollapsed && !searchQuery" class="px-3 mb-3">
            <div class="flex items-center gap-2 px-2 py-1.5 bg-gradient-to-r from-blue-50 to-transparent rounded-lg">
              <div class="w-1 h-4 bg-gradient-to-b from-blue-500 to-blue-600 rounded-full"></div>
              <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Analytics</p>
            </div>
          </div>
          
          <NavItem
            v-if="shouldShowNavItem('cms.analytics.operations')"
            icon="PresentationChartLineIcon"
            label="Operations"
            route-name="cms.analytics.operations"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.analytics.operations')"
            @click="navigateTo('cms.analytics.operations')"
          />
          
          <NavItem
            v-if="shouldShowNavItem('cms.analytics.finance')"
            icon="CurrencyDollarIcon"
            label="Finance"
            route-name="cms.analytics.finance"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.analytics.finance')"
            @click="navigateTo('cms.analytics.finance')"
          />

          <div v-if="!searchQuery" class="pt-4 pb-2">
            <div class="border-t border-gray-100"></div>
          </div>

          <NavItem
            v-if="shouldShowNavItem('cms.expenses')"
            icon="BanknotesIcon"
            label="Expenses"
            route-name="cms.expenses"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.expenses')"
            @click="navigateTo('cms.expenses.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.quotations')"
            icon="DocumentDuplicateIcon"
            label="Quotations"
            route-name="cms.quotations"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.quotations')"
            @click="navigateTo('cms.quotations.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.inventory')"
            icon="CubeIcon"
            label="Inventory"
            route-name="cms.inventory"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.inventory')"
            @click="navigateTo('cms.inventory.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.materials') && (hasFabrication || hasConstruction)"
            icon="CubeIcon"
            label="Materials"
            route-name="cms.materials"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.materials')"
            @click="navigateTo('cms.materials.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.assets')"
            icon="ComputerDesktopIcon"
            label="Assets"
            route-name="cms.assets"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.assets')"
            @click="navigateTo('cms.assets.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.payroll')"
            icon="UserGroupIcon"
            label="Payroll"
            route-name="cms.payroll"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.payroll')"
            @click="navigateTo('cms.payroll.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.payroll.workers')"
            icon="UsersIcon"
            label="Workers"
            route-name="cms.payroll.workers"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.payroll.workers')"
            @click="navigateTo('cms.payroll.workers.index')"
          />

          <div v-if="!searchQuery" class="pt-4 pb-2">
            <div class="border-t border-gray-100"></div>
          </div>

          <!-- Construction Modules Submenu -->
          <div v-if="!sidebarCollapsed && !searchQuery && hasConstruction" class="px-3 pt-2 mb-3">
            <div class="flex items-center gap-2 px-2 py-1.5 bg-gradient-to-r from-orange-50 to-transparent rounded-lg">
              <div class="w-1 h-4 bg-gradient-to-b from-orange-500 to-orange-600 rounded-full"></div>
              <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Construction</p>
            </div>
          </div>

          <NavItem
            v-if="shouldShowNavItem('cms.projects') && hasConstruction"
            icon="BuildingOffice2Icon"
            label="Projects"
            route-name="cms.projects"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.projects')"
            @click="navigateTo('cms.projects.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.subcontractors') && hasConstruction"
            icon="UserGroupIcon"
            label="Subcontractors"
            route-name="cms.subcontractors"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.subcontractors')"
            @click="navigateTo('cms.subcontractors.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.equipment') && hasConstruction"
            icon="WrenchScrewdriverIcon"
            label="Equipment"
            route-name="cms.equipment"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.equipment')"
            @click="navigateTo('cms.equipment.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.labour.crews') && hasConstruction"
            icon="UserGroupIcon"
            label="Labour Crews"
            route-name="cms.labour.crews"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.labour.crews')"
            @click="navigateTo('cms.labour.crews.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.labour.timesheets') && hasConstruction"
            icon="ClockIcon"
            label="Timesheets"
            route-name="cms.labour.timesheets"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.labour.timesheets')"
            @click="navigateTo('cms.labour.timesheets.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.boq') && hasConstruction"
            icon="DocumentTextIcon"
            label="BOQ"
            route-name="cms.boq"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.boq')"
            @click="navigateTo('cms.boq.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.progress-billing') && hasConstruction"
            icon="DocumentCheckIcon"
            label="Progress Billing"
            route-name="cms.progress-billing"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.progress-billing')"
            @click="navigateTo('cms.progress-billing.certificates.index')"
          />

          <div v-if="!searchQuery" class="pt-4 pb-2">
            <div class="border-t border-gray-100"></div>
          </div>

          <!-- Operations Modules Submenu -->
          <div v-if="!sidebarCollapsed && !searchQuery && (hasFabrication || hasConstruction)" class="px-3 pt-2 mb-3">
            <div class="flex items-center gap-2 px-2 py-1.5 bg-gradient-to-r from-blue-50 to-transparent rounded-lg">
              <div class="w-1 h-4 bg-gradient-to-b from-blue-500 to-blue-600 rounded-full"></div>
              <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Operations</p>
            </div>
          </div>

          <NavItem
            v-if="shouldShowNavItem('cms.production') && hasFabrication"
            icon="CogIcon"
            label="Production"
            route-name="cms.production"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.production')"
            @click="navigateTo('cms.production.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.installation') && (hasFabrication || hasConstruction)"
            icon="WrenchScrewdriverIcon"
            label="Installation"
            route-name="cms.installation"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.installation')"
            @click="navigateTo('cms.installation.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.inventory') && (hasFabrication || hasConstruction)"
            icon="CubeIcon"
            label="Stock Management"
            route-name="cms.inventory"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.inventory')"
            @click="navigateTo('cms.inventory.stock-levels.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.fleet')"
            icon="TruckIcon"
            label="Fleet"
            route-name="cms.fleet"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.fleet')"
            @click="navigateTo('cms.fleet.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.documents')"
            icon="FolderIcon"
            label="Documents"
            route-name="cms.documents"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.documents')"
            @click="navigateTo('cms.documents.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.safety') && hasConstruction"
            icon="ShieldCheckIcon"
            label="Safety"
            route-name="cms.safety"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.safety')"
            @click="navigateTo('cms.safety.incidents.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.quality') && (hasFabrication || hasConstruction)"
            icon="CheckBadgeIcon"
            label="Quality"
            route-name="cms.quality"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.quality')"
            @click="navigateTo('cms.quality.inspections.index')"
          />

          <div v-if="!searchQuery" class="pt-4 pb-2">
            <div class="border-t border-gray-100"></div>
          </div>

          <!-- Payroll Configuration Submenu -->
          <div v-if="!sidebarCollapsed && !searchQuery" class="px-3 pt-2 mb-3">
            <div class="flex items-center gap-2 px-2 py-1.5 bg-gradient-to-r from-emerald-50 to-transparent rounded-lg">
              <div class="w-1 h-4 bg-gradient-to-b from-emerald-500 to-emerald-600 rounded-full"></div>
              <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Payroll Config</p>
            </div>
          </div>

          <NavItem
            v-if="shouldShowNavItem('cms.payroll.configuration.allowance-types')"
            icon="BanknotesIcon"
            label="Allowance Types"
            route-name="cms.payroll.configuration.allowance-types"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.payroll.configuration.allowance-types')"
            @click="navigateTo('cms.payroll.configuration.allowance-types.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.payroll.configuration.deduction-types')"
            icon="MinusCircleIcon"
            label="Deduction Types"
            route-name="cms.payroll.configuration.deduction-types"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.payroll.configuration.deduction-types')"
            @click="navigateTo('cms.payroll.configuration.deduction-types.index')"
          />

          <!-- HR Management Submenu -->
          <div v-if="!sidebarCollapsed && !searchQuery" class="px-3 pt-4 mb-3">
            <div class="flex items-center gap-2 px-2 py-1.5 bg-gradient-to-r from-purple-50 to-transparent rounded-lg">
              <div class="w-1 h-4 bg-gradient-to-b from-purple-500 to-purple-600 rounded-full"></div>
              <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">HR Management</p>
            </div>
          </div>

          <NavItem
            v-if="shouldShowNavItem('cms.departments')"
            icon="BuildingOffice2Icon"
            label="Departments"
            route-name="cms.departments"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.departments')"
            @click="navigateTo('cms.departments.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.leave')"
            icon="CalendarDaysIcon"
            label="Leave Management"
            route-name="cms.leave"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.leave')"
            @click="navigateTo('cms.leave.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.shifts')"
            icon="ClockIcon"
            label="Shifts"
            route-name="cms.shifts"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.shifts')"
            @click="navigateTo('cms.shifts.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.attendance')"
            icon="ClipboardDocumentCheckIcon"
            label="Attendance"
            route-name="cms.attendance"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.attendance')"
            @click="navigateTo('cms.attendance.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.overtime')"
            icon="ClockIcon"
            label="Overtime"
            route-name="cms.overtime"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.overtime')"
            @click="navigateTo('cms.overtime.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.recruitment')"
            icon="BriefcaseIcon"
            label="Recruitment"
            route-name="cms.recruitment"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.recruitment')"
            @click="navigateTo('cms.recruitment.job-postings.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.hrms-onboarding')"
            icon="AcademicCapIcon"
            label="Onboarding"
            route-name="cms.hrms-onboarding"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.hrms-onboarding')"
            @click="navigateTo('cms.hrms-onboarding.templates.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.performance')"
            icon="ChartBarIcon"
            label="Performance"
            route-name="cms.performance"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.performance')"
            @click="navigateTo('cms.performance.goals.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.training')"
            icon="AcademicCapIcon"
            label="Training"
            route-name="cms.training"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.training')"
            @click="navigateTo('cms.training.programs')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.hr-reports')"
            icon="DocumentChartBarIcon"
            label="HR Reports"
            route-name="cms.hr-reports"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.hr-reports')"
            @click="navigateTo('cms.hr-reports.index')"
          />

          <div v-if="!searchQuery" class="pt-4 pb-2">
            <div class="border-t border-gray-100"></div>
          </div>

          <NavItem
            v-if="shouldShowNavItem('cms.time-tracking')"
            icon="ClockIcon"
            label="Time Tracking"
            route-name="cms.time-tracking"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.time-tracking')"
            @click="navigateTo('cms.time-tracking.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.recurring-invoices')"
            icon="ArrowPathIcon"
            label="Recurring Invoices"
            route-name="cms.recurring-invoices"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.recurring-invoices')"
            @click="navigateTo('cms.recurring-invoices.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.approvals')"
            icon="CheckCircleIcon"
            label="Approvals"
            route-name="cms.approvals"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.approvals')"
            @click="navigateTo('cms.approvals.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.approvals')"
            icon="CheckCircleIcon"
            label="Approvals"
            route-name="cms.approvals"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.approvals')"
            @click="navigateTo('cms.approvals.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.accounting')"
            icon="CalculatorIcon"
            label="Chart of Accounts"
            route-name="cms.accounting"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.accounting')"
            @click="navigateTo('cms.accounting.index')"
          />

          <div v-if="!searchQuery" class="pt-4 pb-2">
            <div class="border-t border-gray-100"></div>
          </div>

          <!-- Settings Submenu -->
          <div v-if="!sidebarCollapsed && !searchQuery" class="px-3 mb-3">
            <div class="flex items-center gap-2 px-2 py-1.5 bg-gradient-to-r from-gray-100 to-transparent rounded-lg">
              <div class="w-1 h-4 bg-gradient-to-b from-gray-500 to-gray-600 rounded-full"></div>
              <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Settings</p>
            </div>
          </div>

          <NavItem
            v-if="shouldShowNavItem('cms.settings.index')"
            icon="Cog6ToothIcon"
            label="Company Settings"
            route-name="cms.settings"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.settings.index')"
            @click="navigateTo('cms.settings.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.settings.document-templates') && company?.has_bizdocs_module"
            icon="DocumentTextIcon"
            label="Document Templates"
            route-name="cms.settings.document-templates"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.settings.document-templates')"
            @click="navigateTo('cms.settings.document-templates.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.settings.pricing-rules') && hasFabrication"
            icon="CalculatorIcon"
            label="Pricing Rules"
            route-name="cms.settings.pricing-rules"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.settings.pricing-rules')"
            @click="navigateTo('cms.settings.pricing-rules')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.settings.industry-presets.index')"
            icon="SwatchIcon"
            label="Industry Presets"
            route-name="cms.settings.industry-presets"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.settings.industry-presets')"
            @click="navigateTo('cms.settings.industry-presets.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.settings.email.index')"
            icon="EnvelopeIcon"
            label="Email Settings"
            route-name="cms.settings.email"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.settings.email')"
            @click="navigateTo('cms.settings.email.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.settings.sms.index')"
            icon="DevicePhoneMobileIcon"
            label="SMS Settings"
            route-name="cms.settings.sms"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.settings.sms')"
            @click="navigateTo('cms.settings.sms.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.settings.currency.index')"
            icon="BanknotesIcon"
            label="Currency"
            route-name="cms.settings.currency"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.settings.currency')"
            @click="navigateTo('cms.settings.currency.index')"
          />

          <NavItem
            v-if="shouldShowNavItem('cms.security.settings')"
            icon="ShieldCheckIcon"
            label="Security"
            route-name="cms.security"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.security')"
            @click="navigateTo('cms.security.settings')"
          />
        </nav>
      </div>

      <!-- User Profile - Fixed at Bottom -->
      <div class="flex-shrink-0 border-t border-gray-100 p-4 bg-gradient-to-t from-gray-50 to-white sticky bottom-0 z-10 backdrop-blur-sm">
        <div v-if="!sidebarCollapsed" class="flex items-center gap-3 p-2 rounded-xl hover:bg-white/80 transition-all duration-200 cursor-pointer group">
          <div class="w-10 h-10 bg-gradient-to-br from-gray-200 to-gray-300 rounded-xl flex items-center justify-center flex-shrink-0 ring-2 ring-gray-100 group-hover:ring-blue-100 transition-all">
            <UserCircleIcon class="h-7 w-7 text-gray-600 group-hover:text-blue-600 transition-colors" aria-hidden="true" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-900 truncate">{{ user?.name }}</p>
            <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
          </div>
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
        <div v-else class="flex justify-center">
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
                <button @click="navigateTo('cms.dashboard')" class="text-gray-500 hover:text-gray-700 transition">
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
                      <Link :href="route('cms.notifications.index')" class="text-xs text-blue-600 hover:text-blue-700 font-medium">
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
                        :href="route('cms.settings.index')"
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
                    @click="navigateTo('cms.dashboard'); mobileMenuOpen = false"
                    :class="[
                      isActive('cms.dashboard') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50',
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
</template>

<script setup lang="ts">
import { ref, provide, computed, onMounted, onUnmounted } from 'vue'
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
} from '@heroicons/vue/24/outline'
import SlideOver from '@/components/CMS/SlideOver.vue'
import JobForm from '@/components/CMS/Forms/JobForm.vue'
import CustomerForm from '@/components/CMS/Forms/CustomerForm.vue'
import InvoiceForm from '@/components/CMS/Forms/InvoiceForm.vue'
import ExpenseForm from '@/components/CMS/Forms/ExpenseForm.vue'
import NavItem from '@/components/CMS/NavItem.vue'
import PwaInstallPrompt from '@/components/CMS/PwaInstallPrompt.vue'
import { useCMSSlideOver } from '@/composables/useCMSSlideOver'

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
const slideOver = useCMSSlideOver()

// Quick search functionality
const searchQuery = ref('')
const allNavItems = ref([
  { label: 'Dashboard', route: 'cms.dashboard', keywords: ['home', 'overview', 'main'] },
  { label: 'Jobs', route: 'cms.jobs', keywords: ['work', 'projects', 'tasks'] },
  { label: 'Measurements', route: 'cms.measurements', keywords: ['measure', 'dimensions', 'fabrication'] },
  { label: 'Customers', route: 'cms.customers', keywords: ['clients', 'contacts'] },
  { label: 'Invoices', route: 'cms.invoices', keywords: ['bills', 'billing', 'payments'] },
  { label: 'Payments', route: 'cms.payments', keywords: ['transactions', 'money'] },
  { label: 'Reports', route: 'cms.reports', keywords: ['analytics', 'statistics'] },
  { label: 'Operations', route: 'cms.analytics.operations', keywords: ['analytics', 'performance'] },
  { label: 'Finance', route: 'cms.analytics.finance', keywords: ['analytics', 'financial', 'money'] },
  { label: 'Expenses', route: 'cms.expenses', keywords: ['costs', 'spending'] },
  { label: 'Quotations', route: 'cms.quotations', keywords: ['quotes', 'estimates'] },
  { label: 'Inventory', route: 'cms.inventory', keywords: ['stock', 'products', 'items'] },
  { label: 'Materials', route: 'cms.materials', keywords: ['materials', 'supplies', 'stock', 'aluminium', 'glass', 'profiles'] },
  { label: 'Assets', route: 'cms.assets', keywords: ['equipment', 'property'] },
  { label: 'Payroll', route: 'cms.payroll', keywords: ['salary', 'wages', 'pay'] },
  { label: 'Workers', route: 'cms.payroll.workers', keywords: ['employees', 'staff', 'team'] },
  // Construction Modules
  { label: 'Projects', route: 'cms.projects', keywords: ['construction', 'sites', 'building'] },
  { label: 'Subcontractors', route: 'cms.subcontractors', keywords: ['contractors', 'vendors', 'suppliers'] },
  { label: 'Equipment', route: 'cms.equipment', keywords: ['machinery', 'tools', 'assets'] },
  { label: 'Labour Crews', route: 'cms.labour.crews', keywords: ['teams', 'workers', 'crew'] },
  { label: 'Timesheets', route: 'cms.labour.timesheets', keywords: ['hours', 'time tracking', 'attendance'] },
  { label: 'BOQ', route: 'cms.boq', keywords: ['bill of quantities', 'estimates', 'quantities'] },
  { label: 'Progress Billing', route: 'cms.progress-billing', keywords: ['certificates', 'billing', 'retention'] },
  // Payroll Configuration
  { label: 'Allowance Types', route: 'cms.payroll.configuration.allowance-types', keywords: ['benefits', 'perks'] },
  { label: 'Deduction Types', route: 'cms.payroll.configuration.deduction-types', keywords: ['taxes', 'withholding'] },
  { label: 'Departments', route: 'cms.departments', keywords: ['divisions', 'units'] },
  { label: 'Leave Management', route: 'cms.leave', keywords: ['vacation', 'time off', 'absence'] },
  { label: 'Shifts', route: 'cms.shifts', keywords: ['schedule', 'roster', 'timing'] },
  { label: 'Attendance', route: 'cms.attendance', keywords: ['clock in', 'presence', 'tracking'] },
  { label: 'Overtime', route: 'cms.overtime', keywords: ['extra hours', 'ot'] },
  { label: 'Recruitment', route: 'cms.recruitment', keywords: ['hiring', 'jobs', 'candidates'] },
  { label: 'Onboarding', route: 'cms.hrms-onboarding', keywords: ['new hire', 'orientation'] },
  { label: 'Performance', route: 'cms.performance', keywords: ['reviews', 'goals', 'appraisal'] },
  { label: 'Training', route: 'cms.training', keywords: ['learning', 'development', 'courses'] },
  { label: 'HR Reports', route: 'cms.hr-reports', keywords: ['human resources', 'analytics'] },
  { label: 'Time Tracking', route: 'cms.time-tracking', keywords: ['hours', 'timesheet'] },
  { label: 'Recurring Invoices', route: 'cms.recurring-invoices', keywords: ['subscription', 'repeat'] },
  { label: 'Approvals', route: 'cms.approvals', keywords: ['pending', 'review', 'authorize'] },
  { label: 'Chart of Accounts', route: 'cms.accounting', keywords: ['accounting', 'ledger', 'books'] },
  // Settings
  { label: 'Company Settings', route: 'cms.settings.index', keywords: ['configuration', 'preferences', 'setup', 'company'] },
  { label: 'Pricing Rules', route: 'cms.settings.pricing-rules', keywords: ['rates', 'pricing', 'fabrication', 'costs'] },
  { label: 'Industry Presets', route: 'cms.settings.industry-presets.index', keywords: ['templates', 'industry', 'setup'] },
  { label: 'Email Settings', route: 'cms.settings.email.index', keywords: ['mail', 'smtp', 'notifications'] },
  { label: 'SMS Settings', route: 'cms.settings.sms.index', keywords: ['text', 'messages', 'notifications'] },
  { label: 'Currency', route: 'cms.settings.currency.index', keywords: ['money', 'exchange', 'rates'] },
  { label: 'Security', route: 'cms.security.settings', keywords: ['password', 'authentication', 'access'] },
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

// PWA update notification — listen for the custom event fired by usePWA.ts
// instead of using the native browser confirm() dialog
onMounted(() => {
  window.addEventListener('pwa:update-available', handlePwaUpdate)
})

onUnmounted(() => {
  window.removeEventListener('pwa:update-available', handlePwaUpdate)
})

function handlePwaUpdate(event: Event) {
  const { apply } = (event as CustomEvent).detail
  window.dispatchEvent(new CustomEvent('bizboost:toast', {
    detail: {
      type: 'info',
      title: 'Update available',
      message: 'A new version is ready.',
      duration: 0, // keep visible until dismissed
      action: {
        label: 'Reload',
        onClick: apply,
      },
    }
  }))
}

// Access global shared data from Inertia
const company = computed(() => page.props.company)
const user = computed(() => page.props.auth?.user)
const cmsUser = computed(() => page.props.cmsUser)
const userCompanies = computed(() => (page.props as any).userCompanies ?? [])

// Company switcher state
const showCompanySwitcher = ref(false)

function switchCompany(companyId: number) {
  showCompanySwitcher.value = false
  router.post(route('cms.switch-company'), { company_id: companyId })
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
    router.post(route('cms.notifications.mark-read', notification.id), {}, {
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
  if (routeName === 'cms.dashboard') {
    return routePath === '/cms' || routePath === '/cms/dashboard'
  }
  
  // Convert route name to path pattern (e.g., 'cms.jobs' -> '/cms/jobs')
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
