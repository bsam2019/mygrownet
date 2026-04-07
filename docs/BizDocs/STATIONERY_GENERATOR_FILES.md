# Stationery Generator - Complete File List

**Date:** April 1, 2026  
**Purpose:** Complete list of all files used by the stationery generator for manual review

---

## Request Flow

```
User Browser (Vue)
    ↓
Controller (HTTP)
    ↓
DTO (Data Transfer Object)
    ↓
UseCase (Application Logic)
    ↓
Service (PDF Generation)
    ↓
Blade Template (HTML)
    ↓
DomPDF (PDF Output)
```

---

## 1. Frontend Files

### Main Component
**File:** `resources/js/pages/BizDocs/Stationery/Generator.vue`
- **Purpose:** User interface for configuring and generating stationery
- **Key Variables:**
  - `form.documents_per_page` - User selection (1, 2, 4, 6, 8, 10)
  - Sends to backend via `route('bizdocs.stationery.generate')`
- **Check:** Line where form data is sent to backend

---

## 2. Backend Files (Request Flow)

### 2.1 Routes
**File:** `routes/bizdocs.php`
- **Purpose:** Defines routes for stationery ge