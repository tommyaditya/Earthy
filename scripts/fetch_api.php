<?php
$url = $argv[1] ?? 'http://localhost/Maps/api/destination.php?id=11';
$opts = ["http"=>["method"=>"GET","header"=>"Accept: application/json\r\n"]];
$context = stream_context_create($opts);
$result = @file_get_contents($url, false, $context);
if ($result === false) {
    $err = error_get_last();
    echo "REQUEST FAILED: ";
    var_export($err);
    echo "\n";
    // print response headers if any
    global $http_response_header;
    var_export($http_response_header);
    echo "\n";
} else {
    echo "RESPONSE:\n";
    echo $result . "\n";
    global $http_response_header;
    echo "HEADERS:\n";
    var_export($http_response_header);
    echo "\n";
}
