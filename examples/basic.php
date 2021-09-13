<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Rhino\InputData\InputData;

$data = new InputData([
    'foo' => 'bar',
    'seven' => '7.5',
    'july3' => '2021-07-03 11:45:30',
    'fruit' => ['apple', 'banana', 'pear'],
]);

echo '$data->string(key, default)' . PHP_EOL;

var_dump($data->string('foo'));
var_dump($data->string('seven'));
var_dump($data->string('baz'));
var_dump($data->string('baz', null));
var_dump($data->string('baz', 'qux'));
var_dump($data->string('fruit'));

echo '$data->int(key, default)' . PHP_EOL;

var_dump($data->int('foo'));
var_dump($data->int('seven'));
var_dump($data->int('baz'));
var_dump($data->int('baz', 12));
var_dump($data->int('baz', null));
var_dump($data->int('fruit'));

echo '$data->decimal(key, default)' . PHP_EOL;

var_dump($data->decimal('foo'));
var_dump($data->decimal('seven'));
var_dump($data->decimal('baz'));
var_dump($data->decimal('baz', 12));
var_dump($data->decimal('baz', null));
var_dump($data->decimal('fruit'));

var_dump($data->dateTime('july3', 'Pacific/Auckland'));
var_dump($data->dateTime('july3'));
var_dump($data->dateTime('baz'));
var_dump($data->dateTime('baz', null, 'now'));

var_dump($data->arr('baz')->getData());
var_dump(count($data->arr('fruit')));
var_dump($data->arr('fruit')->isEmpty());
var_dump($data->arr('baz')->isEmpty());
