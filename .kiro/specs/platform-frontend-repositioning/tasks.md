# Implementation Tasks

## Phase 1: Foundation & Setup (Week 1)

### 1.1 Create Terminology Mapping System
- [x] Create `resources/js/composables/useSafeTerminology.ts` composable









- [x] Define terminology mapping object (earnings→store credit, commissions→referral rewards, etc.)






- [x] Create helper functions for text replacement





- [x] Add TypeScript types for terminology keys






















- _Requirements: 13_

### 1.2 Update Main Navigation Component
- [x] Update `resources/js/components/custom/Navigation.vue`





- [x] Simplify to 7 main items: Home, About, Join/Starter Kits, Marketplace, Training, Rewards & Loyalty, Contact

- [x] Remove complex dropdowns (Investment, Commission, Matrix)

- [x] Update mobile navigation to match

- [x] Test responsive behavior

- _Requirements: 1_

### 1.3 Create New Public Pages Structure
- [x] Create `resources/js/Pages/About.vue` (if not exists, otherwise update)








- [x] Create `resources/js/Pages/StarterKits/Index.vue` route structure









- [x] Create `resources/js/Pages/Training/Index.vue` route structure





- [x] Create `resources/js/Pages/Rewards/Index.vue` route structure





- [x] Create `resources/js/Pages/Roadmap/Index.vue`





- [x] Create `resources/js/Pages/LegalAssurance/Index.vue`










- _Requirements: 3, 4, 7, 8, 9_

### 1.4 Update Laravel Routes
- [x] Update `routes/web.php` with new public routes




- [x] Set `/home` as default authenticated redirect

- [x] Add 301 redirects for old URLs

- [x] Update route names for consistency

- [x] Test all route changes


- _Requirements: 1_

### 1.5 Enhance Home Hub Component
- [x] Update `resources/js/Pages/HomeHub/Index.vue`







- [x] Add welcome banner section

- [x] Create "Primary Apps" section with featured modules

- [x] Create "Additional Services" section

- [x] Add product-focused quick stats

- [x] Update module descriptions to emphasize business tools

- [x] Reorder modules (BizBoost, GrowFinance, GrowBiz first)


- _Requirements: 11_



## Phase 2: Public Marketing Pages Redesign (Week 2)

### 2.1 Update Hero Component (Homepage)
- [x] Update `resources/js/components/custom/Hero.vue`
- [x] Change headline to "Empower Your Business with MyGrowNet"
- [x] Update subtitle to emphasize "business empowerment and e-commerce platform"
- [x] Replace trust indicators (remove network size, 7 levels, 6 income streams)
- [x] Add product-focused metrics (e.g., "500+ Training Modules", "3 Business Tools", "Growing Marketplace")
- [x] Update CTAs to "Join Now" and "Explore Marketplace"
- [x] Remove or minimize network-building card
- _Requirements: 2_

### 2.2 Update Feature Highlights Component (Homepage)
- [x] Update `resources/js/components/custom/FeatureHighlights.vue`
- [x] Removed "7-Level Network" feature entirely
- [x] Added "Business Tools" feature (BizBoost, GrowFinance, productivity tools)
- [x] Added "Marketplace" feature (products and services)
- [x] Maintained: Venture Builder, BGF, Learning & Development, Profit Sharing
- [x] Visual hierarchy now emphasizes products over network
- _Requirements: 2_

### 2.3 Rewrite How It Works Component (Homepage)
- [x] Update `resources/js/components/custom/HowItWorks.vue`
- [x] Reduced from 4 steps to 3 steps
- [x] Step 1: "Choose Your Subscription" - Select starter kit
- [x] Step 2: "Access Business Tools" - Use BizBoost, GrowFinance, etc.
- [x] Step 3: "Grow Your Business" - Apply learning and tools
- [x] Removed entire "6 Ways to Earn" section
- [x] Updated subtitle to focus on business tools and training
- _Requirements: 2_

### 2.4 Create New Homepage Sections
- [ ] Create `resources/js/components/custom/WhatsAvailableNow.vue`
- [ ] Create `resources/js/components/custom/ComingSoon.vue`
- [ ] Create `resources/js/components/custom/FoundersMessage.vue`
- [x] Update `resources/js/Pages/Welcome.vue` to remove network marketing components
- [x] Move network marketing content to dedicated Referral Program page
- [ ] Ensure proper section ordering
- _Requirements: 2_

