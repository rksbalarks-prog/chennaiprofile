"""
Remove photos without detectable human faces from Female_img folder.
Uses OpenCV's Haar Cascade face detector.
Non-face images are moved to Female_img_rejected/ folder (not deleted).
"""
import cv2
import os
import shutil

IMG_DIR = os.path.join(os.path.dirname(__file__), 'Female_img')
REJECT_DIR = os.path.join(os.path.dirname(__file__), 'Female_img_rejected')

# Load face detector
face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')

if not os.path.isdir(IMG_DIR):
    print(f"ERROR: {IMG_DIR} not found")
    exit(1)

os.makedirs(REJECT_DIR, exist_ok=True)

files = sorted([f for f in os.listdir(IMG_DIR) if f.lower().endswith(('.jpg', '.jpeg', '.png', '.webp', '.gif'))])
total = len(files)
removed = 0
kept = 0
errors = 0

print(f"Scanning {total} images in {IMG_DIR}...")

for i, fname in enumerate(files):
    fpath = os.path.join(IMG_DIR, fname)
    try:
        img = cv2.imread(fpath)
        if img is None:
            # Can't read image - reject
            shutil.move(fpath, os.path.join(REJECT_DIR, fname))
            removed += 1
            print(f"  [{i+1}/{total}] {fname} - REJECTED (unreadable)")
            continue

        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

        # Detect faces with multiple scale factors for better detection
        faces = face_cascade.detectMultiScale(
            gray,
            scaleFactor=1.1,
            minNeighbors=4,
            minSize=(30, 30)
        )

        if len(faces) == 0:
            # Try again with more lenient settings
            faces = face_cascade.detectMultiScale(
                gray,
                scaleFactor=1.05,
                minNeighbors=3,
                minSize=(20, 20)
            )

        if len(faces) == 0:
            shutil.move(fpath, os.path.join(REJECT_DIR, fname))
            removed += 1
            if (i+1) % 50 == 0 or removed <= 10:
                print(f"  [{i+1}/{total}] {fname} - REJECTED (no face)")
        else:
            kept += 1
            if (i+1) % 100 == 0:
                print(f"  [{i+1}/{total}] {fname} - OK ({len(faces)} face(s))")
    except Exception as e:
        shutil.move(fpath, os.path.join(REJECT_DIR, fname))
        errors += 1
        removed += 1
        print(f"  [{i+1}/{total}] {fname} - ERROR: {e}")

print(f"\nDone!")
print(f"  Total scanned: {total}")
print(f"  Kept (face detected): {kept}")
print(f"  Rejected (no face): {removed}")
print(f"  Errors: {errors}")
print(f"  Rejected files moved to: {REJECT_DIR}")
