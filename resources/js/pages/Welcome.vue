<template>
  <div class="min-h-screen bg-white">
    <Navigation :useModalLogin="true" @login-click="showLoginModal = true" @register-click="showLoginModal = true" />

    <main>
      <!-- Hero -->
      <section class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-950 to-violet-950 text-white">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,_var(--tw-gradient-stops))] from-violet-400/10 via-transparent to-transparent"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_right,_var(--tw-gradient-stops))] from-indigo-400/10 via-transparent to-transparent"></div>
        <div class="absolute top-1/4 left-1/3 w-[40rem] h-[40rem] bg-violet-500/20 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 w-80 h-80 bg-indigo-500/10 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex flex-col lg:flex-row items-center gap-10 lg:gap-16 pt-14 pb-14 sm:pt-16 sm:pb-20 lg:pt-20 lg:pb-28">
            <div class="flex-1 max-w-2xl">
              <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/5 border border-white/10 rounded-full text-xs text-violet-200 mb-6 backdrop-blur-sm">
                <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
                Learn. Earn. Grow.
              </div>
              <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight leading-[1.08]">
                Your Complete
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-300 to-indigo-300">Business Growth Platform</span>
              </h1>
              <p class="mt-4 text-base sm:text-lg text-slate-300/80 leading-relaxed max-w-xl">
                One integrated ecosystem combining SME tools, e-commerce, network marketing, and wealth-building — designed to help you learn, earn, and grow.
              </p>
              <div class="mt-8 flex flex-col sm:flex-row gap-4">
                <button @click="showLoginModal = true" class="group inline-flex items-center justify-center px-7 py-3 bg-white text-slate-900 rounded-xl font-semibold text-sm hover:bg-slate-100 transition-all duration-200 shadow-2xl shadow-violet-500/10">
                  Start your journey
                  <svg class="ml-2 w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </button>
                <a href="#how-it-works" class="inline-flex items-center justify-center px-7 py-3 border border-white/15 text-slate-200 rounded-xl font-medium text-sm hover:bg-white/5 transition-all duration-200">
                  How it works
                  <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                </a>
              </div>
              <div class="mt-8 flex flex-wrap items-center gap-6 text-sm text-slate-400">
                <div class="flex items-center gap-2">
                  <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                  No credit card
                </div>
                <div class="flex items-center gap-2">
                  <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                  Cancel anytime
                </div>
                <div class="flex items-center gap-2">
                  <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                  All features included
                </div>
              </div>
            </div>

            <div class="flex-1 w-full max-w-lg">
              <div class="relative">
                <div class="absolute -inset-4 bg-gradient-to-r from-violet-500/20 to-indigo-500/20 rounded-3xl blur-xl"></div>
                <div class="relative bg-gradient-to-br from-white/[0.08] to-white/[0.02] border border-white/10 rounded-2xl p-8 backdrop-blur-sm">
                  <div class="flex items-center gap-3 mb-8">
                    <div class="flex -space-x-2">
                      <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-400 to-indigo-400 border-2 border-slate-800"></div>
                      <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-blue-400 border-2 border-slate-800"></div>
                      <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-400 to-teal-400 border-2 border-slate-800"></div>
                    </div>
                    <div class="text-xs text-slate-300">Trusted by 2,000+ entrepreneurs</div>
                  </div>
                  <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl border border-white/5">
                      <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center text-white text-sm font-bold">B</div>
                        <div>
                          <div class="text-sm font-medium text-white">BizBoost Ads</div>
                          <div class="text-xs text-slate-400">Campaign performance</div>
                        </div>
                      </div>
                      <div class="text-right">
                        <div class="text-sm font-semibold text-emerald-400">+124%</div>
                        <div class="text-xs text-slate-400">ROI</div>
                      </div>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl border border-white/5">
                      <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white text-sm font-bold">M</div>
                        <div>
                          <div class="text-sm font-medium text-white">GrowMart</div>
                          <div class="text-xs text-slate-400">Online store</div>
                        </div>
                      </div>
                      <div class="text-right">
                        <div class="text-sm font-semibold text-white">K284k</div>
                        <div class="text-xs text-slate-400">Revenue</div>
                      </div>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl border border-white/5">
                      <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center text-white text-sm font-bold">Z</div>
                        <div>
                          <div class="text-sm font-medium text-white">ZamStay</div>
                          <div class="text-xs text-slate-400">Bookings</div>
                        </div>
                      </div>
                      <div class="text-right">
                        <div class="text-sm font-semibold text-white">47</div>
                        <div class="text-xs text-slate-400">Reservations</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Stats -->
        <div class="relative border-t border-white/5">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
              <div v-for="(stat, key) in stats" :key="key" class="text-center">
                <div class="text-2xl sm:text-3xl font-bold text-white">{{ stat.value }}</div>
                <div class="mt-1 text-sm text-slate-400">{{ stat.label }}</div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Divider -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="w-full h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>
      </div>

      <!-- Apps -->
      <section v-if="featuredApps && featuredApps.length > 0" class="py-20 sm:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="max-w-xl mb-14">
            <div class="w-12 h-1 bg-gradient-to-r from-violet-500 to-indigo-500 rounded-full mb-6"></div>
            <span class="text-xs font-semibold text-violet-600 uppercase tracking-widest">Ecosystem</span>
            <h2 class="mt-4 text-3xl sm:text-4xl font-bold text-slate-900 tracking-tight">Everything you need,<br>one platform</h2>
            <p class="mt-4 text-lg text-slate-500 leading-relaxed">Each tool integrates seamlessly with the others, so your data flows where you need it.</p>
          </div>
          <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div v-for="app in featuredApps" :key="app.slug"
              class="group relative rounded-2xl border border-slate-100 bg-white p-6 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center text-white text-lg font-bold mb-5 shadow-lg shadow-violet-200/50">
                {{ app.name.charAt(0) }}
              </div>
              <h3 class="text-base font-semibold text-slate-900">{{ app.name }}</h3>
              <p class="mt-2 text-sm text-slate-500 leading-relaxed">{{ app.description }}</p>
              <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-violet-500 to-indigo-500 scale-x-0 group-hover:scale-x-100 transition-transform duration-300 rounded-b-2xl"></div>
            </div>
          </div>
        </div>
      </section>

      <!-- Features -->
      <section id="features" class="relative py-20 sm:py-28 bg-slate-50 overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-violet-100/20 via-transparent to-transparent pointer-events-none"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="max-w-xl mb-14">
            <div class="w-12 h-1 bg-gradient-to-r from-violet-500 to-indigo-500 rounded-full mb-6"></div>
            <span class="text-xs font-semibold text-violet-600 uppercase tracking-widest">Capabilities</span>
            <h2 class="mt-4 text-3xl sm:text-4xl font-bold text-slate-900 tracking-tight">Built around three<br>core pillars</h2>
            <p class="mt-4 text-lg text-slate-500 leading-relaxed">Learn new skills, earn real income, and grow your business — all on one platform.</p>
          </div>
          <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div class="group rounded-2xl border border-slate-100 bg-white p-6 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center mb-5 text-white shadow-lg shadow-emerald-200/50">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/></svg>
              </div>
              <h3 class="text-base font-semibold text-slate-900">Learn</h3>
              <p class="mt-2 text-sm text-slate-500 leading-relaxed">Access courses, tutorials, and resources to build digital and entrepreneurial skills at your own pace.</p>
            </div>
            <div class="group rounded-2xl border border-slate-100 bg-white p-6 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center mb-5 text-white shadow-lg shadow-amber-200/50">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
              </div>
              <h3 class="text-base font-semibold text-slate-900">Earn</h3>
              <p class="mt-2 text-sm text-slate-500 leading-relaxed">Generate income through commissions, network marketing, e-commerce sales, and investment opportunities.</p>
            </div>
            <div class="group rounded-2xl border border-slate-100 bg-white p-6 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center mb-5 text-white shadow-lg shadow-violet-200/50">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605"/></svg>
              </div>
              <h3 class="text-base font-semibold text-slate-900">Grow</h3>
              <p class="mt-2 text-sm text-slate-500 leading-relaxed">Scale your business with SME tools — inventory, invoicing, websites, document management, and more.</p>
            </div>
            <div class="group rounded-2xl border border-slate-100 bg-white p-6 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center mb-5 text-white shadow-lg shadow-violet-200/50">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25-2.25M12 13.875V7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <h3 class="text-base font-semibold text-slate-900">Integrated Ecosystem</h3>
              <p class="mt-2 text-sm text-slate-500 leading-relaxed">All your tools work as one — inventory, e-commerce, bookings, finance, and content in a unified dashboard.</p>
            </div>
            <div class="group rounded-2xl border border-slate-100 bg-white p-6 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center mb-5 text-white shadow-lg shadow-violet-200/50">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
              </div>
              <h3 class="text-base font-semibold text-slate-900">Multi-Vendor Marketplace</h3>
              <p class="mt-2 text-sm text-slate-500 leading-relaxed">Full e-commerce with product management, payments, order tracking, and delivery — plus vendor commissions.</p>
            </div>
            <div class="group rounded-2xl border border-slate-100 bg-white p-6 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center mb-5 text-white shadow-lg shadow-violet-200/50">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg>
              </div>
              <h3 class="text-base font-semibold text-slate-900">Network Marketing</h3>
              <p class="mt-2 text-sm text-slate-500 leading-relaxed">Referral tracking, multi-level commissions, sponsor management, and team-building tools built right in.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- How It Works -->
      <section id="how-it-works" class="py-20 sm:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="max-w-xl mb-14">
            <div class="w-12 h-1 bg-gradient-to-r from-violet-500 to-indigo-500 rounded-full mb-6"></div>
            <span class="text-xs font-semibold text-violet-600 uppercase tracking-widest">Getting started</span>
            <h2 class="mt-4 text-3xl sm:text-4xl font-bold text-slate-900 tracking-tight">Your journey in<br>three steps</h2>
            <p class="mt-4 text-lg text-slate-500 leading-relaxed">No technical skills required. Start building your digital wealth today.</p>
          </div>
          <div class="relative">
            <div class="absolute top-12 left-0 right-0 h-px bg-gradient-to-r from-violet-200 via-indigo-200 to-transparent hidden sm:block"></div>
            <div class="grid sm:grid-cols-3 gap-8">
              <div class="relative p-8 rounded-2xl border border-slate-100 bg-white shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                <span class="absolute top-6 right-6 text-5xl font-bold text-slate-50">01</span>
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center text-white text-lg font-bold mb-6 shadow-lg shadow-violet-200/50">1</div>
                <h3 class="text-lg font-semibold text-slate-900">Create your account</h3>
                <p class="mt-3 text-sm text-slate-500 leading-relaxed">Sign up in under a minute. No credit card required.</p>
              </div>
              <div class="relative p-8 rounded-2xl border border-slate-100 bg-white shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                <span class="absolute top-6 right-6 text-5xl font-bold text-slate-50">02</span>
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center text-white text-lg font-bold mb-6 shadow-lg shadow-violet-200/50">2</div>
                <h3 class="text-lg font-semibold text-slate-900">Explore the ecosystem</h3>
                <p class="mt-3 text-sm text-slate-500 leading-relaxed">Activate the apps you need — store, inventory, finance, bookings, or all of them.</p>
              </div>
              <div class="relative p-8 rounded-2xl border border-slate-100 bg-white shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                <span class="absolute top-6 right-6 text-5xl font-bold text-slate-50">03</span>
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center text-white text-lg font-bold mb-6 shadow-lg shadow-violet-200/50">3</div>
                <h3 class="text-lg font-semibold text-slate-900">Start earning</h3>
                <p class="mt-3 text-sm text-slate-500 leading-relaxed">Sell products, earn commissions, build your network, and grow your wealth — all from one place.</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Social Proof -->
      <section class="relative py-20 sm:py-28 bg-slate-50 overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,_var(--tw-gradient-stops))] from-violet-100/30 via-transparent to-transparent pointer-events-none"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="max-w-xl mb-14">
            <div class="w-12 h-1 bg-gradient-to-r from-violet-500 to-indigo-500 rounded-full mb-6"></div>
            <span class="text-xs font-semibold text-violet-600 uppercase tracking-widest">Testimonials</span>
            <h2 class="mt-4 text-3xl sm:text-4xl font-bold text-slate-900 tracking-tight">What our users<br>are saying</h2>
          </div>
          <div class="grid sm:grid-cols-3 gap-5">
            <div class="rounded-2xl border border-slate-100 bg-white p-8 shadow-sm hover:shadow-lg transition-all duration-300 relative">
              <div class="absolute -top-3 left-8 w-6 h-6 bg-violet-500 rounded-full flex items-center justify-center text-white text-xs">&#8220;</div>
              <div class="flex items-center gap-1 mb-5">
                <svg v-for="i in 5" :key="i" class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
              </div>
              <p class="text-sm text-slate-600 leading-relaxed italic">"MyGrowNet transformed how we manage our online presence. Having ads, store, and bookings in one place saves us hours every day."</p>
              <div class="mt-6 pt-5 border-t border-slate-100">
                <div class="text-sm font-semibold text-slate-900">Mutale Banda</div>
                <div class="text-xs text-slate-400">Small Business Owner</div>
              </div>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-8 shadow-sm hover:shadow-lg transition-all duration-300 relative">
              <div class="absolute -top-3 left-8 w-6 h-6 bg-violet-500 rounded-full flex items-center justify-center text-white text-xs">&#8220;</div>
              <div class="flex items-center gap-1 mb-5">
                <svg v-for="i in 5" :key="i" class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
              </div>
              <p class="text-sm text-slate-600 leading-relaxed italic">"The integrated ecosystem is a game-changer. I can track ad performance, manage orders, and check my commission payouts from the same screen."</p>
              <div class="mt-6 pt-5 border-t border-slate-100">
                <div class="text-sm font-semibold text-slate-900">Chilufya Mwila</div>
                <div class="text-xs text-slate-400">Online Seller</div>
              </div>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-8 shadow-sm hover:shadow-lg transition-all duration-300 relative">
              <div class="absolute -top-3 left-8 w-6 h-6 bg-violet-500 rounded-full flex items-center justify-center text-white text-xs">&#8220;</div>
              <div class="flex items-center gap-1 mb-5">
                <svg v-for="i in 5" :key="i" class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
              </div>
              <p class="text-sm text-slate-600 leading-relaxed italic">"Setting up my store and first ad campaign took less than an hour. The network marketing tools are incredibly powerful for growing a team."</p>
              <div class="mt-6 pt-5 border-t border-slate-100">
                <div class="text-sm font-semibold text-slate-900">Mwansa Tembo</div>
                <div class="text-xs text-slate-400">Entrepreneur</div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- CTA -->
      <section class="relative overflow-hidden py-20 sm:py-28">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-indigo-950 to-violet-950"></div>
        <div class="absolute top-0 left-1/3 w-[30rem] h-[30rem] bg-violet-500/10 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <span class="text-xs font-semibold text-violet-300 uppercase tracking-widest">Get started</span>
          <h2 class="mt-4 text-3xl sm:text-4xl font-bold text-white tracking-tight">Ready to grow your business?</h2>
          <p class="mt-4 text-lg text-slate-400 leading-relaxed">Join thousands of users already learning, earning, and growing with MyGrowNet. No commitment. No risk.</p>
          <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
            <button @click="showLoginModal = true" class="group inline-flex items-center justify-center px-8 py-3.5 bg-white text-slate-900 rounded-xl font-semibold text-sm hover:bg-slate-100 transition-all duration-200 shadow-2xl shadow-violet-500/10">
              Start free
              <svg class="ml-2 w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </button>
            <a href="#how-it-works" class="inline-flex items-center justify-center px-8 py-3.5 border border-white/15 text-slate-300 rounded-xl font-medium text-sm hover:bg-white/5 transition-all duration-200">
              How it works
            </a>
          </div>
        </div>
      </section>
    </main>

    <Footer />
    <LoginModal :show="showLoginModal" @close="showLoginModal = false" />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import Navigation from '@/components/custom/Navigation.vue';
import Footer from '@/components/custom/Footer.vue';
import LoginModal from '@/components/LoginModal.vue';

const showLoginModal = ref(false);

defineProps({
  featuredApps: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) }
});
</script>
