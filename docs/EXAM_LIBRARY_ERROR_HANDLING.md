# Exam Library Error Handling Documentation

## Overview
This document describes the comprehensive error handling implementation for the Exam Library feature to ensure all questions are saved correctly to the database.

## Backend Error Handling (ExamLibraryController.php)

### 1. Database Transactions
All database operations are wrapped in transactions to ensure data integrity:

```php
DB::beginTransaction();
try {
    // Database operations
    DB::commit();
} catch (Exception $e) {
    DB::rollBack();
    // Error handling
}
```

### 2. Try-Catch Blocks
Every controller method includes comprehensive try-catch blocks:

- **ValidationException**: Catches validation errors (422 status)
- **ModelNotFoundException**: Catches missing records (404 status)
- **Exception**: Catches all other errors (500 status)

### 3. Validation Layers

#### Question Creation (`storeQuestion`)
- **Series Verification**: Ensures the series exists before creating questions
- **Question Type Validation**: Only allows `multiple_choice` and `essay`
- **Multiple Choice Validation**:
  - Must have at least 2 choices
  - Must have a correct answer selected
- **Database Verification**: Confirms question was saved with valid ID
- **Fresh Reload**: Returns freshly loaded question with usage count

#### Question Update (`updateQuestion`)
- Same validation as creation
- Checks if question is used in exams
- Warns about affected exams
- Verifies update success with `fresh()` method

#### Question Deletion (`deleteQuestion`)
- Prevents deletion if used in exams
- Verifies deletion was successful
- Provides usage count in error message

### 4. Error Logging
All errors are logged with context:

```php
\Log::error('Error creating question: ' . $e->getMessage(), [
    'series_id' => $seriesId,
    'request_data' => $request->all()
]);
```

### 5. Response Structure
Consistent JSON response format:

**Success:**
```json
{
    "success": true,
    "message": "Question created successfully!",
    "question": { ... }
}
```

**Error:**
```json
{
    "success": false,
    "message": "Detailed error message"
}
```

## Frontend Error Handling (questions.blade.php)

### 1. Client-Side Validation

Before sending to server:
- **Empty Question Check**: Ensures question text is not empty
- **Multiple Choice Validation**:
  - At least 2 choices required
  - Correct answer must be selected
  - Selected answer must not be empty
- **Input Trimming**: Removes whitespace from all inputs

### 2. Enhanced Error Messages

Uses `showAlert()` instead of browser `alert()`:
- Better UX with styled alerts
- Auto-dismiss after 5 seconds
- Color-coded (green for success, red for error)

### 3. HTTP Response Handling

```javascript
// Check if response is ok
if (!response.ok) {
    const errorData = await response.json();
    throw new Error(errorData.message || 'Failed to save question');
}
```

### 4. Verification After Save

```javascript
await loadQuestions(); // Reload to verify save
```

Reloads the questions list after successful save to ensure the question appears.

### 5. Error Logging

```javascript
console.error('Error saving question:', error);
```

Logs errors to browser console for debugging.

## Error Scenarios Handled

### 1. Network Errors
- **Scenario**: Server unreachable, timeout
- **Handling**: Catch block shows user-friendly message
- **Message**: "An error occurred while saving. Please try again."

### 2. Validation Errors
- **Scenario**: Invalid data format, missing required fields
- **Handling**: Returns 422 status with detailed validation errors
- **Message**: "Validation failed: [specific errors]"

### 3. Database Errors
- **Scenario**: Save fails, update fails, delete fails
- **Handling**: Transaction rollback, error logging
- **Message**: "Failed to [action] question. Please try again."

### 4. Not Found Errors
- **Scenario**: Series or question doesn't exist
- **Handling**: Returns 404 status
- **Message**: "Question series not found." or "Question not found."

### 5. Business Logic Errors
- **Scenario**: Deleting question in use, insufficient choices
- **Handling**: Returns 400 status with specific message
- **Message**: "Cannot delete this question. It is currently being used in X exam(s)."

## Testing Checklist

### Backend Testing
- [ ] Create question with valid data → Success
- [ ] Create question with invalid series ID → 404 error
- [ ] Create MCQ with < 2 choices → Validation error
- [ ] Create MCQ without correct answer → Validation error
- [ ] Update question successfully → Success
- [ ] Update question in use → Warning message
- [ ] Delete unused question → Success
- [ ] Delete question in use → Error prevented
- [ ] Network failure during save → Transaction rollback

### Frontend Testing
- [ ] Submit empty question → Client-side error
- [ ] Submit MCQ with 1 choice → Client-side error
- [ ] Submit MCQ without selecting answer → Client-side error
- [ ] Submit valid question → Success alert
- [ ] Question appears in list after save → Verification
- [ ] Server error → User-friendly error message
- [ ] Network error → User-friendly error message

## Best Practices Implemented

1. **Fail-Safe Transactions**: All database operations use transactions
2. **Defensive Programming**: Multiple validation layers (client + server)
3. **User-Friendly Messages**: Clear, actionable error messages
4. **Error Logging**: All errors logged for debugging
5. **Data Verification**: Confirms saves with fresh database reads
6. **Graceful Degradation**: System remains stable even on errors
7. **Consistent Responses**: Standardized JSON response format
8. **Type Safety**: Strict validation of question types
9. **Usage Protection**: Prevents deletion/modification of in-use questions
10. **Audit Trail**: Logs include context for troubleshooting

## Future Enhancements

1. **Retry Logic**: Automatic retry for transient network errors
2. **Offline Support**: Queue operations when offline
3. **Optimistic Updates**: Update UI immediately, rollback on error
4. **Detailed Validation**: Field-level error highlighting
5. **Error Analytics**: Track error patterns for improvement
6. **Batch Operations**: Handle multiple question saves with partial failure recovery

## Monitoring

Check Laravel logs for errors:
```bash
tail -f storage/logs/laravel.log
```

Look for entries like:
```
[timestamp] local.ERROR: Error creating question: [message] {"series_id":1,"request_data":{...}}
```

## Support

If questions fail to save:
1. Check browser console for JavaScript errors
2. Check Laravel logs for backend errors
3. Verify database connection
4. Check CSRF token validity
5. Verify user permissions
