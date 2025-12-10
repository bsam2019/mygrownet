# Requirements Document

## Introduction

MyGrowNet has evolved into a comprehensive business empowerment platform with multiple valuable products and services including:
- **BizBoost**: Complete business management and marketing automation suite
- **GrowFinance**: Accounting and financial management tools
- **GrowBiz**: Team and employee management system
- **Venture Builder**: Co-investment opportunities in vetted businesses
- **Business Growth Fund (BGF)**: Short-term business financing
- **Learning Resources**: Educational content, workshops, and skill development
- **Business Tools**: Calculators, templates, and planning resources

However, the current frontend heavily emphasizes the network marketing (MLM) structure - with commission levels, team size, matrix positions, and referral links dominating the homepage, dashboards, and navigation. This creates several challenges:

1. **Perception Problem**: Platform appears as "just another MLM" rather than a legitimate business tools provider
2. **Value Obscured**: Powerful products like BizBoost and GrowFinance are buried while network stats are prominent
3. **Barrier to Adoption**: Potential users focused on business tools may be deterred by MLM emphasis
4. **Misaligned Messaging**: "Build Network" is positioned as Step 3 in "How It Works" before product usage

This specification addresses the strategic repositioning of the MyGrowNet frontend to prioritize products, services, and business value while de-emphasizing (but not removing) the MLM aspects. The network marketing component will remain fully functional but will be discovered organically through member experience rather than being the primary focus of the user interface.

**Goal**: Transform MyGrowNet's public perception from "an MLM platform with some tools" to "a powerful business platform with community benefits" - where the referral system is a bonus feature that members discover and leverage after experiencing product value.

## Requirements

### Requirement 1: Simplified Navigation Structure

**User Story:** As a visitor to MyGrowNet, I want a clean and minimal navigation that focuses on core features, so that I can easily understand what the platform offers without being overwhelmed.

#### Acceptance Criteria

1. WHEN a visitor views the main navigation THEN the system SHALL display only: Home, About, Products & Services, Marketplace, Training, Contact
2. WHEN a visitor explores navigation THEN the system SHALL NOT display network marketing elements, commission structures, or matrix visualizations in main navigation
3. WHEN a visitor accesses the site THEN the system SHALL present a clean, minimal navigation structure focused on products and services
4. WHEN a visitor browses pages THEN the system SHALL maintain consistent navigation across all public pages
5. WHEN a visitor views dropdown menus THEN the system SHALL organize content by product/service categories only
6. WHEN a visitor uses mobile navigation THEN the system SHALL prioritize product access with no network features visible
7. WHEN a visitor searches the site THEN the system SHALL return results prioritizing products, training, and marketplace content
8. WHEN an authenticated member views navigation THEN the system SHALL show "Referral Program" link in user account menu only, not in main navigation

### Requirement 2: Homepage Redesign with Clear Value Proposition

**User Story:** As a potential member visiting MyGrowNet, I want to immediately understand the platform as a business empowerment and e-commerce platform, so that I can evaluate it based on tangible products and services.

#### Acceptance Criteria

1. WHEN a visitor lands on the homepage THEN the system SHALL display a hero section describing MyGrowNet as a "business empowerment and e-commerce platform"
2. WHEN a visitor views the hero section THEN the system SHALL provide two primary CTAs: "Explore Products" and "Browse Marketplace"
3. WHEN a visitor scrolls the homepage THEN the system SHALL display a "Products & Services" section showcasing: BizBoost, GrowFinance, GrowBiz, Marketplace, Training, Business Tools
4. WHEN a visitor views feature descriptions THEN the system SHALL focus on product capabilities and business value only
5. WHEN a visitor explores the homepage THEN the system SHALL include a "What's Available Now" section listing current products and services
6. WHEN a visitor views future features THEN the system SHALL include a "Coming Soon" section mentioning: Vendor dashboard, Venture Builder, Additional business tools, Mobile app
7. WHEN a visitor reads the homepage THEN the system SHALL include a "Founder's Message" introducing the purpose and mission of MyGrowNet
8. WHEN a visitor views testimonials THEN the system SHALL highlight success stories related to product usage, skill development, and business growth (not income claims)
9. WHEN a visitor browses the homepage THEN the system SHALL NOT display any network marketing elements, referral structures, or earning opportunities

