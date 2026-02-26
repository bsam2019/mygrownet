# MyGrowNet Storage (GrowBackup) - Product Concept

**Last Updated:** February 21, 2026
**Status:** Planning
**Version:** 1.0

## Overview

MyGrowNet Storage (GrowBackup) is a secure, S3-backed file storage module that provides members with cloud storage capabilities as part of the GrowNet product ecosystem. This is a **GrowNet MLM product** available as:
- **Starter Kit Product**: Included in initial member registration packages
- **Level Upgrade Product**: Enhanced storage quotas as members advance through professional levels
- **Standalone Subscription**: Optional upgrade for additional storage needs

The system enforces quota limits, provides secure access controls, and supports future platform needs like GrowBuilder backups and training materials.

**Architecture**: Built using **Domain-Driven Design (DDD)** principles with clear separation between Domain, Application, Infrastructure, and Presentation layers.

## Product Vision

Build a reliable, scalable storage solution that:
- Empowers members with secure file storage tied to their subscription
- Supports platform growth (backups, training, marketplace)
- Maintains security and privacy by default
- Scales cost-effectively using S3-compatible providers

## Core Value Proposition

### For Members
- Secure cloud storage integrated with GrowNet subscription
- Easy file management (upload, organize, download)
- Access files anywhere, anytime
- Automatic quota management based on tier

### For Platform
- Centralized storage infrastructure for all platform needs
- Cost-effective S3-compatible storage (Wasabi)
- Scalable architecture supporting future features
- Audit trail for compliance and security

## Target Users

### Primary (MVP)
- **GrowNet Members**: Personal file storage based on subscription tier
- **Platform Admins**: Manage plans, quotas, and monitor usage

### Secondary (Phase 2+)
- **Team Leaders**: Shared folders for team collaboration
- **GrowBuilder Users**: Website backup storage
- **Content Creators**: Training materials and marketplace media

## Product Scope

### MVP (Phase 1)
**Must Have:**
- User file manager with folders and files
- Upload, download, rename, move, delete operations
- Storage quota enforcement per plan/tier
- Private-by-default storage (no public buckets)
- Temporary signed download links
- Admin tools for plan management and usage monitoring

**Won't Have (MVP):**
- Public sharing with expiry/password
- Team/shared folders
- File versioning
- Website backup integration
- Advanced anti-abuse rules
- Detailed analytics

### Phase 2
- Share links with expiry and password protection
- Team/shared folders for leaders
- File version history
- GrowBuilder backup integration
- Enhanced anti-abuse (video limits, file type restrictions)
- Audit logs and analytics dashboard

### Phase 3+
- Training materials library
- GrowMarket media storage
- Advanced search and tagging
- File previews and thumbnails
- Mobile app integration

## Storage Plans (GrowNet Products)

### Plan Tiers (GrowNet MLM Products)

MyGrowNet Storage is sold as a GrowNet product with commissions and level bonuses:

| Plan | Quota | Max File Size | Sharing | Product Type | Price Point |
|------|-------|---------------|---------|--------------|-------------|
| **Starter (Lite)** | 2 GB | 25 MB | No | Starter Kit (included) | Bundled |
| **Basic** | 20 GB | 100 MB | Phase 2 | Level Upgrade / Standalone | K50/month |
| **Growth** | 100 GB | 500 MB | Phase 2 | Level Upgrade / Standalone | K150/month |
| **Pro** | 500 GB | 2 GB | Phase 2 | Level Upgrade / Standalone | K300/month |

### GrowNet Integration

**As Starter Kit Product:**
- Automatically provisioned when member registers
- Included in starter kit bundle pricing
- Generates referral commissions for upline
- Counts toward monthly qualification points

**As Level Upgrade Product:**
- Unlocked at specific professional levels (e.g., Growth at Senior, Pro at Manager)
- Optional upgrade for members at qualifying levels
- Generates upgrade commissions
- Contributes to level advancement points

**Commission Structure:**
- Level 1 (Direct Referral): 20% commission
- Level 2: 10% commission
- Level 3: 5% commission
- Follows standard GrowNet MLM commission rules

### Plan Features Matrix

| Feature | Starter | Basic | Growth | Pro |
|---------|---------|-------|--------|-----|
| Folder organization | ✓ | ✓ | ✓ | ✓ |
| Direct download links | ✓ | ✓ | ✓ | ✓ |
| Share links (Phase 2) | ✗ | ✓ | ✓ | ✓ |
| Team folders (Phase 2) | ✗ | ✗ | ✓ | ✓ |
| File versioning (Phase 2) | ✗ | ✗ | ✓ | ✓ |
| Priority support | ✗ | ✗ | ✗ | ✓ |
| Generates MLM commissions | ✗ | ✓ | ✓ | ✓ |
| Counts toward LP/MAP | ✓ | ✓ | ✓ | ✓ |

