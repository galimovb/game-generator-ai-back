import os
import torch
import json
import time
import requests
from PIL import Image
from transformers import (
    AutoProcessor,
    AutoModelForImageTextToText,
    BitsAndBytesConfig
)

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODELS_DIR = os.path.join(BASE_DIR, "models")

# Список моделей для тестирования
MODELS = {
    "Qwen2.5-VL-3B": {
        "path": os.path.join(MODELS_DIR, "Qwen2.5-VL-3B"),
        "repo": "Qwen/Qwen2.5-VL-3B-Instruct",
        "use_4bit": False,
        "dtype": "float16"
    }
}


def get_quant_config():
    """4-bit конфигурация для экономии памяти"""
    return BitsAndBytesConfig(
        load_in_4bit=True,
        bnb_4bit_compute_dtype=torch.float16,
        bnb_4bit_use_double_quant=True,
        bnb_4bit_quant_type="nf4"
    )


def check_model_files(model_path):
    """Проверяет наличие файлов модели"""
    if not os.path.exists(model_path):
        return False

    # Проверяем наличие config.json
    if not os.path.exists(os.path.join(model_path, "config.json")):
        return False

    # Проверяем наличие весов
    has_weights = False
    for file in os.listdir(model_path):
        if file.endswith(('.safetensors', '.bin')):
            has_weights = True
            break

    return has_weights


def get_folder_size_gb(path):
    """Возвращает размер папки в GB"""
    total = 0
    for root, dirs, files in os.walk(path):
        for f in files:
            fp = os.path.join(root, f)
            total += os.path.getsize(fp)
    return total / (1024 ** 3)


def download_model_if_needed(model_key, model_info):
    """Скачивает модель если её нет"""
    model_path = model_info["path"]

    if check_model_files(model_path):
        print(f"✓ {model_key} already exists")
        return True

    print(f"\n⬇ Downloading {model_info['repo']}...")
    os.makedirs(model_path, exist_ok=True)

    try:
        from huggingface_hub import snapshot_download
        snapshot_download(
            repo_id=model_info['repo'],
            local_dir=model_path,
            local_dir_use_symlinks=False,
            resume_download=True,
            ignore_patterns=['*.md', '*.txt']
        )
        print(f"✓ {model_key} downloaded")
        return True
    except Exception as e:
        print(f"✗ Download failed: {e}")
        return False


def load_and_test_model(model_key, model_info):
    """Загружает модель и возвращает результаты теста"""
    result = {
        "model": model_key,
        "status": "unknown",
        "load_time": 0,
        "inference_time": 0,
        "error": None,
        "answer": None,
        "model_size_gb": 0,
        "quantization": "4-bit" if model_info["use_4bit"] else "float32"
    }

    # Проверяем существование модели
    if not check_model_files(model_info["path"]):
        result["status"] = "error"
        result["error"] = f"Model not found at {model_info['path']}"
        return result

    try:
        print(f"\n=== Loading {model_key} ({result['quantization']}) ===")
        start_load = time.time()

        # Параметры загрузки
        load_kwargs = {
            "device_map": "cpu",
            "torch_dtype": torch.float16 if model_info.get("dtype") == "float16" else torch.float32,
            "trust_remote_code": True,
            "low_cpu_mem_usage": True
        }

        # Добавляем квантизацию если нужно
        if model_info["use_4bit"]:
            load_kwargs["quantization_config"] = get_quant_config()

        # Загружаем модель
        model = AutoModelForImageTextToText.from_pretrained(
            model_info["path"],
            **load_kwargs
        )

        processor = AutoProcessor.from_pretrained(
            model_info["path"],
            trust_remote_code=True
        )

        load_time = time.time() - start_load
        result["load_time"] = round(load_time, 2)

        # Размер модели на диске
        result["model_size_gb"] = round(get_folder_size_gb(model_info["path"]), 2)

        # Загружаем тестовое изображение
        image_path = os.path.join(BASE_DIR, "img.png")
        if os.path.exists(image_path):
            image = Image.open(image_path).convert("RGB")
        else:
            url = "http://images.cocodataset.org/val2017/000000039769.jpg"
            image = Image.open(requests.get(url, stream=True).raw).convert("RGB")

        # Тестовый промпт
        messages = [
            {
                "role": "user",
                "content": [
                    {"type": "image"},
                    {"type": "text",
                     "text": "Что на этом фото? Предложи активную игру для детей, основываясь на местности."}
                ],
            }
        ]

        text = processor.apply_chat_template(messages, add_generation_prompt=True)
        inputs = processor(
            text=[text],
            images=[image],
            padding=True,
            return_tensors="pt"
        )

        # Инференс
        print("Generating...")
        start_inf = time.time()

        with torch.no_grad():
            generated_ids = model.generate(
                **inputs,
                max_new_tokens=120,
                do_sample=False,
                temperature=None,  # убираем конфликт
                top_p=None,
                top_k=None,
                num_beams=1
            )

        inference_time = time.time() - start_inf

        output_text = processor.batch_decode(
            generated_ids[:, inputs["input_ids"].shape[1]:],
            skip_special_tokens=True
        )[0]

        result["status"] = "success"
        result["inference_time"] = round(inference_time, 2)
        result["answer"] = output_text.strip()

        print(f"✓ {model_key} OK - Load: {load_time:.2f}s, Inf: {inference_time:.2f}s")

    except Exception as e:
        result["status"] = "error"
        result["error"] = str(e)
        print(f"✗ {model_key} failed: {e}")

    return result


