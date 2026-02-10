# Phase 1 Implementation Plan - Exam Management Two-Tab Interface

## Overview
Transform the exam management page into a two-tab interface similar to "Manage Applicants"

## Tab Structure

### Tab 1: "Qualified Applicants"
**Purpose:** Display all applicants with status = "qualified" for this vacancy

**Features:**
- Auto-load qualified applicants on page load
- Checkbox selection for bulk actions
- Search functionality
- Status filter
- View profile button
- Pagination

**Columns:**
- [ ] Checkbox (for bulk selection)
- Name
- Email
- Application Date
- Status
- Actions (View Profile)

### Tab 2: "Exam Lobby" 
**Purpose:** Real-time view of applicants in the exam lobby (Phase 2)

**Note:** For Phase 1, we'll create the tab structure but show "Coming soon" message

## Implementation Steps for Phase 1

### 1. Update Applications Model
- [x] Add `link_sent_at`, `exam_token`, `exam_token_expires_at` fields (migration done)
- [ ] Update fillable array in Applications model

### 2. Create Controller Method
- [ ] Add `getQualifiedApplicants()` method to ExamController
- [ ] Returns JSON for AJAX requests
- [ ] Returns view for initial load

### 3. Create Blade View
- [ ] Backup current manage_exam.blade.php
- [ ] Create new two-tab structure
- [ ] Tab 1: Qualified applicants table with checkboxes
- [ ] Tab 2: Placeholder for exam lobby
- [ ] Move scheduling form outside tabs (always visible)
- [ ] Update button positions

### 4. Add JavaScript
- [ ] Tab switching functionality
- [ ] Checkbox selection (select all, individual)
- [ ] Search and filter for qualified applicants
- [ ] AJAX loading for tab content

### 5. Add Routes
- [ ] GET /admin/exam/{vacancy_id}/qualified - Get qualified applicants
- [ ] (Phase 2) GET /admin/exam/{vacancy_id}/lobby - Get lobby data

## File Changes

### Files to Modify:
1. `app/Models/Applications.php` - Add new fields to fillable
2. `app/Http/Controllers/ExamController.php` - Add getQualifiedApplicants method
3. `resources/views/admin/manage_exam.blade.php` - Complete redesign
4. `routes/web.php` - Add new routes

### Files to Create:
1. `resources/views/admin/exam/partials/qualified_applicants_row.blade.php` - Table row partial

## UI Layout

```
┌─────────────────────────────────────────────────────────────┐
│ ← Back    Exam Overview                    [EXAM SCHEDULED] │
├─────────────────────────────────────────────────────────────┤
│ Information Systems Analyst III                              │
│ VACANCY ID: ISAIII-022, COS Position                        │
├─────────────────────────────────────────────────────────────┤
│ [Qualified Applicants] [Exam Lobby]                         │
├─────────────────────────────────────────────────────────────┤
│ Tab Content Area (70% width)          │ Schedule Form (30%) │
│                                        │                     │
│ [Search] [Filter]                      │ Venue: ___          │
│                                        │ Date: ___           │
│ ┌──────────────────────────────┐      │ Time: ___           │
│ │ [✓] Name  Email  Date Status │      │                     │
│ │ [ ] John  j@...  ...  Qual.  │      │ [Save & Notify]     │
│ │ [ ] Jane  ja...  ...  Qual.  │      │ [Send Link]         │
│ └──────────────────────────────┘      │                     │
│                                        │ [Edit Questions]    │
│                                        │ [Start Exam]        │
└────────────────────────────────────────┴─────────────────────┘
```

## Success Criteria for Phase 1

- [x] Migration runs successfully
- [ ] Two tabs visible and switchable
- [ ] Qualified applicants tab shows all qualified applicants for the vacancy
- [ ] Checkboxes work for individual and bulk selection
- [ ] Search and filter work via AJAX
- [ ] Scheduling form remains on the right side
- [ ] All existing buttons remain functional
- [ ] Responsive design works down to 768px
- [ ] No console errors

## Next Steps (Phase 2)
- Implement exam lobby tracking
- Add real-time AJAX refresh
- Create lobby API endpoint
- Add "Remove from lobby" action
