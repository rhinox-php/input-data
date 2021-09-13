<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/vendor/autoload.php';

use Rhino\InputData\InputData;

$response = (new \GuzzleHttp\Client())->get('https://api.agify.io/?name=petah');
$data = InputData::jsonDecode($response->getBody());

echo $data->string('name') . ' is estimated to be ' . $data->int('age') . ' years old.' . PHP_EOL;
