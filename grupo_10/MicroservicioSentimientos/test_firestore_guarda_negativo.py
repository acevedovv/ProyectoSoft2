import unittest
from unittest.mock import patch
from app import app

class TestFirestoreSave(unittest.TestCase):
    def setUp(self):
        self.client = app.test_client()

    @patch("app.db.collection")
    @patch("app.requests.post")
    @patch("app.validate_token")
    def test_firestore_save_on_negative(self, mock_auth, mock_post, mock_collection):
        mock_auth.return_value = None
        mock_post.return_value.status_code = 200
        mock_collection.return_value.add.return_value = None

        data = {"text": "This is horrible", "phone_number": "+573001112233"}
        response = self.client.post("/sentiment/", json=data,
                                    headers={"Authorization": "Bearer test", "Content-Type": "application/json"})
        self.assertEqual(response.status_code, 200)
        self.assertIn(b"negativo", response.data)
        mock_collection.return_value.add.assert_called()
