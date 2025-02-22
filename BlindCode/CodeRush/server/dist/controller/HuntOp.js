var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
import { ApiError } from "../util/ApiError.js";
import { ApiResponse } from "../util/ApiResponse.js";
import { getTeamsByLevel, addLevelAndSecretKey, addSecretCodeToTeam, addQuestionf, getRandomQuestionByLevel, registerTeam, findTeamsByLevel, addLevelToTeam } from "../db/Query.Nosql.js";
import AsyncHandler from "../util/AsyncHandler.js";
const FilterTeams = (req, res) => __awaiter(void 0, void 0, void 0, function* () {
    try {
        const { level } = req.params; // Extract level from URL params
        const limit = parseInt(req.query.limit) || 10; // Extract limit from query string
        if (!level) {
            return res.status(400).json(new ApiError(400, "Invalid Level"));
        }
        const FilterData = yield getTeamsByLevel(level, limit);
        if (!FilterData) {
            return res.status(404).json(new ApiError(404, "No teams found for this level"));
        }
        return res.status(200).json(new ApiResponse(200, FilterData, "Successful"));
    }
    catch (error) {
        return res.status(500).json(new ApiError(500, error));
    }
});
const addSecreaKey = (req, res) => __awaiter(void 0, void 0, void 0, function* () {
    console.log("Entered addSecreaKey function");
    try {
        console.log("Received Data:", req.body); // Debugging
        const { password, name, level, secretCode } = req.body;
        // Validate request body
        if (!password || !name || password !== "Ankit879#$" || name !== "Ankit879" || !level || !secretCode) {
            return res.status(400).json(new ApiError(400, "Missing or incorrect fields"));
        }
        console.log("Validation passed");
        // Try to add the secret key
        try {
            const result = yield addLevelAndSecretKey(level, secretCode);
            console.log("Database Insert Result:", result); // Debugging
            if (!result) {
                return res.status(500).json(new ApiError(500, "Something went wrong while entering the code"));
            }
            return res.status(200).json(new ApiResponse(200, result, "Successful"));
        }
        catch (dbError) {
            console.error("Error adding secret key:", dbError);
            return res.status(500).json(new ApiError(500, "Database Error"));
        }
    }
    catch (error) {
        console.error("Unexpected Error:", error);
        return res.status(500).json(new ApiError(500, error || "Internal Server Error"));
    }
});
const TeamReg = (req, res) => __awaiter(void 0, void 0, void 0, function* () {
    try {
        const { TeamName, Scode } = req.body;
        // Validate input
        if (!TeamName || !Scode) {
            return res.status(400).json(new ApiError(400, "Invalid data. TeamName and Scode are required."));
        }
        // Call the function to add the secret code to the team
        const updated = yield addSecretCodeToTeam(TeamName, Scode);
        // Ensure updated is a valid response object
        if (!updated || typeof updated !== "object" || !("status" in updated)) {
            return res.status(500).json(new ApiError(500, "Unexpected error occurred."));
        }
        // ✅ Fix: Pass only `updated.message` as the message in `ApiResponse`
        return res.status(updated.status).json(new ApiResponse(updated.status, updated.data || {}, updated.message));
    }
    catch (error) {
        return res.status(500).json(new ApiError(500, error instanceof Error ? error.message : "Internal Server Error"));
    }
});
const addQuestion = (req, res) => __awaiter(void 0, void 0, void 0, function* () {
    try {
        const { question, output, level } = req.body;
        if (!question || !output || !Array.isArray(output) || output.length === 0 || !level) {
            return res.status(400).json({ status: 400, message: "Invalid input. Question and outputs are required." });
        }
        const result = yield addQuestionf(question, output, level);
        if (result.status === 409) {
            return res.status(409).json(result); // Question already exists
        }
        return res.status(200).json(new ApiResponse(200, result, "Successfully added question."));
    }
    catch (error) {
        return res.status(500).json({ status: 500, message: error || "Internal Server Error" });
    }
});
const getRandomQ = (req, res) => __awaiter(void 0, void 0, void 0, function* () {
    try {
        const { level } = req.body;
        console.log(level);
        if (!level) {
            return res.status(400).json(new ApiError(400, "Missing or incorrect fields"));
        }
        const data = yield getRandomQuestionByLevel(level);
        // ✅ Proper error handling based on status
        if (data.status !== 200) {
            return res.status(data.status).json(new ApiError(data.status, data.message));
        }
        return res.status(200).json(new ApiResponse(200, data.data, "Successful"));
    }
    catch (error) {
        return res.status(500).json(new ApiError(500, error instanceof Error ? error.message : "Internal Server Error"));
    }
});
const addTechHubteam = AsyncHandler((req, res) => __awaiter(void 0, void 0, void 0, function* () {
    const { TeamName } = req.body;
    if (!TeamName)
        throw new ApiError(400, "Invalid Team Name");
    const TeamReg = yield registerTeam(TeamName);
    if (!TeamReg)
        throw new ApiError(500, `Something wnet wrong while creating team ${TeamReg}`);
    return res.status(200).json(new ApiResponse(200, TeamReg, "Successful"));
}));
const Thtl = AsyncHandler((req, res) => __awaiter(void 0, void 0, void 0, function* () {
    const { level, limit } = req.body;
    if (!level && !limit)
        throw new ApiError(400, "Invalid Team Name");
    const findlevel = yield findTeamsByLevel(level, limit);
    if (!findlevel)
        throw new ApiError(500, `Something wnet wrong while creating team ${findlevel}`);
    return res.status(200).json(new ApiResponse(200, findlevel, "Successful"));
}));
const Alts = AsyncHandler((req, res) => __awaiter(void 0, void 0, void 0, function* () {
    const { TeamName, level } = req.body;
    if (!level && !TeamName)
        throw new ApiError(400, "Invalid Team Name");
    const findlevel = yield addLevelToTeam(TeamName, level);
    if (!findlevel)
        throw new ApiError(500, `Something wnet wrong while creating team ${findlevel}`);
    return res.status(200).json(new ApiResponse(200, findlevel, "Successful"));
}));
export { FilterTeams, addSecreaKey, TeamReg, addQuestion, getRandomQ, addTechHubteam, Alts, Thtl };