### Requirement 3: Legal and Compliant "About Us" Page

**User Story:** As a visitor or regulatory authority reviewing MyGrowNet, I want clear information about the company structure and operations, so that I understand this is a legitimate business platform, not an investment scheme.

#### Acceptance Criteria

1. WHEN a visitor views the About page THEN the system SHALL clearly state "MyGrowNet is a private limited company"
2. WHEN a visitor reads about operations THEN the system SHALL state focus areas as: commerce, training, and member empowerment
3. WHEN a visitor reviews business model THEN the system SHALL explicitly state: "No deposit-taking, no guaranteed payouts"
4. WHEN a visitor reads about rewards THEN the system SHALL state: "Rewards come from real product activity and company revenue, not member deposits"
5. WHEN a visitor explores company information THEN the system SHALL emphasize product sales and subscription revenue as the business model
6. WHEN a visitor views the mission THEN the system SHALL focus on business empowerment, skill development, and e-commerce facilitation
7. WHEN a visitor reads disclaimers THEN the system SHALL include clear legal language protecting the brand from investment scheme classification

### Requirement 4: Comprehensive "Join / Starter Kits" Page

**User Story:** As a potential member, I want to understand what I get immediately when I join, so that I can make an informed decision based on tangible value rather than earning potential.

#### Acceptance Criteria

1. WHEN a visitor views the Join page THEN the system SHALL clearly list what the starter kit includes (products, training access, tools)
2. WHEN a visitor explores membership benefits THEN the system SHALL emphasize immediate value: marketplace access, training modules, business tools, loyalty rewards
3. WHEN a visitor reads requirements THEN the system SHALL display monthly activity requirements for maintaining membership benefits
4. WHEN a visitor views joining steps THEN the system SHALL provide clear, simple steps to get started
5. WHEN a visitor reads descriptions THEN the system SHALL use safe language with no profit claims or income projections
6. WHEN a visitor compares options THEN the system SHALL show different starter kit tiers based on included products and training access
7. WHEN a visitor proceeds to join THEN the system SHALL emphasize "What you get today" over "What you could earn"

### Requirement 5: Professional Marketplace Page

**User Story:** As a visitor or member, I want to browse a professional marketplace with clear product categories, so that I can purchase products and see MyGrowNet as a legitimate e-commerce platform.

#### Acceptance Criteria

1. WHEN a visitor accesses the Marketplace THEN the system SHALL display clean product categories with professional design
2. WHEN a visitor browses products THEN the system SHALL show available products with clear images, descriptions, and pricing
3. WHEN a visitor views limited inventory THEN the system SHALL display "More products coming soon" messaging
4. WHEN a visitor explores categories THEN the system SHALL highlight MyGrowNet-branded products prominently
5. WHEN a visitor views vendor options THEN the system SHALL show vendor registration marked as "beta" or "coming soon"
6. WHEN a visitor searches products THEN the system SHALL provide filtering and sorting capabilities
7. WHEN a visitor views product pages THEN the system SHALL include reviews, ratings, and purchase options

### Requirement 6: Carefully Designed "Rewards & Loyalty" Page

**User Story:** As a member, I want to understand how I can earn rewards through platform activity, so that I see benefits tied to product purchases and engagement rather than recruitment.

#### Acceptance Criteria

1. WHEN a member views the Rewards page THEN the system SHALL use the heading "Rewards & Loyalty Benefits"
2. WHEN a member reads about earning THEN the system SHALL state: "Earn points when you buy products"
3. WHEN a member explores redemption THEN the system SHALL explain: "Redeem points for discounts"
4. WHEN a member views benefits THEN the system SHALL list: Access member-only offers, Store credit rewards, Additional training benefits for active members
5. WHEN a member reads descriptions THEN the system SHALL NOT mention cash rewards or fixed returns
6. WHEN a member views point values THEN the system SHALL tie points to specific product purchases and platform activities
7. WHEN a member explores the page THEN the system SHALL avoid any language suggesting guaranteed income or investment returns

