var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
import { config } from "dotenv";
config();
import app from "./app.js";
import { connectDb } from "./db/mongoConfig.js";
import { connectRedis } from "./db/redis.db.js";
import { runWebSocket } from "./Websocket/Websocket.main.js";
import rabbitmq from "./queues/rabbitMq.js";
const connectAll = () => __awaiter(void 0, void 0, void 0, function* () {
    yield rabbitmq.connectRabbitMq("StartRoom");
    console.log("queue is connected");
    yield connectDb();
    console.log("mongodb connected");
    yield connectRedis();
    //@ts-ignore.
    yield runWebSocket();
});
try {
    connectAll().then(() => {
        app.listen(4008, () => {
            console.log("server running on 4008");
        });
    });
}
catch (error) {
    console.log(error);
}
