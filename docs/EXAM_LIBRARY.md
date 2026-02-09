# Exam Library Feature Documentation

## Overview
The Exam Library is a centralized repository for creating and managing reusable questions that can be used across multiple exams. This feature allows administrators to build a question bank organized into series, making exam creation more efficient and consistent.

## Key Features

### 1. Question Series Management
- **Create Series**: Organize questions into named collections (e.g., "Math - Algebra Basics", "Java Fundamentals")
- **Edit Series**: Update series name and description
- **Delete Series**: Remove series (only if no questions are in use)
- **Search Series**: Find series by name or description

### 2. Question Management
- **Multiple Question Types**:
  - Multiple Choice
  - True/False
  - Essay
  - Short Answer

- **Question Metadata**:
  - Difficulty Level (Easy, Medium, Hard)
  - Category
  - Tags for better organization
  - Answer guides for essay questions

### 3. Reusability System
- Questions are stored once in the database
- Questions can be referenced in multiple exams
- Usage tracking shows which exams use each question
- Protection against deleting questions in use

### 4. Smart Updates
When editing a question that's used in exams:
- System warns you about affected exams
- Updates apply globally to all exams using that question
- Cannot delete questions currently in use

## Database Structure

### Tables Created

#### `question_series`
- `id`: Primary key
- `series_name`: Name of the question series
- `description`: Optional description
- `created_by`: Reference to admin who created it
- `timestamps`: Created and updated timestamps

#### `library_questions`
- `id`: Primary key
- `series_id`: Foreign key to question_series
- `question`: The question text
- `question_type`: Type of question (enum)
- `choices`: JSON array of choices (for multiple choice/true-false)
- `correct_answer`: The correct answer
- `essay_answer_guide`: Guide for grading essays
- `difficulty_level`: Easy, Medium, or Hard
- `category`: Optional category
- `tags`: JSON array of tags
- `timestamps`: Created and updated timestamps

#### `exam_library_usage`
- `id`: Primary key
- `vacancy_id`: Which exam uses this question
- `library_question_id`: Foreign key to library_questions
- `order`: Order of question in the exam
- `timestamps`: Created and updated timestamps

## User Guide

### Accessing Exam Library
1. Click on "Exam Library" button in the admin navigation
2. You'll see a grid of all question series

### Creating a Question Series
1. Click "Create Question Series" button
2. Enter series name (required)
3. Add optional description
4. Click "Save Series"

### Managing Questions in a Series
1. Click on any question series card
2. You'll see all questions in that series
3. Use filters to find specific questions:
   - Search by question text
   - Filter by question type
   - Filter by difficulty level

### Adding a Question
1. Inside a series, click "Add Question"
2. Fill in the question details:
   - Question text
   - Question type
   - Difficulty level
   - Choices (for multiple choice/true-false)
   - Correct answer
   - Optional category and tags
3. Click "Save Question"

### Editing a Question
1. Click the edit icon on any question
2. Modify the details
3. If the question is used in exams, you'll see a warning
4. Click "Save Question" to apply changes globally

### Deleting Questions
- Questions can only be deleted if they're not used in any exam
- The system will prevent deletion and show how many exams use the question

## API Endpoints

### Series Management
- `GET /admin/exam-library` - View all series
- `POST /admin/exam-library/series` - Create new series
- `PUT /admin/exam-library/series/{id}` - Update series
- `DELETE /admin/exam-library/series/{id}` - Delete series
- `GET /admin/exam-library/series/{id}` - View series questions page

### Question Management
- `GET /admin/exam-library/series/{id}/questions` - Get questions (AJAX)
- `POST /admin/exam-library/series/{id}/questions` - Create question
- `PUT /admin/exam-library/questions/{id}` - Update question
- `DELETE /admin/exam-library/questions/{id}` - Delete question
- `GET /admin/exam-library/questions/selection` - Get questions for exam creation

## Models

### QuestionSeries
- Relationships:
  - `creator()` - BelongsTo Admin
  - `questions()` - HasMany LibraryQuestion
- Attributes:
  - `question_count` - Computed count of questions

### LibraryQuestion
- Relationships:
  - `series()` - BelongsTo QuestionSeries
  - `examUsages()` - HasMany ExamLibraryUsage
- Methods:
  - `isUsedInExams()` - Check if question is used
  - `getUsageCountAttribute()` - Get usage count

### ExamLibraryUsage
- Relationships:
  - `libraryQuestion()` - BelongsTo LibraryQuestion
  - `vacancy()` - BelongsTo JobVacancy

## Best Practices

### Organizing Questions
1. Create series by subject or topic
2. Use clear, descriptive series names
3. Add descriptions to help others understand the series purpose

### Writing Questions
1. Be clear and concise in question text
2. Use appropriate difficulty levels
3. Add tags for better searchability
4. Provide answer guides for essay questions

### Managing Reusability
1. Review usage count before editing questions
2. Consider creating a new question instead of editing one in use
3. Use categories and tags to group related questions

## Security & Permissions
- Only authenticated admins can access Exam Library
- All actions are logged with admin ID
- Foreign key constraints prevent orphaned records
- Cascade deletes ensure data integrity

## Future Enhancements
Potential features for future development:
- Import/Export questions
- Question templates
- Collaborative question creation
- Question review and approval workflow
- Analytics on question performance
- Bulk operations (copy, move, delete)
- Version history for questions

## Troubleshooting

### Cannot Delete Series
**Issue**: Error when trying to delete a series
**Solution**: Check if any questions in the series are being used in exams. Remove questions from exams first, or delete unused questions.

### Cannot Delete Question
**Issue**: Error when trying to delete a question
**Solution**: The question is being used in one or more exams. The system shows how many exams use it. You cannot delete questions in use.

### Changes Not Reflecting
**Issue**: Updated question doesn't show changes in exam
**Solution**: Clear browser cache and refresh. Changes to library questions apply globally and should reflect immediately.

## Support
For issues or questions about the Exam Library feature, contact the development team or refer to the main application documentation.
