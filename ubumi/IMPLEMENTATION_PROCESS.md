# Ubumi - Implementation Process

**Last Updated:** January 21, 2026  
**Status:** Planning Phase  
**Timeline:** 6-12 months to pilot launch

---

## Phase-Based Implementation

### PHASE 1: Family Tree & Identity (MVP)
**Timeline:** Months 1-3  
**Goal:** Launch functional digital family tree system

#### Features

##### 1.1 Family Account Creation
- Family registration flow
- Family name and basic information
- Primary admin designation
- Terms acceptance and consent

##### 1.2 Family Tree Builder
- Add family members (parent-child, siblings, guardians)
- Visual tree representation
- Relationship mapping
- Multi-generational support

##### 1.3 Member Profiles
**Core Fields:**
- Name (required)
- Photo (optional)
- Date of birth (optional, can be approximate)
- Relationship to admin (required)
- Gender (optional)
- Location (optional)
- Contact information (optional)

**Flexible Data Entry:**
- Support for "approximately [age]" instead of exact DOB
- Nickname/clan name fields
- Alternative name spellings
- Deceased member marking

##### 1.4 Family Roles
- **Admin (Family Head/Coordinator)**
  - Full access to family tree
  - Can add/edit/remove members
  - Approve merge requests
  - Manage family settings
  
- **Member**
  - View family tree
  - Edit own profile
  - Propose additions
  - Suggest merges

- **Caregiver** (optional role)
  - Assist specific family members
  - Receive targeted alerts
  - Limited administrative access

#### Technical Requirements

**Frontend:**
- Android app (Kotlin or Flutter)
- Simple, intuitive UI
- Offline-first architecture
- Low-data mode
- Local language support (English first)

**Backend:**
- REST or GraphQL API
- Secure authentication (JWT)
- Family-based data segmentation
- Role-based access control

**Database:**
- Person records
- Relationship mapping
- Family accounts
- User authentication

**Hosting:**
- Cloud-based (Africa region preferred)
- Scalable infrastructure
- Automated backups
- 99.9% uptime target

#### Deliverables
- Functional Android app
- Admin web dashboard (optional)
- User documentation
- Privacy policy and terms
- Pilot recruitment materials

---

### PHASE 2: Wellness Check-In System
**Timeline:** Months 4-5  
**Goal:** Enable non-clinical health status sharing

#### Features

##### 2.1 Check-In Interface
**Simple Status Options:**
- 😊 "I am well"
- 😐 "I am not feeling well"
- 🆘 "I need assistance"

**Additional Inputs:**
- Optional note (free text or voice)
- Optional date/time confirmation
- Location sharing (consent-based, optional)

##### 2.2 Check-In Frequency
- Configurable per family
- Suggested: weekly for adults, daily for vulnerable members
- Reminders (push notifications)
- Missed check-in tracking

##### 2.3 Notification System
**Alert Triggers:**
- "Not feeling well" status
- "Need assistance" status
- Missed check-ins (configurable threshold)

**Alert Recipients:**
- Family admin
- Designated caregivers
- Specified family members

**Alert Channels:**
- In-app notifications
- SMS (for offline users)
- Email (optional)

##### 2.4 Legal & Ethical Safeguards
**Clear Disclaimers:**
- "This is not a diagnostic tool"
- "Not a substitute for healthcare"
- "Seek professional medical help for emergencies"

**Consent Management:**
- Explicit consent for check-ins
- Opt-in for notifications
- Right to disable at any time

#### Technical Requirements

**New Components:**
- Check-in recording system
- Notification engine
- SMS gateway integration
- Push notification service

**Data Storage:**
- Check-in history
- Notification logs
- User preferences

#### Deliverables
- Check-in feature in app
- Notification system
- Updated user guide
- Legal disclaimer screens

---

### PHASE 3: Alerts & Care Coordination
**Timeline:** Months 6-7  
**Goal:** Proactive family care coordination

#### Features

