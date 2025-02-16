import { config } from "dotenv";
config();
import app from "./app.js";
import { connectDb } from "./db/mongoConfig.js";
try {
    connectDb().then(() => {
        app.listen(4008, () => {
            console.log("server running on 4008");
        });
    });
}
catch (error) {
    console.log(error);
}
