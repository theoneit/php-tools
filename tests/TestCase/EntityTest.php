<?php
declare(strict_types=1);

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

use App\EntityExample;
use Tools\Entity;
use Tools\TestSuite\TestCase;

/**
 * EntityTest class
 */
class EntityTest extends TestCase
{
    /**
     * @var int
     */
    protected static int $currentErrorLevel;

    /**
     * @var \Tools\Entity
     */
    protected Entity $Entity;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$currentErrorLevel = error_reporting(E_ALL & ~E_USER_DEPRECATED);
    }

    /**
     * @inheritDoc
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        error_reporting(self::$currentErrorLevel);
    }

    /**
     * Called before every test method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->Entity = new EntityExample(['code' => 200]);
    }

    /**
     * Test for `__debugInfo()` method
     * @uses \Tools\Entity::__debugInfo()
     * @test
     */
    public function testDebugInfo(): void
    {
        ob_start();
        $expectedLine = __LINE__ + 1;
        debug($this->Entity);
        $dump = ob_get_clean() ?: '';
        $this->assertStringContainsString(EntityExample::class, $dump);

        $this->skipIf(IS_WIN);
        $this->assertStringContainsString(__FILE__ . ' (line ' . $expectedLine . ')', $dump);
        $this->assertStringContainsString('########## DEBUG ##########', $dump);
        $this->assertStringContainsString('App\EntityExample {', $dump);
    }

    /**
     * Test for `has()` method
     * @uses \Tools\Entity::has()
     * @test
     */
    public function testHas(): void
    {
        $this->assertTrue($this->Entity->has('code'));
        $this->assertFalse($this->Entity->has('noExisting'));

        //`has()` method with empty, `null` and `false` values returns `true`
        $this->assertTrue($this->Entity->set('keyWithEmptyValue', '')->has('keyWithEmptyValue'));
        $this->assertTrue($this->Entity->set('keyWithFalse', false)->has('keyWithFalse'));
    }

    /**
     * Test for `hasValue()` method
     * @uses \Tools\Entity::hasValue()
     * @test
     */
    public function testHasValue(): void
    {
        $this->assertTrue($this->Entity->hasValue('code'));
        $this->assertFalse($this->Entity->hasValue('noExisting'));

        //`hasValue()` method with empty, `null` and `false` values return `false`
        $this->assertFalse($this->Entity->set('keyWithEmptyValue', '')->hasValue('keyWithEmptyValue'));
        $this->assertFalse($this->Entity->set('keyWithFalse', false)->hasValue('keyWithFalse'));
    }

    /**
     * Test for `__get()` and `get()` methods
     * @uses \Tools\Entity::__get()
     * @uses \Tools\Entity::get()
     * @test
     * @noinspection PhpUndefinedFieldInspection
     */
    public function testGet(): void
    {
        $this->assertSame(200, $this->Entity->code);
        $this->assertSame(200, $this->Entity->get('code'));

        $this->assertNull($this->Entity->noExisting);
        $this->assertNull($this->Entity->get('noExisting'));
        $this->assertSame('default', $this->Entity->get('noExisting', 'default'));
    }

    /**
     * Test for `isEmpty()` method
     * @uses \Tools\Entity::isEmpty()
     * @test
     */
    public function testIsEmpty(): void
    {
        $this->assertFalse($this->Entity->isEmpty('code'));
        $this->assertTrue($this->Entity->isEmpty('noExisting'));

        //`isEmpty()` method with empty, `null` and `false` values return `true`
        $this->assertTrue($this->Entity->set('keyWithEmptyValue', '')->isEmpty('keyWithEmptyValue'));
        $this->assertTrue($this->Entity->set('keyWithFalse', false)->isEmpty('keyWithFalse'));
    }

    /**
     * Test for `set()` method
     * @uses \Tools\Entity::set()
     * @test
     */
    public function testSet(): void
    {
        $result = $this->Entity->set('newKey', 'newValue');
        $this->assertInstanceOf(Entity::class, $result);
        $this->assertSame('newValue', $this->Entity->get('newKey'));

        $this->Entity->set(['alfa' => 'first', 'beta' => 'second']);
        $this->assertSame('first', $this->Entity->get('alfa'));
        $this->assertSame('second', $this->Entity->get('beta'));

        $this->assertSame('', $this->Entity->set('keyWithEmptyValue', '')->get('keyWithEmptyValue'));
    }

    /**
     * Test for `toArray()` method
     * @uses \Tools\Entity::toArray()
     * @test
     */
    public function testToArray(): void
    {
        $expected = ['code' => 200, 'newKey' => 'newValue'];
        $result = $this->Entity->set('newKey', 'newValue')->toArray();
        $this->assertSame($expected, $result);

        $expected += ['subEntity' => ['subKey' => 'subValue']];
        $subEntity = new EntityExample(['subKey' => 'subValue']);
        $result = $this->Entity->set(compact('subEntity'))->toArray();
        $this->assertSame($expected, $result);
    }

    /**
     * Test for the `ArrayAccess` interface methods
     * @uses \Tools\Entity::offsetExists()
     * @uses \Tools\Entity::offsetGet()
     * @uses \Tools\Entity::offsetSet()
     * @uses \Tools\Entity::offsetUnset()
     * @test
     */
    public function testArrayAccess(): void
    {
        $this->Entity['newKey'] = 'a key';
        $this->assertTrue(isset($this->Entity['newKey']));
        $this->assertSame('a key', $this->Entity['newKey']);
        unset($this->Entity['newKey']);
        $this->assertFalse(isset($this->Entity['newKey']));
    }
}
