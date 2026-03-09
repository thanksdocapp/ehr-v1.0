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
        "Here is how a doctor creates a new patient in ThanksDoc.\n"
        "From the Patients area, click New Patient to open the registration form.\n"
        "Enter the patient details: first name, last name, gender, and date of birth.\n"
        "Add contact information like email and phone number, then complete the address and postcode.\n"
        "Provide emergency contact details, insurance information, and any allergy or medical history notes.\n"
        "When everything is complete, click Create Patient.\n"
        "The system confirms the record and the new patient appears in the list, ready for appointments and care."
    )

    screenshots_dir = r"C:\Users\chukw\AppData\Local\Temp\cursor\screenshots"
    images_with_durations = [
        (os.path.join(screenshots_dir, "doctor-create-patient-01-form-top.png"), 6),
        (os.path.join(screenshots_dir, "doctor-create-patient-01-form-top.png"), 10),
        (os.path.join(screenshots_dir, "doctor-create-patient-02-filled-form.png"), 14),
        (os.path.join(screenshots_dir, "doctor-create-patient-04-create-button.png"), 14),
        (os.path.join(screenshots_dir, "doctor-create-patient-05-patient-list.png"), 16),
    ]

    missing_images = [path for path, _ in images_with_durations if not os.path.isfile(path)]
    if missing_images:
        missing_list = "\n".join(missing_images)
        raise FileNotFoundError(f"Missing screenshots:\n{missing_list}")

    output_audio = r"C:\Users\chukw\Documents\doctor-create-patient-voice.mp3"
    output_video = r"C:\Users\chukw\Documents\doctor-create-patient-demo.mp4"

    asyncio.run(generate_voiceover(narration, output_audio))
    build_video_with_audio(images_with_durations, output_audio, output_video)

    print(f"Voiceover saved to: {output_audio}")
    print(f"Video saved to: {output_video}")


if __name__ == "__main__":
    main()
