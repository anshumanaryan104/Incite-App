class Urls {
  // For WSL backend: Use WSL IP address
  static String baseUrl = "http://172.21.158.105:3000/api/";
  static String baseServer = "http://172.21.158.105:3000/";

  // AI Chatbot Endpoints
  static String aiInitSession = "${baseUrl}ask-ai/init";
  static String aiQuery = "${baseUrl}ask-ai/query";
  static String aiHistory = "${baseUrl}ask-ai/history";
  static String aiStatus = "${baseUrl}ai-status";
}
