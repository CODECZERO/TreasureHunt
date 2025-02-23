<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 2em;
            background: #f4f4f4;
        }
        .container {
            max-width: 400px;
            margin: auto;
            padding: 2em;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease-in-out;
        }
        h1 {
            color: #333;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: 0.3s;
        }
        input:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
        }
        button {
            width: 100%;
            padding: 12px;
            border: none;
            background: #007bff;
            color: white;
            font-size: 18px;
            border-radius: 5px;
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
    </style>
    <script>
        async function registerTeam(event) {
            event.preventDefault(); // Prevent form from refreshing the page
            
            const teamName = document.getElementById("TeamName").value.trim();
            const secretCode = document.getElementById("Scode").value.trim();
            const resultDiv = document.getElementById("result");
            const loader = document.getElementById("loader");

            resultDiv.innerHTML = ""; // Clear previous messages

            if (!teamName || !secretCode) {
                resultDiv.innerHTML = "<p class='error'>⚠️ Both fields are required.</p>";
                return;
            }

            loader.style.display = "block"; // Show loading animation

            try {
                const response = await fetch("http://localhost:4008/team/register", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        TeamName: teamName,
                        Scode: secretCode
                    })
                });

                const data = await response.json();
                loader.style.display = "none"; // Hide loader

                if (response.ok) {
                    resultDiv.innerHTML = `<p class='success'>✅ ${data.message || "Successfully registered!"}</p>`;
                } else {
                    resultDiv.innerHTML = `<p class='error'>❌ ${data.message || "Registration failed."}</p>`;
                }
            } catch (error) {
                loader.style.display = "none";
                resultDiv.innerHTML = "<p class='error'>⚠️ Error connecting to the server.</p>";
            }
        }
    </script>
</head>
<body>
    <h1>🚀 Level Login</h1>
    <div class="container">
        <div id="result"></div>
        <div id="loader" class="loader"></div>

        <form onsubmit="registerTeam(event)">
            <label for="TeamName">🏆 Team Name:</label>
            <input type="text" id="TeamName" name="TeamName" required autofocus>

            <label for="Scode">🔑 Secret Code:</label>
            <input type="text" id="Scode" name="Scode" required>

            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>
