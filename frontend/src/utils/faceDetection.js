let faceAPIModel = null;
let modelLoaded = false;

export const loadFaceModel = async () => {
  if (modelLoaded && faceAPIModel) {
    return faceAPIModel;
  }

  try {
    const faceapi = await import("face-api.js");

    await faceapi.nets.tinyFaceDetector.loadFromUri("/models");

    faceAPIModel = faceapi;
    modelLoaded = true;
    return faceapi;
  } catch (error) {
    console.error("Failed to load face-api model:", error);
    return null;
  }
};

export const checkIfFaceExists = async (file) => {
  try {
    const faceapi = await loadFaceModel();
    if (!faceapi) {
      console.warn("Face-api not available, allowing upload");
      return true;
    }

    return new Promise((resolve) => {
      const reader = new FileReader();
      reader.onload = async (e) => {
        try {
          const img = new Image();
          img.onload = async () => {
            // Resize for faster detection
            const canvas = document.createElement("canvas");
            const targetWidth = 300;
            const scale = targetWidth / img.width;
            canvas.width = targetWidth;
            canvas.height = img.height * scale;

            const ctx = canvas.getContext("2d");
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

            const detections = await faceapi.detectSingleFace(
              canvas,
              new faceapi.TinyFaceDetectorOptions()
            );

            resolve(!!detections);
          };
          img.onerror = () => {
            console.warn("Failed to load image for face detection");
            resolve(true);
          };
          img.src = e.target.result;
        } catch (error) {
          console.error("Face detection error:", error);
          resolve(true);
        }
      };
      reader.onerror = () => {
        console.warn("Failed to read file");
        resolve(true);
      };
      reader.readAsDataURL(file);
    });
  } catch (error) {
    console.error("Unexpected error in checkIfFaceExists:", error);
    return true;
  }
};
