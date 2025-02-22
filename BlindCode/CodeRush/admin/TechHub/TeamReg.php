<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Team</title>
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

<h1>🚀 Register Your Team 🚀</h1>

<form id="teamForm">
    <input type="text" id="teamName" placeholder="Enter Team Name" required>
    <button type="submit">Register</button>
</form>

<p id="message"></p>

<script>
    const apiUrl = "http://localhost:4008/techHub/reg"; // Replace with actual Node.js URL

    document.getElementById("teamForm").addEventListener("submit", async function(event) {
        event.preventDefault();

        const teamName = document.getElementById("teamName").value.trim();
        if (!teamName) {
            document.getElementById("message").textContent = "Please enter a valid team name.";
            return;
        }

        try {
            const response = await fetch(apiUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ TeamName: teamName })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || "Failed to register team.");
            }

            document.getElementById("message").textContent = "✅ Team registered successfully!";
            document.getElementById("teamForm").reset();

        } catch (error) {
            console.error("Error:", error);
            document.getElementById("message").textContent = `❌ ${error.message}`;
        }
    });
</script>

</body>
</html>
