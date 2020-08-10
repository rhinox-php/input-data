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

        // @todo test big ints
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

        // @todo test high precision floats
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
        // @todo test timestamps
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
        // @todo test actual array values
        // @todo test nested arrays
        // @todo test object
        // @todo test keys
        // @todo test invalid data
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
        // @todo test simple values
        // @todo test object
        // @todo test invalid data
        // $this->markTestIncomplete();
    }
}
