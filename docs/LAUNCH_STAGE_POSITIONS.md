# MyGrowNet Launch Stage - Organizational Positions

**Date:** November 5, 2025  
**Phase:** Phase 1 (SHORT TERM - 0-6 Months)  
**Status:** Seeded and Ready

---

## Total Positions: 14

Based on the organizational structure documentation and the seeder implementation, MyGrowNet has **14 positions** defined for the launch stage.

---

## Position Breakdown by Level

### C-Level Positions (5)
**Organizational Level:** `c_level`  
**Reporting To:** CEO (except CEO who reports to Board)

1. **Chief Executive Officer (CEO)**
   - Department: Executive
   - Level: 1 (Top of hierarchy)
   - Salary Range: K50,000 - K100,000
   - Reports To: Board of Directors

2. **Chief Operating Officer (COO)**
   - Department: Operations
   - Level: 2
   - Salary Range: K40,000 - K60,000
   - Reports To: CEO

3. **Chief Financial Officer (CFO)**
   - Department: Finance & Compliance
   - Level: 2
   - Salary Range: K45,000 - K70,000
   - Reports To: CEO

4. **Chief Technology Officer (CTO)**
   - Department: Technology
   - Level: 2
   - Salary Range: K35,000 - K55,000
   - Reports To: CEO

5. **Chief Growth Officer (CGO)**
   - Department: Growth & Marketing
   - Level: 2
   - Salary Range: K35,000 - K55,000
   - Reports To: CEO

---

### Manager Level Positions (4)
**Organizational Level:** `manager`  
**Reporting To:** Respective C-Level Officers

6. **Operations Manager** ⭐ PRIORITY #1
   - Department: Operations
   - Level: 3
   - Salary Range: K15,000 - K25,000
   - Reports To: COO
   - **Critical for launch operations**

7. **Finance & Compliance Lead**
   - Department: Finance & Compliance
   - Level: 3
   - Salary Range: K12,000 - K20,000
   - Reports To: CFO

8. **Technology Lead**
   - Department: Technology
   - Level: 3
   - Salary Range: K10,000 - K18,000
   - Reports To: CTO

9. **Growth & Marketing Lead**
   - Department: Growth & Marketing
   - Level: 3
   - Salary Range: K10,000 - K18,000
   - Reports To: CGO

---

### Team Lead Positions (1)
**Organizational Level:** `team_lead`

10. **Member Support Team Lead**
    - Department: Operations
    - Level: 4
    - Salary Range: K6,000 - K10,000
    - Reports To: Operations Manager

---

### Individual Contributor Positions (4)
**Organizational Level:** `individual`

11. **Member Support Agent**
    - Department: Operations
    - Level: 5
    - Salary Range: K3,000 - K5,000
    - Reports To: Member Support Team Lead

12. **Accountant**
    - Department: Finance & Compliance
    - Level: 4
    - Salary Range: K5,000 - K8,000
    - Reports To: Finance & Compliance Lead

13. **Content Creator**
    - Department: Growth & Marketing
    - Level: 4
    - Salary Range: K4,000 - K7,000
    - Reports To: Growth & Marketing Lead

14. **Social Media Manager**
    - Department: Growth & Marketing
    - Level: 4
    - Salary Range: K4,000 - K7,000
    - Reports To: Growth & Marketing Lead

---

## Department Distribution

### Executive (1 position)
- CEO

### Operations (3 positions)
- COO
- Operations Manager
- Member Support Team Lead
- Member Support Agent

### Finance & Compliance (2 positions)
- CFO
- Finance & Compliance Lead
- Accountant

### Technology (2 positions)
- CTO
- Technology Lead

### Growth & Marketing (4 positions)
- CGO
- Growth & Marketing Lead
- Content Creator
- Social Media Manager

---

## Organizational Hierarchy

```
CEO (Level 1)
├── COO (Level 2)
│   └── Operations Manager (Level 3)
│       └── Member Support Team Lead (Level 4)
│           └── Member Support Agent (Level 5)
│
├── CFO (Level 2)
│   └── Finance & Compliance Lead (Level 3)
│       └── Accountant (Level 4)
│
├── CTO (Level 2)
│   └── Technology Lead (Level 3)
│
└── CGO (Level 2)
    └── Growth & Marketing Lead (Level 3)
        ├── Content Creator (Level 4)
        └── Social Media Manager (Level 4)
```

---

## Salary Budget Summary

### Total Minimum Annual Salary Budget
- C-Level: K205,000 (5 positions)
- Manager Level: K47,000 (4 positions)
- Team Lead: K6,000 (1 position)
- Individual Contributors: K16,000 (4 positions)

**Total Minimum:** K274,000 per year

### Total Maximum Annual Salary Budget
- C-Level: K340,000 (5 positions)
- Manager Level: K81,000 (4 positions)
- Team Lead: K10,000 (1 position)
- Individual Contributors: K27,000 (4 positions)

**Total Maximum:** K458,000 per year

---

## Priority Hiring Order

Based on the organizational structure document, the recommended hiring order is:

### Immediate (Month 0-1)
1. ✅ **CEO** - Already in place (founder/owner)
2. ⭐ **Operations Manager** - CRITICAL for launch

### Short Term (Month 1-3)
3. **Member Support Agent** - Handle member inquiries
4. **Finance & Compliance Lead** - Ensure regulatory compliance
5. **Technology Lead** - Platform maintenance

### Medium Term (Month 3-6)
6. **COO** - Scale operations
7. **Growth & Marketing Lead** - Member acquisition
8. **Content Creator** - Marketing materials
9. **Social Media Manager** - Online presence

### As Needed (Month 6+)
10. **CFO** - Financial strategy
11. **CTO** - Technology strategy
12. **CGO** - Growth strategy
13. **Member Support Team Lead** - When support team grows
14. **Accountant** - When financial complexity increases

---

## Next Steps

### To Populate the System
1. **Run migrations:**
   ```bash
   php artisan migrate
   ```

2. **Seed organizational structure:**
   ```bash
   php artisan db:seed --class=OrganizationalStructureSeeder
   ```

3. **Verify positions created:**
   - Navigate to `/admin/positions`
   - Should see all 14 positions
   - Check organizational chart at `/admin/organization`

### To Start Hiring
1. **Create job postings** for priority positions
2. **Use hiring roadmap** at `/admin/organization/hiring-roadmap`
3. **Track candidates** through the system
4. **Assign employees** to positions as they're hired

---

## Additional Features Seeded

### KPIs (Key Performance Indicators)
- Defined for each position
- Measurable targets
- Tracking frequency (daily/weekly/monthly/quarterly)

### Responsibilities
- Detailed role descriptions
- Priority levels (critical/high/medium/low)
- Categories (strategic/operational/administrative/technical)

### Hiring Roadmap
- Phase 1, 2, 3 planning
- Target hire dates
- Budget allocation
- Status tracking

---

## Summary

✅ **14 positions** defined for launch stage  
✅ **5 departments** established  
✅ **5-level hierarchy** (CEO → Individual Contributors)  
✅ **Salary budget:** K274,000 - K458,000 annually  
✅ **Priority #1:** Operations Manager  
✅ **All data seeded** and ready to use

The organizational structure is now fully implemented and ready to support MyGrowNet's growth from launch through the first 6 months of operation.

---

**Document Created:** November 5, 2025  
**Based On:** docs/ORGANIZATIONAL_STRUCTURE_IMPLEMENTATION_PLAN.md  
**Seeder:** database/seeders/OrganizationalStructureSeeder.php
