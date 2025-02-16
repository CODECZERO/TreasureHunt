var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
import { Scode } from "../model/SecreatCode.js";
import { Question } from "../model/Question.js";
import { Teams } from "../model/Teams.js";
const recentQuestions = new Map(); // Store question & timestamp
const addLevelAndSecretKey = (level, secretCode) => __awaiter(void 0, void 0, void 0, function* () {
    try {
        if (!level || !secretCode) {
            throw new Error("Level and secretCode are required");
        }
        // Check if a document with this level already exists
        const existingLevel = yield Scode.findOne({ levelName: level }).lean().select("_id");
        if (existingLevel) {
            // If the level exists, push the new secret code into the array
            const updated = yield Scode.updateOne({ levelName: level }, { $addToSet: { secretCodes: secretCode } } // Prevents duplicate codes
            );
            return updated.modifiedCount > 0
                ? { message: "Secret code added successfully", status: 200 }
                : { message: "Secret code already exists", status: 409 };
        }
        else {
            // If the level doesn't exist, create a new entry
            const newEntry = new Scode({
                levelName: level,
                secretCodes: [secretCode], // Store secretCode as an array
            });
            yield newEntry.save();
            return { message: "New level and secret code added successfully", status: 201 };
        }
    }
    catch (error) {
        throw new Error(`Error adding secret code: ${error}`);
    }
});
const addQuestionf = (question, outputs, level) => __awaiter(void 0, void 0, void 0, function* () {
    try {
        if (!question || !outputs || !Array.isArray(outputs) || outputs.length === 0 || !level) {
            return { status: 400, message: "Invalid input. Question, outputs, and level are required." };
        }
        // Check if the question already exists for the given level
        const existingQuestion = yield Question.findOne({ Question: question, level: level }).lean().select("_id");
        if (existingQuestion) {
            return { status: 409, message: "This question already exists for this level." };
        }
        // Create and save the new question
        const newQuestion = new Question({
            Question: question,
            output: outputs,
            level: level
        });
        const savedQuestion = yield newQuestion.save();
        return { status: 201, data: savedQuestion, message: "Question added successfully." };
    }
    catch (error) {
        return { status: 500, message: error.message || "Internal Server Error" };
    }
});
const getRandomQuestionByLevel = (level) => __awaiter(void 0, void 0, void 0, function* () {
    try {
        if (!level) {
            return { status: 400, message: "Level is required." };
        }
        const fiveMinutesAgo = Date.now() - 5 * 60 * 1000; // Calculate time limit
        // Remove expired questions from cache
        for (const [question, timestamp] of recentQuestions.entries()) {
            if (timestamp < fiveMinutesAgo) {
                recentQuestions.delete(question);
            }
        }
        // Fetch questions from DB that match the given level
        const allQuestions = yield Question.find({ level })
            .lean()
            .select("Question output")
            .exec();
        if (!allQuestions.length) {
            return { status: 404, message: "No questions available for this level." };
        }
        // Filter out recently used questions
        const availableQuestions = allQuestions.filter(q => !recentQuestions.has(q.Question));
        // If no new questions available, allow reuse after 5 minutes
        const selectedQuestion = availableQuestions.length
            ? availableQuestions[Math.floor(Math.random() * availableQuestions.length)]
            : allQuestions[Math.floor(Math.random() * allQuestions.length)];
        // Store the selected question in cache with the current timestamp
        recentQuestions.set(selectedQuestion.Question, Date.now());
        return { status: 200, data: selectedQuestion, message: "Random question retrieved successfully." };
    }
    catch (error) {
        return { status: 500, message: error instanceof Error ? error.message : "Internal Server Error" };
    }
});
const addSecretCodeToTeam = (teamName, newCode) => __awaiter(void 0, void 0, void 0, function* () {
    try {
        if (!teamName || !newCode)
            throw new Error("Team name and secret code are required.");
        const validCode = yield Scode.findOne({ secretCodes: newCode }).lean().select("levelName");
        if (!validCode)
            return { status: 400, message: "Invalid secret code." };
        const existingTeam = yield Teams.findOne({ Scode: newCode }).lean().select("_id");
        if (existingTeam)
            return { status: 400, message: "Secret code already in use by another team." };
        const updatedTeam = yield Teams.findOneAndUpdate({ TeamName: teamName }, {
            $addToSet: { Scode: newCode },
            $set: { level: validCode.levelName }
        }, { new: true, upsert: true, runValidators: true }).lean();
        return updatedTeam
            ? { status: 200, data: updatedTeam, message: "Secret code added successfully." }
            : { status: 500, message: "Failed to add secret code." };
    }
    catch (error) {
        return { status: 500, message: error || "Internal Server Error" };
    }
});
const getTeamsByLevel = (level, limit) => __awaiter(void 0, void 0, void 0, function* () {
    try {
        if (!level)
            throw new Error("Level not provided");
        const teams = yield Teams.find({ level })
            .lean()
            .select("TeamName Scode level createdAt")
            .sort({ createdAt: 1 })
            .limit(limit);
        return teams.length
            ? { status: 200, data: teams }
            : { status: 404, message: "No teams found for this level" };
    }
    catch (error) {
        return { status: 500, message: error instanceof Error ? error.message : "Internal Server Error" };
    }
});
export { addLevelAndSecretKey, getTeamsByLevel, addSecretCodeToTeam, getRandomQuestionByLevel, addQuestionf };
