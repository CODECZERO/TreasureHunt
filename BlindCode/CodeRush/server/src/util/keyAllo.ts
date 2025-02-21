import { parentPort } from "worker_threads";
import { AllocatKey } from "../db/redis.db.js";

const allocateKey = async () => {
    try {
        const key = await AllocatKey();
        parentPort.postMessage(key);
    } catch (error) {
        parentPort.postMessage(null);
    }
};


parentPort.on("message", () => {
    allocateKey();
});
