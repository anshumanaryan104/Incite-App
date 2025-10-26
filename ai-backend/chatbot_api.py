from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from langchain_openai import ChatOpenAI
from langgraph.graph import StateGraph, START, END
from langchain_core.messages import HumanMessage, SystemMessage, BaseMessage
from typing import Annotated, TypedDict
import operator
from langgraph.checkpoint.memory import MemorySaver
import os
from dotenv import load_dotenv
import logging

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

load_dotenv()

# Get environment configuration
ENV = os.getenv("ENVIRONMENT", "development")
DEBUG = ENV == "development"

# Allowed origins for CORS (configure for production)
ALLOWED_ORIGINS = os.getenv("ALLOWED_ORIGINS", "*").split(",")

app = FastAPI(
    title="News AI Chatbot API",
    description="AI-powered chatbot for news articles using LangGraph and OpenAI GPT-5",
    version="1.0.0",
    debug=DEBUG
)

# CORS configuration
app.add_middleware(
    CORSMiddleware,
    allow_origins=ALLOWED_ORIGINS,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# LangGraph State Definition
class ChatbotState(TypedDict):
    title: str
    summary: str
    source_url: str
    messages: Annotated[list[BaseMessage], operator.add]

# Initialize LLM and Memory
# Using MemorySaver - history will persist as long as server is running
# DO NOT restart server while testing chat history!
memory = MemorySaver()

# OpenAI configuration
OPENAI_API_KEY = os.getenv("OPENAI_API_KEY")
if not OPENAI_API_KEY:
    logger.error("OPENAI_API_KEY not found in environment variables")
    raise ValueError("OPENAI_API_KEY is required")

logger.info("Initializing OpenAI GPT-5 model...")
llm = ChatOpenAI(
    model="gpt-5",
    temperature=0,
    api_key=OPENAI_API_KEY,
    output_version="responses/v1"  # Required for web_search_preview tool
)
tool = {"type": "web_search_preview"}
llm_with_tools = llm.bind_tools([tool])
logger.info("AI model initialized successfully with web search")

# Request Models
class ChatRequest(BaseModel):
    title: str
    summary: str
    source_url: str
    question: str
    thread_id: str = "default"

class ChatResponse(BaseModel):
    answer: str
    thread_id: str

class HistoryRequest(BaseModel):
    thread_id: str

class MessageHistory(BaseModel):
    role: str  # 'user' or 'ai'
    source_url: str

class HistoryResponse(BaseModel):
    messages: list[MessageHistory]
    thread_id: str

# LangGraph Node
def chatbot_node(state: ChatbotState) -> ChatbotState:
    system_message = SystemMessage(
        content=f"""You are a helpful AI assistant for a news app. Your job is to answer user
questions about a specific news article they are reading.

ARTICLE INFORMATION:
Title: {state['title']}
Summary: {state['summary']}
Full Article URL: {state['source_url']}

INSTRUCTIONS:
1. FIRST AND MOST IMPORTANT: Use web search to fetch and read the COMPLETE article from the URL        
above. This is mandatory for every question.
2. After reading the full article, answer the user's question based on the complete article content    
 (not just the summary).
3. If the article content directly mentions the answer, cite it and reference the article.
4. If the article doesn't have enough information to answer the question, use additional web search    
to find current, accurate information related to the topic.
5. Keep your response concise and factual (2-3 sentences max).
6. If you used additional web search beyond the article, mention that you verified it with current     
information.
7. Only answer questions related to the article topic. If the question is completely unrelated to      
the article, politely say "This question is not related to the article" and do NOT perform any web     
search.
8. Always prioritize information from the original article URL first, then supplement with web
search if needed.

WORKFLOW:
Step 1: Fetch full article from {state['source_url']} using web search
Step 2: Read and understand the complete article
Step 3: Check if user's question is related to the article
Step 4: Answer based on article content, or use additional web search if needed
"""
  )

    # Get the last message (user question)
    user_messages = [msg for msg in state["messages"] if isinstance(msg, HumanMessage)]
    if user_messages:
        latest_question = user_messages[-1]
        response = llm_with_tools.invoke([system_message, latest_question])
        return {"messages": [response]}

    return {"messages": []}

# Build LangGraph
def build_graph():
    builder = StateGraph(ChatbotState)
    builder.add_node("chatbot_node", chatbot_node)
    builder.add_edge(START, "chatbot_node")
    builder.add_edge("chatbot_node", END)
    return builder.compile(checkpointer=memory)

graph = build_graph()

# API Endpoints
@app.get("/")
async def root():
    return {"message": "News AI Chatbot API is running", "version": "1.0"}

@app.post("/api/chat", response_model=ChatResponse)
async def chat(request: ChatRequest):
    try:
        logger.info(f"Processing chat request for thread_id: {request.thread_id}")

        # Prepare input data
        input_data = {
            "title": request.title,
            "summary": request.summary,
            "source_url": request.source_url,
            "messages": [HumanMessage(content=request.question)]
        }

        # Configuration with thread ID for conversation memory
        config = {"configurable": {"thread_id": request.thread_id}}

        # Run the graph
        result = graph.invoke(input_data, config=config)

        # Extract the response
        if result and "messages" in result:
            messages = result["messages"]
            # Get the last AI message
            ai_messages = [msg for msg in messages if hasattr(msg, 'content')]
            if ai_messages:
                # Handle both string and list content formats from GPT-5
                content = ai_messages[-1].content
                if isinstance(content, list):
                    # GPT-5 returns list of content blocks
                    answer = "".join([block.get('text', '') if isinstance(block, dict) else str(block) for block in content])
                else:
                    answer = str(content)

                logger.info(f"Successfully generated response for thread_id: {request.thread_id}")
                return ChatResponse(answer=answer, thread_id=request.thread_id)

        logger.error("No response generated from AI model")
        raise HTTPException(status_code=500, detail="No response generated")

    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error processing chat request: {str(e)}", exc_info=True)
        raise HTTPException(status_code=500, detail=f"Error processing request: {str(e)}")

@app.post("/api/chat/history", response_model=HistoryResponse)
async def get_chat_history(request: HistoryRequest):
    """
    Retrieve chat history for a specific thread (user + article)
    """
    try:
        # Get state from memory using thread_id
        config = {"configurable": {"thread_id": request.thread_id}}

        # Try to get existing state
        state = memory.get(config)

        messages_list = []

        if state and 'channel_values' in state:
            # Extract messages from state
            channel_values = state['channel_values']
            if 'messages' in channel_values:
                for msg in channel_values['messages']:
                    if isinstance(msg, HumanMessage):
                        messages_list.append(MessageHistory(
                            role="user",
                            content=str(msg.content)
                        ))
                    elif hasattr(msg, 'content'):
                        # AI message
                        content = msg.content
                        if isinstance(content, list):
                            text = "".join([block.get('text', '') if isinstance(block, dict) else str(block) for block in content])
                        else:
                            text = str(content)
                        messages_list.append(MessageHistory(
                            role="ai",
                            content=text
                        ))

        return HistoryResponse(
            messages=messages_list,
            thread_id=request.thread_id
        )

    except Exception as e:
        # If no history exists, return empty
        return HistoryResponse(
            messages=[],
            thread_id=request.thread_id
        )

@app.get("/health")
async def health_check():
    """Health check endpoint for load balancers and monitoring"""
    return {
        "status": "healthy",
        "service": "ai-chatbot",
        "version": "1.0.0",
        "environment": ENV
    }

# Startup and shutdown events
@app.on_event("startup")
async def startup_event():
    logger.info("AI Chatbot API is starting up...")
    logger.info(f"Environment: {ENV}")
    logger.info(f"Debug mode: {DEBUG}")

@app.on_event("shutdown")
async def shutdown_event():
    logger.info("AI Chatbot API is shutting down...")

if __name__ == "__main__":
    import uvicorn

    # Get port from environment variable or use default
    PORT = int(os.getenv("PORT", 8000))
    HOST = os.getenv("HOST", "0.0.0.0")

    logger.info(f"Starting server on {HOST}:{PORT}")

    uvicorn.run(
        app,
        host=HOST,
        port=PORT,
        log_level="info" if DEBUG else "warning"
    )
