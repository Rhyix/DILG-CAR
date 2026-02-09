# Exam Library Testing Checklist

## Setup Verification
- [x] Migrations created and run successfully
- [x] Models created (QuestionSeries, LibraryQuestion, ExamLibraryUsage)
- [x] Controller created (ExamLibraryController)
- [x] Routes configured
- [x] Views created (index, questions)

## Manual Testing Steps

### 1. Access Exam Library
1. Log in as admin
2. Navigate to Exam Library (click the Exam Library button)
3. Verify the page loads without errors
4. Should see empty state with "No question series found"

### 2. Create Question Series
1. Click "Create Question Series" button
2. Fill in:
   - Series Name: "Sample Math Questions"
   - Description: "Basic mathematics questions for testing"
3. Click "Save Series"
4. Verify success message appears
5. Verify new series appears in the grid

### 3. View Series Questions
1. Click on the newly created series card
2. Should navigate to questions page
3. Should see empty state with "No questions found"
4. Verify series name and description are displayed

### 4. Create Multiple Choice Question
1. Click "Add Question" button
2. Fill in:
   - Question: "What is 2 + 2?"
   - Type: Multiple Choice
   - Difficulty: Easy
   - Choices: "2", "3", "4", "5"
   - Correct Answer: "4"
   - Category: "Arithmetic"
   - Tags: "basic, addition"
3. Click "Save Question"
4. Verify success message
5. Verify question appears in the list

### 5. Create True/False Question
1. Click "Add Question"
2. Fill in:
   - Question: "The Earth is flat."
   - Type: True/False
   - Difficulty: Easy
   - Correct Answer: "False"
3. Verify only True/False choices appear
4. Save and verify

### 6. Create Essay Question
1. Click "Add Question"
2. Fill in:
   - Question: "Explain the Pythagorean theorem."
   - Type: Essay
   - Difficulty: Medium
   - Answer Guide: "Should mention a² + b² = c² and right triangles"
3. Verify choices and correct answer fields are hidden
4. Verify answer guide field appears
5. Save and verify

### 7. Create Short Answer Question
1. Click "Add Question"
2. Fill in:
   - Question: "What is the capital of France?"
   - Type: Short Answer
   - Difficulty: Easy
   - Correct Answer: "Paris"
3. Save and verify

### 8. Test Search and Filters
1. Use search box to find questions
2. Filter by question type
3. Filter by difficulty level
4. Verify results update correctly

### 9. Edit Question
1. Click edit icon on any question
2. Modify the question text
3. Save changes
4. Verify changes appear in the list

### 10. Delete Question
1. Try to delete a question
2. Confirm deletion
3. Verify question is removed

### 11. Edit Series
1. Go back to main library page
2. Click edit icon on a series
3. Modify name or description
4. Save and verify changes

### 12. Delete Series
1. Try to delete a series with questions
2. Should fail with appropriate message
3. Delete all questions first
4. Then delete the series
5. Verify series is removed

## Expected Behaviors

### Question Type Handling
- **Multiple Choice**: Shows 4 choice inputs by default, can add more
- **True/False**: Shows only True/False options (read-only)
- **Essay**: Hides choices and correct answer, shows answer guide
- **Short Answer**: Hides choices, shows correct answer field

### Validation
- Series name is required
- Question text is required
- Question type is required
- Difficulty level is required
- Correct answer required (except for essay)

### UI/UX
- Modals open and close smoothly
- Success/error messages display correctly
- Cards have hover effects
- Buttons have proper styling
- Responsive design works on different screen sizes

### Data Integrity
- Cannot delete series with questions in use
- Cannot delete questions in use in exams
- Foreign key constraints work properly
- Timestamps update correctly

## Browser Console Checks
- No JavaScript errors
- AJAX requests complete successfully
- Proper CSRF token handling
- JSON responses are valid

## Database Verification
Run these queries to verify data:

```sql
-- Check series
SELECT * FROM question_series;

-- Check questions
SELECT * FROM library_questions;

-- Check question count per series
SELECT qs.series_name, COUNT(lq.id) as question_count
FROM question_series qs
LEFT JOIN library_questions lq ON qs.id = lq.series_id
GROUP BY qs.id;
```

## Performance Checks
- Page loads quickly
- Search is responsive
- No lag when opening modals
- AJAX requests complete in < 1 second

## Accessibility
- All buttons have proper labels
- Forms are keyboard accessible
- Modals can be closed with Escape key
- Proper focus management

## Next Steps After Testing
1. Test integration with exam creation (when implemented)
2. Test question usage tracking
3. Test bulk operations
4. Test with large datasets (100+ questions)
5. Cross-browser testing
6. Mobile responsiveness testing

## Known Limitations
- Currently no question import/export
- No bulk operations yet
- No question versioning
- No collaborative editing
- No question analytics

## Notes
- Document any bugs found
- Note any UI/UX improvements needed
- Track performance issues
- Gather user feedback
