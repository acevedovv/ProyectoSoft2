# Imagen base
FROM python:3.13-slim

# Establecer directorio de trabajo
WORKDIR /app

# Copiar los archivos necesarios
COPY . .

# Instalar dependencias
RUN pip install --upgrade pip && pip install -r requirements.txt

# Exponer el puerto
EXPOSE 5000

# Comando para ejecutar la app
CMD ["python", "app.py"]
