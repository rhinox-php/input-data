<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/vendor/autoload.php';

use Rhino\InputData\InputData;

function readCsv(string $file) {
    $handle = fopen($file, 'r');
    for ($i = 0; $row = fgetcsv($handle); ++$i) {
        yield $i => new InputData($row);
    }
    fclose($handle);
}

foreach (readCsv('csv-data.csv') as $i => $row) {
    if ($i === 0) continue;
    $name = $row->string(0);
    $number = $row->decimal(1, null);
    $date = $row->dateTime(2, 'UTC', null);
    var_dump($name, $number, $date);
}
