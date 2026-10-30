<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewVolunteerDocumentRequest;
use App\Http\Requests\Admin\StoreVolunteerDocumentRequest;
use App\Models\User;
use App\Models\VolunteerDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VolunteerDocumentController extends Controller
{
    public function store(StoreVolunteerDocumentRequest $request, User $user): RedirectResponse
    {
        $file = $request->file('document');

        VolunteerDocument::query()->create([
            'volunteer_profile_id' => $user->volunteerProfile->getKey(),
            'document_type' => $request->validated('document_type'),
            'file_path' => $file->store('volunteer-documents', VolunteerDocument::DISK),
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
        ]);

        return back()->with('status', 'Volunteer document uploaded.');
    }

    public function show(User $user, VolunteerDocument $document): StreamedResponse
    {
        $this->abortUnlessOwnedBy($user, $document);

        $disk = Storage::disk(VolunteerDocument::DISK);
        abort_unless($disk->exists($document->file_path), 404);

        return $disk->download(
            $document->file_path,
            $document->original_filename ?? basename($document->file_path),
        );
    }

    public function review(ReviewVolunteerDocumentRequest $request, User $user, VolunteerDocument $document): RedirectResponse
    {
        $this->abortUnlessOwnedBy($user, $document);

        $document->update([
            'review_status' => $request->validated('review_status'),
            'reviewed_by' => $request->user()->getKey(),
            'reviewed_at' => now(),
        ]);

        return back()->with('status', 'Volunteer document review saved.');
    }

    private function abortUnlessOwnedBy(User $user, VolunteerDocument $document): void
    {
        abort_unless((int) $document->volunteer_profile_id === (int) $user->getKey(), 404);
    }
}
