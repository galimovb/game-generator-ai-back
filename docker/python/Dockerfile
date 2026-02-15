FROM python:3.10

WORKDIR /app

# Системные зависимости
RUN apt-get update && apt-get install -y \
    git \
    curl \
    build-essential \
    && rm -rf /var/lib/apt/lists/*

COPY llm/requirements.txt requirements.txt

# Ставим CPU-версию PyTorch правильно
RUN pip install --upgrade pip
RUN pip install torch --index-url https://download.pytorch.org/whl/cpu

# Остальные зависимости
RUN pip install -r requirements.txt

COPY . .

CMD ["python", "install.py"]
