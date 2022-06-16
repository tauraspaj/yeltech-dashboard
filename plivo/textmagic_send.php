<?php

require_once(__DIR__ . '/vendor/autoload.php');

use TextMagic\Services\TextmagicRestClient;

function sendWithTextMagic($recipient, $message) {
    $client = new TextmagicRestClient('yeltechtechnical', 'r9znGSrXumXc24pvQ4CUXmqCafD2lp');
    $result = ' ';
    try {
        $result = $client->messages->create(
            array(
                'text' => $message,
                'phones' => implode(', ', array($recipient))
            )
        );
    }
    catch (\Exception $e) {
        if ($e instanceof RestException) {
            print '[ERROR] ' . $e->getMessage() . "\n";
            foreach ($e->getErrors() as $key => $value) {
                print '[' . $key . '] ' . implode(',', $value) . "\n";
            }
        } else {
            print '[ERROR] ' . $e->getMessage() . "\n";
        }
        return;
    }
    echo $result['id'];
}

?>
