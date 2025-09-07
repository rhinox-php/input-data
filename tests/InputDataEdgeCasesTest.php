<?php

namespace Rhino\InputData\Tests;

use Rhino\InputData\InputData;

class InputDataEdgeCasesTest extends \PHPUnit\Framework\TestCase
{
    public function testDateTimeWithException(): void
    {
        $inputData = new InputData(['invalid_date' => 'not-a-date']);

        // Test exception handling in dateTime method with default fallback
        $result = $inputData->dateTime('invalid_date', null, 'now');
        $this->assertInstanceOf(\DateTimeImmutable::class, $result);

        // Test with timezone and exception
        $result = $inputData->dateTime('invalid_date', 'UTC', 'now');
        $this->assertInstanceOf(\DateTimeImmutable::class, $result);
        $this->assertSame('UTC', $result->getTimezone()->getName());
    }

    public function testObjectMethodEdgeCases(): void
    {
        // Test object method with null name and object data
        $inputData = new InputData((object) ['key' => 'value']);
        $result = $inputData->object();
        $this->assertInstanceOf(InputData::class, $result);
        $this->assertSame('value', $result->string('key'));

        // Test object method with scalar value that gets converted to object
        $inputData = new InputData(['data' => 'scalar']);
        $result = $inputData->object('data');
        $this->assertInstanceOf(InputData::class, $result);
    }

    public function testArrayMethodWithObjectConversion(): void
    {
        // Test arr method converts object to array
        $obj = (object) ['key1' => 'value1', 'key2' => 'value2'];
        $inputData = new InputData(['object_data' => $obj]);
        $result = $inputData->arr('object_data');
        $this->assertInstanceOf(InputData::class, $result);
        $this->assertTrue($result->isArray());
        $this->assertSame('value1', $result->string('key1'));
    }

    public function testIteratorWithNonIterableData(): void
    {
        // Test getIterator with scalar data
        $inputData = new InputData('scalar');
        $items = [];
        foreach ($inputData as $key => $value) {
            $items[] = [$key, $value];
        }
        $this->assertEmpty($items);
    }

    public function testExceptionClasses(): void
    {
        // Ensure all exception classes are instantiable (for coverage)
        $parseException = new \Rhino\InputData\ParseException('test');
        $this->assertInstanceOf(\Exception::class, $parseException);

        $fileReadException = new \Rhino\InputData\FileReadException('test');
        $this->assertInstanceOf(\Exception::class, $fileReadException);

        $mutationException = new \Rhino\InputData\MutationException('test');
        $this->assertInstanceOf(\Exception::class, $mutationException);

        $inputDataException = new \Rhino\InputData\InputDataException('test');
        $this->assertInstanceOf(\Exception::class, $inputDataException);
    }
}
