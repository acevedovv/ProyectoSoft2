import unittest
from unittest.mock import patch
from app import app

class TestNegativeSentiment(unittest.TestCase):
    def setUp(self):
        self.client = app.test_client()

    @patch("app.validate_token")
    def test_negative_sentiment(self, mock_auth):
        mock_auth.return_value = None
        data = {"text": "I hate everything", "phone_number": "+573001112233"}
        response = self.client.post("/sentiment/", json=data,
                                    headers={"Authorization": "Bearer test", "Content-Type": "application/json"})
        self.assertEqual(response.status_code, 200)
        self.assertIn(b"negativo", response.data)
