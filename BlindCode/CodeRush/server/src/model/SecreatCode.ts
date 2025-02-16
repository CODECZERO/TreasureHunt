import mongoose, { Document } from "mongoose";

// Define an interface for the schema
interface IScode extends Document {
    secretCodes: string[];
    levelName: string;
}

// Define the schema
const SCodeSchema = new mongoose.Schema<IScode>({
    secretCodes: {
        type: [String],
        required: true,
        unique: true,
        index:true,
    },
    levelName: {
        type: String,
        required: true,
    },
});

export const Scode = mongoose.model<IScode>("Scode", SCodeSchema);
