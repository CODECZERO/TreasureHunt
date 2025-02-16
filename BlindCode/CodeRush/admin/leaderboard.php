<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filter Teams by Level</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 2em;
            background: #f4f4f4;
        }
        .container {
            max-width: 500px;
            margin: auto;
            padding: 2em;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        input, button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            font-size: 18px;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background: #0056b3;
        }
        .error {
            color: #d9534f;
            font-weight: bold;
            margin-top: 10px;
        }
        .success {
            color: #28a745;
            font-weight: bold;
            margin-top: 10px;
        }
        .loader {
            display: none;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
            margin: 10px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .team-list {
            text-align: left;
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: #fff;
        }
    </style>
    <script>
        async function filterTeams(event) {
            event.preventDefault(); // Prevent form refresh

            const level = document.getElementById("level").value.trim();
            const limit = document.getElementById("limit").value.trim();
            const resultDiv = document.getElementById("result");
            const loader = document.getElementById("loader");

            resultDiv.innerHTML = ""; // Clear previous results

            if (!level) {
                resultDiv.innerHTML = "<p class='error'>⚠️ Please enter a valid level.</p>";
                return;
            }

            loader.style.display = "block"; // Show loading animation

            try {
                const response = await fetch(`http://localhost:4008/teams/filter/${encodeURIComponent(level)}?limit=${limit}`, {
                    method: "GET",
                    headers: { "Content-Type": "application/json" }
                });

                const data = await response.json();
                loader.style.display = "none"; // Hide loader

                if (response.ok && data.data?.data?.length) {
                    let teamsHtml = `<h3>${data.message}</h3><ul>`;
                    
                    data.data.data.forEach(team => {
                        teamsHtml += `
                            <li>
                                <strong>Team:</strong> ${team.TeamName} <br>
                                <strong>Level:</strong> ${team.level.join(", ")} <br>
                                <strong>Secret Codes:</strong> ${team.Scode.join(", ")} <br>
                                <small>Created: ${new Date(team.createdAt).toLocaleString()}</small>
                            </li>
                            <hr>`;
                    });
                    teamsHtml += "</ul>";

                    resultDiv.innerHTML = `<div class='team-list'>${teamsHtml}</div>`;
                } else {
                    resultDiv.innerHTML = `<p class='error'>❌ ${data.data.message || "No teams found."}</p>`;
                }
            } catch (error) {
                loader.style.display = "none";
                resultDiv.innerHTML = "<p class='error'>⚠️ Error connecting to the server.</p>";
            }
        }
    </script>
</head>
<body>
    <h1>🔍 Filter Teams by Level</h1>
    <div class="container">
        <div id="result"></div>
        <div id="loader" class="loader"></div>

        <form onsubmit="filterTeams(event)">
            <label for="level">🏆 Enter Level:</label>
            <input type="text" id="level" name="level" required>

            <label for="limit">🔢 Enter Limit:</label>
            <input type="number" id="limit" name="limit" value="10" required>

            <button type="submit">Filter Teams</button>
        </form>
    </div>
</body>
</html>
