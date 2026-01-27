from fastapi import FastAPI, UploadFile, File, HTTPException
from fastapi.staticfiles import StaticFiles
from fastapi.templating import Jinja2Templates
from fastapi.requests import Request
from fastapi.responses import JSONResponse
import uvicorn
import json
import os
import shutil
from pathlib import Path

app = FastAPI(title="MyRVM Setup Wizard")

# Paths
BASE_DIR = Path(__file__).resolve().parent.parent.parent
CONFIG_DIR = BASE_DIR / "config"
TEMPLATES_DIR = Path(__file__).parent / "templates"
STATIC_DIR = Path(__file__).parent / "static"

# Ensure config dir exists
CONFIG_DIR.mkdir(parents=True, exist_ok=True)

# Mounts
app.mount("/static", StaticFiles(directory=str(STATIC_DIR)), name="static")
templates = Jinja2Templates(directory=str(TEMPLATES_DIR))

@app.get("/")
async def index(request: Request):
    return templates.TemplateResponse("index.html", {"request": request})

@app.post("/upload")
async def upload_credentials(file: UploadFile = File(...)):
    if not file.filename.endswith(".json"):
        raise HTTPException(status_code=400, detail="Only JSON files are allowed.")
    
    try:
        content = await file.read()
        data = json.loads(content)
        
        # Validate Structure
        required_fields = ["serial_number", "api_key", "name"]
        for field in required_fields:
            if field not in data:
                raise HTTPException(status_code=400, detail=f"Missing required field: {field}")
        
        # Write to secrets.env
        env_path = CONFIG_DIR / "secrets.env"
        with open(env_path, "w") as f:
            f.write(f"RVM_SERIAL_NUMBER={data['serial_number']}\n")
            f.write(f"RVM_API_KEY={data['api_key']}\n")
            f.write(f"RVM_NAME={data['name']}\n")
            f.write(f"RVM_GENERATED_AT={data.get('generated_at', '')}\n")
        
        # Signal success
        return JSONResponse(content={
            "status": "success",
            "message": "Credentials imported successfully. Rebooting into Normal Mode...",
            "data": data
        })

    except json.JSONDecodeError:
        raise HTTPException(status_code=400, detail="Invalid JSON file.")
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    uvicorn.run(app, host="0.0.0.0", port=8080)
