// ===== PHOTO UTILS: Face Detection, Face Crop, Compression =====
// Shared by user-panel.php and admin-panel.php

const PhotoUtils = (() => {
  let _faceApiLoaded = false;
  let _loading = false;

  // Load face-api.js model
  async function loadFaceModel() {
    if (_faceApiLoaded) return true;
    if (_loading) {
      // Wait for ongoing load
      while (_loading) await new Promise(r => setTimeout(r, 100));
      return _faceApiLoaded;
    }
    _loading = true;
    try {
      await faceapi.nets.tinyFaceDetector.loadFromUri('models');
      _faceApiLoaded = true;
    } catch (e) {
      console.warn('Face model load failed:', e);
    }
    _loading = false;
    return _faceApiLoaded;
  }

  // Detect face in an image element, returns detection or null
  async function detectFace(img) {
    if (!_faceApiLoaded) return null;
    try {
      const canvas = document.createElement('canvas');
      const scale = 400 / Math.max(img.naturalWidth, img.naturalHeight);
      canvas.width = img.naturalWidth * scale;
      canvas.height = img.naturalHeight * scale;
      canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
      const det = await faceapi.detectSingleFace(canvas, new faceapi.TinyFaceDetectorOptions({ scoreThreshold: 0.4 }));
      if (!det) return null;
      // Scale detection box back to original image coords
      return {
        x: det.box.x / scale,
        y: det.box.y / scale,
        width: det.box.width / scale,
        height: det.box.height / scale,
        score: det.score
      };
    } catch (e) {
      console.warn('Face detection error:', e);
      return null;
    }
  }

  // Load a File as an Image element
  function fileToImage(file) {
    return new Promise((resolve, reject) => {
      const img = new Image();
      img.onload = () => resolve(img);
      img.onerror = () => reject(new Error('Failed to load image'));
      img.src = URL.createObjectURL(file);
    });
  }

  // Face-centered crop for profile photo (photo1)
  // Returns a canvas cropped around the face with some padding
  function faceCrop(img, faceBox) {
    const pad = 1.8; // padding multiplier around face
    const fw = faceBox.width * pad;
    const fh = faceBox.height * pad;
    const size = Math.max(fw, fh);
    const cx = faceBox.x + faceBox.width / 2;
    const cy = faceBox.y + faceBox.height / 2;

    let sx = cx - size / 2;
    let sy = cy - size / 2;
    let sw = size;
    let sh = size;

    // Clamp to image bounds
    if (sx < 0) sx = 0;
    if (sy < 0) sy = 0;
    if (sx + sw > img.naturalWidth) sw = img.naturalWidth - sx;
    if (sy + sh > img.naturalHeight) sh = img.naturalHeight - sy;

    const outSize = Math.min(600, Math.max(sw, sh)); // max 600px output
    const canvas = document.createElement('canvas');
    canvas.width = outSize;
    canvas.height = outSize;
    canvas.getContext('2d').drawImage(img, sx, sy, sw, sh, 0, 0, outSize, outSize);
    return canvas;
  }

  // Compress image to target size (default 100KB) using canvas
  // Returns a Blob (JPEG)
  async function compressImage(img, maxBytes = 102400) {
    const maxDim = 1200;
    let w = img.naturalWidth || img.width;
    let h = img.naturalHeight || img.height;

    // Scale down if too large
    if (w > maxDim || h > maxDim) {
      const ratio = maxDim / Math.max(w, h);
      w = Math.round(w * ratio);
      h = Math.round(h * ratio);
    }

    const canvas = document.createElement('canvas');
    canvas.width = w;
    canvas.height = h;
    canvas.getContext('2d').drawImage(img, 0, 0, w, h);

    // Try decreasing quality until under maxBytes
    let quality = 0.85;
    let blob = await canvasToBlob(canvas, 'image/jpeg', quality);

    while (blob.size > maxBytes && quality > 0.1) {
      quality -= 0.1;
      blob = await canvasToBlob(canvas, 'image/jpeg', quality);
    }

    // If still too large, scale dimensions down further
    if (blob.size > maxBytes) {
      const shrink = Math.sqrt(maxBytes / blob.size) * 0.9;
      canvas.width = Math.round(w * shrink);
      canvas.height = Math.round(h * shrink);
      canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
      blob = await canvasToBlob(canvas, 'image/jpeg', 0.7);
    }

    return blob;
  }

  function canvasToBlob(canvas, type, quality) {
    return new Promise(resolve => canvas.toBlob(resolve, type, quality));
  }

  // Compress a canvas (for face-cropped images)
  async function compressCanvas(canvas, maxBytes = 102400) {
    let quality = 0.85;
    let blob = await canvasToBlob(canvas, 'image/jpeg', quality);
    while (blob.size > maxBytes && quality > 0.1) {
      quality -= 0.1;
      blob = await canvasToBlob(canvas, 'image/jpeg', quality);
    }
    if (blob.size > maxBytes) {
      const shrink = Math.sqrt(maxBytes / blob.size) * 0.9;
      const c2 = document.createElement('canvas');
      c2.width = Math.round(canvas.width * shrink);
      c2.height = Math.round(canvas.height * shrink);
      c2.getContext('2d').drawImage(canvas, 0, 0, c2.width, c2.height);
      blob = await canvasToBlob(c2, 'image/jpeg', 0.7);
    }
    return blob;
  }

  // ===== MAIN: Process a photo file =====
  // isProfilePhoto: if true (photo1), applies face crop
  // isHoroscope: if true (rasi/amsam), skips face detection
  // Returns { blob, previewUrl, warning } or throws error
  async function processPhoto(file, { isProfilePhoto = false, isHoroscope = false } = {}) {
    if (!file || !file.type.startsWith('image/')) {
      throw new Error('Please select a valid image file.');
    }

    const img = await fileToImage(file);
    let resultBlob;
    let warning = null;

    if (isHoroscope) {
      // Horoscope charts: just compress, no face detection
      resultBlob = await compressImage(img);
    } else {
      // Person photo: detect face
      await loadFaceModel();
      const face = await detectFace(img);

      if (!face) {
        throw new Error('No human face detected in this photo. Please upload a clear photo with a visible face.');
      }

      if (isProfilePhoto) {
        // Face-focused crop for profile picture
        const cropped = faceCrop(img, face);
        resultBlob = await compressCanvas(cropped);
      } else {
        // Other person photos: just compress (face was verified)
        resultBlob = await compressImage(img);
      }
    }

    const previewUrl = URL.createObjectURL(resultBlob);
    const sizeKB = (resultBlob.size / 1024).toFixed(1);

    return { blob: resultBlob, previewUrl, sizeKB, warning };
  }

  // Create a File from Blob (for FormData submission)
  function blobToFile(blob, originalName) {
    const ext = 'jpg';
    const name = originalName ? originalName.replace(/\.[^.]+$/, '.' + ext) : 'photo.' + ext;
    return new File([blob], name, { type: 'image/jpeg' });
  }

  return { loadFaceModel, processPhoto, blobToFile };
})();
