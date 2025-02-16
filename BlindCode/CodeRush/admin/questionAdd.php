<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Question</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        /* General Styling */
        body {
            font-family: 'Poppins', sans-serif;
            text-align: center;
            margin: 0;
            background: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            max-width: 450px;
            width: 90%;
            padding: 2em;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease-in-out;
        }

        /* Form Styling */
        .form-group {
            position: relative;
            margin: 20px 0;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            outline: none;
            transition: 0.3s ease-in-out;
            background: white;
        }

        .form-group textarea {
            resize: none;
            height: 80px;
        }

        .form-group select {
            cursor: pointer;
        }

        .form-group label {
            position: absolute;
            top: 14px;
            left: 12px;
            font-size: 14px;
            color: #777;
            transition: 0.3s ease-in-out;
            pointer-events: none;
        }

        /* Floating Label Effect */
        .form-group input:focus + label,
        .form-group textarea:focus + label,
        .form-group input:not(:placeholder-shown) + label,
        .form-group textarea:not(:placeholder-shown) + label,
        .form-group select:focus + label {
            top: -10px;
            left: 10px;
            font-size: 12px;
            color: #007bff;
            background: #fff;
            padding: 0 5px;
        }

        /* Button */
        .submit-btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 18px;
            font-weight: bold;
            color: white;
            background: linear-gradient(45deg, #007bff, #0056b3);
            cursor: pointer;
            transition: 0.3s ease-in-out;
        }

        .submit-btn:hover {
            background: linear-gradient(45deg, #0056b3, #003d82);
            transform: scale(1.02);
        }

        /* Response Messages */
        .message {
            margin-top: 15px;
            font-size: 14px;
            font-weight: bold;
        }

        .error {
            color: #d9534f;
        }

        .success {
            color: #28a745;
        }

        /* Loader */
        .loader {
            display: none;
            margin: 10px auto;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 4px solid rgba(0, 123, 255, 0.3);
            border-top-color: #007bff;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <script>
        async function addQuestion(event) {
            event.preventDefault(); // Prevent page reload
            
            const question = document.getElementById("question").value.trim();
            const output = document.getElementById("output").value.trim().split(",").map(o => o.trim()); 
            const level = document.getElementById("level").value;
            const resultDiv = document.getElementById("result");
            const loader = document.getElementById("loader");

            console.log(level);

            resultDiv.innerHTML = ""; // Clear previous results

            if (!question || output.length === 0 || output[0] === "" || !level) {
                resultDiv.innerHTML = "<p class='message error'>⚠️ All fields are required.</p>";
                return;
            }

            loader.style.display = "block"; // Show loading animation

            try {
                const response = await fetch("http://localhost:4008/question/add", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        question: question,
                        output: output,
                        level: level
                    })
                });

                const data = await response.json();
                loader.style.display = "none"; // Hide loader

                if (response.ok) {
                    resultDiv.innerHTML = `<p class='message success'>✅ ${data.message || "Question added successfully!"}</p>`;
                } else {
                    resultDiv.innerHTML = `<p class='message error'>❌ ${data.message || "Failed to add question."}</p>`;
                }
            } catch (error) {
                loader.style.display = "none";
                resultDiv.innerHTML = "<p class='message error'>⚠️ Error connecting to the server.</p>";
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>📝 Add a New Question</h2>

        <div id="result"></div>
        <div id="loader" class="loader"></div>

        <form onsubmit="addQuestion(event)">
            <div class="form-group">
                <textarea id="question" name="question" placeholder=" " required></textarea>
                <label for="question">❓ Enter Question</label>
            </div>

            <div class="form-group">
                <input type="text" id="output" name="output" placeholder=" " required>
                <label for="output">🔢 Enter Outputs (comma-separated)</label>
            </div>

            <div class="form-group">
                <select id="level" name="level" required>
                    <option value="" disabled selected>📌 Select Level</option>
                    <option value="Level 1">Level 1</option>
                    <option value="Level 2">Level 2</option>
                    <option value="Level 3">Level 3</option>
                    <option value="Level 4">Level 4</option>
                </select>
            </div>

            <button type="submit" class="submit-btn">Submit Question</button>
        </form>
    </div>
</body>
</html>
