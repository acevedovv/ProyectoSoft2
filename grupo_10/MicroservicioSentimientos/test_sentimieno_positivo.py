import unittest
from unittest.mock import patch
from app import app

class TestPositiveSentiment(unittest.TestCase):
    def setUp(self):
        self.client = app.test_client()

    @patch("app.validate_token")
    def test_positive_sentiment(self, mock_auth):
        mock_auth.return_value = None
        data = {"text": "I love this service", "phone_number": "+573001112233"}
        response = self.client.post("/sentiment/", json=data,
                                    headers={"Authorization": "Bearer test", "Content-Type": "application/json"})
        self.assertEqual(response.status_code, 200)
        self.assertIn(b"positivo", response.data)
