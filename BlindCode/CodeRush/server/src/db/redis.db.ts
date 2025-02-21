import { createClient, RedisClientType } from "redis";
import { ApiError } from "../util/ApiError.js";
import { ApiResponse } from "../util/ApiResponse.js";
import { config } from "dotenv";
config();

let client: RedisClientType;

const keys = {
    key1: process.env.KEY1,
    key2: process.env.KEY2,
    key3: process.env.KEY3,
    key4: process.env.KEY4,
    key5: process.env.KEY5,
}

const ReallocatKey = async (key: string) => {
    try {
        const addKey = await client.lPush("AIModelKey", key);
        if (!addKey) return new ApiError(400, "No key is provied");
        return new ApiResponse(200, "Api key added back");
    } catch (error) {
        return error;
    }
}

const addKeyTORedis = async () => {
    try {
        for (const key of Object.values(keys)) {
    if (key) await ReallocatKey(key)//using this function key will be added back or added to redis server for futhere use.
}
    } catch (error) {
    throw error;
}
}



const connectRedis = async () => {
    try {
        client = await createClient({ url: process.env.REDISURL });
        client.on('error', (err) => { console.log('reids error', err) });
        await client.connect();
        console.log("Redis connected");
        await addKeyTORedis();
    } catch (error) {
        throw error;
    }
}

const AllocatKey = async () => {
    try {
        let getKey = await client.blPop("AIModelKey", 5);
        if (!getKey) {
            await new Promise(resolve => setTimeout(resolve, 10));
            return;
        }
        return getKey.element;
    } catch (error) {
        return error;
    }
}

const isKeyAvaiable = async () => {
    try {
        const isKey = client.lRange("AIModelKey", 0, -1);
        if (!isKey) return false;
        return true;
    } catch (error) {
        return error;
    }
}





export {
    connectRedis,
    ReallocatKey,
    AllocatKey,
    isKeyAvaiable
}