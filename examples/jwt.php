<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use Rhino\InputData\InputData;

class JwtInputData extends InputData
{
    public function jwt(?string $name, string $key): ?JwtInputData
    {
        try {
            return new static(JWT::decode($this->string($name), $key, ['HS256']));
        } catch (SignatureInvalidException $exception) {
            return new static();
        }
    }
}

$key = 'example-key';

$encodedJwt = JWT::encode([
    'foo' => 'bar',
], $key);

$data = new JwtInputData([
    'encodedJwt' => $encodedJwt,
    'invalidJwt' => $encodedJwt . 'bad-signature',
]);

var_dump($data->jwt('encodedJwt', $key)->string('foo'));
var_dump($data->jwt('invalidJwt', $key)->string('foo', 'baz'));
var_dump($data->jwt('encodedJwt', 'bad-key')->string('foo', 'qux'));
