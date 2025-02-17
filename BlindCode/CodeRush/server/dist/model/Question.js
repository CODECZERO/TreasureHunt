import mongoose from "mongoose";
const QuestionSchema = new mongoose.Schema({
    Question: {
        type: String,
        required: true,
        unique: true,
    },
    output: {
        type: [String],
        required: true,
    },
    level: {
        type: String,
        require: true,
        index: true,
    }
}, { timestamps: true });
export const Question = mongoose.model("question", QuestionSchema);
