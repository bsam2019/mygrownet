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
                Beta access now open
              </div>
              <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight leading-[1.08]">
                Amplify your
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-300 to-indigo-300">digital presence</span>
              </h1>
              <p class="mt-4 text-base sm:text-lg text-slate-300/80 leading-relaxed max-w-xl">
                One integrated platform for advertising, e-commerce, bookings, and network growth. Your entire business ecosystem in a single dashboard.
              </p>
              <div class="mt-8 flex flex-col sm:flex-row gap-4">
                <button @click="showLoginModal = true" class="group inline-flex items-center justify-center px-7 py-3 bg-white text-slate-900 rounded-xl font-semibold text-sm hover:bg-slate-100 transition-all duration-200 shadow-2xl shadow-violet-500/10">
                  Get started free
                  <svg class="ml-2 w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </button>
                <a href="#how-it-works" class="inline-flex items-center justify-center px-7 py-3 border border-white/15 text-slate-200 rounded-xl font-medium text-sm hover:bg-white/5 transition-all duration-200">
                  Watch demo
                  <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
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
                    <div class="text-xs text-slate-300">Trusted by 2,000+ businesses</div>
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
                        <div class="text-sm font-semibold text-white">₱284k</div>
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
            <h2 class="mt-4 text-3xl sm:text-4xl font-bold text-slate-900 tracking-tight">Built for real<br>business results</h2>
            <p class="mt-4 text-lg text-slate-500 leading-relaxed">Every feature is designed to help you earn more, work less, and stay in control.</p>
          </div>
          <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div class="group rounded-2xl border border-slate-100 bg-white p-6 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center mb-5 text-white shadow-lg shadow-violet-200/50">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25-2.25M12 13.875V7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <h3 class="text-base font-semibold text-slate-900">Integrated Ecosystem</h3>
              <p class="mt-2 text-sm text-slate-500 leading-relaxed">All your tools work as one — advertising, e-commerce, bookings, and finance in a unified dashboard.</p>
            </div>
            <div class="group rounded-2xl border border-slate-100 bg-white p-6 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center mb-5 text-white shadow-lg shadow-violet-200/50">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
              </div>
              <h3 class="text-base font-semibold text-slate-900">Ad Campaigns</h3>
              <p class="mt-2 text-sm text-slate-500 leading-relaxed">Run AI-optimized campaigns across Meta and Google with real-time performance tracking.</p>
            </div>
            <div class="group rounded-2xl border border-slate-100 bg-white p-6 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center mb-5 text-white shadow-lg shadow-violet-200/50">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg>
              </div>
              <h3 class="text-base font-semibold text-slate-900">Network Growth</h3>
              <p class="mt-2 text-sm text-slate-500 leading-relaxed">Referral tracking, multi-level commissions, and sponsor management built right in.</p>
            </div>
            <div class="group rounded-2xl border border-slate-100 bg-white p-6 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center mb-5 text-white shadow-lg shadow-violet-200/50">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
              </div>
              <h3 class="text-base font-semibold text-slate-900">E-Commerce</h3>
              <p class="mt-2 text-sm text-slate-500 leading-relaxed">Full online store with product management, payments, order tracking, and delivery.</p>
            </div>
            <div class="group rounded-2xl border border-slate-100 bg-white p-6 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center mb-5 text-white shadow-lg shadow-violet-200/50">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-6.75a.75.75 0 01.75-.75h6a.75.75 0 01.75.75V21m-9-21h18M9 3v2.25M15 3v2.25M3 6v15a2.25 2.25 0 002.25 2.25h13.5A2.25 2.25 0 0021 21V6"/></svg>
              </div>
              <h3 class="text-base font-semibold text-slate-900">Bookings</h3>
              <p class="mt-2 text-sm text-slate-500 leading-relaxed">Manage accommodations, event spaces, and reservations from a single dashboard.</p>
            </div>
            <div class="group rounded-2xl border border-slate-100 bg-white p-6 shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center mb-5 text-white shadow-lg shadow-violet-200/50">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
              </div>
              <h3 class="text-base font-semibold text-slate-900">Analytics</h3>
              <p class="mt-2 text-sm text-slate-500 leading-relaxed">Real-time dashboards, custom reports, and actionable insights to guide your decisions.</p>
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
            <h2 class="mt-4 text-3xl sm:text-4xl font-bold text-slate-900 tracking-tight">Three steps to<br>get started</h2>
            <p class="mt-4 text-lg text-slate-500 leading-relaxed">No technical skills required. No complicated setup.</p>
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
                <h3 class="text-lg font-semibold text-slate-900">Choose your tools</h3>
                <p class="mt-3 text-sm text-slate-500 leading-relaxed">Activate the apps you need — store, ads, bookings, or all of them.</p>
              </div>
              <div class="relative p-8 rounded-2xl border border-slate-100 bg-white shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                <span class="absolute top-6 right-6 text-5xl font-bold text-slate-50">03</span>
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center text-white text-lg font-bold mb-6 shadow-lg shadow-violet-200/50">3</div>
                <h3 class="text-lg font-semibold text-slate-900">Start growing</h3>
                <p class="mt-3 text-sm text-slate-500 leading-relaxed">Launch campaigns, fulfill orders, manage reservations — all from one place.</p>
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
          <h2 class="mt-4 text-3xl sm:text-4xl font-bold text-white tracking-tight">Ready to amplify your business?</h2>
          <p class="mt-4 text-lg text-slate-400 leading-relaxed">Join thousands of users already building their digital ecosystem with MyGrowNet. No commitment. No risk.</p>
          <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
            <button @click="showLoginModal = true" class="group inline-flex items-center justify-center px-8 py-3.5 bg-white text-slate-900 rounded-xl font-semibold text-sm hover:bg-slate-100 transition-all duration-200 shadow-2xl shadow-violet-500/10">
              Create free account
              <svg class="ml-2 w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </button>
            <a href="#features" class="inline-flex items-center justify-center px-8 py-3.5 border border-white/15 text-slate-300 rounded-xl font-medium text-sm hover:bg-white/5 transition-all duration-200">
              Learn more
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
