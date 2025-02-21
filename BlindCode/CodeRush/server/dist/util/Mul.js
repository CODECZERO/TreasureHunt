var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
import { Worker } from "worker_threads";
const startKeyAllocation = () => __awaiter(void 0, void 0, void 0, function* () {
    return new Promise((resolve, reject) => {
        const worker = new Worker("./TreasureHunt/BlindCode/CodeRush/server/dist/util/KeyAllocat.js");
        worker.on("message", (key) => {
            console.log("✅ API Key Allocated:", key);
            resolve(key); // Return the allocated key
        });
        worker.on("error", (error) => {
            console.error("Worker error:", error);
            reject(error);
        });
        worker.on("exit", (code) => {
            if (code !== 0) {
                reject(new Error(`Worker stopped with exit code ${code}`));
            }
        });
    });
});
export { startKeyAllocation };
