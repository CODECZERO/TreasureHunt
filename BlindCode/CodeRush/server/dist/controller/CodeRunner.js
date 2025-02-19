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
import { GoogleGenerativeAI } from "@google/generative-ai";
class AiCheck {
    constructor(answer, question) {
        this.answer = "";
        this.question = "";
        this.CheckAnswer = (AI_KEY) => __awaiter(this, void 0, void 0, function* () {
            try {
                if (!this.question || !this.answer)
                    throw new ApiError(400, "Invalid data");
                const genAI = yield new GoogleGenerativeAI(AI_KEY);
                if (!genAI)
                    throw new ApiError(500, "AI Api key is not provied");
                const model = yield genAI.getGenerativeModel({ model: "gemini-1.5-flash" });
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
                const result = yield model.generateContent(prompt);
                if (!result)
                    throw new ApiError(500, "No result");
                return result.response.candidates;
            }
            catch (error) {
                return error;
            }
        });
        this.Judge0 = (code, stdout, IsSafe) => __awaiter(this, void 0, void 0, function* () {
            try {
                if (IsSafe == "False" || IsSafe == "false" || IsSafe == "FALSE")
                    return new ApiError(505, "Code is not safe to run");
            }
            catch (error) {
                return error;
            }
        });
        this.answer = answer;
        this.question = question;
    }
}
export { AiCheck };
