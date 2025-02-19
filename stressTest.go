package main

import (
	"encoding/json"
	"fmt"
	"log"
	"math/rand"
	"net/url"
	"sync"
	"time"

	"github.com/gorilla/websocket"
)

// Constants
const (
	serverURL         = "ws://localhost:3000/" // WebSocket server URL
	totalClients      = 20  // Number of concurrent WebSocket connections
	messagesPerClient = 1  // Number of messages each client will send
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

// Generate a properly formatted JSON message
func generateMessage(clientID, msgIndex int) string {
	msg := MessageData{
		MessageId:     fmt.Sprintf("MSG_%d_%d", clientID, msgIndex),
		TypeOfMessage: "SEND_MESSAGE",
		RoomName:      "room_48",
		UserId:        fmt.Sprintf("user_%d", clientID),
		Question:      "2+2",
		Answer:        "4",
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

	for i := 0; i < messagesPerClient; i++ {
		start := time.Now()

		message := generateMessage(clientID, i) // Generate correctly formatted JSON
		err := conn.WriteMessage(websocket.TextMessage, []byte(message))
		if err != nil {
			log.Printf("Client %d: Failed to send message: %v\n", clientID, err)
			results.mutex.Lock()
			results.failureCount++
			results.mutex.Unlock()
			continue
		}

		_, response, err := conn.ReadMessage()
		duration := time.Since(start)

		results.mutex.Lock()
		results.totalTime += duration
		if err != nil {
			log.Printf("Client %d: Failed to receive response: %v\n", clientID, err)
			results.failureCount++
		} else {
			log.Printf("Client %d: Received response: %s\n", clientID, string(response))
			results.successCount++
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
