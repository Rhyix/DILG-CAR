# Questions Not Loading Fix

## Issue
- Card shows "4 Questions"
- When clicking the card, the questions page shows "There are no questions yet"
- Questions exist in the database but aren't displaying

## Root Cause
The `getSeriesQuestions()` method in the controller was returning the HTML view instead of JSON when the JavaScript tried to load questions via AJAX.

**Why?**
The condition checked:
```php
if (!$request->ajax() && !$request->has('search') && !$request->has('type') && !$request->has('difficulty'))
```

Modern browsers don't always set the `X-Requested-With: XMLHttpRequest` header that Laravel's `ajax()` method checks for, especially when using `fetch()`.

## Solution

### 1. Frontend - Add explicit ajax parameter
```javascript
// Before
const response = await fetch(`/admin/exam-library/series/${seriesId}/questions`);

// After
const response = await fetch(`/admin/exam-library/series/${seriesId}/questions?ajax=1`);
```

### 2. Backend - Check for ajax parameter
```php
// Before
if (!$request->ajax() && !$request->has('search') && !$request->has('type') && !$request->has('difficulty'))

// After  
if (!$request->ajax() && !$request->has('ajax') && !$request->has('search') && !$request->has('type') && !$request->has('difficulty'))
```

### 3. Added Better Error Logging
```javascript
async function loadQuestions() {
    try {
        console.log('Loading questions for series:', seriesId);
        const response = await fetch(`/admin/exam-library/series/${seriesId}/questions?ajax=1`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Questions loaded:', data);
        allQuestions = data;
        renderQuestions(allQuestions);
    } catch (error) {
        console.error('Error loading questions:', error);
        showAlert('Failed to load questions. Please refresh the page.', 'error');
    }
}
```

## Files Modified
1. `app/Http/Controllers/ExamLibraryController.php` - Added `ajax` parameter check
2. `resources/views/admin/exam_library/questions.blade.php` - Added `?ajax=1` to fetch URL and better error handling

## Testing
1. Click on a series card that shows "X Questions"
2. ✅ Questions should now load and display
3. ✅ Check browser console for "Loading questions for series: X" and "Questions loaded: [...]"
4. ✅ Questions should appear in the list

## Status
✅ **FIXED** - Questions now load correctly via AJAX
