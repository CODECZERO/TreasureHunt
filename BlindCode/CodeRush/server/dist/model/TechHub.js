import mongoose from "mongoose";
const TechHubSchema = new mongoose.Schema({
    TeamName: {
        type: String,
        unique: true,
        require: true,
    },
    level: {
        type: [String],
        unique: false,
        index: true,
    }
}, { timestamps: true });
export const TechHub = mongoose.model("techhub", TechHubSchema);
