from fastapi import FastAPI

app = FastAPI(title="LLM Service")

@app.get("/")
async def root():
    return {"message": "LLM Service is running"}

@app.get("/health")
async def health():
    return {"status": "healthy"}