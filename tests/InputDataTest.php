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

        $this->expectException(ParseException::class);
        InputData::jsonDecode('{"a":1,"b":2,"c":3');
    }

    public function testToString(): void
    {
        $this->assertSame('foo', (string) new InputData('foo'));
    }

    public function testClassToString(): void
    {
        $this->assertSame('foo', (string) new InputData(new class {
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
        $this->assertTrue((new InputData([]))->isArray());
    }

    public function testExists(): void
    {
        $this->assertTrue((new InputData(['a' => 1]))->exists('a'));
        $this->assertTrue((new InputData((object) ['a' => 1]))->exists('a'));
        $this->assertFalse((new InputData(null))->exists('b'));
    }
}
