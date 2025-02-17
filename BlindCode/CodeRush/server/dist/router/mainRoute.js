import { Router } from "express";
import { FilterTeams, addSecreaKey, TeamReg, addQuestion, getRandomQ, CheckAnswer } from "../controller/HuntOp.js";
import AsyncHandler from "../util/AsyncHandler.js";
const router = Router();
// 📌 Route to filter teams by level
router.route("/teams/filter/:level").get(AsyncHandler(FilterTeams));
// 📌 Route to add a secret key (admin protected)
router.route("/admin/add-secret").post(AsyncHandler(addSecreaKey));
// 📌 Route to register a team and assign secret codes
router.route("/team/register").post(AsyncHandler(TeamReg));
// 📌 Route to add a new question
router.route("/question/add").post(AsyncHandler(addQuestion));
// 📌 Route to get a random question by level
router.route("/question/random").post(AsyncHandler(getRandomQ));
router.route("/question/Check").post(CheckAnswer);
export default router;
