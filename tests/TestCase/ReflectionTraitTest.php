<?php
/**
 * This file is part of php-tools.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/php-tools
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Tools\Test;

use App\ExampleClass;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Tools\ReflectionTrait;
use Tools\TestSuite\TestTrait;

/**
 * Reflection\ReflectionTrait Test Case
 */
class ReflectionTraitTest extends TestCase
{
    use ReflectionTrait;
    use TestTrait;

    /**
     * Tests for `getProperties()` method
     * @test
     */
    public function testGetProperties()
    {
        $example = new ExampleClass;

        $expected = [
            'privateProperty' => 'this is a private property',
            'firstProperty' => null,
            'secondProperty' => 'a protected property',
            'publicProperty' => 'this is public',
            'staticProperty' => 'a static property',
        ];
        $this->assertEquals($expected, $this->getProperties($example));

        $this->assertArrayKeysEqual(['publicProperty', 'staticProperty'], $this->getProperties($example, ReflectionProperty::IS_PUBLIC));
        $this->assertArrayKeysEqual(['firstProperty', 'secondProperty'], $this->getProperties($example, ReflectionProperty::IS_PROTECTED));
        $this->assertArrayKeysEqual(['privateProperty'], $this->getProperties($example, ReflectionProperty::IS_PRIVATE));
        $this->assertArrayKeysEqual(['staticProperty'], $this->getProperties($example, ReflectionProperty::IS_STATIC));

        unset($expected['privateProperty']);

        $example = $this->getMockBuilder(ExampleClass::class)->getMock();
        $this->assertEquals($expected, $this->getProperties($example));
    }

    /**
     * Tests for `getProperty()` method
     * @test
     */
    public function testGetProperty()
    {
        $example = new ExampleClass;

        $this->assertNull($this->getProperty($example, 'firstProperty'));
        $this->assertEquals('a protected property', $this->getProperty($example, 'secondProperty'));
    }

    /**
     * Tests for `invokeMethod()` method
     * @test
     */
    public function testInvokeMethod()
    {
        $example = new ExampleClass;

        $this->assertEquals('a protected method', $this->invokeMethod($example, 'protectedMethod'));
        $this->assertEquals('example string', $this->invokeMethod($example, 'protectedMethod', ['example string']));
    }

    /**
     * Tests for `getProperty()` method
     * @test
     */
    public function testSetProperty()
    {
        $example = new ExampleClass;

        $this->setProperty($example, 'firstProperty', 'example string');
        $this->assertEquals('example string', $example->firstProperty);

        $this->setProperty($example, 'secondProperty', null);
        $this->assertNull($example->secondProperty);
    }
}
