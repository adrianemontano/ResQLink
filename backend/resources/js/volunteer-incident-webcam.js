/**
 * In-page webcam capture for laptops and desktops, whose browsers ignore the
 * file input's `capture` attribute and open a file picker instead. Phones and
 * tablets keep using their native camera through the file input.
 *
 * Browsers only allow camera access on secure origins: HTTPS, or
 * http://localhost / http://127.0.0.1 during development.
 */

/** Whether "Take photo" should use the in-page webcam window. */
export function shouldUseWebcam() {
    const touchFirst = window.matchMedia('(pointer: coarse)').matches;
    return !touchFirst && window.isSecureContext && Boolean(navigator.mediaDevices?.getUserMedia);
}

/**
 * Open the webcam window and resolve with the captured JPEG File, or null if
 * the volunteer cancels. Rejects when no camera is available or access is
 * denied, so the caller can fall back to the file picker.
 */
export async function captureFromWebcam(modal) {
    const video = modal.querySelector('[data-camera-video]');
    const captureButton = modal.querySelector('[data-camera-capture]');
    const cancelButton = modal.querySelector('[data-camera-cancel]');

    const stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'environment', width: { ideal: 1920 }, height: { ideal: 1080 } },
        audio: false,
    });

    video.srcObject = stream;
    modal.hidden = false;
    document.body.classList.add('status-modal-open');
    captureButton.focus();

    return new Promise((resolve) => {
        const finish = (file) => {
            stream.getTracks().forEach((track) => track.stop());
            video.srcObject = null;
            modal.hidden = true;
            document.body.classList.remove('status-modal-open');
            captureButton.removeEventListener('click', onCapture);
            cancelButton.removeEventListener('click', onCancel);
            document.removeEventListener('keydown', onKeydown);
            resolve(file);
        };
        const onCapture = async () => {
            captureButton.disabled = true;
            try {
                finish(await frameToFile(video));
            } finally {
                captureButton.disabled = false;
            }
        };
        const onCancel = () => finish(null);
        const onKeydown = (event) => {
            if (event.key === 'Escape') finish(null);
        };

        captureButton.addEventListener('click', onCapture);
        cancelButton.addEventListener('click', onCancel);
        document.addEventListener('keydown', onKeydown);
    });
}

async function frameToFile(video) {
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.85));
    if (!blob) return null;
    const stamp = new Date().toISOString().slice(0, 19).replace(/[T:]/g, '-');

    return new File([blob], `incident-photo-${stamp}.jpg`, { type: 'image/jpeg', lastModified: Date.now() });
}
