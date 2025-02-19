import { config } from "dotenv";
config();
import app from "./app.js";
import { connectDb } from "./db/mongoConfig.js";
import { connectRedis } from "./db/redis.db.js";
import { runWebSocket } from "./Websocket/Websocket.main.js";
import rabbitmq from "./queues/rabbitMq.js";


const connectAll=async()=>{
    await rabbitmq.connectRabbitMq("StartRoom");
    console.log("queue is connected");
    await connectDb();
    console.log("mongodb connected");
    await connectRedis();
    //@ts-ignore.
    await runWebSocket();
}

try {

    connectAll().then(() => {
        app.listen(4008, () => {
            console.log("server running on 4008");
        });
    })
} catch (error) {
    console.log(error);
}

