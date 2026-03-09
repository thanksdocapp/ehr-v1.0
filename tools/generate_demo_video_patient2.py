import asyncio
import os
import subprocess
import sys
import tempfile


def ensure_package(package_name: str) -> None:
    try:
        __import__(package_name)
    except ImportError:
        subprocess.check_call([sys.executable, "-m", "pip", "install", package_name])


async def generate_voiceover(text: str, output_path: str) -> None:
    import edge_tts

    voice = "en-GB-SoniaNeural"
    communicator = edge_tts.Communicate(text, voice=voice)
    await communicator.save(output_path)


def build_video_with_audio(
    images_with_durations: list[tuple[str, int]],
    audio_path: str,
    output_path: str,
) -> None:
    import imageio_ffmpeg

    ffmpeg_path = imageio_ffmpeg.get_ffmpeg_exe()
    if not os.path.isfile(ffmpeg_path):
        raise FileNotFoundError(f"ffmpeg not found at {ffmpeg_path}")

    with tempfile.NamedTemporaryFile(mode="w", suffix=".txt", delete=False) as list_file:
        for image_path, duration in images_with_durations:
            list_file.write(f"file '{image_path}'\n")
            list_file.write(f"duration {duration}\n")
        list_file_path = list_file.name

    try:
        command = [
            ffmpeg_path,
            "-y",
            "-f",
            "concat",
            "-safe",
            "0",
            "-i",
            list_file_path,
            "-i",
            audio_path,
            "-c:v",
            "libx264",
            "-pix_fmt",
            "yuv420p",
            "-r",
            "30",
            "-vf",
            "scale=1280:-2",
            "-c:a",
            "aac",
            "-shortest",
            output_path,
        ]
        subprocess.check_call(command)
    finally:
        os.unlink(list_file_path)


def main() -> None:
    ensure_package("edge_tts")
    ensure_package("imageio_ffmpeg")

    narration = (
        "This demo shows a doctor creating a new patient record in ThanksDoc.\n"
        "From the Patients page, select New Patient to open the registration form.\n"
        "Enter identity details and date of birth.\n"
        "Fill in contact information and address, including postcode.\n"
        "Add emergency contact information for the patient.\n"
        "Complete medical information such as insurance details, allergies, and medical history.\n"
        "Finally, click Create Patient to save the record.\n"
        "A confirmation banner appears and the patient is added to the list."
    )

    screenshots_dir = r"C:\Users\chukw\AppData\Local\Temp\cursor\screenshots"
    images_with_durations = [
        (os.path.join(screenshots_dir, "doctor-create-patient2-01-patients-list-highlight.png"), 4),
        (os.path.join(screenshots_dir, "doctor-create-patient2-02-identity-highlight.png"), 4),
        (os.path.join(screenshots_dir, "doctor-create-patient2-03-dob-highlight.png"), 4),
        (os.path.join(screenshots_dir, "doctor-create-patient2-04-contact-address-highlight.png"), 5),
        (os.path.join(screenshots_dir, "doctor-create-patient2-05-emergency-contact-highlight.png"), 4),
        (os.path.join(screenshots_dir, "doctor-create-patient2-06-medical-info-highlight.png"), 5),
        (os.path.join(screenshots_dir, "doctor-create-patient2-07-create-button-highlight.png"), 4),
        (os.path.join(screenshots_dir, "doctor-create-patient2-08-patient-created-banner.png"), 5),
    ]

    missing_images = [path for path, _ in images_with_durations if not os.path.isfile(path)]
    if missing_images:
        missing_list = "\n".join(missing_images)
        raise FileNotFoundError(f"Missing screenshots:\n{missing_list}")

    output_audio = r"C:\Users\chukw\Documents\doctor-create-patient-demo-2-voice.mp3"
    output_video = r"C:\Users\chukw\Documents\doctor-create-patient-demo-2-synced.mp4"

    asyncio.run(generate_voiceover(narration, output_audio))
    build_video_with_audio(images_with_durations, output_audio, output_video)

    print(f"Voiceover saved to: {output_audio}")
    print(f"Video saved to: {output_video}")


if __name__ == "__main__":
    main()
