import requests
import json

# Test the chatbot API
url = "http://localhost:8000/api/chat"

test_data = {
    "title": "India Launches Chandrayaan-4 Moon Mission Successfully",
    "summary": "ISRO successfully launched Chandrayaan-4 mission on October 5, 2025",
    "contents": "The Indian Space Research Organisation (ISRO) achieved a major milestone today by successfully launching the Chandrayaan-4 mission from Satish Dhawan Space Centre in Sriharikota. The spacecraft lifted off at 2:35 PM IST aboard the LVM3-M5 rocket.",
    "question": "When was the Chandrayaan-4 mission launched?",
    "thread_id": "test123"
}

try:
    response = requests.post(url, json=test_data)
    print("Status Code:", response.status_code)
    print("Response:", json.dumps(response.json(), indent=2))
except Exception as e:
    print("Error:", str(e))