##### 3.1 Missed Check-In Alerts
- Configurable thresholds (e.g., 72 hours)
- Escalation rules
- Snooze/acknowledge options

##### 3.2 Caregiver Designation
- Assign caregivers to specific members
- Multiple caregivers per person
- Caregiver dashboard
- Care activity log

##### 3.3 Emergency Contact Escalation
- Primary and secondary contacts
- Automatic escalation if no response
- Emergency contact verification

##### 3.4 Location Sharing (Optional)
- Consent-based
- Time-limited sharing
- Privacy controls
- "Last seen" indicator

##### 3.5 Care Coordination Tools
- Care notes (shared among caregivers)
- Visit scheduling
- Medication reminders (non-medical)
- Appointment tracking

#### Technical Requirements

**New Components:**
- Escalation engine
- Caregiver dashboard
- Location services integration
- Care activity tracking

#### Deliverables
- Enhanced alert system
- Caregiver features
- Care coordination tools
- Updated documentation

---

### PHASE 4: Institutional & Community Integration
**Timeline:** Months 8-12  
**Goal:** Bridge families and community resources

#### Features

##### 4.1 Community Health Worker Integration
- CHW dashboard (separate app/portal)
- Family referrals (with consent)
- Follow-up tracking
- Aggregated community insights

##### 4.2 NGO & Faith-Based Partnerships
- Program enrollment (opt-in)
- Resource directory
- Event notifications
- Support group connections

##### 4.3 Data Sharing Framework
**Strict Controls:**
- Opt-in only
- Explicit consent per partner
- Aggregated data only (no individual records)
- Revocable at any time

**Use Cases:**
- Community health planning
- Resource allocation
- Program effectiveness
- Research (anonymized)

##### 4.4 Institutional Dashboard
- Aggregated statistics
- Community health trends
- Program impact metrics
- No individual identification

#### Technical Requirements

**New Components:**
- Partner portal
- Consent management system
- Data aggregation engine
- Analytics dashboard

**Security:**
- Enhanced encryption
- Audit logging
- Compliance monitoring

#### Deliverables
- Partner integration framework
- Institutional dashboard
- Data sharing agreements
- Compliance documentation

---

## Duplicate Record Handling Strategy

### Problem
Two family members may add duplicate records for the same person due to:
- Different name spellings
- Unknown or approximate dates of birth
- Different nicknames
- Missing information

### Solution: 3-Layer Approach

#### Layer 1: Prevention (Soft, Non-Blocking)

**Smart Similarity Warnings**
When adding a new person, the system:
1. Compares name similarity (phonetic + spelling)
2. Checks approximate age range
3. Matches parent names (if provided)
4. Shows non-blocking warning: *"A similar family member may already exist. Do you want to review?"*

**User Options:**
- View possible matches
- Continue adding anyway
- Merge with existing record

**Important:** Never hard-block creation.

**Guided Data Entry:**
- Encourage uniqueness without forcing it
- Optional nickname/clan name fields
- Relationship-first entry (e.g., "my aunt")
- Estimated age instead of exact DOB

#### Layer 2: Detection (Post-Creation)

**Automated Duplicate Detection**
Background checks flag potential duplicates using:
- Name similarity algorithms
- Shared parents or children
- Similar age ranges
- Photo matching (hashing)

**Family-Level Visibility:**
- Only show duplicate alerts within same family tree
- Never compare across families (privacy)

**Example Alert:**
*"Two records may refer to the same person: [Name A] and [Name B]. Would you like to review?"*

#### Layer 3: Resolution (Human-Centered)

**Merge Request Workflow**

1. **Proposal**: Any member can propose a merge
   - Select records A and B
   - Suggest final name
   - Add notes (e.g., "Same person, different spellings")

2. **Review**: Family admin reviews proposal
   - View both records side-by-side
   - See all relationships
   - Check history

3. **Decision**: Admin approves or rejects
   - Approve: Records merge
   - Reject: Records remain separate
   - Mark as "Possibly same person" for future review