### Level Requirements

| Plan | Minimum Level | Notes |
|------|---------------|-------|
| **Starter** | Associate | Included in starter kit |
| **Basic** | Professional | Available as upgrade |
| **Growth** | Senior | Available as upgrade |
| **Pro** | Manager+ | Available as upgrade |

## Technical Architecture

### Domain-Driven Design (DDD) Approach

This module follows strict DDD principles:

**Domain Layer** (Business Logic)
- Entities: StorageFile, StorageFolder, StoragePlan, StorageQuota
- Value Objects: FileSize, StorageQuota, S3Path, MimeType
- Domain Services: QuotaEnforcementService, FileValidationService
- Repository Interfaces: StorageFileRepository, StorageFolderRepository
- Domain Events: FileUploaded, FileDeleted, QuotaExceeded

**Application Layer** (Use Cases)
- Use Cases: UploadFileUseCase, DeleteFileUseCase, GenerateDownloadUrlUseCase
- Command Handlers: UploadFileCommandHandler, DeleteFileCommandHandler
- Query Handlers: GetUserFilesQueryHandler, GetStorageUsageQueryHandler
- DTOs: UploadInitDTO, FileMetadataDTO, StorageUsageDTO

**Infrastructure Layer** (Technical Implementation)
- Persistence: Eloquent models, repository implementations
- External Services: S3StorageService, WasabiAdapter
- Event Handlers: FileUploadedEventHandler

**Presentation Layer** (API/UI)
- Controllers: StorageFileController, StorageFolderController
- Requests: UploadFileRequest, CreateFolderRequest
- Resources: FileResource, FolderResource

### High-Level Architecture

```
┌─────────────────────────────────────────┐
│   Presentation Layer (Vue 3 UI)         │
│  - File Manager, Upload, Progress       │
└──────────────┬──────────────────────────┘
               │ HTTPS
┌──────────────▼──────────────────────────┐
│   Presentation Layer (Laravel API)      │
│  - Controllers (thin, delegate to app)  │
│  - Requests, Resources                  │
└──────────────┬──────────────────────────┘
               │
┌──────────────▼──────────────────────────┐
│   Application Layer                     │
│  - Use Cases (orchestration)            │
│  - Command/Query Handlers               │
│  - DTOs                                 │
└──────────────┬──────────────────────────┘
               │
┌──────────────▼──────────────────────────┐
│   Domain Layer (Core Business Logic)    │
│  - Entities (rich models)               │
│  - Value Objects (immutable)            │
│  - Domain Services                      │
│  - Repository Interfaces                │
└──────────────┬──────────────────────────┘
               │
┌──────────────▼──────────────────────────┐
│   Infrastructure Layer                  │
│  - Eloquent Models (data access)        │
│  - Repository Implementations           │
│  - S3StorageService                     │
└──────────────┬──────────────────────────┘
               │ S3 API
┌──────────────▼──────────────────────────┐
│   Wasabi S3 Storage                     │
│  - Private Bucket                       │
│  - User-prefixed paths                  │
│  - Encrypted at rest                    │
└─────────────────────────────────────────┘
```

### Storage Provider Strategy

**Primary Provider:** Wasabi (S3-compatible)
- Cost-effective ($5.99/TB/month)
- No egress fees
- S3-compatible API

**Architecture Decision:** Keep code S3-compatible for easy provider switching
- Supports: Backblaze B2, DigitalOcean Spaces, AWS S3
- Use Laravel Filesystem abstraction
- Environment-based configuration

### Data Flow

#### Upload Flow (Direct-to-S3)
```
1. User selects file → Frontend validates size/type
2. POST /api/storage/files/upload-init
   → Backend checks quota
   → Generates signed PUT URL
   → Creates pending file record
3. Frontend PUT to S3 signed URL
   → Direct upload (no server bandwidth)
4. POST /api/storage/files/upload-complete
   → Backend verifies S3 object exists
   → Updates metadata
   → Increments usage counter
```

#### Download Flow
```
1. User clicks download
2. GET /api/storage/files/{id}/download
   → Backend checks authorization
   → Generates signed GET URL (5-15 min expiry)
3. Frontend redirects to signed URL
   → Direct download from S3
```

## Security Model

### Access Control
- **Private Bucket**: No public ACL, all access via signed URLs
- **User Isolation**: Files stored under `storage/{user_id}/` prefix
- **Authorization**: Every API call validates user_id ownership
- **Signed URLs**: Short expiry (5-15 minutes) for downloads

