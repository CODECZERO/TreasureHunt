import express from "express";
import mainORuter from "./router/mainRoute.js";
import cors from "cors"

const app = express();

app.use(cors({
    origin: true,  // This allows all origins
    credentials: true
}));


app.use(express.json()); // ✅ This enables JSON body parsing
app.use(express.urlencoded({ extended: true })); // ✅ Parses URL-encoded data
app.use("/",mainORuter);
// Now define routes AFTER middleware

export default app