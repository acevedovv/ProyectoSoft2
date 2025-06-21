import unittest
from unittest.mock import patch
from app import app

class TestNotificationSent(unittest.TestCase):
    def setUp(self):
        self.client = app.test_client()

    @patch("app.requests.post")
    @patch("app.validate_token")
    def test_notification_on_negative(self, mock_auth, mock_post):
        mock_auth.return_value = None
        mock_post.return_value.status_code = 200

        data = {"text": "This is terrible", "phone_number": "+573001112233"}
        response = self.client.post("/sentiment/", json=data,
                                    headers={"Authorization": "Bearer test", "Content-Type": "application/json"})
        self.assertEqual(response.status_code, 200)
        mock_post.assert_called()
