import { ApiError } from "../util/ApiError.js";
import { GoogleGenerativeAI } from "@google/generative-ai";
import { AllocatKey, ReallocatKey } from "../db/redis.db.js";
class AiCheck {

    private answer = "";
    private question = "";

    constructor(answer: string, question: string) {
        this.answer = answer;
        this.question = question;
    }

    public CheckAnswer = async (AI_KEY: string) => {
        try {
            if (!this.question || !this.answer) throw new ApiError(400, "Invalid data");
            const genAI = await new GoogleGenerativeAI(AI_KEY);
            if (!genAI) throw new ApiError(500, "AI Api key is not provied");
            const model = await genAI.getGenerativeModel({ model: "gemini-1.5-flash" });

            const prompt = `
                Does this code correctly solve the problem: "${this.question}"?
                Code:
                ${this.answer}
        
                Important: 
                1. Do not run or execute the code in any way.
                2. Ensure the code does not contain harmful or dangerous behavior such as:
                - Infinite loops or recursion that may lead to crashes.
                - System resource overload (e.g., excessive memory or CPU usage).
                - Exposure of environment variables, passwords, or sensitive data.
                - Code attempting to modify system files or access unauthorized resources.
                - External network calls or malicious redirects.
                3. If the code uses external libraries, ensure they are safe and commonly used.
                4. Ensure the code does not have any attempts at:
                - **Privilege escalation**, file system access, or shell commands.
                - **Accessing or altering the environment**, files, or system settings.
                5. If the code contains any of the above issues or does not solve the problem correctly, return **False**.
                6. If the code solves the problem correctly and does not contain harmful behavior, return **True**.
                7. Reply **ONLY** with **True** or **False**, and do not explain your answer. 
                8. Always ensure that the code is **safe for execution** and does not contain any suspicious behavior.
                9. Do not allow any form of **self-modifying code** or code that can potentially alter its execution flow dynamically.
                
                Please ensure that **no execution or dangerous actions are attempted**.
                `;

            const result = await model.generateContent(prompt)
            if (!result) throw new ApiError(500, "No result");
            return result.response.candidates;
        } catch (error) {
            return error;
        }
    };


    public Judge0 = async (code: string, stdout: string, IsSafe: string) => {
        try {
            if (IsSafe == "False" || IsSafe == "false" || IsSafe == "FALSE") return new ApiError(505, "Code is not safe to run");



        } catch (error) {
            return error;
        }
    }

    public ModelHandler = async () => {
        try {

            let key: string | null = null;

            // Wait until an API key is available
            while (!key) {
                key = await AllocatKey() as string;
                if (!key) {
                    console.log("No API key available. Retrying in 5s...");
                    await new Promise(resolve => setTimeout(resolve, 5000)); // Wait for 2 seconds before retrying
                }
            }

            const ans = await this.CheckAnswer(key as string);
            await ReallocatKey(key as string);
            return ans;
        } catch (error) {
            throw error;
        }
    }
}

export {
    AiCheck
}

