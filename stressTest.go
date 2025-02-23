package main

import (
	"encoding/json"
	"fmt"
	"log"
	"math/rand"
	"net/url"
	"strings"
	"sync"
	"time"

	"github.com/gorilla/websocket"
)

// Constants
const (
	serverURL         = "ws://localhost:3000/" // WebSocket server URL
	totalClients      = 20  // Number of concurrent WebSocket connections
	messagesPerClient = 1   // Number of messages each client will send
)

// Struct to store test results
type TestResults struct {
	successCount int
	failureCount int
	totalTime    time.Duration
	mutex        sync.Mutex
}

// MessageData represents the JSON message format
type MessageData struct {
	MessageId     string `json:"MessageId"`
	TypeOfMessage string `json:"typeOfMessage"`
	RoomName      string `json:"roomName"`
	UserId        string `json:"userId"`
	Question      string `json:"question"`
	Answer        string `json:"answer"`
}

// AIResponse represents the expected response structure from the AI model
type AIResponse struct {
	MessageId string `json:"MessageId"`
	RoomName  string `json:"roomName"`
	UserId    string `json:"userId"`
	Question  string `json:"question"`
	Answer    string `json:"answer"`
	AIResult  []struct {
		Content struct {
			Parts []struct {
				Text string `json:"text"`
			} `json:"parts"`
			Role string `json:"role"`
		} `json:"content"`
		FinishReason string `json:"finishReason"`
	} `json:"aiResult"`
}

// Generate a properly formatted JSON message with a custom answer
func generateMessage(clientID, msgIndex int, answer string) string {
	msg := MessageData{
		MessageId:     fmt.Sprintf("MSG_%d_%d", clientID, msgIndex),
		TypeOfMessage: "SEND_MESSAGE",
		RoomName:      "Akshf",
		UserId:        fmt.Sprintf("user_%d", clientID),
		Question:      "2+2+3",
		Answer:        answer,
	}
	jsonData, _ := json.Marshal(msg)
	return string(jsonData)
}

// WebSocket client function
func websocketClient(wg *sync.WaitGroup, results *TestResults, clientID int) {
	defer wg.Done()

	// Connect to WebSocket server
	u := url.URL{Scheme: "ws", Host: "localhost:3000"}
	conn, _, err := websocket.DefaultDialer.Dial(u.String(), nil)
	if err != nil {
		log.Printf("Client %d: Failed to connect: %v\n", clientID, err)
		results.mutex.Lock()
		results.failureCount++
		results.mutex.Unlock()
		return
	}
	defer conn.Close()

	// Initially use a base answer; for example "7"
	answer := "7"

	for i := 0; i < messagesPerClient; i++ {
		start := time.Now()

		// Generate the message with the current answer
		message := generateMessage(clientID, i, answer)
		err := conn.WriteMessage(websocket.TextMessage, []byte(message))
		if err != nil {
			log.Printf("Client %d: Failed to send message: %v\n", clientID, err)
			results.mutex.Lock()
			results.failureCount++
			results.mutex.Unlock()
			continue
		}

		// Read the response
		_, response, err := conn.ReadMessage()
		duration := time.Since(start)

		results.mutex.Lock()
		results.totalTime += duration
		if err != nil {
			log.Printf("Client %d: Failed to receive response: %v\n", clientID, err)
			results.failureCount++
		} else {
			// Log the raw response
			log.Printf("Client %d: Received response: %s\n", clientID, string(response))

			// Parse the response JSON into AIResponse
			var aiResp AIResponse
			if err := json.Unmarshal(response, &aiResp); err != nil {
				log.Printf("Client %d: Error parsing response JSON: %v\n", clientID, err)
				results.failureCount++
			} else {
				// Check if the AI result is valid (e.g., text equals "True")
				if len(aiResp.AIResult) > 0 &&
					len(aiResp.AIResult[0].Content.Parts) > 0 &&
					strings.TrimSpace(aiResp.AIResult[0].Content.Parts[0].Text) == "True" {
					log.Printf("Client %d: AI response valid.", clientID)
					results.successCount++
				} else {
					log.Printf("Client %d: AI response invalid. Retrying with alternative answer...", clientID)
					// For example, if the response was "False", try a different answer.
					// You can implement your logic here to modify the message.
					answer = "7" // change the answer to a new value
					// Optionally, you might want to resend the request here.
					// For simplicity, we count this as a failure:
					results.failureCount++
				}
			}
		}
		results.mutex.Unlock()

		time.Sleep(100 * time.Millisecond) // Small delay to avoid flooding
	}
}

// Main function to run the stress test
func main() {
	rand.Seed(time.Now().UnixNano())

	var wg sync.WaitGroup
	results := TestResults{}

	startTime := time.Now()

	// Launch multiple WebSocket clients
	for i := 0; i < totalClients; i++ {
		wg.Add(1)
		go websocketClient(&wg, &results, i)
	}

	wg.Wait()

	totalDuration := time.Since(startTime)
	avgResponseTime := results.totalTime / time.Duration(results.successCount+results.failureCount)

	// Print results
	fmt.Println("\n📊 WebSocket Stress Test Results:")
	fmt.Printf("🔹 Total Clients: %d\n", totalClients)
	fmt.Printf("🔹 Messages per Client: %d\n", messagesPerClient)
	fmt.Printf("✅ Successful Responses: %d\n", results.successCount)
	fmt.Printf("❌ Failed Requests: %d\n", results.failureCount)
	fmt.Printf("⏳ Total Test Duration: %v\n", totalDuration)
	fmt.Printf("⚡ Average Response Time: %v\n", avgResponseTime)
}
