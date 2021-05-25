<?php

function sendMessage($from, $to, $text) {
    // Plivo
    require 'plivo_auth.php';

    $response = $client->messages->create(
        $from, #from
        [$to], #to
        $text #text
    );
    print_r($response);
}

?>