from langchain_openai import ChatOpenAI;
from langgraph.graph import StateGraph, START, END;
from langchain_core.messages import HumanMessage, SystemMessage;
from typing import Annotated, TypedDict;
from langchain_core.messages import BaseMessage;
import operator;
from langgraph.checkpoint.memory import MemorySaver;
import os;
from dotenv import load_dotenv;
load_dotenv();

class chatbot(TypedDict):
    title: str
    summary: str
    contents: str
    messages: Annotated[list[BaseMessage], operator.add]


memory = MemorySaver();
llm = ChatOpenAI(model="gpt-5", temperature=0, api_key = os.getenv("OPENAI_API_KEY"), output_version="responses/v1");
tool = {"type": "web_search_preview"};
llm_with_tools = llm.bind_tools([tool]);
input_data = {"title": "India Launches Chandrayaan-4 Moon Mission Successfully", "summary": "ISRO successfully launched Chandrayaan-4 mission on October 5, 2025, aiming to bring back lunar samples from the South Pole region. The mission marks India's most ambitious space project yet.", "contents": "The Indian Space Research Organisation (ISRO) achieved a major milestone today by successfully launching the Chandrayaan-4 mission from Satish Dhawan Space Centre in Sriharikota. The spacecraft lifted off at 2:35 PM IST aboard the LVM3-M5 rocket. This mission is designed to collect and return lunar soil samples from the Moon's South Pole region, an area believed to contain water ice. Dr. S. Somnath, ISRO Chairman, stated that the mission will take approximately 45 days to reach the Moon and execute a soft landing. The lander will collect up to 2 kg of lunar samples before returning to Earth. Prime Minister Narendra Modi congratulated the ISRO team and called it a proud moment for India. The mission cost is estimated at ₹2,500 crore and involves collaboration with NASA for deep space communication. Scientists expect the samples to provide crucial insights into the Moon's geological history and potential resources for future human settlements."};
def node1(state: chatbot) -> chatbot:
    system_message = SystemMessage(content=f"You are a helpful AI assistant for a news app. Your job is to answer user questions about a specific news article they are reading. INSTRUCTIONS: 1. First, try to answer based on the article content. 2. If the article mentions the answer, cite it directly. 3. If the article doesn't have enough information, use web search to find the current, accurate answer. 4. Keep your response concise and factual (2-3 sentences max). 5. If using web search, mention that you verified it with current information. 6. Only answer questions related to the article, if the question is not related to the article, say that this question is not related to the article and don't use web search. Also don't suggest them anything if the question is not related to the article.");
    human_message = HumanMessage(content="Best Tech Products in 2025?");
    response = llm_with_tools.invoke([system_message] + [human_message]);
    return({"messages": response.content})
builder = StateGraph(chatbot);
builder.add_node("node1", node1);
builder.add_edge(START,"node1");
builder.add_edge("node1", END);
graph = builder.compile(checkpointer=memory);

config = {"configurable": {"thread_id": "123"}};
for chunks in graph.stream(input_data, stream_mode="updates", config=config):
    if "node1" in chunks and "messages" in chunks["node1"]:
        print(chunks["node1"]["messages"])
graph.get_graph().draw_mermaid_png();