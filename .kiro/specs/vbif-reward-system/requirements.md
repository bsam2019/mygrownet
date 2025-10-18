# Requirements Document

## Introduction

The VBIF Reward System is a comprehensive investment management platform that enables fair compensation for investors through multiple reward mechanisms. The system manages investment tiers, calculates fixed annual profit shares, distributes performance-based bonuses, handles multi-level referral commissions, and enforces withdrawal policies with penalties. This implementation will create a robust Laravel-based platform that automates reward calculations, tracks investor hierarchies, manages fund performance, and ensures compliance with withdrawal policies while maintaining financial sustainability.

## Requirements

### Requirement 1: Investment Tier Management

**User Story:** As an investor, I want to select and maintain an investment tier based on my contribution amount, so that I can receive appropriate profit shares and referral bonuses.

#### Acceptance Criteria

1. WHEN an investor registers THEN the system SHALL allow selection from five investment tiers (Basic: K500/3%, Starter: K1000/5%, Builder: K2500/7%, Leader: K5000/10%, Elite: K10000/15%)
2. WHEN an investor's contribution meets a higher tier threshold THEN the system SHALL automatically upgrade their tier and profit share percentage
3. WHEN displaying investor information THEN the system SHALL show current tier, contribution amount, and associated profit share percentage
4. IF an investor attempts to contribute below the minimum tier amount THEN the system SHALL reject the transaction and display appropriate error message

### Requirement 2: Fixed Annual Profit Share Calculation

**User Story:** As an investor, I want to receive guaranteed annual profit shares based on my investment tier, so that I have predictable returns on my investment.

#### Acceptance Criteria

1. WHEN the annual profit distribution period arrives THEN the system SHALL calculate each investor's fixed profit share using their tier percentage and total contribution
2. WHEN calculating annual profits THEN the system SHALL apply the correct percentage (Basic: 3%, Starter: 5%, Builder: 7%, Leader: 10%, Elite: 15%) to each investor's contribution
3. WHEN an investor has been upgraded during the year THEN the system SHALL calculate profit share using a weighted average based on time spent in each tier
4. WHEN annual profits are distributed THEN the system SHALL record the transaction and update investor account balances

### Requirement 3: Performance-Based Quarterly Bonus System

**User Story:** As an investor, I want to receive quarterly bonuses based on actual fund performance, so that I benefit when the fund performs well.

#### Acceptance Criteria

1. WHEN quarterly profits are recorded THEN the system SHALL allocate 5-10% of total profits to the bonus pool
2. WHEN distributing quarterly bonuses THEN the system SHALL calculate each investor's share proportional to their contribution percentage of the total investment pool
3. WHEN an investor's pool contribution is 5% and bonus pool is K500,000 THEN the system SHALL award K25,000 as performance bonus
4. WHEN quarterly distribution occurs THEN the system SHALL record bonus transactions and notify investors of their earnings

### Requirement 4: Multi-Level Referral Commission System

**User Story:** As an investor, I want to earn commissions from direct and indirect referrals, so that I can increase my earnings through network building.

#### Acceptance Criteria

1. WHEN an investor refers someone directly THEN the system SHALL calculate commission based on referrer's tier (Basic: 5%, Starter: 7%, Builder: 10%, Leader: 12%, Elite: 15%)
2. WHEN calculating indirect referrals THEN the system SHALL apply level 2 bonuses (Starter: 2%, Builder: 3%, Leader: 5%, Elite: 7%) and level 3 bonuses (Builder: 1%, Leader: 2%, Elite: 3%)
3. WHEN a referral makes an investment THEN the system SHALL automatically distribute commissions to eligible upline members within 24 hours
4. WHEN tracking referral hierarchy THEN the system SHALL maintain accurate genealogy records up to 3 levels deep

### Requirement 5: 3x3 Matrix System with Spillover

**User Story:** As an investor, I want to participate in a structured 3x3 matrix system with spillover benefits, so that I can benefit from team growth even when my direct referrals are full.

#### Acceptance Criteria