### 2.5 Update Success Stories Component (Homepage)
- [x] Update `resources/js/components/custom/SuccessStories.vue`
- [x] Updated header to focus on tools and training
- [x] Replaced all 3 testimonials with product/skill-focused stories
- [x] Thomas M.: BizBoost marketing automation (40% engagement increase)
- [x] Sarah B.: Financial literacy training and GrowFinance funding
- [x] James K.: Practical training and business operations improvement
- [x] Removed all earning claims and network references
- _Requirements: 2_

### 2.6 Update Call to Action Component (Homepage)
- [x] Update `resources/js/components/custom/CallToAction.vue`
- [x] Updated headline: "Ready to Empower Your Business?"
- [x] Primary CTA: "Get Started Today" → links to registration
- [x] Secondary CTA: "View Starter Kits" → links to /starter-kits
- [x] Updated description to focus on business tools and training
- [x] Updated footer text: "Flexible subscriptions • Business tools included • Training & support"
- [x] Removed all network-building and earning language
- _Requirements: 2_



## Phase 3: Authenticated Frontend Pages Refactoring (Week 3)

### 3.1 Refactor Desktop Dashboard
- [ ] Update `resources/js/Pages/MyGrowNet/Dashboard.vue`
- [ ] Replace "Total Earnings" stat with "Store Credit" or "Rewards Balance"
- [ ] Replace "Team Size" with "Community Members" or move to secondary position
- [ ] Replace "Team Volume" with product-focused metric
- [ ] Move "Commission Levels" section to collapsible or secondary tab
- [ ] Move "Team Volume Visualization" to secondary tab
- [ ] Add "Recent Orders" section
- [ ] Add "Training Progress" section
- [ ] Add "Loyalty Points" section prominently
- [ ] Implement tab structure: Overview, Products, Learning, Community
- _Requirements: 11_

### 3.2 Refactor GrowNet Dashboard
- [ ] Update `resources/js/Pages/MyGrowNet/GrowNet.vue`
- [ ] Update quick stats to show: Store Credit, Loyalty Points, Training Progress, Orders
- [ ] Move "Commission Levels" to collapsible section (collapsed by default)
- [ ] Move "Team Volume" to collapsible section (collapsed by default)
- [ ] Prioritize "Learning Resources" section if user has starter kit
- [ ] Update "Quick Actions" to prioritize product usage over referrals
- [ ] Move "Refer a Friend" lower in priority
- [ ] Update all terminology using safe language
- _Requirements: 11_

### 3.3 Update Dashboard Navigation
- [ ] Update dashboard sidebar/navigation
- [ ] Reorder menu items: Profile, Orders, Training, Rewards, Community (last)
- [ ] Use safe terminology in all menu labels
- [ ] Update icons to match new emphasis
- _Requirements: 11_

### 3.4 Create Product-Focused Dashboard Components
- [ ] Create `RecentOrders.vue` component
- [ ] Create `TrainingProgress.vue` component
- [ ] Create `LoyaltyPointsSummary.vue` component
- [ ] Create `StarterKitStatus.vue` component
- [ ] Create `MarketplaceQuickAccess.vue` component
- [ ] Update existing components to use safe terminology
- _Requirements: 11_

### 3.5 Refactor Network Features to Secondary Position
- [ ] Create `CommunityTab.vue` component for network features
- [ ] Move referral tools to Community tab
- [ ] Move network overview to Community tab
- [ ] Update terminology: "Downline" → "Referred Members", "Upline" → "Referrer"
- [ ] Ensure network features remain functional but less prominent
- _Requirements: 11_



## Phase 4: New Pages & Content (Week 4)

### 4.1 Build About Us Page
- [ ] Update `resources/js/Pages/About.vue`
- [ ] Add section: "MyGrowNet is a private limited company"
- [ ] Add section: Focus areas (commerce, training, member empowerment)
- [ ] Add section: "No deposit-taking, no guaranteed payouts"
- [ ] Add section: "Rewards from real product activity and company revenue"
- [ ] Add mission statement emphasizing business empowerment
- [ ] Add team/company information
- [ ] Include legal disclaimers
- _Requirements: 3_

