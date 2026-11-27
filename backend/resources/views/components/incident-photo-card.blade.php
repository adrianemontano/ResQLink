@props(['incident', 'routePrefix' => 'dispatcher.incidents.photo'])

{{--
    Incident photo card with an enlarge-on-click lightbox (native <dialog>, so
    Esc closes it and focus returns to the thumbnail). Used on the Dispatcher
    and Admin incident pages; routePrefix picks the role's protected photo routes.
--}}
@php
    $photo = $incident->photos->first();
    $dialogId = 'incident-photo-dialog-'.$incident->id;
@endphp

<section class="card compact stack-gap incident-photo-card">
    <h2 class="card-title">Incident Photo &amp; Attachment</h2>

    @if ($photo)
        @php
            $photoUrl = route($routePrefix.'.show', [$incident, $photo]);
            $downloadUrl = route($routePrefix.'.download', [$incident, $photo]);
            $photoName = $photo->original_name ?? 'incident-photo.jpg';
        @endphp
        {{-- A link, not a <button>: global button styles fix the height at 40px.
             Without JavaScript it opens the full photo in a new tab. --}}
        <a class="incident-photo-open" href="{{ $photoUrl }}" target="_blank" rel="noopener"
            data-photo-dialog-open="{{ $dialogId }}" aria-label="Enlarge incident photo">
            <img class="incident-photo-thumb" src="{{ $photoUrl }}" alt="Photo of incident INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }}">
        </a>
        <div class="incident-photo-meta">
            <span>{{ $photoName }} ({{ round(($photo->size ?? 0) / 1024, 1) }} KB)</span>
            <a href="{{ $downloadUrl }}" class="button secondary dispatcher-view-button">Download</a>
        </div>

        <dialog id="{{ $dialogId }}" class="photo-lightbox" aria-label="Incident photo">
            <form method="dialog">
                <button type="submit" class="photo-lightbox-close" aria-label="Close photo">&times;</button>
            </form>
            <img src="{{ $photoUrl }}" alt="Enlarged photo of incident INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }}">
            <div class="incident-photo-meta">
                <span>{{ $photoName }}</span>
                <a href="{{ $downloadUrl }}" class="button primary">Download full photo</a>
            </div>
        </dialog>
    @else
        <div class="empty-panel">
            <svg class="empty-panel-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            <p class="empty-panel-title">No photo uploaded</p>
            <small>The Barangay Responder did not attach an image to this report.</small>
        </div>
    @endif
</section>

@once
    @push('scripts')
        <script>
            document.addEventListener('click', (event) => {
                const opener = event.target.closest('[data-photo-dialog-open]');
                if (opener) {
                    const dialog = document.getElementById(opener.dataset.photoDialogOpen);
                    if (dialog?.showModal) {
                        event.preventDefault();
                        dialog.showModal();
                    }
                    return;
                }
                // Clicking the dimmed backdrop (the dialog element itself) closes it.
                if (event.target instanceof HTMLDialogElement && event.target.classList.contains('photo-lightbox')) {
                    event.target.close();
                }
            });
        </script>
    @endpush
@endonce
