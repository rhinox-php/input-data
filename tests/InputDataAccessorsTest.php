<?php

namespace Rhino\InputData\Tests;

use Rhino\InputData\InputData;

class InputDataAccessorsTest extends \PHPUnit\Framework\TestCase
{
    public function testString(): void
    {
        $inputData = new InputData([
            'str' => 'foo',
            'int' => 7,
        ]);
        $this->assertSame('foo', $inputData->string('str'));
        $this->assertSame('7', $inputData->string('int'));
        $this->assertSame('', $inputData->string('missing'));
        $this->assertSame('default', $inputData->string('missing', 'default'));

        $inputData = new InputData('foo');
        $this->assertSame('foo', $inputData->string());

        $inputData = new InputData([]);
        $this->assertSame('default', $inputData->string(null, 'default'));

        $inputData = new InputData([
            'wat' => (object) [],
        ]);
        $this->assertSame('default', $inputData->string('wat', 'default'));
    }

    public function testInt(): void
    {
        $inputData = new InputData([
            'str' => 'foo',
            'int' => 7,
        ]);
        $this->assertSame(0, $inputData->int('str'));
        $this->assertSame(7, $inputData->int('int'));
        $this->assertSame(5, $inputData->int('str', 5));

        $this->assertSame(8, (new InputData(8))->int());
        $this->assertSame(8, (new InputData('8.1'))->int());
        $this->assertSame(9, (new InputData(9))->int(null, null));
        $this->assertSame(2, (new InputData([]))->int(null, 2));
        $this->assertSame(3, (new InputData(null))->int(null, 3));
        $this->assertSame(null, (new InputData([]))->int(null, null));
        $this->assertSame(null, (new InputData((object) []))->int(null, null));

        // Test big integers
        $this->assertSame(PHP_INT_MAX, (new InputData((string) PHP_INT_MAX))->int());
        $this->assertSame(PHP_INT_MIN, (new InputData((string) PHP_INT_MIN))->int());
    }

    public function testDecimal(): void
    {
        $inputData = new InputData([
            'str' => 'foo',
            'dec' => 7.1,
        ]);
        $this->assertSame((float) 0, $inputData->decimal('str'));
        $this->assertSame(7.1, $inputData->decimal('dec'));
        $this->assertSame(5.1, $inputData->decimal('str', 5.1));

        $this->assertSame(8.3, (new InputData(8.3))->decimal());
        $this->assertSame(9.4, (new InputData('9.4'))->decimal());
        $this->assertSame(10.2, (new InputData(10.2))->decimal(null, 9.5));
        $this->assertSame(1.2, (new InputData([]))->decimal(null, 1.2));
        $this->assertSame(1.3, (new InputData(null))->decimal(null, 1.3));
        $this->assertSame(null, (new InputData([]))->decimal(null, null));
        $this->assertSame(2.3, (new InputData((object) []))->decimal(null, 2.3));

        // Test high precision floats
        $this->assertSame(3.14159265359, (new InputData('3.14159265359'))->decimal());
        $this->assertSame(1.7976931348623157E+308, (new InputData('1.7976931348623157E+308'))->decimal());
    }

    public function testBool(): void
    {
        $inputData = new InputData([
            'str' => 'foo',
            't' => true,
            'f' => false,
        ]);
        $this->assertSame(true, $inputData->bool('str'));
        $this->assertSame(true, $inputData->bool('t'));
        $this->assertSame(false, $inputData->bool('f'));
        $this->assertSame(false, $inputData->bool('default'));
        $this->assertSame(true, $inputData->bool('default', true));

        $this->assertSame(true, (new InputData(true))->bool());
        $this->assertSame(false, (new InputData(''))->bool());
        $this->assertSame(false, (new InputData('0'))->bool());
        $this->assertSame(true, (new InputData(true))->bool(null, null));
        $this->assertSame(true, (new InputData([]))->bool(null, true));
        $this->assertSame(false, (new InputData([]))->bool(null, false));
        $this->assertSame(null, (new InputData([]))->bool(null, null));
        $this->assertSame(true, (new InputData((object) []))->bool(null, true));
    }

