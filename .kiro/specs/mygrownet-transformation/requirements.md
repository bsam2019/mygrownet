# Requirements Document

## Introduction

MyGrowNet is a comprehensive transformation of the existing VBIF platform into a wealth-building ecosystem that combines financial education, community-driven growth, tiered incentives, physical rewards, mentorship programs, and recurring revenue streams. The platform will create a "family-like network" where members learn, earn, invest, and contribute to shared goals while benefiting from both digital and physical rewards.

The transformation introduces new membership tiers (Bronze to Elite), recurring monthly subscriptions, community projects with profit-sharing, physical asset rewards, gamification elements, mentorship programs, and business facilitation services for elite members.

## Requirements

### Requirement 1: Tiered Membership System with Recurring Revenue

**User Story:** As a platform member, I want to choose from different membership tiers with monthly subscriptions, so that I can access tier-appropriate benefits and contribute to a sustainable revenue model.

#### Acceptance Criteria

1. WHEN a user registers THEN the system SHALL offer five membership tiers: Bronze (K150/month), Silver (K300/month), Gold (K500/month), Diamond (K1,000/month), and Elite (K1,500/month)
2. WHEN a user selects a tier THEN the system SHALL require monthly subscription payment processing
3. WHEN a user's subscription is active THEN the system SHALL provide tier-specific monthly shares: Bronze (K50), Silver (K150), Gold (K300), Diamond (K500), Elite (K700)
4. WHEN a user fails to pay monthly subscription THEN the system SHALL downgrade their tier access after a 7-day grace period
5. WHEN a user upgrades their tier THEN the system SHALL immediately grant access to higher-tier benefits

### Requirement 2: Five-Level Multilevel Commission System

**User Story:** As a member, I want to earn commissions from a five-level deep referral network, so that I can build a sustainable income through team development and product sales.

#### Acceptance Criteria

1. WHEN a member refers someone who purchases a package THEN the system SHALL calculate 12% commission on Level 1 (direct) referrals
2. WHEN Level 1 referrals make referrals THEN the system SHALL calculate 6% commission on Level 2 referrals
3. WHEN Level 2 referrals make referrals THEN the system SHALL calculate 4% commission on Level 3 referrals
4. WHEN Level 3 referrals make referrals THEN the system SHALL calculate 2% commission on Level 4 referrals
5. WHEN Level 4 referrals make referrals THEN the system SHALL calculate 1% commission on Level 5 referrals
6. WHEN commissions are calculated THEN the system SHALL base them on actual product/package purchases, not just recruitment
7. WHEN team members maintain active subscriptions THEN the system SHALL continue paying ongoing commissions
8. WHEN commissions are processed THEN the system SHALL complete payments within 24 hours

### Requirement 3: Enhanced Tier Advancement with Team Volume Requirements

**User Story:** As a member, I want to advance through tiers based on both personal referrals and team performance, so that I can unlock leadership bonuses and exclusive benefits.

#### Acceptance Criteria

1. WHEN a Bronze member gets 3 active referrals AND generates K5,000 team volume THEN the system SHALL allow upgrade to Silver tier
2. WHEN a Silver member gets 10 active referrals AND generates K15,000 team volume THEN the system SHALL allow upgrade to Gold tier
3. WHEN a Gold member gets 25 active referrals AND generates K50,000 team volume THEN the system SHALL allow upgrade to Diamond tier
4. WHEN a Diamond member gets 50 active referrals AND generates K150,000 team volume THEN the system SHALL allow upgrade to Elite tier
5. WHEN a member achieves Silver tier THEN the system SHALL award K500 achievement bonus plus 2% monthly team volume bonus
6. WHEN a member achieves Gold tier THEN the system SHALL award K2,000 achievement bonus plus 5% monthly team volume bonus and leadership bonus eligibility
7. WHEN a member achieves Diamond tier THEN the system SHALL award K5,000 achievement bonus plus 7% monthly team volume bonus and quarterly profit-sharing access
8. WHEN a member achieves Elite tier THEN the system SHALL award K10,000 achievement bonus plus 10% monthly team volume bonus and annual profit-sharing access
9. WHEN tier qualifications are maintained for 3 consecutive months THEN the system SHALL confirm permanent tier status

### Requirement 4: Performance-Based Physical Asset Incentive System

**User Story:** As a member, I want to receive physical rewards based on my performance and tier achievements, so that I can benefit from tangible assets that recognize my success and provide real-world value.

#### Acceptance Criteria