### 4.2 Build Join / Starter Kits Page
- [ ] Build `resources/js/Pages/StarterKits/Index.vue`
- [ ] Create starter kit comparison cards
- [ ] Add "What's Included" section with detailed list
- [ ] Add "Membership Benefits" section (immediate value focus)
- [ ] Add "Monthly Activity Requirements" section
- [ ] Add "How to Join" step-by-step guide
- [ ] Use safe language throughout (no profit claims)
- [ ] Add FAQ section
- [ ] Create backend controller if needed
- _Requirements: 4_

### 4.3 Build Marketplace Page Structure
- [ ] Build `resources/js/Pages/Marketplace/Index.vue`
- [ ] Create product category navigation
- [ ] Create product grid layout
- [ ] Add "More products coming soon" messaging
- [ ] Highlight MyGrowNet-branded products
- [ ] Add vendor registration section (marked as "beta")
- [ ] Create product search and filter functionality
- [ ] Ensure professional, e-commerce appearance
- _Requirements: 5_

### 4.4 Build Training/Learning Hub
- [ ] Build `resources/js/Pages/Training/Index.vue`
- [ ] Create course/module listing
- [ ] Add category filters (Financial Literacy, Business Management, etc.)
- [ ] Show member-only content clearly
- [ ] Add "Future Training Expansions" section
- [ ] Create progress tracking display
- [ ] Add value proposition section
- [ ] Link to existing training content
- _Requirements: 7_

### 4.5 Build Rewards & Loyalty Page
- [ ] Build `resources/js/Pages/Rewards/Index.vue`
- [ ] Add heading: "Rewards & Loyalty Benefits"
- [ ] Add section: "Earn points when you buy products"
- [ ] Add section: "Redeem points for discounts"
- [ ] Add section: "Member-only offers"
- [ ] Add section: "Store credit rewards"
- [ ] Add section: "Training benefits for active members"
- [ ] Ensure NO mention of cash rewards or fixed returns
- [ ] Add points calculator or estimator
- _Requirements: 6_

### 4.5b Build Referral Program Page (NEW)
- [x] Build `resources/js/Pages/ReferralProgram/Index.vue`
- [x] Add hero section explaining community growth program
- [x] Add "How It Works" section (3 steps)
- [x] Add 7-Level Community Network section with ProfessionalLevels component
- [x] Add network structure explanation (3×3 matrix, spillover, level advancement)
- [x] Include MyGrowNetStats component
- [x] Include RewardSystem component
- [x] Add important disclaimers section
- [x] Add CTA section with Join Now and Learn More buttons
- [x] Use safe terminology throughout
- [x] Update routes in web.php
- _Requirements: 2, 6_

### 4.6 Build Vision/Roadmap Page
- [ ] Build `resources/js/Pages/Roadmap/Index.vue`
- [ ] Create quarterly timeline visualization
- [ ] Q1: Marketplace + Membership + Starter Kits
- [ ] Q2: Training expansion + Vendor onboarding
- [ ] Q3: Venture Builder pilot
- [ ] Q4: Mobile app and scaled products
- [ ] Mark completed items
- [ ] Add "Subject to change" disclaimer
- _Requirements: 8_

### 4.7 Build Legal Assurance Section
- [ ] Build `resources/js/Pages/LegalAssurance/Index.vue` or add to About page
- [ ] Add statement: "MyGrowNet does not take deposits"
- [ ] Add statement: "No guaranteed returns"
- [ ] Add statement: "Rewards based on company activities and product sales"
- [ ] Add statement: "Members voluntarily join business projects under separate companies"
- [ ] Add compliance information
- [ ] Link from footer and About page
- _Requirements: 9_

### 4.8 Update Contact Page
- [ ] Update `resources/js/Pages/Contact.vue` if needed
- [ ] Ensure contact form is functional
- [ ] Add support information
- [ ] Add business hours
- [ ] Add physical address if applicable
- _Requirements: 1_



## Phase 5: Visual Consistency & Polish (Week 5)

### 5.1 Apply Visual Design System
- [ ] Create or update `resources/css/design-system.css`
- [ ] Define consistent button styles
- [ ] Define icon set and usage guidelines
- [ ] Define light color palette
- [ ] Define typography scale
- [ ] Define spacing system
- [ ] Apply to all updated components
- _Requirements: 12_

### 5.2 Update All Icons
- [ ] Audit all icon usage across updated pages
- [ ] Ensure consistent icon style (Heroicons)
- [ ] Update icon colors to match new palette
- [ ] Add aria-labels for accessibility
- [ ] Test icon visibility and clarity
- _Requirements: 12_

