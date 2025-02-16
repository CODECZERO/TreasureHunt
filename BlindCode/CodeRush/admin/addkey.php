<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Secret Key</title>
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
    </style>
    <script>
        async function addSecretKey(event) {
            event.preventDefault(); // Prevent page reload
            
            const password = document.getElementById("password").value.trim();
            const name = document.getElementById("name").value.trim();
            const level = document.getElementById("level").value.trim();
            const secretCode = document.getElementById("secretCode").value.trim();
            const resultDiv = document.getElementById("result");
            const loader = document.getElementById("loader");

            resultDiv.innerHTML = ""; // Clear previous results

            if (!password || !name || !level || !secretCode) {
                resultDiv.innerHTML = "<p class='error'>⚠️ All fields are required.</p>";
                return;
            }

            loader.style.display = "block"; // Show loading animation

            try {
                const response = await fetch("http://localhost:4008/admin/add-secret", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        password: password,
                        name: name,
                        level: level,
                        secretCode: secretCode
                    })
                });

                const data = await response.json();
                loader.style.display = "none"; // Hide loader

                if (response.ok) {
                    resultDiv.innerHTML = `<p class='success'>✅ ${data.message || "Secret key added successfully!"}</p>`;
                } else {
                    resultDiv.innerHTML = `<p class='error'>❌ ${data.message || "Failed to add secret key."}</p>`;
                }
            } catch (error) {
                loader.style.display = "none";
                resultDiv.innerHTML = "<p class='error'>⚠️ Error connecting to the server.</p>";
            }
        }
    </script>
</head>
<body>
    <h1>🔑 Add Secret Key</h1>
    <div class="container">
        <div id="result"></div>
        <div id="loader" class="loader"></div>

        <form onsubmit="addSecretKey(event)">
            <label for="password">🔐 Admin Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="name">👤 Admin Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="level">🏆 Enter Level:</label>
            <input type="text" id="level" name="level" required>

            <label for="secretCode">🔑 Secret Code:</label>
            <input type="text" id="secretCode" name="secretCode" required>

            <button type="submit">Add Secret Key</button>
        </form>
    </div>
</body>
</html>
