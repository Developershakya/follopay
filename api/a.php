<?php
$url = "https://follopay.free.nf/api/deactivate-posts.php?key=my_super_secret_12345";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_VERBOSE, true);  // debug info

$response = curl_exec($ch);

if(curl_errno($ch)){
    echo "cURL Error: " . curl_error($ch);
} else {
    echo "Response from server:\n";
    echo $response;
}

curl_close($ch);
?>