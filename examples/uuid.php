<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/vendor/autoload.php';

use Ramsey\Uuid\Exception\UuidExceptionInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Rhino\InputData\InputData;

class UuidInputData extends InputData
{
    public function uuid(?string $name = null): ?UuidInterface
    {
        try {
            return Uuid::fromString($this->string($name));
        } catch (UuidExceptionInterface $exception) {
            return null;
        }
    }
}

$data = new UuidInputData([
    'v1' => 'f16daac0-1479-11ec-82a8-0242ac130003',
    'v4' => 'cbcfd6f2-5c73-437f-8b8a-c3249f1a62bf',
    'invalid' => 'foo'
]);

var_dump($data->uuid('v1'));
var_dump($data->uuid('v4'));
var_dump($data->uuid('invalid'));
