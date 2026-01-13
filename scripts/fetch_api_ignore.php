<?php
$url = $argv[1] ?? 'http://localhost/Maps/api/destination.php?id=11';
$opts = ["http"=>["method"=>"GET","header"=>"Accept: application/json\r\n","ignore_errors"=>true]];
$context = stream_context_create($opts);
$result = @file_get_contents($url, false, $context);
if ($result === false) {
    echo "NO BODY, but headers:\n";
    var_export($http_response_header);
    echo "\n";
} else {
    echo "BODY:\n";
    echo $result . "\n";
    echo "HEADERS:\n";
    var_export($http_response_header);
    echo "\n";
}
