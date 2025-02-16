import mongoose from "mongoose";
// Define the schema
const SCodeSchema = new mongoose.Schema({
    secretCodes: {
        type: [String],
        required: true,
        unique: true,
        index: true,
    },
    levelName: {
        type: String,
        required: true,
    },
});
export const Scode = mongoose.model("Scode", SCodeSchema);