def main():
    """Главная функция"""
    print("=" * 60)
    print("MODEL TESTING SUITE WITH 4-BIT QUANTIZATION")
    print("=" * 60)
    print(f"PyTorch: {torch.__version__}")
    print(f"CPU cores: {os.cpu_count()}")

    # Проверка памяти
    try:
        import psutil
        mem = psutil.virtual_memory()
        print(f"RAM: {mem.total / (1024 ** 3):.1f}GB total, {mem.available / (1024 ** 3):.1f}GB available")
    except:
        pass

    print(f"Models directory: {MODELS_DIR}")

    # Сначала скачиваем недостающие модели
    print("\n" + "=" * 60)
    print("CHECKING MODELS...")
    print("=" * 60)

    for model_key, model_info in MODELS.items():
        download_model_if_needed(model_key, model_info)

    # Тестируем модели
    print("\n" + "=" * 60)
    print("TESTING MODELS...")
    print("=" * 60)

    results = []
    for model_key, model_info in MODELS.items():
        result = load_and_test_model(model_key, model_info)
        results.append(result)
        print("-" * 40)

    # Формируем финальный JSON
    final_result = {
        "timestamp": time.strftime("%Y-%m-%d %H:%M:%S"),
        "system": {
            "cpu_cores": os.cpu_count(),
            "pytorch_version": torch.__version__,
            "cuda_available": torch.cuda.is_available()
        },
        "models": results,
        "summary": {
            "total_models": len(results),
            "successful": sum(1 for r in results if r["status"] == "success"),
            "failed": sum(1 for r in results if r["status"] == "error")
        }
    }

    # Выводим JSON
    print("\n" + "=" * 60)
    print("FINAL RESULTS (JSON):")
    print("=" * 60)
    print(json.dumps(final_result, indent=2, ensure_ascii=False))

    # Сохраняем в файл
    output_file = os.path.join(BASE_DIR, "model_test_results.json")
    with open(output_file, "w", encoding="utf-8") as f:
        json.dump(final_result, f, indent=2, ensure_ascii=False)

    print(f"\n✓ Results saved to: {output_file}")

    # Краткий итог
    print("\n" + "=" * 60)
    print("SUMMARY:")
    for r in results:
        status_icon = "✓" if r["status"] == "success" else "✗"
        print(f"{status_icon} {r['model']}: {r['status']} ({r.get('quantization', 'N/A')})")

    return final_result


if __name__ == "__main__":
    main()