**Decision Authority by Role:**

| Role | Permission |
|------|------------|
| Admin / Family Head | Approve merge |
| Member | Propose merge |
| Caregiver | Comment only |

**Merge Process:**
- Preserve all history
- Keep aliases (alternative names)
- Combine relationships
- Log who approved and when
- **Nothing is silently deleted**

**Conflict Handling:**
- If family disagrees, records remain separate
- Mark as "Possibly same person"
- Revisit later
- **Do not force consensus**

**Elder/Sensitive Cases:**
- Require admin approval
- Extra confirmation prompts
- Respect cultural sensitivity

### Technical Implementation

**Data Model:**
```
Person
- id
- family_id
- name
- date_of_birth (nullable)
- approximate_age (nullable)
- photo
- is_deceased
- created_by
- created_at

PersonAlias
- id
- person_id
- alias_name
- alias_type (nickname, spelling_variant, clan_name)

Relationship
- id
- person_id
- related_person_id
- relationship_type

MergeProposal
- id
- family_id
- person_a_id
- person_b_id
- proposed_name
- notes
- proposed_by
- status (pending, approved, rejected)
- reviewed_by
- reviewed_at

MergeHistory
- id
- merge_proposal_id
- person_a_data (JSON)
- person_b_data (JSON)
- merged_person_id
- merged_at
```

**Key Rules:**
- Never delete original records
- Merges are reversible
- Full audit trail maintained
- Soft deletes only

### User Experience Messaging

**Avoid Technical Language:**
- ❌ "Duplicate detected"
- ✅ "Similar family member"

- ❌ "Merge duplicates"
- ✅ "Combine records"

- ❌ "Conflict resolution"
- ✅ "Possibly the same person"

**Policy Statement (In-App):**
*"Families remember people differently. This app allows families to identify, discuss, and combine records together when they agree."*

### Why This Approach Works
- ✅ Respects imperfect family knowledge
- ✅ Prevents data loss
- ✅ Builds trust
- ✅ Scales as families grow
- ✅ Aligns with African family dynamics
- ✅ Honors cultural hierarchy

---

## Development Roadmap

### Pre-Development (Weeks 1-4)
- [ ] Stakeholder interviews (20+ families)
- [ ] User research and personas
- [ ] Wireframes and user flows
- [ ] Technical architecture design
- [ ] Legal compliance review
- [ ] Budget and resource planning

### Phase 1 Development (Months 1-3)
- [ ] Week 1-2: Project setup and infrastructure
- [ ] Week 3-4: Authentication and family accounts
- [ ] Week 5-6: Family tree builder
- [ ] Week 7-8: Member profiles and relationships
- [ ] Week 9-10: Offline functionality
- [ ] Week 11-12: Testing and refinement

### Phase 2 Development (Months 4-5)
- [ ] Week 1-2: Check-in interface
- [ ] Week 3-4: Notification system
- [ ] Week 5-6: SMS integration
- [ ] Week 7-8: Testing and refinement

### Phase 3 Development (Months 6-7)
- [ ] Week 1-2: Alert escalation
- [ ] Week 3-4: Caregiver features
- [ ] Week 5-6: Care coordination tools
- [ ] Week 7-8: Testing and refinement

### Pilot Launch (Month 8)
- [ ] Recruit 100-300 families
- [ ] Onboarding and training
- [ ] Launch in Lusaka (urban)
- [ ] Launch in one rural district
- [ ] Monitor and support

### Pilot Evaluation (Months 9-12)
- [ ] Collect user feedback
- [ ] Analyze usage data
- [ ] Measure success metrics
- [ ] Iterate and improve
- [ ] Plan Phase 4 (institutional integration)

---

## Technical Stack Recommendation

### Frontend
**Option 1: Flutter (Recommended)**
- Cross-platform (Android + iOS future)
- Single codebase
- Good offline support
- Rich UI components

