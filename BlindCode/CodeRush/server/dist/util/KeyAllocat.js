var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
import { parentPort } from "worker_threads";
import { client } from "../db/redis.db.js";
const AllocatKey = () => __awaiter(void 0, void 0, void 0, function* () {
    try {
        while (true) {
            const getKey = yield client.blPop("AIModelKey", 5);
            if (getKey) {
                parentPort === null || parentPort === void 0 ? void 0 : parentPort.postMessage(getKey.element);
            }
            if (!getKey) {
                yield new Promise(resolve => setTimeout(resolve, 5000));
            }
        }
    }
    catch (error) {
        parentPort === null || parentPort === void 0 ? void 0 : parentPort.postMessage(error);
    }
});
AllocatKey();
