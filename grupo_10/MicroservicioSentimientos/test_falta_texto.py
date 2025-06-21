import unittest
from app import app

class TestMissingTextField(unittest.TestCase):
    def setUp(self):
        self.client = app.test_client()
        self.headers = {
            "Content-Type": "application/json",
            "Authorization": "Bearer test-token"
        }

    def test_missing_text_field(self):
        data = {"phone_number": "+573001112233"}
        response = self.client.post("/sentiment/", json=data, headers=self.headers)
        self.assertEqual(response.status_code, 400)
        self.assertIn(b"El campo 'text' es requerido", response.data)
