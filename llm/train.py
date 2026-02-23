import os
import json
import torch
from PIL import Image
from transformers import AutoProcessor, AutoModelForImageTextToText
from tqdm import tqdm
import time

# Конфигурация
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_PATH = os.path.join(BASE_DIR, "models", "Qwen2-VL-2B")
IMAGES_DIR = os.path.join(BASE_DIR, "train-data", "images")
OUTPUT_FILE = "game_generation_results.json"

# Загружаем модель и процессор
print("Загрузка модели...")
processor = AutoProcessor.from_pretrained(MODEL_PATH, trust_remote_code=True)
model = AutoModelForImageTextToText.from_pretrained(
    MODEL_PATH,
    device_map="cpu",
    torch_dtype=torch.float16,
    trust_remote_code=True,
    low_cpu_mem_usage=True
)
print("Модель загружена!")


# Функция для генерации игры по фото
def generate_game_from_image(image_path, items, players, age):
    try:
        # Загружаем изображение
        image = Image.open(image_path).convert("RGB")

        # Формируем промпт
        prompt = f"На фото местность. Доступные предметы: {', '.join(items)}. Игроков: {players}. Средний возраст: {age}. Предложи активную игру для этих условий. Название игры и подробные правила."

        # Подготавливаем входные данные
        messages = [
            {
                "role": "user",
                "content": [
                    {"type": "image"},
                    {"type": "text", "text": prompt}
                ]
            }
        ]

        text = processor.apply_chat_template(messages, add_generation_prompt=True)
        inputs = processor(
            text=[text],
            images=[image],
            padding=True,
            return_tensors="pt"
        )

        # Генерируем ответ
        with torch.no_grad():
            generated_ids = model.generate(
                **inputs,
                max_new_tokens=200,
                do_sample=True,
                temperature=0.7,
                top_p=0.9,
                num_beams=1
            )

        # Декодируем ответ
        output_text = processor.batch_decode(
            generated_ids[:, inputs["input_ids"].shape[1]:],
            skip_special_tokens=True
        )[0]

        return {
            "success": True,
            "game": output_text.strip()
        }

    except Exception as e:
        return {
            "success": False,
            "error": str(e)
        }


# Основная функция для прогона всех фото
def process_all_images():
    results = []

    # Получаем список всех изображений
    image_files = [f for f in os.listdir(IMAGES_DIR)
                   if f.lower().endswith(('.jpg', '.jpeg', '.png', '.webp'))]

    print(f"Найдено {len(image_files)} изображений")

    # Для каждого изображения генерируем несколько вариаций игр
    for img_file in tqdm(image_files, desc="Обработка изображений"):
        image_path = os.path.join(IMAGES_DIR, img_file)

        # Генерируем 3 разных варианта для одного фото (с разными параметрами)
        for variant in range(3):
            # Случайные параметры для разнообразия
            items = random.choice([
                ["мяч"],
                ["мяч", "скамейка"],
                ["палки", "мел"],
                ["скакалка"],
                ["кегли", "мяч", "мел"]
            ])
            players = random.randint(4, 16)
            age = random.randint(5, 12)

            result = generate_game_from_image(image_path, items, players, age)

            if result["success"]:
                results.append({
                    "image": img_file,
                    "items": items,
                    "players": players,
                    "age": age,
                    "game": result["game"],
                    "timestamp": time.strftime("%Y-%m-%d %H:%M:%S")
                })

            # Небольшая задержка чтобы не нагружать систему
            time.sleep(0.5)

    # Сохраняем результаты
    with open(OUTPUT_FILE, 'w', encoding='utf-8') as f:
        json.dump(results, f, ensure_ascii=False, indent=2)

    print(f"\nОбработано {len(results)} вариантов")
    print(f"Результаты сохранены в {OUTPUT_FILE}")

    return results


# Запуск
if __name__ == "__main__":
    import random  # добавим импорт

    results = process_all_images()

    # Покажем несколько примеров
    print("\nПримеры результатов:")
    for i, r in enumerate(results[:3]):
        print(f"\n--- Пример {i + 1} ---")
        print(f"Фото: {r['image']}")
        print(f"Предметы: {r['items']}")
        print(f"Игроков: {r['players']}, Возраст: {r['age']}")
        print(f"Игра: {r['game'][:200]}...")