### 5.3 Implement Safe Language Globally
- [ ] Run global search for "earnings" → replace with "store credit" or "rewards balance"
- [ ] Run global search for "commissions" → replace with "referral rewards"
- [ ] Run global search for "wallet balance" → replace with "rewards balance"
- [ ] Run global search for "investment" → replace with "subscription"
- [ ] Run global search for "returns" → replace with "benefits"
- [ ] Run global search for "downline" → replace with "referred members"
- [ ] Run global search for "upline" → replace with "referrer"
- [ ] Update all email templates
- [ ] Update all notification messages
- _Requirements: 13_

### 5.4 Update Documentation
- [ ] Update platform description in README
- [ ] Update API documentation if applicable
- [ ] Create user guide for new navigation
- [ ] Update admin documentation
- [ ] Document terminology changes
- [ ] Create migration guide for existing users
- _Requirements: 14_

### 5.5 Mobile Responsiveness Testing
- [ ] Test all updated pages on mobile devices
- [ ] Test navigation on small screens
- [ ] Test Home Hub on mobile
- [ ] Test dashboard on mobile
- [ ] Fix any responsive issues
- [ ] Ensure touch targets are adequate
- _Requirements: 12_

### 5.6 Cross-Browser Testing
- [ ] Test on Chrome
- [ ] Test on Firefox
- [ ] Test on Safari
- [ ] Test on Edge
- [ ] Fix any browser-specific issues
- [ ] Test on different screen sizes
- _Requirements: 12_



## Phase 6: Testing & Quality Assurance

### 6.1 Content Audit
- [ ] Review all public pages for MLM/investment language
- [ ] Verify safe terminology usage throughout
- [ ] Check for income claims or guarantees
- [ ] Verify legal compliance of all statements
- [ ] Get legal review if possible
- [ ] Create content checklist for future updates
- _Requirements: 13, 14_

### 6.2 User Flow Testing
- [ ] Test new user registration flow
- [ ] Test first-time login experience (should land on Home Hub)
- [ ] Test navigation from Home Hub to all modules
- [ ] Test accessing MyGrowNet Core from Home Hub
- [ ] Test accessing network features
- [ ] Test marketplace browsing
- [ ] Test training access
- [ ] Verify all links work correctly
- _Requirements: 1, 11_

### 6.3 Accessibility Testing
- [ ] Run WAVE accessibility checker on all pages
- [ ] Test keyboard navigation
- [ ] Test screen reader compatibility
- [ ] Verify color contrast ratios (WCAG AA)
- [ ] Add alt text to all images
- [ ] Ensure form labels are proper
- [ ] Fix any accessibility issues found
- _Requirements: 12_

### 6.4 Performance Testing
- [ ] Run Lighthouse audits on all pages
- [ ] Optimize images
- [ ] Implement lazy loading where appropriate
- [ ] Check bundle sizes
- [ ] Optimize CSS delivery
- [ ] Test page load times
- [ ] Fix any performance issues
- _Requirements: 12_

### 6.5 SEO Optimization
- [ ] Update meta titles and descriptions
- [ ] Ensure proper heading hierarchy
- [ ] Add structured data where appropriate
- [ ] Create/update sitemap
- [ ] Set up 301 redirects for changed URLs
- [ ] Update robots.txt if needed
- [ ] Submit updated sitemap to search engines
- _Requirements: 1_

### 6.6 Analytics Setup
- [ ] Set up tracking for new pages
- [ ] Create custom events for key actions
- [ ] Set up conversion tracking
- [ ] Create dashboard for monitoring metrics
- [ ] Test analytics implementation
- _Requirements: Design document success metrics_



## Phase 7: Deployment & Rollout

### 7.1 Feature Flag Implementation
- [ ] Create feature flag system (if not exists)
- [ ] Add flag for "new_frontend"
- [ ] Implement flag checks in routes
- [ ] Create admin interface to toggle flag
- [ ] Test flag functionality
- [ ] Document flag usage
- _Requirements: Design document rollout strategy_

### 7.2 Staging Deployment
- [ ] Deploy to staging environment
- [ ] Run full test suite on staging
- [ ] Conduct user acceptance testing
- [ ] Fix any issues found
- [ ] Get stakeholder approval
- _Requirements: All_