    public function testDateTime(): void
    {
        $inputData = new InputData([
            'd1' => '2019-07-24',
            'd2' => null,
            'd3' => 'invalid date',
        ]);
        $this->assertInstanceOf(\DateTimeImmutable::class, $inputData->dateTime('d1'));
        $this->assertSame('2019-07-24', $inputData->dateTime('d1')->format('Y-m-d'));
        $this->assertSame('2019-07-24T00:00:00+0000', $inputData->dateTime('d1', 'UTC')->format(DATE_ISO8601));
        $this->assertSame('2019-07-24T00:00:00+1200', $inputData->dateTime('d1', 'Pacific/Auckland')->format(DATE_ISO8601));
        $this->assertSame(null, $inputData->dateTime('d2', null, null));
        $this->assertSame(null, $inputData->dateTime('d2', 'Pacific/Auckland', null));
        $this->assertSame('2019-08-25', $inputData->dateTime('d3', null, '2019-08-25')->format('Y-m-d'));
        $this->assertSame('2019-08-25T00:00:00+1200', $inputData->dateTime('d3', 'Pacific/Auckland', '2019-08-25')->format(DATE_ISO8601));
        // Test timestamps
        $timestamp = '@' . time(); // Use @ prefix for timestamp format
        $result = (new InputData(['timestamp' => $timestamp]))->dateTime('timestamp');
        $this->assertInstanceOf(\DateTimeImmutable::class, $result);
    }

    public function testArr(): void
    {
        $inputData = new InputData([
            'a1' => [1, 2, 3],
        ]);
        $this->assertCount(3, $inputData->arr('a1'));
        $this->assertInstanceOf(InputData::class, $inputData->arr('a1'));
        foreach ($inputData->arr('a1') as $i => $n) {
            $this->assertSame($i->int() + 1, $n->int());
        }
        $this->assertSame([], (new InputData('foo'))->arr()->getData());
        $this->assertSame(['a' => 1], (new InputData((object) ['a' => 1]))->arr()->getData());
        // Test actual array values
        $this->assertSame([1, 2, 3], $inputData->arr('a1')->getData());

        // Test nested arrays
        $nested = new InputData(['nested' => [['a' => 1], ['b' => 2]]]);
        $this->assertSame([['a' => 1], ['b' => 2]], $nested->arr('nested')->getData());
        $this->assertSame(1, $nested->arr('nested')->arr(0)->int('a'));

        // Test object conversion to array
        $objData = new InputData(['obj' => (object) ['key' => 'value']]);
        $this->assertSame(['key' => 'value'], $objData->arr('obj')->getData());

        // Test array keys
        $keyed = new InputData(['items' => ['first' => 1, 'second' => 2]]);
        $this->assertTrue($keyed->arr('items')->exists('first'));
        $this->assertTrue($keyed->arr('items')->exists('second'));

        // Test invalid data defaults
        $this->assertSame(['default'], (new InputData(['invalid' => null]))->arr('invalid', ['default'])->getData());
    }

    public function testObject(): void
    {
        $inputData = new InputData((object) [
            'o1' => (object) [
                'i1' => 123,
            ],
        ]);
        $this->assertEquals((object) [
            'i1' => 123,
        ], $inputData->object('o1')->getData());

        $inputData = new InputData([
            'o2' => ['i2' => 456],
        ]);
        $this->assertEquals((object) ['i2' => 456], $inputData->object('o2')->getData());
    }

    public function testJson(): void
    {
        $inputData = new InputData([
            'j1' => json_encode([1, 2, 3]),
            'j2' => '[invalid json]',
        ]);
        $this->assertCount(3, $inputData->json('j1')->arr());
        $this->assertInstanceOf(InputData::class, $inputData->json('j1'));
        $this->assertNull(($inputData->json('j2')->string('abc', null)));
        $this->assertSame('123', ($inputData->json('j3', ['abc' => 123])->string('abc')));
        // Test simple JSON values
        $simpleJson = new InputData(['simple' => '"hello world"']);
        $this->assertSame('hello world', $simpleJson->json('simple')->string());

        $numberJson = new InputData(['number' => '42']);
        $this->assertSame('42', $numberJson->json('number')->string());

        // Test JSON object
        $objectJson = new InputData(['obj' => '{"name":"John","age":30}']);
        $result = $objectJson->json('obj');
        $this->assertSame('John', $result->string('name'));
        $this->assertSame(30, $result->int('age'));

        // Test invalid JSON data
        $invalidJson = new InputData(['bad' => 'not json at all']);
        $this->assertSame([], $invalidJson->json('bad')->getData());
        $this->assertSame(['fallback'], $invalidJson->json('bad', ['fallback'])->getData());
    }

    public function testRaw(): void
    {
        $inputData = new InputData(['abc' => 123]);
        $this->assertSame(123, $inputData->raw('abc'));

        $inputData = new InputData(fopen(__FILE__, 'r'));
        $this->assertSame(123, $inputData->raw('def', 123));

        $inputData = new InputData([1, 2, 3]);
        $this->assertSame(1, $inputData->raw(0));
    }
}
