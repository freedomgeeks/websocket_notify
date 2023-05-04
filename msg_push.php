<?php
require_once __DIR__ . '/vendor/autoload.php';
$client = new \WebSocket\Client('ws://127.0.0.1:2346');
$chan['uid'] = 'server';
$chan['to'] = 1;
$chan['data'] = 'hello';
$client->send(json_encode($chan,JSON_UNESCAPED_UNICODE));