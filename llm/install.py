import os
from huggingface_hub import snapshot_download

MODELS = {
    "Qwen2-VL-2B": "Qwen/Qwen2-VL-2B-Instruct",
    "Qwen2.5-VL-3B": "Qwen/Qwen2.5-VL-3B-Instruct",
}

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODELS_DIR = os.path.join(BASE_DIR, "models")

def download_model(model_key, repo_id):
    model_path = os.path.join(MODELS_DIR, model_key)

    if os.path.exists(model_path) and os.listdir(model_path):
        print(f"✓ {model_key} already exists")
        return

    print(f"\n⬇ Downloading {repo_id} safely...")
    os.makedirs(model_path, exist_ok=True)

    snapshot_download(
        repo_id=repo_id,
        local_dir=model_path,
        local_dir_use_symlinks=False
    )

    print(f"✓ {model_key} fully downloaded")

if __name__ == "__main__":
    os.makedirs(MODELS_DIR, exist_ok=True)

    print("=== Safe Qwen Downloader ===")

    for key, repo in MODELS.items():
        download_model(key, repo)

    print("\n✓ All models downloaded")