### File Validation
- **MIME Type Allowlist**: Configurable per plan
- **File Size Limits**: Enforced at upload-init
- **Path Sanitization**: Prevent directory traversal attacks
- **Checksum Verification**: Optional SHA-256 validation

### Anti-Abuse (Phase 2)
- Rate limiting on upload-init and download endpoints
- Block executable types (.exe, .bat, .sh, .js) for normal plans
- Video file limits (count/size) per plan
- Virus scanning integration (optional)

## User Experience

### Key User Flows

#### First-Time User
1. Navigate to Storage module
2. See empty state with "Upload your first file" CTA
3. View quota indicator (0 GB / 2 GB used)
4. Upload file via drag-drop or button
5. See upload progress
6. File appears in list

#### Organizing Files
1. Create folder structure
2. Drag files to folders (or use move dialog)
3. Navigate breadcrumb trail
4. Rename files/folders inline

#### Quota Management
1. User approaches quota limit
2. Warning appears at 80% usage
3. At 100%, upload blocked with upgrade CTA
4. User upgrades plan → quota increases immediately

### Error Handling
- **Quota Exceeded**: "Storage full. Upgrade your plan or delete files."
- **File Too Large**: "File exceeds 25 MB limit for Starter plan. Upgrade to Basic."
- **Upload Failed**: "Upload failed. Retry?" with retry button
- **Unsupported Type**: "File type not allowed. Allowed: PDF, DOC, JPG, PNG"

## Integration with GrowNet

### Product Integration
- **Product Type**: `storage` in products table
- **Product Variants**: Starter, Basic, Growth, Pro
- **Pricing**: Monthly subscription model
- **Commission Eligible**: Yes (except Starter when bundled)

### Subscription Sync
- Storage plan derived from GrowNet product subscription
- Automatic quota adjustment on tier change
- Nightly sync job: `storage:sync-subscriptions`
- Integrates with existing `user_product_subscriptions` table

### MLM Commission Integration
- Purchases trigger commission calculations via existing MLM system
- Uses standard 3-level commission structure
- Generates Lifetime Points (LP) for level advancement
- Generates Monthly Activity Points (MAP) for qualification
- Subscription renewals count as recurring commissions

### Points System Integration

**Lifetime Points (LP):**
- Starter Kit (included): 50 LP
- Basic upgrade: 100 LP
- Growth upgrade: 300 LP
- Pro upgrade: 500 LP

**Monthly Activity Points (MAP):**
- Active subscription: 20 MAP/month (auto-renewal)
- Referral sale: 50 MAP
- Team member upgrade: 30 MAP

### Inactive Member Handling
- Files NOT deleted when subscription lapses
- Plan downgraded to Starter (2 GB)
- If used > quota: block uploads until upgrade or deletion
- Grace period: 30 days to reactivate or clean up
- No commissions generated during inactive period

### Admin Override
- Admins can manually assign storage plans
- Useful for promotions, testing, or special cases
- Override tracked in `user_storage_subscriptions.source`
- Manual assignments don't generate commissions

## Success Metrics

### MVP Success Criteria
- 80% of active members upload at least one file within 30 days
- Average upload success rate > 95%
- Zero security incidents (unauthorized access)
- Page load time < 2 seconds for file manager
- Upload speed limited only by user bandwidth

### Phase 2 Metrics
- 30% of eligible users create share links
- Team folder adoption by 50% of leaders
- GrowBuilder backup usage by 40% of website owners

## Risks and Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| S3 costs exceed budget | High | Monitor usage, set alerts, implement quotas strictly |
| Abuse (large file spam) | Medium | Rate limiting, file type restrictions, admin monitoring |
| Data loss | High | S3 versioning enabled, regular backup audits |
| Slow uploads | Medium | Direct-to-S3 upload, CDN for downloads (Phase 2) |
| Quota drift (DB vs S3) | Medium | Nightly reconciliation job, transaction safety |

## Future Enhancements (Phase 3+)

- **Mobile App**: Native iOS/Android file access
- **File Previews**: In-browser preview for images, PDFs, videos
- **Advanced Search**: Full-text search, tags, filters
- **Collaboration**: Comments, annotations, real-time editing
- **API Access**: Developer API for third-party integrations
- **Backup Automation**: Scheduled backups for GrowBuilder sites
- **Media Library**: Centralized asset management for platform

## Changelog

### 2026-02-21 (Update 2)
- Clarified GrowNet MLM product integration (starter kit + level upgrades)
- Added commission structure and level requirements
- Emphasized Domain-Driven Design architecture
- Added LP/MAP points integration
- Documented product subscription integration

### 2026-02-21 (Initial)
- Initial concept document created
- Defined MVP scope and Phase 2 features
- Established storage plans aligned with GrowNet tiers
- Documented technical architecture and security model
