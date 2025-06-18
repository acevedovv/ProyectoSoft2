import requests
import os
import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.naive_bayes import MultinomialNB
from sklearn.model_selection import train_test_split
from flask import Flask, request, jsonify
import pickle
import firebase_admin
from firebase_admin import credentials, firestore

# Cargar variables de entorno
MICROSERVICE_NOTIFICATION = os.getenv("MICROSERVICE_NOTIFICATION", "http://127.0.0.1:8001/api")
X_API_KEY = os.getenv("X_API_Key", "123")

# Inicializar Firebase
cred = credentials.Certificate("sentimientos-55c9d-firebase-adminsdk-fbsvc-39819a648a.json")
firebase_admin.initialize_app(cred)
db = firestore.client()

# Cargar el dataset
df = pd.read_csv("training.1600000.processed.noemoticon.csv", encoding="ISO-8859-1", header=None)
 
# Renombrar columnas
df.columns = ["sentiment", "id", "date", "flag", "user", "text"]

# Convertir etiquetas 0 -> negativo, 4 -> positivo
df["sentiment"] = df["sentiment"].replace({0: "negativo", 4: "positivo"})

# Vectorización del texto
vectorizer = TfidfVectorizer(stop_words="english")
X = vectorizer.fit_transform(df["text"])
y = df["sentiment"]

# Dividir en conjunto de entrenamiento y prueba
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Entrenar modelo Naive Bayes
model = MultinomialNB()
model.fit(X_train, y_train)

# Guardar modelo y vectorizador
with open("model.pkl", "wb") as model_file:
    pickle.dump(model, model_file)

with open("vectorizer.pkl", "wb") as vectorizer_file:
    pickle.dump(vectorizer, vectorizer_file)

app = Flask(__name__)

# Cargar modelo y vectorizador previamente entrenados
try:
    with open("model.pkl", "rb") as model_file:
        model = pickle.load(model_file)

    with open("vectorizer.pkl", "rb") as vectorizer_file:
        vectorizer = pickle.load(vectorizer_file)
except Exception as e:
    print(f"Error al cargar modelo o vectorizador: {e}")
    exit(1)
    
GATEWAY_AUTH_URL = "http://gateway/api/validate-token"  # Ruta del API Gateway

def validate_token():
    token = request.headers.get("Authorization")
    if not token:
        return jsonify({"error": "Unauthorized"}), 401

    response = requests.get(GATEWAY_AUTH_URL, headers={"Authorization": token})
    if response.status_code != 200:
        return jsonify({"error": "Invalid token"}), 401

    return None

@app.route('/sentiment/', methods=['POST'])
def sentiment():
    auth_error = validate_token()
    if auth_error:
        return auth_error 
    data = request.json
    text = data.get("text", "")
    phone = data.get("phone_number", "")  # Obtener el número de teléfono

    if not text:
        return jsonify({"error": "El campo 'text' es requerido"}), 400
    if not phone:
        return jsonify({"error": "El campo 'phone' es requerido"}), 400

    try:
        text_vectorized = vectorizer.transform([text])
        sentiment_result = model.predict(text_vectorized)[0]

        registro = {
            "text": text,
            "sentiment": sentiment_result,
            "timestamp": firestore.SERVER_TIMESTAMP
        }

        

        if sentiment_result == "negativo":
            notification_data = {
                "message": f"Alerta: Se detectó un sentimiento negativo en el texto: '{text}'",
                "phone_number": phone  # Incluir el número de teléfono
            }
            headers = {
                "X-API-Key": X_API_KEY,
                "Content-Type": "application/json"
            }

            response = requests.post(f"{MICROSERVICE_NOTIFICATION}/send-notification",
                                     json=notification_data, headers=headers)

            if response.status_code == 200:
                print("✅ Notificación enviada correctamente.")
                db.collection("predicciones").add(registro)
            else:
                print(f"⚠️ Error al enviar la notificación: {response.status_code} - {response.text}")

        return jsonify({"sentiment": sentiment_result})
    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=False)
