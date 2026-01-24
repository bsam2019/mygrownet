# Duplicate Handling - Technical Specification

**Last Updated:** January 21, 2026  
**Status:** Technical Design  
**Component:** Family Tree Management

## Overview

This document provides detailed technical specifications for handling duplicate family member records in the Ubumi platform.

## Problem Statement

Family members may create duplicate records for the same person due to:
- Different name spellings or nicknames
- Unknown or approximate dates of birth
- Missing information
- Cultural naming variations
- Multiple people adding the same relative independently

## Design Principles

1. **Never block creation** - Allow duplicates to be created
2. **Detect intelligently** - Use algorithms to find potential duplicates
3. **Resolve collaboratively** - Family decides, system facilitates
4. **Preserve history** - Never delete data, only merge
5. **Respect hierarchy** - Honor family decision-making structure

---

## Architecture

### 3-Layer System

```
┌─────────────────────────────────────────────────┐
│           Layer 1: Prevention                    │
│  (Soft warnings before record creation)          │
└─────────────────────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────┐
│           Layer 2: Detection                     │
│  (Background duplicate identification)           │
└─────────────────────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────┐
│           Layer 3: Resolution                    │
│  (Human-centered merge workflow)                 │
└─────────────────────────────────────────────────┘
```

---

## Layer 1: Prevention

### Smart Similarity Check

**Trigger:** When user attempts to add a new family member

**Algorithm:**

```python
def check_similarity(new_person, existing_family_members):
    """
    Check for potential duplicates before creation
    Returns: List of potential matches with confidence scores
    """
    matches = []
    
    for existing in existing_family_members:
        score = 0
        
        # Name similarity (40% weight)
        name_score = calculate_name_similarity(
            new_person.name, 
            existing.name
        )
        score += name_score * 0.4
        
        # Age similarity (30% weight)
        if new_person.age and existing.age:
            age_diff = abs(new_person.age - existing.age)
            age_score = max(0, 1 - (age_diff / 10))
            score += age_score * 0.3
        
        # Parent match (30% weight)
        if has_common_parents(new_person, existing):
            score += 0.3
        
        # Threshold: 0.6 = potential duplicate
        if score >= 0.6:
            matches.append({
                'person': existing,
                'confidence': score
            })
    
    return sorted(matches, key=lambda x: x['confidence'], reverse=True)

def calculate_name_similarity(name1, name2):
    """
    Uses Levenshtein distance and phonetic matching
    """
    # Normalize names
    n1 = normalize_name(name1)
    n2 = normalize_name(n2)
    
    # Levenshtein distance
    lev_score = 1 - (levenshtein_distance(n1, n2) / max(len(n1), len(n2)))
    
    # Phonetic matching (Soundex or Metaphone)
    phonetic_score = 1.0 if soundex(n1) == soundex(n2) else 0.0
    
    # Combined score
    return (lev_score * 0.6) + (phonetic_score * 0.4)
```

### UI Flow

```
User clicks "Add Family Member"
    ↓
Enters name, age, relationship
    ↓
On "Save" click:
    ↓
System checks for similar records
    ↓
If matches found (confidence >= 0.6):
    ↓
Show warning modal:
    "A similar family member may already exist"
    [List of potential matches with photos]
    
    Options:
    - "This is the same person" → Go to merge flow
    - "View details" → Compare side-by-side
    - "Continue anyway" → Create new record
    ↓
User decides
```

---

## Layer 2: Detection

### Background Duplicate Scanner

**Frequency:** Runs nightly for each family

**Process:**
1. Fetch all family members
2. Compare each pair using similarity algorithm
3. Flag pairs with confidence >= 0.7
4. Create duplicate alert for family admin
5. Store in `duplicate_alerts` table

### Database Schema

```sql
CREATE TABLE duplicate_alerts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    family_id BIGINT NOT NULL,
    person_a_id BIGINT NOT NULL,
    person_b_id BIGINT NOT NULL,
    confidence_score DECIMAL(3,2) NOT NULL,
    status ENUM('pending', 'reviewed', 'merged', 'dismissed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    reviewed_by BIGINT NULL,
    
    FOREIGN KEY (family_id) REFERENCES families(id),
    FOREIGN KEY (person_a_id) REFERENCES persons(id),
    FOREIGN KEY (person_b_id) REFERENCES persons(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id),
    
    INDEX idx_family_status (family_id, status),
    INDEX idx_confidence (confidence_score DESC)
);
```

### Notification

When duplicates detected:
- In-app notification to family admin
- Weekly digest email (if multiple alerts)
- Dashboard badge count

---

## Layer 3: Resolution

### Merge Workflow

#### Step 1: Proposal

**Who can propose:** Any family member

**UI:**
```
[Person A Card]  [Person B Card]
    ↓                 ↓
[Merge These Records Button]
    ↓
Merge Proposal Form:
- Suggested name: [dropdown: A's name / B's name / Custom]
- Keep photo: [A / B / Both]
- Notes: [text area]
- [Submit for Review]
```

