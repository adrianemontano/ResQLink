{{--
    Incident photo. "Take photo" opens the device's rear camera directly on
    phones and tablets (capture="environment"). On laptops and desktops, which
    ignore `capture`, it opens the in-page webcam window below instead
    (resources/js/volunteer-incident-webcam.js), falling back to a file picker. Large photos are resized in the browser to about 1.5 MB before
    upload, under PHP's default 2 MB upload limit
    (resources/js/volunteer-incident-photo.js).
--}}
<div class="volunteer-field volunteer-photo-field" data-incident-photo>
    <label for="incident-photo-camera">Photo of the incident</label>

    <input id="incident-photo-camera" class="volunteer-photo-input" name="photo" type="file"
        accept="image/jpeg,image/png,image/*" capture="environment" data-photo-input>
    <input id="incident-photo-gallery" class="volunteer-photo-input" type="file"
        accept="image/jpeg,image/png,image/*" data-photo-gallery>

    <div class="volunteer-photo-actions" data-photo-empty>
        <label for="incident-photo-camera" class="button volunteer-photo-camera-button" role="button" data-photo-take>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
            Take photo
        </label>
        <label for="incident-photo-gallery" class="volunteer-photo-gallery-link" role="button">or choose from gallery</label>
    </div>

    <figure class="volunteer-photo-preview" data-photo-preview hidden>
        <img src="" alt="Preview of the incident photo" data-photo-preview-image>
        <figcaption>
            <span data-photo-name></span>
            <span class="volunteer-photo-preview-actions">
                <label for="incident-photo-camera" class="button secondary dispatcher-view-button" role="button" data-photo-take>Retake</label>
                <button type="button" class="button secondary dispatcher-view-button" data-photo-remove>Remove</button>
            </span>
        </figcaption>
    </figure>

    <small class="volunteer-photo-hint">Optional. JPEG or PNG; large photos are resized automatically.</small>
    <small class="volunteer-photo-status" data-photo-status role="status" hidden></small>
    @error('photo')
        <small role="alert">{{ $message }} Please take the photo again.</small>
    @enderror

    <div class="volunteer-camera-modal" data-camera-modal hidden>
        <div class="volunteer-camera-dialog" role="dialog" aria-modal="true" aria-labelledby="volunteer-camera-title">
            <h2 id="volunteer-camera-title">Take a photo</h2>
            <video data-camera-video autoplay playsinline muted></video>
            <div class="volunteer-camera-actions">
                <button type="button" class="button secondary dispatcher-view-button" data-camera-cancel>Cancel</button>
                <button type="button" class="button" data-camera-capture>Capture</button>
            </div>
        </div>
    </div>
</div>
