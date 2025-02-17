<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Check Answer</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin: 50px; }
        input, button { padding: 10px; margin: 10px; width: 300px; }
    </style>
</head>
<body>

    <h2>AI Answer Checker</h2>
    <form id="answerForm">
        <input type="text" id="question" placeholder="Enter your question" required><br>
        <input type="text" id="answer" placeholder="Enter your answer" required><br>
        <button type="submit">Check Answer</button>
    </form>

    <h3>Response:</h3>
    <p id="responseText"></p>

    <script>
        document.getElementById("answerForm").addEventListener("submit", async function(event) {
            event.preventDefault(); // Prevent form from refreshing the page

            const question = document.getElementById("question").value;
            const answer = document.getElementById("answer").value;
            const responseText = document.getElementById("responseText");

            responseText.textContent = "Checking..."; // Show loading message

            try {
                const response = await fetch("http://localhost:4008/question/Check", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ question, answer })
                });

                const result = await response.json();
                responseText.textContent = result?.data?.[0]?.content?.parts?.[0]?.text || "Error in response";
            } catch (error) {
                responseText.textContent = "Failed to connect to backend.";
            }
        });
    </script>

</body>
</html>
