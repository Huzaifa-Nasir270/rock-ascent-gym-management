<?php
$data = json_decode(file_get_contents('https://generativelanguage.googleapis.com/v1beta/models?key=AIzaSyAtnTobcXh0ywzsGZYoaNtMSX9FE7v75OY'), true);
if(isset($data['models'])) {
    foreach($data['models'] as $m) {
        if(strpos($m['name'], 'gemini') !== false) {
            echo $m['name'] . "\n";
        }
    }
} else {
    print_r($data);
}