### Requirement 7: Value-Focused Training Page

**User Story:** As a member, I want to access skills and business training that provides real value, so that I can develop capabilities that help my business or career.

#### Acceptance Criteria

1. WHEN a member accesses Training THEN the system SHALL display a list of available training modules
2. WHEN a member views content THEN the system SHALL show member-only content clearly marked
3. WHEN a member explores offerings THEN the system SHALL indicate future training expansions
4. WHEN a member reads descriptions THEN the system SHALL provide clear explanations of training value and outcomes
5. WHEN a member browses modules THEN the system SHALL organize content by skill category and difficulty level
6. WHEN a member views progress THEN the system SHALL display completion status and earned certifications
7. WHEN a member accesses training THEN the system SHALL emphasize practical business skills and knowledge development

### Requirement 8: Transparent Vision/Roadmap Section

**User Story:** As a visitor or member, I want to see the platform's development roadmap, so that I understand the long-term direction without overpromising.

#### Acceptance Criteria

1. WHEN a visitor views the Roadmap THEN the system SHALL display quarterly milestones in simple format
2. WHEN a visitor reads Q1 plans THEN the system SHALL show: Marketplace + Membership + Starter Kits
3. WHEN a visitor reads Q2 plans THEN the system SHALL show: Training expansion + Vendor onboarding
4. WHEN a visitor reads Q3 plans THEN the system SHALL show: Venture Builder pilot
5. WHEN a visitor reads Q4 plans THEN the system SHALL show: Mobile app and scaled products
6. WHEN a visitor explores the roadmap THEN the system SHALL show long-term direction without overpromising features
7. WHEN a visitor views timeline THEN the system SHALL mark items as "Completed", "In Progress", or "Planned"

### Requirement 9: Legal Assurance / Operations Transparency Section

**User Story:** As a stakeholder or regulatory reviewer, I want clear information about how MyGrowNet operates legally, so that I understand the business model and compliance approach.

#### Acceptance Criteria

1. WHEN a visitor views legal information THEN the system SHALL state: "MyGrowNet does not take deposits"
2. WHEN a visitor reads disclaimers THEN the system SHALL state: "No guaranteed returns"
3. WHEN a visitor explores operations THEN the system SHALL state: "Rewards are based on company activities and product sales"
4. WHEN a visitor reads about projects THEN the system SHALL state: "Members voluntarily join business projects under separate companies"
5. WHEN a visitor views terms THEN the system SHALL include clear legal language avoiding investment scheme classification
6. WHEN a visitor explores compliance THEN the system SHALL provide transparent information about business structure and revenue sources
7. WHEN a visitor reads policies THEN the system SHALL emphasize subscription-based model with product sales revenue

### Requirement 10: Subscription-Focused Registration Flow

**User Story:** As a new user registering for MyGrowNet, I want to choose a subscription tier based on the products and services I'll receive, so that I make an informed decision based on value rather than earning potential.

#### Acceptance Criteria

1. WHEN a user begins registration THEN the system SHALL present subscription tiers with product/service benefits as the primary information
2. WHEN a user views tier options THEN the system SHALL display: included learning resources, coaching sessions, business tools access, and community project participation
3. WHEN a user compares tiers THEN the system SHALL use product-focused language: "Basic Access", "Professional Package", "Business Builder", "Executive Suite", "Elite Membership"
4. WHEN a user selects a tier THEN the system SHALL show a clear breakdown of monthly subscription value vs. cost
5. WHEN a user enters referral information THEN the system SHALL present it as an optional field with minimal emphasis
6. WHEN a user completes registration THEN the system SHALL send a welcome email focused on accessing products and getting started with learning resources
7. WHEN a user has no referrer THEN the system SHALL assign a default sponsor without making this prominent in the UI

### Requirement 11: Cleaned Member Dashboard (Logged-In Users)

**User Story:** As a logged-in member, I want a dashboard that focuses on my products, orders, and training progress, so that I engage with core platform value rather than being distracted by network statistics.

