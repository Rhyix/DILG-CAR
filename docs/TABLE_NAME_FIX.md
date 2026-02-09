# Table Name Mismatch Fix

## Error
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'u114697288_db_rhrmspb.exam_library_usages' doesn't exist
```

## Root Cause
Laravel automatically pluralizes model names to determine table names:
- Model: `ExamLibraryUsage`
- Expected table (by Laravel): `exam_library_usages` (plural)
- Actual table (in migration): `exam_library_usage` (singular)

## Solution
Added explicit table name in the `ExamLibraryUsage` model:

```php
class ExamLibraryUsage extends Model
{
    use HasFactory;

    protected $table = 'exam_library_usage'; // ← Added this line

    protected $fillable = [
        'vacancy_id',
        'library_question_id',
        'order',
    ];
}
```

## Files Modified
- `app/Models/ExamLibraryUsage.php` - Added `$table` property

## Status
✅ **FIXED** - The model now correctly references the `exam_library_usage` table.

## Testing
Try saving a question again. The error should be resolved.
