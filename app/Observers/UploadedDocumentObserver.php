<?php

namespace App\Observers;

use App\Models\UploadedDocument;
use Illuminate\Support\Facades\Auth;

class UploadedDocumentObserver
{
    private const TRACKED_FIELDS = [
        'status',
        'remarks',
        'storage_path',
        'original_name',
        'stored_name',
    ];

    public function updated(UploadedDocument $document): void
    {
        $changes = $this->extractTrackedChanges($document);
        if (empty($changes)) {
            return;
        }

        $adminActor = Auth::guard('admin')->user();
        $userActor = Auth::guard('web')->user();
        $actor = $adminActor ?? $userActor;

        $log = activity()
            ->event('audit_update')
            ->performedOn($document)
            ->withProperties([
                'section' => 'Application List',
                'user_id' => $document->user_id,
                'vacancy_id' => $document->vacancy_id,
                'document_type' => $document->document_type,
                'changes' => [
                    'document_' . (string) $document->document_type => $changes,
                ],
                'audit_source' => 'observer',
            ]);

        if ($actor) {
            $log->causedBy($actor);
        }

        $log->log('Uploaded document critical fields changed.');
    }

    private function extractTrackedChanges(UploadedDocument $document): array
    {
        $changedKeys = array_keys($document->getChanges());
        $trackedKeys = array_intersect($changedKeys, self::TRACKED_FIELDS);
        $changes = [];

        foreach ($trackedKeys as $field) {
            $old = $document->getOriginal($field);
            $new = $document->getAttribute($field);

            if ($old === $new) {
                continue;
            }

            $changes[$field] = [
                'old' => $old,
                'new' => $new,
            ];
        }

        return $changes;
    }
}

