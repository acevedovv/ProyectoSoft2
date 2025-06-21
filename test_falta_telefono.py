import unittest
from app import app

class TestMissingPhoneField(unittest.TestCase):
    def setUp(self):
        self.client = app.test_client()
        self.headers = {
            "Content-Type": "application/json",
            "Authorization": "Bearer test-token"
        }

    def test_missing_phone_field(self):
        data = {"text": "Esto es un ejemplo positivo"}
        response = self.client.post("/sentiment/", json=data, headers=self.headers)
        self.assertEqual(response.status_code, 400)
        self.assertIn(b"El campo 'phone' es requerido", response.data)
