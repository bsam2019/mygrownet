# Requirements Document

## Introduction

BizBoost needs a standalone landing/welcome page that allows users who are only interested in BizBoost to learn about the app and install it as a PWA. This is similar to how GrowBiz and GrowFinance could have their own landing pages. Registration and authentication are handled through the main MyGrowNet platform, but users coming from BizBoost should be redirected back to BizBoost after authentication.

Currently, BizBoost is only accessible through the HomeHub module grid after login. This limits BizBoost's reach to potential SME customers who may want to discover and install BizBoost directly.

## Requirements

### Requirement 1: Standalone Landing Page

**User Story:** As a potential BizBoost user, I want to access a dedicated BizBoost landing page, so that I can learn about and install BizBoost before signing up.

#### Acceptance Criteria

1. WHEN a user visits `/bizboost/welcome` THEN the system SHALL display a BizBoost-branded landing page with product information (no auth required)
2. WHEN the landing page loads THEN the system SHALL display key features, benefits, and pricing tiers for BizBoost
3. WHEN a user is on the landing page THEN the system SHALL provide clear call-to-action buttons for "Get Started" and "Install App"
4. IF the user is on a mobile device THEN the system SHALL prominently display PWA installation options
5. WHEN the user clicks "Get Started" THEN the system SHALL redirect to the main app login/register page with a `redirect=/bizboost` parameter

### Requirement 2: Post-Authentication Redirect

**User Story:** As a user who discovered BizBoost through its landing page, I want to be redirected to BizBoost after logging in, so that I can start using the app immediately.

#### Acceptance Criteria

1. WHEN a user clicks "Get Started" from the BizBoost landing page THEN the system SHALL redirect to `/login?redirect=/bizboost` or `/register?redirect=/bizboost`
2. WHEN authentication is complete with a BizBoost redirect parameter THEN the system SHALL redirect the user to `/bizboost/setup` (if new) or `/bizboost` (if setup complete)
3. IF the user already has an account and BizBoost setup complete THEN the system SHALL redirect them directly to `/bizboost` dashboard
4. WHEN a user logs in with the redirect parameter THEN the system SHALL honor the redirect instead of going to HomeHub

### Requirement 3: PWA Installation from Landing Page

**User Story:** As a mobile user, I want to install BizBoost as a standalone app from the landing page, so that I can access it like a native app on my device.

#### Acceptance Criteria

1. WHEN a user visits the BizBoost landing page on a supported mobile browser THEN the system SHALL display an "Install App" button
2. WHEN the user clicks "Install App" THEN the system SHALL trigger the PWA installation prompt
3. IF the user is on iOS THEN the system SHALL display step-by-step instructions for adding to home screen
4. WHEN BizBoost is installed as a PWA THEN the system SHALL use the BizBoost-specific manifest (`bizboost-manifest.json`)
5. WHEN the PWA is launched THEN the system SHALL open directly to `/bizboost` with BizBoost branding

### Requirement 4: Direct Access URL Support

**User Story:** As a BizBoost user, I want to access BizBoost directly via a dedicated URL, so that I can bookmark and share the app easily.

#### Acceptance Criteria

1. WHEN a user visits `/bizboost` while authenticated THEN the system SHALL display the BizBoost dashboard
2. WHEN a user visits `/bizboost` without authentication THEN the system SHALL display the landing page with login/register options
3. WHEN sharing the BizBoost URL THEN the system SHALL generate appropriate Open Graph meta tags for BizBoost branding
4. IF the user has BizBoost as their primary module THEN the system SHALL redirect from `/home` to `/bizboost` after login

### Requirement 5: Module Seeder Update

**User Story:** As a system administrator, I want BizBoost to be properly configured in the module seeder, so that it appears correctly on the HomeHub and supports standalone access.

#### Acceptance Criteria

1. WHEN the module seeder runs THEN the system SHALL create/update the BizBoost module with standalone route configuration
2. WHEN BizBoost is seeded THEN the system SHALL include the landing page route in the routes configuration
3. WHEN BizBoost is displayed on HomeHub THEN the system SHALL show the correct icon, color, and description
4. WHEN checking module access THEN the system SHALL allow unauthenticated access to the BizBoost landing page

### Requirement 6: Landing Page Content

**User Story:** As a potential BizBoost user, I want to see compelling information about BizBoost on the landing page, so that I understand the value and can make an informed decision.

#### Acceptance Criteria

1. WHEN the landing page loads THEN the system SHALL display a hero section with BizBoost branding and tagline
2. WHEN viewing the landing page THEN the system SHALL display feature highlights (AI content, customer management, social media, etc.)
3. WHEN viewing the landing page THEN the system SHALL display pricing tiers (Free, Starter, Growth, Pro)
4. WHEN viewing the landing page THEN the system SHALL display testimonials or use cases (optional)
5. WHEN viewing the landing page THEN the system SHALL display a footer with links to main MyGrowNet platform
