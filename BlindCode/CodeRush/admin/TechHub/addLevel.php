<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Level to Team</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
        }
        h1 {
            color: #333;
        }
        form {
            background: white;
            padding: 20px;
            display: inline-block;
            box-shadow: 0px 4px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        input, button {
            padding: 10px;
            margin: 5px;
            font-size: 16px;
        }
        button {
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        #message {
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h1>🏆 Add Level to Team 🏆</h1>

<form id="levelForm">
    <input type="text" id="teamName" placeholder="Enter Team Name" required>
    <input type="text" id="level" placeholder="Enter Level" required>
    <button type="submit">Add Level</button>
</form>

<p id="message"></p>

<script>
    const apiUrl = "http://localhost:4008/techHub/addLevel"; // Replace with your actual Node.js API URL

    document.getElementById("levelForm").addEventListener("submit", async function(event) {
        event.preventDefault();

        const teamName = document.getElementById("teamName").value.trim();
        const level = document.getElementById("level").value.trim();
        
        if (!teamName || !level) {
            document.getElementById("message").textContent = "❌ Please enter both Team Name and Level.";
            return;
        }

        try {
            const response = await fetch(apiUrl, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ TeamName: teamName, level: level })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || "Failed to add level.");
            }

            document.getElementById("message").textContent = "✅ Level added successfully!";
            document.getElementById("levelForm").reset();

        } catch (error) {
            console.error("Error:", error);
            document.getElementById("message").textContent = `❌ ${error.message}`;
        }
    });
</script>

</body>
</html>
