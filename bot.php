<?php

$botToken = "8133684879:AAEXQ0HZRftf2N8bSg5pkiVB_NpL6NmY0u8";
$groupUsername = "@sub4subyoutubeSub4Subyoutubes"; // group username
$checkUser = "@follopay"; // user to check

function callTelegram($method, $params=[]) {
    global $botToken;
    $url = "https://api.telegram.org/bot{$botToken}/{$method}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

$response = callTelegram("getChatMember", [
    "chat_id" => $groupUsername,
    "user_id" => $checkUser
]);

if (isset($response['ok']) && $response['ok']) {
    $status = $response['result']['status'];

    if (in_array($status, ["member", "creator", "administrator"])) {
        echo "User *$checkUser* is in the group 🎉 (Status: {$status})";
    } else {
        echo "User *$checkUser* is NOT in the group (Status: {$status})";
    }
} else {
    echo "User *$checkUser* not found or bot can't access info ❌";
}

?>
