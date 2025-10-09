class Urls {
  // Production AWS EC2 Deployment
  static String baseUrl = "http://15.206.148.126:3000/api/";
  static String baseServer = "http://15.206.148.126:3000/";

  // AI Backend (Direct FastAPI Connection)
  static String aiBackendUrl = "http://15.206.148.126:8000/";

  // AI Chatbot Endpoints (Direct to AI Backend)
  static String aiChat = "${aiBackendUrl}api/chat";
  static String aiChatHistory = "${aiBackendUrl}api/chat/history";
  static String aiHealth = "${aiBackendUrl}health";

  // Legacy AI endpoints (via Express proxy - if needed)
  static String aiInitSession = "${baseUrl}ask-ai/init";
  static String aiQuery = "${baseUrl}ask-ai/query";
  static String aiHistory = "${baseUrl}ask-ai/history";
  static String aiStatus = "${baseUrl}ai-status";
}