**API Endpoint:**
```
POST /api/families/{familyId}/merge-proposals
{
    "person_a_id": 123,
    "person_b_id": 456,
    "proposed_name": "John Mwansa",
    "keep_photo_from": "person_a",
    "notes": "Same person, different spellings"
}
```

#### Step 2: Review

**Who reviews:** Family admin only

**UI:**
```
Merge Proposal #123
Proposed by: [Member Name]
Date: [Date]

┌─────────────────────────────────────────┐
│  Person A          vs.      Person B     │
│  Name: John Mwansa          Jon Mwanza   │
│  Age: 45                    ~45          │
│  Photo: [img]               [img]        │
│  Added by: Alice            Bob          │
│  Relationships:             Relationships:│
│  - Son of Mary              - Son of Mary│
│  - Father of Peter          - Father of Pete│
└─────────────────────────────────────────┘

Proposed merged record:
Name: John Mwansa
Photo: [from Person A]
Notes: Same person, different spellings

[Approve Merge]  [Reject]  [Need More Info]
```

#### Step 3: Execution

**On approval:**

```python
def execute_merge(proposal_id, admin_user_id):
    """
    Merge two person records
    """
    proposal = get_merge_proposal(proposal_id)
    person_a = get_person(proposal.person_a_id)
    person_b = get_person(proposal.person_b_id)
    
    # Start transaction
    with database.transaction():
        # 1. Create merged person record
        merged_person = create_person({
            'family_id': person_a.family_id,
            'name': proposal.proposed_name,
            'photo': select_photo(proposal),
            'date_of_birth': select_dob(person_a, person_b),
            'is_merged': True,
            'merged_from': [person_a.id, person_b.id]
        })
        
        # 2. Create aliases for alternative names
        create_alias(merged_person.id, person_a.name, 'original_name')
        if person_a.name != person_b.name:
            create_alias(merged_person.id, person_b.name, 'original_name')
        
        # 3. Transfer all relationships
        transfer_relationships(person_a.id, merged_person.id)
        transfer_relationships(person_b.id, merged_person.id)
        
        # 4. Transfer check-in history
        transfer_checkins(person_a.id, merged_person.id)
        transfer_checkins(person_b.id, merged_person.id)
        
        # 5. Soft delete original records
        soft_delete_person(person_a.id)
        soft_delete_person(person_b.id)
        
        # 6. Create merge history record
        create_merge_history({
            'merge_proposal_id': proposal.id,
            'person_a_data': serialize(person_a),
            'person_b_data': serialize(person_b),
            'merged_person_id': merged_person.id,
            'merged_by': admin_user_id,
            'merged_at': now()
        })
        
        # 7. Update proposal status
        update_proposal_status(proposal.id, 'merged')
        
        # 8. Notify family members
        notify_family_of_merge(person_a.family_id, merged_person)
    
    return merged_person
```

### Undo Merge

**Who can undo:** Family admin only  
**Time limit:** 30 days after merge

```python
def undo_merge(merge_history_id, admin_user_id):
    """
    Reverse a merge operation
    """
    history = get_merge_history(merge_history_id)
    
    with database.transaction():
        # 1. Restore original records
        person_a = restore_person(history.person_a_data)
        person_b = restore_person(history.person_b_data)
        
        # 2. Restore relationships
        restore_relationships(person_a.id, history.person_a_data)
        restore_relationships(person_b.id, history.person_b_data)
        
        # 3. Soft delete merged record
        soft_delete_person(history.merged_person_id)
        
        # 4. Log undo action
        log_merge_undo(merge_history_id, admin_user_id)
        
        # 5. Notify family
        notify_family_of_undo(person_a.family_id)
    
    return (person_a, person_b)
```

---

## Database Schema

### Core Tables

