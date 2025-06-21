import unittest
from app import app

class TestMissingToken(unittest.TestCase):
    def setUp(self):
        self.client = app.test_client()

    def test_missing_token(self):
        data = {"text": "Test", "phone_number": "+573001112233"}
        response = self.client.post("/sentiment/", json=data)
        self.assertEqual(response.status_code, 401)