**Option 2: Native Android (Kotlin)**
- Best performance
- Full Android features
- Steeper learning curve

### Backend
**Recommended: Laravel (PHP)**
- Rapid development
- Strong ecosystem
- Good documentation
- Aligns with MyGrowNet stack

**Alternative: Node.js (Express)**
- JavaScript full-stack
- Real-time capabilities
- Scalable

### Database
**Recommended: PostgreSQL**
- Robust and reliable
- Good for relational data
- JSON support for flexibility

### Hosting
**Recommended: AWS (Africa Region)**
- Cape Town data center
- Scalable infrastructure
- Comprehensive services

**Alternative: DigitalOcean**
- Simpler setup
- Cost-effective
- Good for MVP

### Services
- **Authentication**: Laravel Sanctum or Firebase Auth
- **Push Notifications**: Firebase Cloud Messaging
- **SMS**: Africa's Talking or Twilio
- **File Storage**: AWS S3 or DigitalOcean Spaces
- **Analytics**: Mixpanel or custom

---

## Resource Requirements

### Team
- **Product Manager**: 1 (full-time)
- **Backend Developer**: 1-2 (full-time)
- **Mobile Developer**: 1-2 (full-time)
- **UI/UX Designer**: 1 (part-time)
- **QA Tester**: 1 (part-time)
- **Community Liaison**: 1 (part-time)

### Budget Estimate (USD)
- **Development**: $30,000 - $50,000
- **Infrastructure**: $500 - $1,000/month
- **Legal/Compliance**: $5,000 - $10,000
- **Pilot Operations**: $10,000 - $15,000
- **Total Year 1**: $60,000 - $90,000

### Timeline
- **MVP Development**: 3 months
- **Testing & Refinement**: 1 month
- **Pilot Preparation**: 1 month
- **Pilot Execution**: 6 months
- **Total**: 11-12 months to validated product

---

## Monetization Strategy

### Short-Term (Years 1-2)
- **Free core features** for all families
- **Grant funding** from health/social NGOs
- **Pilot partnerships** with community organizations

### Medium-Term (Years 3-4)
**Premium Family Plans** ($2-5/month):
- Larger family trees (100+ members)
- Extended history and archives
- Advanced alert customization
- Priority support
- Ad-free experience

**Institutional Licensing** ($50-200/month):
- Community health worker access
- Aggregated analytics
- Custom integrations
- Training and support

### Long-Term (Years 5+)
- **White-label solutions** for other countries
- **API access** for health systems
- **Data insights** (aggregated, anonymized)
- **Premium features** (video check-ins, health tracking)

**Important:** No ads in early stages. No sale of personal data ever.

---

## Success Criteria

### Phase 1 (MVP)
- [ ] 100+ families registered
- [ ] 80%+ family tree completion rate
- [ ] 4.0+ app store rating
- [ ] <5% critical bug rate

### Phase 2 (Check-Ins)
- [ ] 60%+ weekly check-in rate
- [ ] <2 hour average alert response time
- [ ] 70%+ user satisfaction score

### Phase 3 (Care Coordination)
- [ ] 40%+ families using caregiver features
- [ ] 50%+ reduction in missed check-ins
- [ ] 5+ documented early interventions

### Pilot Overall
- [ ] 200+ active families
- [ ] 70%+ monthly retention
- [ ] 80%+ would recommend to others
- [ ] Zero major security incidents
- [ ] Positive community feedback

---

## Risk Mitigation

### Technical Risks
- **Offline sync conflicts**: Implement robust conflict resolution
- **Data loss**: Automated backups, redundancy
- **Performance issues**: Load testing, optimization
- **Security breaches**: Penetration testing, audits

### User Adoption Risks
- **Low elderly adoption**: Assisted onboarding, family training
- **Trust concerns**: Transparency, community endorsements
- **Feature confusion**: Simple UI, progressive disclosure
- **Language barriers**: Multi-language support, voice input