1. WHEN a Bronze member completes first month with 1+ active referral THEN the system SHALL provide branded starter kit (merchandise, training materials)
2. WHEN a Silver member maintains tier for 3 months with K15,000+ team volume THEN the system SHALL offer smartphone or tablet (K2,000-K4,000 value)
3. WHEN a Gold member maintains tier for 6 months with K50,000+ team volume THEN the system SHALL offer motorbike or office equipment package (K8,000-K15,000 value)
4. WHEN a Diamond member maintains tier for 9 months with K150,000+ team volume THEN the system SHALL offer car or small property down payment (K25,000-K50,000 value)
5. WHEN an Elite member maintains tier for 12 months with K500,000+ team volume THEN the system SHALL offer luxury car or property investment (K75,000-K150,000 value)
6. WHEN physical rewards are earned THEN the system SHALL require performance maintenance for 12 months to transfer full ownership
7. WHEN assets are provided THEN the system SHALL offer asset management services and income generation opportunities
8. WHEN qualification periods are not maintained THEN the system SHALL implement asset recovery or payment plan options

### Requirement 5: Comprehensive Profit Sharing and Performance Bonuses

**User Story:** As a member, I want to participate in profit sharing and earn performance bonuses based on my team's success, so that I can benefit from the overall platform growth and my leadership efforts.

#### Acceptance Criteria

1. WHEN Gold+ members maintain their tier for 6+ months THEN the system SHALL include them in quarterly profit-sharing pool (5% of company profits)
2. WHEN Diamond+ members maintain their tier for 6+ months THEN the system SHALL include them in annual profit-sharing pool (10% of company profits)
3. WHEN Elite members maintain their tier for 12+ months THEN the system SHALL include them in premium annual profit-sharing pool (15% of company profits)
4. WHEN monthly team volume exceeds K10,000 THEN the system SHALL award 2% performance bonus on team turnover
5. WHEN monthly team volume exceeds K25,000 THEN the system SHALL award 5% performance bonus on team turnover
6. WHEN monthly team volume exceeds K50,000 THEN the system SHALL award 7% performance bonus on team turnover
7. WHEN monthly team volume exceeds K100,000 THEN the system SHALL award 10% performance bonus on team turnover
8. WHEN quarterly reports are due THEN the system SHALL publish transparent profit-sharing calculations and distributions
9. WHEN leadership bonuses are earned THEN the system SHALL award additional 1-3% based on team development and mentorship activities

### Requirement 6: Gamification and Recognition System

**User Story:** As a member, I want to see my progress through gamification elements and receive recognition, so that I stay motivated and engaged with the platform.

#### Acceptance Criteria

1. WHEN members perform activities THEN the system SHALL update leaderboards for referrals, subscriptions, and project participation
2. WHEN members reach milestones THEN the system SHALL award achievement badges for first project contribution, tier promotion, or mentorship completion
3. WHEN weekly periods end THEN the system SHALL award leaderboard bonuses to top recruiters
4. WHEN quarterly periods end THEN the system SHALL conduct raffles for motorbikes, plots, and smartphones
5. WHEN annual events occur THEN the system SHALL organize recognition galas with awards and physical gifts
6. WHEN members achieve significant milestones THEN the system SHALL provide certificates and public recognition

### Requirement 7: Mentorship and Peer Support System

**User Story:** As a member, I want access to mentorship and peer support, so that I can learn from experienced members and grow my knowledge and skills.

#### Acceptance Criteria

1. WHEN members join THEN the system SHALL assign them to peer circles for collaboration and accountability
2. WHEN higher-tier members are available THEN the system SHALL facilitate mentorship ladder connections
3. WHEN expert office hours are scheduled THEN the system SHALL allow booking sessions with financial and business experts
4. WHEN Elite members are active for 6+ months THEN the system SHALL provide dedicated manager access
5. WHEN innovation labs are active THEN the system SHALL allow members to propose new ventures for community funding
6. WHEN mentorship sessions occur THEN the system SHALL track completion for achievement badges

### Requirement 8: Business Facilitation for Elite Members

**User Story:** As an Elite member with sustained activity, I want access to business facilitation services, so that I can receive support for creating and growing real businesses.

#### Acceptance Criteria

1. WHEN Elite members maintain Silver/Diamond rank for 6+ months THEN the system SHALL grant business facilitation eligibility
2. WHEN eligible members request support THEN the system SHALL provide business plan mentorship and guidance
3. WHEN business registration is needed THEN the system SHALL assist with registration, tax, and licensing processes
4. WHEN seed capital is available THEN the system SHALL provide access to grants and joint venture opportunities
5. WHEN businesses are launched THEN the system SHALL provide ongoing coaching and project guidance
6. WHEN business milestones are reached THEN the system SHALL track progress and provide additional support

