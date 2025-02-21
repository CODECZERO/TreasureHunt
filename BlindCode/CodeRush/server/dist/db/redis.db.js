var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
import { createClient } from "redis";
import { ApiError } from "../util/ApiError.js";
import { ApiResponse } from "../util/ApiResponse.js";
import { config } from "dotenv";
config();
let client;
const keys = {
    key1: process.env.KEY1,
    key2: process.env.KEY2,
    key3: process.env.KEY3,
    key4: process.env.KEY4,
    key5: process.env.KEY5,
};
const ReallocatKey = (key) => __awaiter(void 0, void 0, void 0, function* () {
    try {
        const addKey = yield client.lPush("AIModelKey", key);
        if (!addKey)
            return new ApiError(400, "No key is provied");
        return new ApiResponse(200, "Api key added back");
    }
    catch (error) {
        return error;
    }
});
const addKeyTORedis = () => __awaiter(void 0, void 0, void 0, function* () {
    try {
        for (const key of Object.values(keys)) {
            if (key)
                yield ReallocatKey(key); //using this function key will be added back or added to redis server for futhere use.
        }
    }
    catch (error) {
        throw error;
    }
});
const connectRedis = () => __awaiter(void 0, void 0, void 0, function* () {
    try {
        client = yield createClient({ url: process.env.REDISURL });
        client.on('error', (err) => { console.log('reids error', err); });
        yield client.connect();
        console.log("Redis connected");
        yield addKeyTORedis();
    }
    catch (error) {
        throw error;
    }
});
const AllocatKey = () => __awaiter(void 0, void 0, void 0, function* () {
    try {
        let getKey = yield client.blPop("AIModelKey", 5);
        if (!getKey) {
            yield new Promise(resolve => setTimeout(resolve, 10));
            return;
        }
        return getKey.element;
    }
    catch (error) {
        return error;
    }
});
const isKeyAvaiable = () => __awaiter(void 0, void 0, void 0, function* () {
    try {
        const isKey = client.lRange("AIModelKey", 0, -1);
        if (!isKey)
            return false;
        return true;
    }
    catch (error) {
        return error;
    }
});
export { connectRedis, ReallocatKey, AllocatKey, isKeyAvaiable };
