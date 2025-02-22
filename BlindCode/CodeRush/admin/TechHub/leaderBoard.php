<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
        }
        h1 {
            color: #333;
        }
        table {
            width: 60%;
            margin: 20px auto;
            border-collapse: collapse;
            background: white;
            box-shadow: 0px 4px 8px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #007BFF;
            color: white;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
    </style>
</head>
<body>

<h1>🏆 Leaderboard 🏆</h1>

<label for="levelInput">Enter Level:</label>
<input type="text" id="levelInput" placeholder="Enter level">
<button onclick="fetchLeaderboard()">Get Leaderboard</button>

<table id="leaderboardTable">
    <thead>
        <tr>
            <th>Rank</th>
            <th>Team Name</th>
            <th>Level</th>
            <th>Joined At</th>
        </tr>
    </thead>
    <tbody id="leaderboardBody">
        <tr>
            <td colspan="4">Enter a level and click "Get Leaderboard"</td>
        </tr>
    </tbody>
</table>
<script>
    const apiUrl = "http://localhost:4008/techHub/leaderboard"; // Replace with actual API URL

    async function fetchLeaderboard() {
        const level = document.getElementById("levelInput").value.trim();
        const limit = 10; // Default limit

        if (!level) {
            alert("Please enter a level!");
            return;
        }

        try {
            const response = await fetch(apiUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ level, limit })
            });

            const data = await response.json();
            console.log("API Response:", data); // Debugging

            if (!response.ok || !data.data || !Array.isArray(data.data)) {
                throw new Error(data.message || "Invalid response format");
            }

            updateLeaderboard(data.data); // Ensure data.data is an array

        } catch (error) {
            console.error("Error:", error);
            document.getElementById("leaderboardBody").innerHTML = `
                <tr><td colspan="4">${error.message}</td></tr>`;
        }
    }

    function updateLeaderboard(leaderboard) {
        const leaderboardBody = document.getElementById("leaderboardBody");
        leaderboardBody.innerHTML = "";

        if (!Array.isArray(leaderboard) || leaderboard.length === 0) {
            leaderboardBody.innerHTML = `<tr><td colspan="4">No leaderboard data available.</td></tr>`;
            return;
        }

        leaderboard.forEach((team, index) => {
            const row = `<tr>
                <td>${index + 1}</td>
                <td>${team.teamName}</td>
                <td>${Array.isArray(team.level) ? team.level.join(", ") : team.level}</td>
                <td>${new Date(team.joinedAt).toLocaleString()}</td>
            </tr>`;
            leaderboardBody.innerHTML += row;
        });
    }
</script>


</body>
</html>