#### Acceptance Criteria

1. WHEN a member logs in THEN the system SHALL display dashboard sections for: Profile, Orders, Loyalty points, Training access, Starter kit status, Referral tools, Activity status
2. WHEN a member views the dashboard THEN the system SHALL show "Project participation" marked as "coming soon"
3. WHEN a member explores their account THEN the system SHALL NOT display anything that looks like: balance growth, interest, ROI, earnings from investments
4. WHEN a member views financial information THEN the system SHALL show: order history, loyalty points balance, store credit, referral rewards (not commission earnings)
5. WHEN a member accesses training THEN the system SHALL display progress, completed modules, and available content
6. WHEN a member views their profile THEN the system SHALL emphasize: membership tier, activity status, training achievements, loyalty level
7. WHEN a member navigates the dashboard THEN the system SHALL place network/team features in a secondary "Community" or "Network" tab, not on the main dashboard

### Requirement 12: Visual Consistency and Branding

**User Story:** As a user of MyGrowNet, I want a consistent, professional visual experience, so that the platform feels trustworthy and well-designed.

#### Acceptance Criteria

1. WHEN a user navigates the site THEN the system SHALL use consistent button styles across all pages
2. WHEN a user views icons THEN the system SHALL use clean, simple icons that match the brand aesthetic
3. WHEN a user explores pages THEN the system SHALL apply a light colour palette that feels professional and approachable
4. WHEN a user interacts with elements THEN the system SHALL provide minimalistic design with smooth spacing
5. WHEN a user views different sections THEN the system SHALL maintain consistent typography and visual hierarchy
6. WHEN a user accesses the platform THEN the system SHALL present soft branding elements that build trust
7. WHEN a user experiences the interface THEN the system SHALL ensure all visual elements support the "business platform" positioning, not "MLM scheme" appearance

### Requirement 13: Safe Language and Terminology Throughout Platform

**User Story:** As a platform administrator or legal reviewer, I want all platform language to avoid investment/financial return terminology, so that we maintain legal compliance and proper positioning.

#### Acceptance Criteria

1. WHEN any page displays financial information THEN the system SHALL use terms like "loyalty points", "store credits", "referral rewards" instead of "earnings", "commissions", "returns"
2. WHEN documentation is displayed THEN the system SHALL replace any references to "returns", "profit shares", "investment payouts" with product-focused language
3. WHEN members view their account THEN the system SHALL show "Rewards Balance" or "Store Credit" instead of "Wallet Balance" or "Earnings"
4. WHEN the platform describes benefits THEN the system SHALL use "member benefits", "loyalty rewards", "activity bonuses" instead of "income", "profits", "dividends"
5. WHEN network features are shown THEN the system SHALL use "Community", "Network", "Referrals" instead of "Downline", "Matrix", "MLM"
6. WHEN future features are mentioned THEN the system SHALL clearly separate "Available Now" vs "Future Features" to avoid overpromising
7. WHEN any content is created THEN the system SHALL ensure no part of the front end uses financial-return language that could be misinterpreted as investment promises

### Requirement 14: Documentation Updates for Simplified Model

**User Story:** As a team member or external reviewer reading documentation, I want clear, updated documentation that reflects the simplified platform model, so that I understand the current state and legal positioning.

#### Acceptance Criteria

1. WHEN documentation is accessed THEN the system SHALL describe MyGrowNet following the simplified, early-stage structure
2. WHEN features are documented THEN the system SHALL clearly separate "Available Now" vs "Future Features"
3. WHEN the business model is explained THEN the system SHALL emphasize: subscription revenue, product sales, training fees, marketplace commissions
4. WHEN operational model is described THEN the system SHALL include a section explaining: loyalty points, store credits, training access, referral rewards (not investment returns)
5. WHEN compliance is documented THEN the system SHALL clearly state: no deposit-taking, no guaranteed returns, rewards from company activities
6. WHEN technical documentation is updated THEN the system SHALL remove old references to investment-focused features
7. WHEN new features are added THEN the system SHALL ensure documentation maintains the "business platform" positioning


