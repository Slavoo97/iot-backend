import cv2
import os
import re

# Nastavenia
input_folder = "../www/upload/images"  # Cesta k priecinku s JPG fotkami zachytenymi programom
output_video = "../www/upload/videos/timelapse.mp4"  # Nazov vystupneho videa
fps = 24  # Pocet snimok za sekundu

# Funkcia na zoradenie obrazkov podla casovej znacky (timestamp)
def sort_images_by_timestamp(filenames):
    def extract_timestamp(filename):
        match = re.search(r"image_(\d{8}_\d{6})\.jpg", filename)
        return match.group(1) if match else ""
    return sorted(filenames, key=extract_timestamp)

# Funkcia na nacitanie a zoradenie obrazkov
def load_images_from_folder(folder):
    images = []
    filenames = [f for f in os.listdir(folder) if f.endswith(".jpg") or f.endswith(".jpeg")]
    sorted_filenames = sort_images_by_timestamp(filenames)

    for filename in sorted_filenames:
        print(f"Nacitavam obrazok: {filename}")
        img_path = os.path.join(folder, filename)
        img = cv2.imread(img_path)
        if img is not None:
            images.append(img)
    return images

# Hlavna funkcia na vytvorenie videa
def create_timelapse(input_folder, output_video, fps):
    images = load_images_from_folder(input_folder)

    if not images:
        print("V adresari sa nenasli ziadne JPG obrazky.")
        return

    # Zisti rozlisenie obrazkov
    height, width, _ = images[0].shape

    # Inicializuj zapisovac videa s použitím FFmpeg backendu
    fourcc = cv2.VideoWriter_fourcc(*'mp4v')  # Codec pre mp4 format
    out = cv2.VideoWriter(output_video, fourcc, fps, (width, height))

    print("Vytvaram timelapse video...")
    for i, img in enumerate(images):
        out.write(img)
        print(f"Pridavam snimok {i+1}/{len(images)}")

    # Uvolni zdroje
    out.release()
    print(f"Timelapse video bolo uspesne vytvorene: {output_video}")

# Spusti program
if __name__ == "__main__":
    create_timelapse(input_folder, output_video, fps)
