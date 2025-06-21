import unittest
from unittest.mock import patch
from app import app

class TestInvalidToken(unittest.TestCase):
    def setUp(self):
        self.client = app.test_client()

    @patch("app.requests.get")
    def test_invalid_token(self, mock_get):
        mock_get.return_value.status_code = 401
        headers = {
            "Content-Type": "application/json",
            "Authorization": "Bearer invalid"
        }
        data = {"text": "Test", "phone_number": "+573001112233"}
        response = self.client.post("/sentiment/", json=data, headers=headers)
        self.assertEqual(response.status_code, 401)
