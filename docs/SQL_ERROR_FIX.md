# SQL Error Fix - Difficulty Level

## Problem
When saving questions, an SQL error occurred:
```
SQLSTATE[23000]: Integrity constraint violation
```

The loader was looping indefinitely and the question was not being saved.

## Root Cause
The `difficulty_level` column in the `library_questions` table had:
- A default value of `'medium'`
- Was NOT nullable

However, the frontend was sending `null` for this field since we removed the difficulty selector from the UI.

## Solution

### 1. Updated Original Migration
Changed `2026_02_09_080001_create_library_questions_table.php`:
```php
// Before
$table->enum('difficulty_level', ['easy', 'medium', 'hard'])->default('medium');

// After
$table->enum('difficulty_level', ['easy', 'medium', 'hard'])->nullable();
```

### 2. Created Alter Migration
Created `2026_02_09_163327_make_difficulty_level_nullable_in_library_questions.php`:
```php
public function up(): void
{
    Schema::table('library_questions', function (Blueprint $table) {
        $table->enum('difficulty_level', ['easy', 'medium', 'hard'])->nullable()->change();
    });
}
```

### 3. Ran Migration
```bash
php artisan migrate
```

## Testing
Try saving a question now. The SQL error should be resolved and questions should save successfully.

## Files Modified
1. `database/migrations/2026_02_09_080001_create_library_questions_table.php`
2. `database/migrations/2026_02_09_163327_make_difficulty_level_nullable_in_library_questions.php` (NEW)

## Status
✅ Migration completed successfully
✅ `difficulty_level` column is now nullable
✅ Questions can be saved with `null` difficulty level
