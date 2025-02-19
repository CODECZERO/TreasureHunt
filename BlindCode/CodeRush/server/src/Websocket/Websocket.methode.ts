import { ConsumeMessage } from "amqplib";
import WebSocket from "ws";
import { MessageData, CustomWebSocket, rooms, clients } from './Websocket.main.js';
import { ApiError } from "../util/ApiError.js";
import rabbitmq from "../queues/rabbitMq.js";
import { AiCheck } from "../controller/CodeRunner.js";
import { isKeyAvaiable } from "../db/redis.db.js";

const sendMessage = async (messageData: MessageData, ws: CustomWebSocket): Promise<void> => {
    try {
        const messageInfo = JSON.stringify(messageData);
        await rabbitmq.publishData(messageInfo, messageData.roomName);
    } catch (error) {
        console.error("Error while sending message:", error);
        throw new ApiError(500, "Error while sending message");
    }
};

//this function will handle AI model in parallel
const PmP = async (parsedMessage: MessageData, roomName: string) => {//parallel Ai model processing
    try {

        const AiCheckModel = new AiCheck(parsedMessage.answer, parsedMessage.question);
        const ans = await AiCheckModel.ModelHandler();//non-blocking execution 
        console.log(ans);
        if (rooms[roomName]) {
            for (const client of rooms[roomName]) {
                if (client.readyState === WebSocket.OPEN && client.userId === parsedMessage.userId) {
                    client.send(JSON.stringify({
                        MessageId: parsedMessage.MessageId,
                        roomName: parsedMessage.roomName,
                        userId: parsedMessage.userId,
                        question: parsedMessage.question,
                        answer: parsedMessage.answer,
                        aiResult: ans
                    }));
                }
            }
        }
    } catch (error) {
        console.log(error);
        throw new ApiError(500, "Error while running Ai Model");
    }
}


const broadcastMessage = async (message: ConsumeMessage, roomName: string): Promise<void> => {
    try {
        const messageContent = message.content.toString();
        const parsedMessage: MessageData = JSON.parse(messageContent);
        PmP(parsedMessage, roomName);//parallel Ai model processing -Pmp
    } catch (error) {
        console.error("Error while broadcasting message:", error);
        throw new ApiError(500, "Error while broadcasting message");
    }
};

const receiveMessage = async (ws: CustomWebSocket): Promise<void> => {
    try {
        if (!ws.roomName) {
            throw new ApiError(400, "Room name not set for WebSocket");
        }
        
        // const isKey = await isKeyAvaiable();
        // if (!isKey) {
        //     console.log("No AI key available. Waiting 10s before retrying...");
        //     await new Promise(resolve => setTimeout(resolve, 200)); // Wait for 2 seconds before retrying          
        //     return;
        // }

        await rabbitmq.subData(ws.roomName);
        await rabbitmq.channel.consume(rabbitmq.queue.queue, async (message: ConsumeMessage | null) => {
            if (message) {
                await broadcastMessage(message, ws.roomName!).catch(console.error);
            }
        });
    } catch (error) {
        console.error("Error while receiving message:", error);
        throw new ApiError(500, "Error while receiving message");
    }
};

const closeSocket = async (messageData: MessageData, ws: CustomWebSocket): Promise<void> => {
    try {
        if (messageData.roomName && rooms[messageData.roomName]) {
            rooms[messageData.roomName].delete(ws);
            if (rooms[messageData.roomName].size === 0) {
                delete rooms[messageData.roomName];
            }
        }
        ws.close();
    } catch (error) {
        console.error("Error while closing socket:", error);
        throw new ApiError(500, "Error while closing socket");
    }
};

export {
    sendMessage,
    receiveMessage,
    closeSocket,
};