### 7.3 Communication Preparation
- [ ] Draft announcement email for existing members
- [ ] Create FAQ document for support team
- [ ] Update support scripts
- [ ] Create video walkthrough of changes (optional)
- [ ] Prepare social media announcements
- _Requirements: Design document communication plan_

### 7.4 Production Deployment
- [ ] Create deployment checklist
- [ ] Schedule deployment window
- [ ] Deploy to production
- [ ] Enable feature flag for small percentage of users (10%)
- [ ] Monitor error logs
- [ ] Monitor analytics
- [ ] Gradually increase rollout percentage
- _Requirements: All_

### 7.5 Post-Launch Monitoring
- [ ] Monitor error rates
- [ ] Monitor page load times
- [ ] Monitor user feedback
- [ ] Track key metrics (engagement, conversion)
- [ ] Address any critical issues immediately
- [ ] Create weekly status reports
- _Requirements: Design document success metrics_

### 7.6 Gradual Rollout
- [ ] Week 1: 10% of users
- [ ] Week 2: 25% of users (if no major issues)
- [ ] Week 3: 50% of users
- [ ] Week 4: 75% of users
- [ ] Week 5: 100% of users
- [ ] Remove feature flag after successful rollout
- _Requirements: Design document rollout strategy_



## Phase 8: Post-Launch Optimization

### 8.1 Gather User Feedback
- [ ] Create feedback survey
- [ ] Conduct user interviews
- [ ] Monitor support tickets for themes
- [ ] Analyze user behavior data
- [ ] Identify pain points
- [ ] Create prioritized improvement list
- _Requirements: Design document success metrics_

### 8.2 A/B Testing
- [ ] Identify elements to test (CTAs, headlines, layouts)
- [ ] Set up A/B testing framework
- [ ] Run tests on key pages
- [ ] Analyze results
- [ ] Implement winning variations
- _Requirements: Design document success metrics_

### 8.3 Content Refinement
- [ ] Review content based on user feedback
- [ ] Update unclear messaging
- [ ] Add missing information
- [ ] Improve SEO content
- [ ] Update testimonials
- _Requirements: 13, 14_

### 8.4 Performance Optimization
- [ ] Analyze performance metrics
- [ ] Optimize slow-loading pages
- [ ] Reduce bundle sizes further
- [ ] Implement caching strategies
- [ ] Optimize database queries if needed
- _Requirements: 12_

### 8.5 Continuous Improvement
- [ ] Establish monthly review process
- [ ] Create content update schedule
- [ ] Monitor competitor positioning
- [ ] Stay updated on compliance requirements
- [ ] Plan future enhancements
- _Requirements: Design document maintenance plan_

## Success Criteria

### Phase Completion Checklist

**Phase 1 Complete When**:
- [ ] All navigation updated and tested
- [ ] Home Hub enhanced and functional
- [ ] New page structures created
- [ ] Routes updated and redirects working

**Phase 2 Complete When**:
- [ ] Homepage fully redesigned
- [ ] All homepage components updated
- [ ] Content emphasizes products over network
- [ ] Visual design is consistent

**Phase 3 Complete When**:
- [ ] Both dashboards refactored
- [ ] Network features moved to secondary position
- [ ] Safe terminology applied throughout
- [ ] Product-focused metrics prominent

**Phase 4 Complete When**:
- [ ] All new pages built and functional
- [ ] Content reviewed for compliance
- [ ] Legal language approved
- [ ] All pages responsive

**Phase 5 Complete When**:
- [ ] Visual consistency achieved
- [ ] All testing complete
- [ ] Documentation updated
- [ ] No critical bugs

**Phase 6 Complete When**:
- [ ] All quality checks passed
- [ ] Accessibility compliant
- [ ] Performance optimized
- [ ] SEO implemented

**Phase 7 Complete When**:
- [ ] Successfully deployed to production
- [ ] 100% rollout achieved
- [ ] No critical issues
- [ ] User feedback positive

**Phase 8 Complete When**:
- [ ] Optimization cycle established
- [ ] Continuous improvement process in place
- [ ] Metrics tracking automated
- [ ] Team trained on new system

## Notes

- Each task should be completed and tested before moving to the next
- Regular code reviews should be conducted throughout
- Keep stakeholders informed of progress
- Document any deviations from the plan
- Maintain backward compatibility where possible
- Prioritize user experience and legal compliance

