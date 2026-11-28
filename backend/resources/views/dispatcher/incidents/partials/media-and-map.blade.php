<div class="detail-right">
    {{-- Incident Photo / Video Attachment Card --}}
    <section class="card compact" style="margin-bottom: var(--space-4);">
        <h2 style="margin-top: 0">Incident Photo &amp; Video Attachments</h2>
        @if ($incident->photos && $incident->photos->isNotEmpty())
            <div style="display: grid; gap: 12px;">
                @foreach ($incident->photos as $media)
                    @php
                        $isVideo = ($media->media_type ?? '') === 'video' || str_starts_with((string) $media->mime_type, 'video/');
                    @endphp
                    <div class="photo-preview-container" style="text-align: center;">
                        @if ($isVideo)
                            <video controls preload="metadata" style="width: 100%; max-height: 240px; border-radius: 6px; border: 1px solid var(--border); background: #000;">
                                <source src="{{ route('dispatcher.incidents.photo.show', [$incident, $media]) }}" type="{{ $media->mime_type }}">
                            </video>
                        @else
                            <a href="#photo-modal-{{ $media->id }}" onclick="document.getElementById('photo-modal-{{ $media->id }}').style.display='flex'; return false;">
                                <img class="incident-photo-preview" src="{{ route('dispatcher.incidents.photo.show', [$incident, $media]) }}"
                                     alt="Incident Photo"
                                     style="max-width: 100%; max-height: 220px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border); box-shadow: var(--shadow-sm); cursor: pointer;">
                            </a>
                            <div id="photo-modal-{{ $media->id }}" style="display: none; position: fixed; z-index: 9999; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); align-items: center; justify-content: center;" onclick="this.style.display='none';">
                                <div style="position: relative; max-width: 90%; max-height: 90%; background: #ffffff; padding: 12px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.5);" onclick="event.stopPropagation();">
                                    <button type="button" onclick="document.getElementById('photo-modal-{{ $media->id }}').style.display='none';" style="position: absolute; top: -12px; right: -12px; background: var(--rosewood); color: #fff; border: 2px solid #fff; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; font-size: 16px; font-weight: bold; line-height: 1;">&times;</button>
                                    <img src="{{ route('dispatcher.incidents.photo.show', [$incident, $media]) }}" alt="Incident Photo Enlarge" style="max-width: 100%; max-height: 75vh; display: block; border-radius: 4px;">
                                </div>
                            </div>
                        @endif
                        <div style="margin-top: 8px; display: flex; justify-content: space-between; align-items: center; gap: 8px; font-size: 12px; color: var(--text-muted);">
                            <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $media->original_name ?? 'attachment' }} ({{ round($media->size / 1024, 1) }} KB)</span>
                            <a href="{{ route('dispatcher.incidents.photo.download', [$incident, $media]) }}" class="button secondary dispatcher-view-button">
                                Download
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="padding: var(--space-4); text-align: center; color: var(--text-muted); background: var(--surface-alt); border-radius: 6px; border: 1px dashed var(--border);">
                <svg viewBox="0 0 24 24" style="width: 36px; height: 36px; stroke: var(--text-muted); stroke-width: 1.5; fill: none; margin-bottom: 6px; opacity: 0.6;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                <p style="margin: 0; font-size: 13px; font-weight: 500;">No photo or video uploaded</p>
                <small style="color: var(--text-muted); font-size: 11px;">The field responder did not attach media to this report.</small>
            </div>
        @endif
    </section>

    <section class="card compact map-card">
        <h2 style="margin-top: 0">Incident Location</h2>
        @if ($incident->latitude !== null && $incident->longitude !== null)
            <div class="map-wrap">
                <x-incident-map
                    map-id="incident-mini-map"
                    :focus-lat="$incident->latitude"
                    :focus-lng="$incident->longitude"
                    :focus-incident-id="$incident->id"
                    :incident-status="$incident->status"
                    :show-search="false"
                />
            </div>
        @else
            <p style="color: var(--gray-500); font-size: 13px; padding: var(--space-4); text-align: center;">No location data available.</p>
        @endif
    </section>
</div>