### Operational Risks
- **Resource constraints**: Phased approach, MVP focus
- **Regulatory changes**: Legal monitoring, compliance updates
- **Partner dependencies**: Multiple SMS providers, backup plans
- **Funding gaps**: Diversified funding sources

---

## Next Immediate Steps

### Week 1-2: Validation
1. **Stakeholder Interviews**
   - 10 families (urban + rural)
   - 5 community health workers
   - 3 NGO representatives
   - 2 government health officials

2. **Competitive Analysis**
   - Review existing solutions
   - Identify gaps and opportunities
   - Define unique value proposition

3. **Legal Review**
   - Data protection requirements
   - Health information regulations
   - Terms of service draft
   - Privacy policy draft

### Week 3-4: Planning
1. **Detailed PRD** (Product Requirements Document)
2. **Technical Architecture** document
3. **UI/UX Wireframes** (key screens)
4. **Project Plan** with milestones
5. **Budget Breakdown** and funding strategy

### Week 5-6: Preparation
1. **Team Assembly** (hire or assign)
2. **Development Environment** setup
3. **Design System** creation
4. **Pilot Partner** identification
5. **Community Engagement** plan

---

## Appendices

### A. User Stories

**As a family admin, I want to:**
- Create a family account so my family can stay connected
- Add family members to our tree so we have a complete record
- Receive alerts when someone needs help so I can respond quickly
- Manage who can see what information so privacy is protected

**As a family member, I want to:**
- Check in regularly so my family knows I'm okay
- See my family tree so I understand my lineage
- Get reminders to check in so I don't forget
- Update my own profile so my information is current

**As a caregiver, I want to:**
- Monitor specific family members so I can provide care
- Receive targeted alerts so I know when to act
- Log care activities so the family is informed
- Coordinate with other caregivers so we work together

**As an elderly user, I want to:**
- Simple check-in options so I can participate easily
- Voice input so I don't have to type
- Large text and buttons so I can see clearly
- Help from family members so I'm not alone

### B. Key Screens

1. **Onboarding Flow**
   - Welcome screen
   - Family creation
   - First member addition
   - Permissions explanation

2. **Family Tree View**
   - Visual tree representation
   - Member cards
   - Add member button
   - Search and filter

3. **Check-In Interface**
   - Status selection (3 options)
   - Optional note
   - Submit button
   - History view

4. **Alerts Dashboard**
   - Active alerts
   - Alert history
   - Response tracking
   - Settings

5. **Member Profile**
   - Photo and basic info
   - Relationships
   - Check-in history
   - Edit options

### C. Technical Diagrams

**System Architecture:**
```
[Mobile App] <-> [API Gateway] <-> [Application Server]
                                          |
                    +---------------------+---------------------+
                    |                     |                     |
              [Database]          [File Storage]        [Notification Service]
                    |                     |                     |
              [Backup System]      [CDN]              [SMS Gateway]
```

**Data Flow:**
```
User Check-In -> App (offline storage) -> Sync when online -> 
API -> Database -> Trigger notifications -> SMS/Push -> 
Family members receive alert
```

### D. Glossary

- **Family Tree**: Digital representation of family relationships
- **Check-In**: Self-reported wellness status update
- **Alert**: Notification sent when attention needed
- **Family Admin**: Primary coordinator for family account
- **Caregiver**: Designated helper for specific members
- **Merge**: Combining duplicate records
- **Active Member**: User who checked in recently
- **Offline-First**: App works without internet connection

---

## Document Control

**Version:** 1.0  
**Author:** MyGrowNet Development Team  
**Review Date:** January 21, 2026  
**Next Review:** February 21, 2026  
**Status:** Living Document

---

## Changelog

### Version 1.0 (January 21, 2026)
- Initial implementation process document
- Defined 4-phase approach
- Detailed duplicate handling strategy
- Established technical stack recommendations
- Created development roadmap
- Defined success criteria and risk mitigation

---

*"Building with families, for families."*
