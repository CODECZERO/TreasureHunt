import { ApiError } from "../util/ApiError.js";
import { ApiResponse } from "../util/ApiResponse.js";
import { Request, Response, NextFunction } from "express";
import { getTeamsByLevel, addLevelAndSecretKey, addSecretCodeToTeam, addQuestionf, getRandomQuestionByLevel } from "../db/Query.Nosql.js";

const FilterTeams = async (req: Request, res: Response) => {
    try {
        const { level } = req.params; // Extract level from URL params
        const limit = parseInt(req.query.limit as string) || 10; // Extract limit from query string

        if (!level) {
            return res.status(400).json(new ApiError(400, "Invalid Level"));
        }

        const FilterData = await getTeamsByLevel(level, limit);

        if (!FilterData) {
            return res.status(404).json(new ApiError(404, "No teams found for this level"));
        }

        return res.status(200).json(new ApiResponse(200, FilterData, "Successful"));
    } catch (error) {
        return res.status(500).json(new ApiError(500, error));
    }
};

const addSecreaKey = async (req: Request, res: Response) => {
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
            const result = await addLevelAndSecretKey(level, secretCode);
            console.log("Database Insert Result:", result); // Debugging

            if (!result) {
                return res.status(500).json(new ApiError(500, "Something went wrong while entering the code"));
            }

            return res.status(200).json(new ApiResponse(200, result, "Successful"));
        } catch (dbError) {
            console.error("Error adding secret key:", dbError);
            return res.status(500).json(new ApiError(500, "Database Error"));
        }
    } catch (error) {
        console.error("Unexpected Error:", error);
        return res.status(500).json(new ApiError(500, error || "Internal Server Error"));
    }
};

const TeamReg = async (req: Request, res: Response) => {
    try {
        const { TeamName, Scode } = req.body;

        // Validate input
        if (!TeamName || !Scode) {
            return res.status(400).json(new ApiError(400, "Invalid data. TeamName and Scode are required."));
        }

        // Call the function to add the secret code to the team
        const updated = await addSecretCodeToTeam(TeamName, Scode);

        // Ensure updated is a valid response object
        if (!updated || typeof updated !== "object" || !("status" in updated)) {
            return res.status(500).json(new ApiError(500, "Unexpected error occurred."));
        }

        // ✅ Fix: Pass only `updated.message` as the message in `ApiResponse`
        return res.status(updated.status).json(new ApiResponse(updated.status, updated.data || {}, updated.message));
    } catch (error) {
        return res.status(500).json(new ApiError(500, error instanceof Error ? error.message : "Internal Server Error"));
    }
};

const addQuestion = async (req: Request, res: Response) => {
    try {
        const { question, output,level } = req.body;
        if (!question || !output || !Array.isArray(output) || output.length === 0||!level) {
            return res.status(400).json({ status: 400, message: "Invalid input. Question and outputs are required." });
        }

        const result = await addQuestionf(question, output,level);


        if (result.status === 409) {
            return res.status(409).json(result); // Question already exists
        }

        return res.status(200).json(new ApiResponse(200, result, "Successfully added question."));
    } catch (error) {
        return res.status(500).json({ status: 500, message: error || "Internal Server Error" });
    }
};
const getRandomQ = async (req: Request, res: Response) => {
    try {
        const { level } = req.body;
        console.log(level);

        if (!level) {
            return res.status(400).json(new ApiError(400, "Missing or incorrect fields"));
        }

        const data = await getRandomQuestionByLevel(level);

        // ✅ Proper error handling based on status
        if (data.status !== 200) {
            return res.status(data.status).json(new ApiError(data.status, data.message));
        }

        return res.status(200).json(new ApiResponse(200, data.data, "Successful"));
    } catch (error) {
        return res.status(500).json(new ApiError(500, error instanceof Error ? error.message : "Internal Server Error"));
    }
};


export {
    FilterTeams,
    addSecreaKey,
    TeamReg,
    addQuestion,
    getRandomQ
}