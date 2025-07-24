FROM python:3.10-slim

WORKDIR /app

# Copiar TODO el contenido del directorio
COPY . .

# Instalar solo las dependencias necesarias
RUN pip install --no-cache-dir -r requirements.txt gunicorn

EXPOSE 5000

# Comando de ejecución (igual que funcionaba localmente)
CMD ["gunicorn", "--bind", "0.0.0.0:5000", "app:app"]
