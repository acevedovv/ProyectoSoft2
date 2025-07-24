import os
import requests
from flask import Flask, request, jsonify
import pickle
import firebase_admin
from firebase_admin import credentials, firestore
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.naive_bayes import MultinomialNB

app = Flask(__name__)

# Configuración inicial
MICROSERVICE_NOTIFICATION = None
MICROSERVICE_NOTIFICATION = os.getenv("MICROSERVICE_NOTIFICATION", "http://127.0.0.1:8001/api")
X_API_KEY = os.getenv("X_API_Key", "123")
GATEWAY_AUTH_URL = os.getenv("GATEWAY_AUTH_URL", "http://localhost:8000/api/validate-token")

# Inicializar Firebase
cred = credentials.Certificate("sentimientos-31181-firebase-adminsdk-fbsvc-303f64d05a.json")
firebase_admin.initialize_app(cred)
db = firestore.client()

# Cargar o entrenar modelo
def load_or_train_model():
    try:
        # Intenta cargar el modelo pre-entrenado
        with open("model.pkl", "rb") as model_file, \
             open("vectorizer.pkl", "rb") as vectorizer_file:
            model = pickle.load(model_file)
            vectorizer = pickle.load(vectorizer_file)
            print("✅ Modelo y vectorizador cargados desde archivos .pkl")
            return model, vectorizer
    except:
        print("⚠️ No se encontraron modelos pre-entrenados, entrenando nuevo modelo...")
        import pandas as pd
        df = pd.read_csv("training.1600000.processed.noemoticon.csv", 
                        encoding="ISO-8859-1", 
                        header=None)
        
        df.columns = ["sentiment", "id", "date", "flag", "user", "text"]
        df["sentiment"] = df["sentiment"].replace({0: "negativo", 4: "positivo"})
        
        vectorizer = TfidfVectorizer(stop_words="english")
        X = vectorizer.fit_transform(df["text"])
        y = df["sentiment"]
        
        model = MultinomialNB()
        model.fit(X, y)
        
        # Guardar para próximas ejecuciones
        with open("model.pkl", "wb") as model_file, \
             open("vectorizer.pkl", "wb") as vectorizer_file:
            pickle.dump(model, model_file)
            pickle.dump(vectorizer, vectorizer_file)
        
        return model, vectorizer

# Cargar modelo al iniciar
model, vectorizer = load_or_train_model()

# Funciones de utilidad
def validate_token():
    token = request.headers.get("Authorization")
    if not token:
        return jsonify({"error": "Unauthorized"}), 401

    response = requests.get(GATEWAY_AUTH_URL, headers={"Authorization": token})
    if response.status_code != 200:
        return jsonify({"error": "Invalid token"}), 401 
    return None
		

def send_notification(phone, text):
    notification_data = {
        "message": f"Alerta: Sentimiento negativo detectado: '{text}'",
        "phone_number": phone
    }
    headers = {
        "X-API-Key": X_API_KEY,
        "Content-Type": "application/json"
    }
    response = requests.post(
        f"{MICROSERVICE_NOTIFICATION}/send-notification",
        json=notification_data, 
        headers=headers
    )
    return response

# Endpoint principal
@app.route('/sentiment/', methods=['POST'])  # Nota la barra al final
def sentiment():
    # Validación de token
   # auth_error = validate_token()
   # if auth_error:
    #    return auth_error 
    
    # Validación de datos de entrada
    data = request.get_json()  # Mejor práctica para obtener JSON
    if not data:
        return jsonify({"error": "Se requiere datos JSON"}), 400
        
    text = data.get("text", "")
    phone = data.get("phone_number", "")

    if not text:
        return jsonify({"error": "El campo 'text' es requerido"}), 400
    if not phone:
        return jsonify({"error": "El campo 'phone_number' es requerido"}), 400

    try:
        # Predicción de sentimiento
        text_vectorized = vectorizer.transform([text])
        sentiment_result = model.predict(text_vectorized)[0]

        # Registro en Firestore
        registro = {
            "text": text,
            "sentiment": sentiment_result,
            "phone_number": phone,
            "timestamp": firestore.SERVER_TIMESTAMP
        }

        # Manejo de sentimiento negativo
        if sentiment_result == "negativo":
            try:
                notification_data = {
                    "message": f"Alerta: Sentimiento negativo detectado: '{text[:50]}...'",
                    "phone_number": phone
                }
                headers = {
                    "X-API-Key": X_API_KEY,
                    "Content-Type": "application/json"
                }
                
                # Solo intenta enviar si el microservicio está configurado
                if MICROSERVICE_NOTIFICATION:
                    response = requests.post(
                        f"{MICROSERVICE_NOTIFICATION}/send-notification",
                        json=notification_data,
                        headers=headers,
                        timeout=3  # Timeout de 3 segundos
                    )
                    if response.status_code != 200:
                        print(f"⚠️ Error en notificación: {response.status_code}")
                
                # Guardar registro siempre
                db.collection("predicciones").add(registro)
                
            except requests.exceptions.RequestException as e:
                print(f"⚠️ Error de conexión con servicio de notificaciones: {str(e)}")
                db.collection("predicciones").add(registro)
        
        # Respuesta exitosa en todos los casos
        return jsonify({
            "success": True,
            "sentiment": sentiment_result,
            "text_received": text[:100] + "..." if len(text) > 100 else text
        })
        
    except Exception as e:
        print(f"❌ Error interno: {str(e)}")
        return jsonify({
            "error": "Error interno del servidor",
            "details": str(e)
        }), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=False)
