import { parentPort } from "worker_threads";
import { AllocatKey } from "../db/redis.db.js";

const allocateKey = async () => {
    try {
        const key = await AllocatKey();
        console.dir(key);
        parentPort!.postMessage(key);
    } catch (error) {
        parentPort!.postMessage(null);
    }
};

if (parentPort) {
    parentPort.on("message", async (msg) => {
        // Check that the message is the expected command
        if (msg === "allocateKey") {
            await allocateKey();
        }
    });
}