1. WHEN an investor joins THEN the system SHALL place them in a 3x3 matrix structure allowing 3 direct referrals per level
2. WHEN an investor's direct referral slots are full THEN the system SHALL implement spillover by placing new referrals in the next available downline position
3. WHEN calculating matrix positions THEN the system SHALL support 3 levels with total capacity of 39 downline slots (3 + 9 + 27)
4. WHEN spillover occurs THEN the system SHALL notify affected upline members and update their matrix view

### Requirement 6: Withdrawal Policy Enforcement

**User Story:** As an investor, I want clear withdrawal rules with appropriate penalties, so that I understand the terms and the fund maintains stability.

#### Acceptance Criteria

1. WHEN an investor attempts early withdrawal (before 12 months) THEN the system SHALL require emergency approval and apply 50% penalty on fixed profit share plus forfeit all bonuses
2. WHEN an investor requests full withdrawal after 12 months THEN the system SHALL allow withdrawal with 30 days notice and no penalties
3. WHEN an investor requests partial withdrawal after 12 months THEN the system SHALL allow up to 50% of profits withdrawal while keeping capital invested
4. WHEN calculating withdrawal penalties THEN the system SHALL apply graduated penalties based on withdrawal timing (0-1 month: 100% profit + 12% capital, 1-3 months: 100% profit + 12% capital, 3-6 months: 50% profit + 6% capital, 6-12 months: 30% profit + 3% capital)

### Requirement 7: Reinvestment Bonus System

**User Story:** As an investor, I want to receive bonus profit shares for reinvesting my earnings, so that I'm incentivized to grow my investment long-term.

#### Acceptance Criteria

1. WHEN an investor reinvests profits in year 2 THEN the system SHALL apply enhanced profit share rates (Starter: 8%, Builder: 10%, Leader: 12%, Elite: 17%)
2. WHEN calculating reinvestment bonuses THEN the system SHALL track reinvested amounts separately and apply bonus rates only to reinvested portions
3. WHEN displaying investor dashboard THEN the system SHALL show current profit share rate and potential reinvestment bonus rate
4. WHEN reinvestment occurs THEN the system SHALL update the investor's effective profit share percentage for future calculations

### Requirement 8: Commission Clawback System

**User Story:** As a system administrator, I want to implement commission clawbacks for early withdrawals, so that the referral system remains sustainable and fair.

#### Acceptance Criteria

1. WHEN a referred investor withdraws within 1-3 months THEN the system SHALL clawback 25-50% of commissions paid to their upline
2. WHEN calculating clawbacks THEN the system SHALL apply time-based percentages (0-1 month: 50%, 1-3 months: 25%, 3+ months: 0%)
3. WHEN clawback occurs THEN the system SHALL deduct amounts from upline member accounts and record the transaction
4. WHEN upline members have insufficient balance for clawback THEN the system SHALL create a debt record to be settled from future earnings

### Requirement 9: Real-time Dashboard and Reporting

**User Story:** As an investor, I want to view my investment performance, referral tree, and earnings in real-time, so that I can track my progress and make informed decisions.

#### Acceptance Criteria

1. WHEN an investor accesses their dashboard THEN the system SHALL display current tier, total investment, profit shares earned, referral commissions, and matrix position
2. WHEN viewing referral tree THEN the system SHALL show visual representation of 3x3 matrix with member positions and earnings contribution
3. WHEN checking withdrawal eligibility THEN the system SHALL display lock-in period remaining, available withdrawal amount, and penalty preview
4. WHEN earnings are updated THEN the system SHALL reflect changes in real-time without requiring page refresh

### Requirement 10: Security and Compliance

**User Story:** As a system administrator, I want robust security measures and compliance features, so that the platform protects investor data and prevents fraudulent activities.

#### Acceptance Criteria

1. WHEN an investor attempts withdrawal THEN the system SHALL require SMS/Email OTP verification
2. WHEN new investors register THEN the system SHALL require ID verification and prevent duplicate accounts from same IP
3. WHEN suspicious activity is detected THEN the system SHALL automatically block accounts and notify administrators
4. WHEN storing sensitive data THEN the system SHALL encrypt personal information and financial records using industry-standard encryption