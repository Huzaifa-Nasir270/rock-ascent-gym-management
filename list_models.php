<?php
$geminiApiKey = 'AIzaSyAtnTobcXh0ywzsGZYoaNtMSX9FE7v75OY';
$url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $geminiApiKey;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

echo $response;