### Requirement 9: Educational Content and Resource Management

**User Story:** As a member, I want access to educational content and resources appropriate to my tier, so that I can continuously improve my financial literacy and business skills.

#### Acceptance Criteria

1. WHEN members access the platform THEN the system SHALL provide tier-appropriate educational content updated monthly
2. WHEN Bronze members log in THEN the system SHALL provide e-books, templates, and basic financial tips
3. WHEN Silver+ members access content THEN the system SHALL provide videos, webinars, and advanced courses
4. WHEN Gold+ members access resources THEN the system SHALL provide business planning toolkits and investment courses
5. WHEN Elite members access content THEN the system SHALL provide VIP mentorship content and innovation lab access
6. WHEN content is updated THEN the system SHALL notify members of new materials and resources

### Requirement 10: Asset Appreciation and Income Generation Program

**User Story:** As a member who has received physical assets, I want to generate additional income from these assets, so that I can maximize the value of my rewards.

#### Acceptance Criteria

1. WHEN members receive land assets THEN the system SHALL provide options to lease out for agricultural or commercial use
2. WHEN members receive vehicle assets THEN the system SHALL facilitate rental unit participation programs
3. WHEN members receive property assets THEN the system SHALL connect them with rental management services
4. WHEN asset income is generated THEN the system SHALL track earnings and provide monthly income reports
5. WHEN assets appreciate in value THEN the system SHALL provide annual valuation updates
6. WHEN members want to liquidate assets THEN the system SHALL provide market-rate buyback options

### Requirement 11: Weekly and Quarterly Incentive Programs

**User Story:** As a member, I want access to regular incentive programs and raffles, so that I can earn additional rewards beyond my tier benefits.

#### Acceptance Criteria

1. WHEN weekly periods end THEN the system SHALL award top 10 recruiters with cash bonuses or gadgets
2. WHEN profit-boost weeks are declared THEN the system SHALL increase commission rates by 25% for that week
3. WHEN quarterly periods end THEN the system SHALL conduct raffles for motorbikes, land plots, and smartphones
4. WHEN raffle entries are earned THEN the system SHALL award entries based on referral activity and subscription consistency
5. WHEN special promotions run THEN the system SHALL notify all eligible members via dashboard and email
6. WHEN incentive programs conclude THEN the system SHALL publish winner announcements and distribute rewards

### Requirement 12: Comprehensive Earnings Simulation and Compliance

**User Story:** As a member, I want to see realistic earning projections with detailed scenarios and understand the compliance structure, so that I can make informed decisions about my participation and growth strategy.

#### Acceptance Criteria

1. WHEN members view tier information THEN the system SHALL display realistic monthly earnings scenarios: Bronze (K150-K500), Silver (K800-K3,000), Gold (K2,500-K8,000), Diamond (K7,500-K25,000), Elite (K20,000-K75,000)
2. WHEN earnings simulations are shown THEN the system SHALL provide examples for members with 5 active referrals building 3 levels deep
3. WHEN projections are calculated THEN the system SHALL break down income sources: multilevel commissions (25%), team volume bonuses (15%), subscription shares (30%), profit sharing (20%), achievement bonuses (10%)
4. WHEN compliance information is displayed THEN the system SHALL emphasize that earnings are based on actual product sales and team performance, not just recruitment
5. WHEN legal disclaimers are shown THEN the system SHALL state that results vary and depend on individual effort, market conditions, and team development
6. WHEN business structure is explained THEN the system SHALL clarify that MyGrowNet operates as a legitimate MLM business focused on education and community building
7. WHEN sustainability metrics are published THEN the system SHALL show that commission payouts do not exceed 25% of total revenue to ensure long-term viability

### Requirement 13: Dashboard and Tracking System

**User Story:** As a member, I want a comprehensive dashboard to track my earnings, tier status, and asset progress, so that I can monitor my growth and engagement with the platform.

#### Acceptance Criteria

1. WHEN members log in THEN the system SHALL display current tier status, earnings summary, and referral count
2. WHEN earnings are generated THEN the system SHALL show real-time commission tracking and subscription shares
3. WHEN physical rewards are earned THEN the system SHALL display asset progress and claim status
4. WHEN community projects are active THEN the system SHALL show project participation and profit sharing
5. WHEN achievements are unlocked THEN the system SHALL display badges, leaderboard position, and milestone progress
6. WHEN withdrawal requests are made THEN the system SHALL provide instant withdrawal processing for commissions and bonuses