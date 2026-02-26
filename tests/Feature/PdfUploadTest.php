<?php

namespace Tests\Feature;

use App\Models\UploadedDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdfUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_accepts_valid_pdf_and_stores_metadata(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->createWithContent('doc.pdf', "%PDF-1.7\n%TEST\n");
        $response = $this->post(route('application_status.upload', [$user->id, 'VAC-001']), [
            'cert_uploads' => [
                'pqe_result' => $file
            ]
        ]);

        $response->assertRedirect();

        $document = UploadedDocument::where('user_id', $user->id)
            ->where('document_type', 'pqe_result')
            ->first();

        $this->assertNotNull($document);
        $this->assertSame('', $document->remarks);
        Storage::disk('public')->assertExists($document->storage_path);
    }

    public function test_upload_rejects_invalid_pdf_header(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->createWithContent('bad.pdf', 'NOT_A_PDF');
        $response = $this->post(route('application_status.upload', [$user->id, 'VAC-001']), [
            'cert_uploads' => [
                'pqe_result' => $file
            ]
        ]);

        $response->assertSessionHasErrors(['cert_uploads.pqe_result']);
        $this->assertDatabaseMissing('uploaded_documents', [
            'user_id' => $user->id,
            'document_type' => 'pqe_result',
        ]);
    }

    public function test_upload_rejects_oversized_pdf(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->create('big.pdf', 10241, 'application/pdf');
        $response = $this->post(route('application_status.upload', [$user->id, 'VAC-001']), [
            'cert_uploads' => [
                'pqe_result' => $file
            ]
        ]);

        $response->assertSessionHasErrors(['cert_uploads.pqe_result']);
    }

    public function test_finalize_pds_upload_succeeds(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('finalize_pds', ['go_to' => 'dashboard_user']), [
            'doc_track' => 'COS',
            'declaration' => '1',
            'consent' => '1',
            'confirmation' => '1',
            'cert_uploads' => [
                'passport_photo' => UploadedFile::fake()->createWithContent('passport_photo.pdf', "%PDF-1.7\n%TEST\n"),
                'signed_pds' => UploadedFile::fake()->createWithContent('signed_pds.pdf', "%PDF-1.7\n%TEST\n"),
                'signed_work_exp_sheet' => UploadedFile::fake()->createWithContent('signed_work_exp_sheet.pdf', "%PDF-1.7\n%TEST\n"),
                'photocopy_diploma' => UploadedFile::fake()->createWithContent('photocopy_diploma.pdf', "%PDF-1.7\n%TEST\n"),
                'application_letter' => UploadedFile::fake()->createWithContent('application_letter.pdf', "%PDF-1.7\n%TEST\n"),
                'cert_training' => UploadedFile::fake()->createWithContent('cert_training.pdf', "%PDF-1.7\n%TEST\n"),
            ]
        ]);

        $response->assertRedirect();
        $document = UploadedDocument::where('user_id', $user->id)
            ->where('document_type', 'application_letter')
            ->first();
        $this->assertNotNull($document);
        Storage::disk('public')->assertExists($document->storage_path);
    }

    public function test_finalize_pds_upload_rolls_back_on_failure(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('finalize_pds', ['go_to' => 'dashboard_user']), [
            'doc_track' => 'COS',
            'declaration' => '1',
            'consent' => '1',
            'confirmation' => '1',
            'simulate_failure' => 1,
            'cert_uploads' => [
                'passport_photo' => UploadedFile::fake()->createWithContent('passport_photo.pdf', "%PDF-1.7\n%TEST\n"),
                'signed_pds' => UploadedFile::fake()->createWithContent('signed_pds.pdf', "%PDF-1.7\n%TEST\n"),
                'signed_work_exp_sheet' => UploadedFile::fake()->createWithContent('signed_work_exp_sheet.pdf', "%PDF-1.7\n%TEST\n"),
                'photocopy_diploma' => UploadedFile::fake()->createWithContent('photocopy_diploma.pdf', "%PDF-1.7\n%TEST\n"),
                'application_letter' => UploadedFile::fake()->createWithContent('application_letter.pdf', "%PDF-1.7\n%TEST\n"),
                'cert_training' => UploadedFile::fake()->createWithContent('cert_training.pdf', "%PDF-1.7\n%TEST\n"),
            ]
        ]);

        $response->assertSessionHasErrors('cert_uploads');
        $this->assertDatabaseMissing('uploaded_documents', [
            'user_id' => $user->id,
            'document_type' => 'application_letter',
        ]);
    }
}
