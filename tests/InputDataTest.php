<?php

namespace Rhino\InputData\Tests;

use Rhino\InputData\InputData;
use Rhino\InputData\ParseException;

class InputDataTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor(): void
    {
        $inputData = new InputData(new InputData([
            'str' => 'foo',
            'int' => 7,
        ]));
        $this->assertSame('foo', $inputData->string('str'));
    }

    public function testDotDelimiters(): void
    {
        $inputData = new InputData([
            'a' => [
                'b' => [
                    'c' => 123,
                ],
            ],
        ]);
        $this->assertSame(123, $inputData->int('a.b.c'));
        $inputData = new InputData((object) [
            'a' => (object) [
                'b' => (object) [
                    'c' => 123,
                ],
            ],
        ]);
        $this->assertSame(123, $inputData->int('a.b.c'));
        $this->assertSame(456, $inputData->int('a.b.c.d.e', 456));
    }

    public function testCount(): void
    {
        $this->assertCount(3, new InputData(['a' => 1, 'b' => 2, 'c' => 3]));
        $this->assertCount(0, new InputData('foo'));
        $this->assertCount(0, new InputData(null));
        $this->assertCount(7, new InputData(new class() implements \Countable
        {
            public function count(): int
            {
                return 7;
            }
        }));
    }

    public function testJsonEncode(): void
    {
        $this->assertSame('{"a":1,"b":2,"c":3}', json_encode(new InputData(['a' => 1, 'b' => 2, 'c' => 3])));
        $this->assertSame('"foo"', json_encode(new InputData('foo')));
    }

    public function testJsonDecode(): void
    {
        $inputData = InputData::jsonDecode('{"a":1,"b":2,"c":3}');
        $this->assertSame('1', $inputData->string('a'));

        $inputData = InputData::jsonDecode('{"foo":{"bar":[1,2,3]}}', false);
        $this->assertEquals((object) ['bar' => [1, 2, 3]], $inputData->object('foo')->getData());

        $inputData = InputData::jsonDecode('{"foo":{"bar":[1,2,3]}}');
        $this->assertSame(['bar' => [1, 2, 3]], $inputData->arr('foo')->getData());

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Error decoding JSON #4 Syntax error');
        InputData::jsonDecode('{"a":1,"b":2,"c":3');
    }

    public function testTryJsonDecode(): void
    {
        $inputData = InputData::tryJsonDecode('invalid');
        $this->assertNull($inputData->getData());

        $inputData = InputData::tryJsonDecode('{"a":1,"b":2,"c":3}');
        $this->assertSame('1', $inputData->string('a'));
    }

    public function testJsonDecodeFile(): void
    {
        $inputData = InputData::jsonDecodeFile(__DIR__ . '/data.json');
        $this->assertSame('bar', $inputData->string('foo'));
    }

    public function testTryJsonDecodeFile(): void
    {
        $inputData = InputData::tryJsonDecodeFile(__DIR__ . '/data.json');
        $this->assertSame('bar', $inputData->string('foo'));

        $inputData = InputData::tryJsonDecodeFile(__DIR__ . '/invalid.json');
        $this->assertNull($inputData->getData());
    }

    public function testToString(): void
    {
        $this->assertSame('foo', (string) new InputData('foo'));
    }

    public function testClassToString(): void
    {
        $this->assertSame('foo', (string) new InputData(new class
        {
            public function __toString()
            {
                return 'foo';
            }
        }));
    }

    public function testEmpty(): void
    {
        $this->assertTrue((new InputData([]))->isEmpty());
    }

    public function testArray(): void
    {
        $inputData = new InputData([
            'foo' => [
                1,
            ],
            'bar' => [[
                'baz' => 2,
            ]],
        ]);
        $this->assertTrue($inputData->isArray());
        $this->assertSame(1, $inputData->arr('foo')->int(0));
        $this->assertSame(2, $inputData->arr('bar')->arr(0)->int('baz'));
        $this->assertIsArray((new InputData(['a' => 'foo']))->arr('a')->getData());
    }

    public function testExists(): void
    {
        $this->assertTrue((new InputData(['a' => 1]))->exists('a'));
        $this->assertTrue((new InputData((object) ['a' => 1]))->exists('a'));
        $this->assertFalse((new InputData(null))->exists('b'));
        $this->assertTrue((new InputData(['a' => ['b' => 'c']]))->exists('a.b'));
    }

    public function testFind(): void
    {
        $this->assertSame(1, (new InputData(['a' => 1, 'b' => 1]))->find(fn($value) => $value->int() === 1)->int());
        $this->assertSame(2, (new InputData(['a' => 1, 'b' => 2]))->find(fn($value, $key) => $key->string() === 'b')->int());
    }
}
