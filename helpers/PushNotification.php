<?php

class PushNotification {

    private $serverKey = "YOUR_FIREBASE_SERVER_KEY";
    private $firebaseUrl = "https://YOUR_PROJECT_ID.firebaseio.com/tokens.json";

    public function sendToAll($title, $message, $extraData = []) {

        // 🔹 Get all tokens from Firebase Realtime Database
        $response = @file_get_contents($this->firebaseUrl);

        if (!$response) {
            error_log("Failed to fetch tokens from Firebase");
            return false;
        }

        $tokens = json_decode($response, true);

        if (!$tokens || !is_array($tokens)) {
            error_log("No tokens found");
            return false;
        }

        foreach ($tokens as $token => $value) {

            $payload = [
                "to" => $token,
                "notification" => [
                    "title" => $title,
                    "body"  => $message,
                    "sound" => "default"
                ],
                "data" => $extraData
            ];

            $headers = [
                "Authorization: key=" . $this->serverKey,
                "Content-Type: application/json"
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/fcm/send");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_exec($ch);
            curl_close($ch);
        }

        return true;
    }
}
