import os
import sys
import subprocess
from transformers import AutoTokenizer, AutoModelForCausalLM, CLIPModel, CLIPProcessor

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODELS_DIR = os.path.join(BASE_DIR, "models")
LLM_DIR = os.path.join(MODELS_DIR, "phi-2")
CLIP_DIR = os.path.join(MODELS_DIR, "clip")

LLM_NAME = "microsoft/phi-2"
CLIP_NAME = "openai/clip-vit-base-patch32"


def create_directories():
    os.makedirs(MODELS_DIR, exist_ok=True)
    os.makedirs(LLM_DIR, exist_ok=True)
    os.makedirs(CLIP_DIR, exist_ok=True)
    os.makedirs(os.path.join(BASE_DIR, "lora"), exist_ok=True)
    print("✓ Directories ready")


def install_requirements():
    print("Installing requirements...")
    subprocess.check_call([sys.executable, "-m", "pip", "install", "-r", "requirements.txt"])
    print("✓ Requirements installed")


def download_llm():
    if os.listdir(LLM_DIR):
        print("✓ Phi-2 already exists")
        return

    print("Downloading Phi-2...")

    tokenizer = AutoTokenizer.from_pretrained(LLM_NAME, use_fast=True)
    model = AutoModelForCausalLM.from_pretrained(
        LLM_NAME,
        torch_dtype="auto",
        low_cpu_mem_usage=True
    )

    tokenizer.save_pretrained(LLM_DIR)
    model.save_pretrained(LLM_DIR)

    print("✓ Phi-2 downloaded")


def download_clip():
    if os.listdir(CLIP_DIR):
        print("✓ CLIP already exists")
        return

    print("Downloading CLIP...")

    model = CLIPModel.from_pretrained(CLIP_NAME)
    processor = CLIPProcessor.from_pretrained(CLIP_NAME)

    model.save_pretrained(CLIP_DIR)
    processor.save_pretrained(CLIP_DIR)

    print("✓ CLIP downloaded")


if __name__ == "__main__":
    print("Starting installation...\n")

    create_directories()
    install_requirements()
    download_llm()
    download_clip()

    print("\nInstallation complete.")