```sql
-- Main person table
CREATE TABLE persons (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    family_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    photo_url VARCHAR(500),
    date_of_birth DATE NULL,
    approximate_age INT NULL,
    gender ENUM('male', 'female', 'other', 'prefer_not_to_say') NULL,
    is_deceased BOOLEAN DEFAULT FALSE,
    is_merged BOOLEAN DEFAULT FALSE,
    merged_from JSON NULL, -- Array of original person IDs
    created_by BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL, -- Soft delete
    
    FOREIGN KEY (family_id) REFERENCES families(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    
    INDEX idx_family (family_id),
    INDEX idx_name (name),
    INDEX idx_deleted (deleted_at)
);

-- Alternative names/aliases
CREATE TABLE person_aliases (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    person_id BIGINT NOT NULL,
    alias_name VARCHAR(255) NOT NULL,
    alias_type ENUM('nickname', 'spelling_variant', 'clan_name', 'original_name') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE CASCADE,
    
    INDEX idx_person (person_id),
    INDEX idx_alias (alias_name)
);

-- Merge proposals
CREATE TABLE merge_proposals (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    family_id BIGINT NOT NULL,
    person_a_id BIGINT NOT NULL,
    person_b_id BIGINT NOT NULL,
    proposed_name VARCHAR(255) NOT NULL,
    keep_photo_from ENUM('person_a', 'person_b', 'both') DEFAULT 'person_a',
    notes TEXT,
    proposed_by BIGINT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'merged') DEFAULT 'pending',
    reviewed_by BIGINT NULL,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (family_id) REFERENCES families(id),
    FOREIGN KEY (person_a_id) REFERENCES persons(id),
    FOREIGN KEY (person_b_id) REFERENCES persons(id),
    FOREIGN KEY (proposed_by) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id),
    
    INDEX idx_family_status (family_id, status),
    INDEX idx_proposed_by (proposed_by)
);

-- Merge history (audit trail)
CREATE TABLE merge_history (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    merge_proposal_id BIGINT NOT NULL,
    person_a_data JSON NOT NULL, -- Full snapshot of person A
    person_b_data JSON NOT NULL, -- Full snapshot of person B
    merged_person_id BIGINT NOT NULL,
    merged_by BIGINT NOT NULL,
    merged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    undone_at TIMESTAMP NULL,
    undone_by BIGINT NULL,
    
    FOREIGN KEY (merge_proposal_id) REFERENCES merge_proposals(id),
    FOREIGN KEY (merged_person_id) REFERENCES persons(id),
    FOREIGN KEY (merged_by) REFERENCES users(id),
    FOREIGN KEY (undone_by) REFERENCES users(id),
    
    INDEX idx_merged_person (merged_person_id),
    INDEX idx_merged_at (merged_at)
);
```

---

## API Endpoints

### Check for Duplicates
```
POST /api/families/{familyId}/check-duplicates
Request:
{
    "name": "John Mwansa",
    "age": 45,
    "parent_ids": [12, 34]
}

Response:
{
    "matches": [
        {
            "person_id": 123,
            "name": "Jon Mwanza",
            "age": 45,
            "photo_url": "...",
            "confidence": 0.85,
            "reasons": ["Similar name", "Same age", "Same parents"]
        }
    ]
}
```

### Create Merge Proposal
```
POST /api/families/{familyId}/merge-proposals
Request:
{
    "person_a_id": 123,
    "person_b_id": 456,
    "proposed_name": "John Mwansa",
    "keep_photo_from": "person_a",
    "notes": "Same person"
}

Response:
{
    "proposal_id": 789,
    "status": "pending",
    "message": "Merge proposal submitted for review"
}
```

### Review Merge Proposal
```
PUT /api/families/{familyId}/merge-proposals/{proposalId}
Request:
{
    "action": "approve", // or "reject"
    "admin_notes": "Confirmed with family"
}

Response:
{
    "status": "merged",
    "merged_person_id": 999,
    "message": "Records successfully merged"
}
```

### Undo Merge
```
POST /api/families/{familyId}/merge-history/{historyId}/undo
Request:
{
    "reason": "Merged by mistake"
}

Response:
{
    "status": "undone",
    "restored_person_ids": [123, 456],
    "message": "Merge successfully undone"
}
```

---

## User Experience Guidelines

### Messaging

**Prevention Stage:**
- ✅ "A similar family member may already exist"
- ❌ "Duplicate detected"

**Detection Stage:**
- ✅ "These records might be the same person"
- ❌ "Duplicate found"

**Resolution Stage:**
- ✅ "Combine these records"
- ❌ "Merge duplicates"

### Visual Design

**Similarity Indicators:**
- High confidence (>0.8): Yellow warning icon
- Medium confidence (0.6-0.8): Blue info icon
- Show confidence as "Very likely" / "Possibly" / "Maybe"

**Comparison View:**
- Side-by-side cards
- Highlight differences in red
- Highlight similarities in green
- Show relationship overlap

---

## Testing Strategy

### Unit Tests
- Name similarity algorithm accuracy
- Age comparison logic
- Parent matching logic
- Merge execution integrity
- Undo merge functionality

### Integration Tests
- End-to-end merge workflow
- API endpoint responses
- Database transaction integrity
- Notification delivery

### User Acceptance Tests
- Family admin can review proposals
- Members can propose merges
- Merged records preserve all data
- Undo works within time limit
- Notifications are clear and actionable

---

## Performance Considerations

### Optimization
- Index on `name`, `family_id`, `deleted_at`
- Cache similarity calculations
- Batch duplicate detection (nightly)
- Limit comparison to same family only

### Scalability
- Partition by family_id
- Archive old merge history (>1 year)
- Async processing for large families

---

## Security & Privacy

### Access Control
- Only family members can see duplicates
- Only admin can approve merges
- Audit log for all merge actions
- No cross-family data exposure

### Data Protection
- Soft deletes only (never hard delete)
- Full audit trail maintained
- Encrypted sensitive data
- GDPR-compliant data export

---

## Changelog

### Version 1.0 (January 21, 2026)
- Initial technical specification
- 3-layer architecture defined
- Database schema designed
- API endpoints specified
- UX guidelines established

---

**Status:** Ready for Development  
**Next Step:** Implementation in Phase 1
