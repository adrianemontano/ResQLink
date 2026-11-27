/**
 * Volunteer incident photo: camera capture, preview, and in-browser resizing.
 *
 * The camera input (capture="environment") is the one submitted as `photo`.
 * A gallery pick is copied into it, so the server always receives one file.
 * Photos larger than the limits below are re-encoded as JPEG. The target stays
 * under PHP's default 2 MB upload_max_filesize as well as the app's 5 MB rule,
 * because phone camera photos are often 3–10 MB.
 */
import { captureFromWebcam, shouldUseWebcam } from './volunteer-incident-webcam';

const MAX_DIMENSION = 1920;
const TARGET_BYTES = 1.5 * 1024 * 1024;
const JPEG_QUALITIES = [0.85, 0.7, 0.55];

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-incident-photo]').forEach(initializePhotoField);
});

function initializePhotoField(field) {
    const input = field.querySelector('[data-photo-input]');
    const gallery = field.querySelector('[data-photo-gallery]');
    const empty = field.querySelector('[data-photo-empty]');
    const preview = field.querySelector('[data-photo-preview]');
    const previewImage = field.querySelector('[data-photo-preview-image]');
    const fileName = field.querySelector('[data-photo-name]');
    const status = field.querySelector('[data-photo-status]');
    let previewUrl = null;

    const setStatus = (message) => {
        status.textContent = message;
        status.hidden = !message;
    };

    const showPreview = (file) => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = file ? URL.createObjectURL(file) : null;
        previewImage.src = previewUrl || '';
        fileName.textContent = file ? `${file.name} · ${formatSize(file.size)}` : '';
        preview.hidden = !file;
        empty.hidden = Boolean(file);
    };

    const usePhoto = async (file) => {
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            clearInput(input);
            setStatus('Please choose an image file.');
            return;
        }
        setStatus('Preparing photo…');
        const prepared = await resizeIfNeeded(file);
        setInputFile(input, prepared);
        showPreview(prepared);
        setStatus('');
    };

    input.addEventListener('change', () => usePhoto(input.files[0]));
    gallery.addEventListener('change', () => {
        usePhoto(gallery.files[0]);
        clearInput(gallery);
    });
    // Laptops and desktops: open the in-page webcam instead of a file picker.
    const modal = field.querySelector('[data-camera-modal]');
    field.querySelectorAll('[data-photo-take]').forEach((trigger) => {
        trigger.addEventListener('click', async (event) => {
            if (!modal || !shouldUseWebcam()) return;
            event.preventDefault();
            try {
                const photo = await captureFromWebcam(modal);
                if (photo) await usePhoto(photo);
            } catch (error) {
                console.warn('Webcam unavailable; falling back to the file picker.', error);
                setStatus('No camera is available or access was blocked, so choose a photo file instead.');
                input.click();
            }
        });
    });

    field.querySelector('[data-photo-remove]').addEventListener('click', () => {
        clearInput(input);
        showPreview(null);
        setStatus('');
    });
}

async function resizeIfNeeded(file) {
    try {
        const image = await loadImage(file);
        const scale = Math.min(1, MAX_DIMENSION / Math.max(image.width, image.height));
        if (scale === 1 && file.size <= TARGET_BYTES && ['image/jpeg', 'image/png'].includes(file.type)) {
            return file;
        }
        const canvas = document.createElement('canvas');
        canvas.width = Math.round(image.width * scale);
        canvas.height = Math.round(image.height * scale);
        canvas.getContext('2d').drawImage(image, 0, 0, canvas.width, canvas.height);
        let blob = null;
        for (const quality of JPEG_QUALITIES) {
            blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', quality));
            if (!blob || blob.size <= TARGET_BYTES) break;
        }
        if (!blob) return file;
        const name = file.name.replace(/\.[^.]+$/, '') || 'incident-photo';
        return new File([blob], `${name}.jpg`, { type: 'image/jpeg', lastModified: Date.now() });
    } catch {
        // The browser cannot decode this image (e.g. some HEIC files); send it
        // as-is and let server validation explain the problem.
        return file;
    }
}

function loadImage(file) {
    return new Promise((resolve, reject) => {
        const url = URL.createObjectURL(file);
        const image = new Image();
        image.onload = () => { URL.revokeObjectURL(url); resolve(image); };
        image.onerror = (error) => { URL.revokeObjectURL(url); reject(error); };
        image.src = url;
    });
}

function setInputFile(input, file) {
    const transfer = new DataTransfer();
    transfer.items.add(file);
    input.files = transfer.files;
}

function clearInput(input) {
    input.value = '';
}

function formatSize(bytes) {
    return bytes >= 1024 * 1024 ? `${(bytes / 1024 / 1024).toFixed(1)} MB` : `${Math.round(bytes / 1024)} KB`;
}
