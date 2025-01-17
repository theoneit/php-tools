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
 * @since       1.0.2
 */

namespace Tools\TestSuite;

use BadMethodCallException;
use Exception;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Error\Deprecated;
use PHPUnit\Framework\Exception as PHPUnitException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Throwable;
use Tools\Filesystem;

/**
 * A trait that provides some assertion methods.
 * @method static void assertIsArray($var, ?string $message = '') Asserts that `$var` is an array
 * @method static void assertIsBool($var, ?string $message = '') Asserts that `$var` is a boolean
 * @method static void assertIsCallable($var, ?string $message = '') Asserts that `$var` is a callable
 * @method static void assertIsFloat($var, ?string $message = '') Asserts that `$var` is a float
 * @method static void assertIsHtml($var, ?string $message = '') Asserts that `$var` is a html string
 * @method static void assertIsInt($var, ?string $message = '') Asserts that `$var` is an int
 * @method static void assertIsIterable($var, ?string $message = '') Asserts that `$var` is iterable, i.e. that it is an array or an object implementing `Traversable`
 * @method static void assertIsJson($var, ?string $message = '') Asserts that `$var` is a json string
 * @method static void assertIsObject($var, ?string $message = '') Asserts that `$var` is an object
 * @method static void assertIsPositive($var, ?string $message = '') Asserts that `$var` is a positive number
 * @method static void assertIsResource($var, ?string $message = '') Asserts that `$var` is a resource
 * @method static void assertIsString($var, ?string $message = '') Asserts that `$var` is a string
 * @method static void assertIsUrl($var, ?string $message = '') Asserts that `$var` is an url
 */
trait TestTrait
{
    /**
     * Magic `__call()` method.
     *
     * Provides some `assertIs*()` methods (eg, `assertIsString()`).
     * @param string $name Name of the method
     * @param array $arguments Arguments
     * @return void
     * @since 1.1.12
     */
    public function __call(string $name, array $arguments): void
    {
        self::__callStatic($name, $arguments);
    }

    /**
     * Magic `__callStatic()` method.
     *
     * Provides some `assertIs*()` methods (eg, `assertIsString()`).
     * @param string $name Name of the method
     * @param array $arguments Arguments
     * @return void
     * @since 1.1.12
     * @throws \BadMethodCallException
     */
    public static function __callStatic(string $name, array $arguments): void
    {
        if (str_starts_with($name, 'assertIs')) {
            $count = count($arguments);
            if (!$count || $count > 2) {
                throw new BadMethodCallException(sprintf('Method %s::%s() expects at least 1 argument, maximum 2, %d passed', __CLASS__, $name, $count));
            }

            switch ($name) {
                case 'assertIsJson':
                    $function = 'json_validate';
                    break;
                default:
                    /** @var callable $function */
                    $function = 'is_' . strtolower(substr($name, 8));
            }

            if (is_callable($function)) {
                $var = array_shift($arguments);
                /** @var callable $callable */
                $callable = [__CLASS__, 'assertTrue'];
                call_user_func_array($callable, [$function($var), ...$arguments]);

                return;
            }
        }

        throw new BadMethodCallException(sprintf('Method %s::%s() does not exist', __CLASS__, $name));
    }

    /**
     * Asserts that the array keys are equal to `$expectedKeys`
     * @param array-key[] $expectedKeys Expected keys
     * @param array $array Array to check
     * @param string $message The failure message that will be appended to the generated message
     * @return void
     */
    public static function assertArrayKeysEqual(array $expectedKeys, array $array, string $message = ''): void
    {
        $keys = array_keys($array);
        sort($keys);
        sort($expectedKeys);
        self::assertEquals($expectedKeys, $keys, $message);
    }

    /**
     * Asserts that a callable throws a `Deprecated`
     * @param callable $function A callable you want to test and that should throw a `Deprecated` exception
     * @param string $expectedMessage The expected message
     * @return void
     * @since 1.6.5
     */
    public static function assertDeprecated(callable $function, string $expectedMessage = ''): void
    {
        try {
            call_user_func($function);
        } catch (Throwable $e) {
            self::assertInstanceOf(Deprecated::class, $e, sprintf('Expected exception `%s`, unexpected type `%s`', Deprecated::class, get_class($e)));
            if ($expectedMessage) {
                self::assertStringStartsWith($expectedMessage, $e->getMessage(), sprintf('Expected message exception `%s`, unexpected message `%s`', $expectedMessage, $e->getMessage()));
            }
        }

        if (!isset($e)) {
            self::fail('Expected exception `' . Deprecated::class . '`, but no exception throw');
        }
    }

    /**
     * Asserts that a callable throws an exception
     * @param callable $function A callable you want to test and that should raise the expected exception
     * @param string $expectedException Expected exception
     * @param string $expectedMessage The expected message
     * @return void
     * @since 1.1.7
     */
    public static function assertException(callable $function, string $expectedException = Exception::class, string $expectedMessage = ''): void
    {
        if (!is_subclass_of($expectedException, Throwable::class)) {
            self::fail('Class `' . $expectedException . '` is not a throwable or does not exist');
        }
        if ($expectedException == Deprecated::class || is_subclass_of($expectedException, Deprecated::class)) {
            [, $method] = explode('::', __METHOD__);
            trigger_error('You cannot use `' . $method . '()` for deprecations, use instead `assertDeprecated()`');
        }

        try {
            call_user_func($function);
        } catch (Deprecated $e) {
            //Do nothing
        } catch (Throwable $e) {
            self::assertTrue($expectedException === get_class($e), sprintf('Expected exception `%s`, unexpected type `%s`', $expectedException, get_class($e)));

            if ($expectedMessage) {
                self::assertNotEmpty($e->getMessage(), 'Expected message exception `' . $expectedMessage . '`, but no message for the exception');
                self::assertEquals($expectedMessage, $e->getMessage(), sprintf('Expected message exception `%s`, unexpected message `%s`', $expectedMessage, $e->getMessage()));
            }
        }

        if (!isset($e)) {
            self::fail('Expected exception `' . $expectedException . '`, but no exception throw');
        }
    }

    /**
     * Asserts that a filename has the `$expectedExtension`.
     *
     * If `$expectedExtension` is an array, asserts that the filename has at least one of those values.
     *
     * It is not necessary it actually exists.
     * The assertion is case-insensitive (eg, for `PIC.JPG`, the expected extension is `jpg`).
     * @param string|string[] $expectedExtension Expected extension or an array of extensions
     * @param string $filename Filename
     * @param string $message The failure message that will be appended to the generated message
     * @return void
     */
    public static function assertFileExtension($expectedExtension, string $filename, string $message = ''): void
    {
        self::assertContains(Filesystem::instance()->getExtension($filename), (array)$expectedExtension, $message);
    }

    /**
     * Asserts that a filename have a MIME content type.
     *
     * If `$expectedMime` is an array, asserts that the filename has at least one of those values.
     * @param string|string[] $expectedMime MIME content type or an array of types
     * @param string $filename Filename
     * @param string $message The failure message that will be appended to the generated message
     * @return void
     */
    public static function assertFileMime($expectedMime, string $filename, string $message = ''): void
    {
        self::assertFileExists($filename);
        self::assertContains(mime_content_type($filename), (array)$expectedMime, $message);
    }

    /**
     * Asserts that an image file has `$expectedWidth` and `$expectedHeight`
     * @param int $expectedWidth Expected image width
     * @param int $expectedHeight Expected mage height
     * @param string $filename Path to the tested file
     * @param string $message The failure message that will be appended to the generated message
     * @return void
     */
    public static function assertImageSize(int $expectedWidth, int $expectedHeight, string $filename, string $message = ''): void
    {
        self::assertFileExists($filename);
        [$actualWidth, $actualHeight] = getimagesize($filename) ?: [0 => 0, 1 => 0];
        self::assertEquals($actualWidth, $expectedWidth, $message);
        self::assertEquals($actualHeight, $expectedHeight, $message);
    }

    /**
     * Asserts that `$var` is an array and is not empty
     * @param mixed $var Variable to check
     * @param string $message The failure message that will be appended to the generated message
     * @return void
     * @since 1.0.6
     */
    public static function assertIsArrayNotEmpty($var, string $message = ''): void
    {
        self::assertIsArray($var, $message);
        self::assertNotEmpty(array_filter($var), $message);
    }

    /**
     * Asserts that an object is an instance of `MockObject`
     * @param object $object Object
     * @param string $message The failure message that will be appended to the generated message
     * @return void
     * @since 1.5.2
     */
    public static function assertIsMock(object $object, string $message = ''): void
    {
        self::assertInstanceOf(MockObject::class, $object, $message ?: 'Failed asserting that a `' . get_class($object) . '` object is a mock');
    }

    /**
     * Asserts that the object properties are equal to `$expectedProperties`
     * @param string[] $expectedProperties Expected properties
     * @param object|object[] $object Object you want to check or an array of objects
     * @param string $message The failure message that will be appended to the generated message
     * @return void
     */
    public function assertObjectPropertiesEqual(array $expectedProperties, $object, string $message = ''): void
    {
        self::assertArrayKeysEqual($expectedProperties, (array)$object, $message);
    }

    /**
     * Asserts that `$firstClass` and `$secondClass` classes have the same methods
     * @param class-string|object $firstClass First class as string or object
     * @param class-string|object $secondClass Second class as string or object
     * @param string $message The failure message that will be appended to the generated message
     * @return void
     */
    public static function assertSameMethods($firstClass, $secondClass, string $message = ''): void
    {
        [$firstClassMethods, $secondClassMethods] = [get_class_methods($firstClass), get_class_methods($secondClass)];
        sort($firstClassMethods);
        sort($secondClassMethods);
        self::assertEquals($firstClassMethods, $secondClassMethods, $message);
    }

    /**
     * Returns a partial mock object for the specified abstract class.
     *
     * This works like the `createPartialMock()` method, but uses abstract classes and allows you to set constructor arguments
     * @param class-string $originalClassName Abstract class you want to mock
     * @param string[] $mockedMethods Methods you want to mock
     * @param array $arguments Constructor arguments
     * @return \PHPUnit\Framework\MockObject\MockObject
     * @since 1.7.1
     */
    public function createPartialMockForAbstractClass(string $originalClassName, array $mockedMethods = [], array $arguments = []): MockObject
    {
        if (!$this instanceof PHPUnitTestCase) {
            throw new PHPUnitException('Is this trait used by a class that extends `' . PHPUnitTestCase::class . '`?');
        }

        return $this->getMockForAbstractClass($originalClassName, $arguments, '', true, true, true, $mockedMethods);
    }

    /**
     * Expects the next assertion to fail. Optionally it can verify that the exception message is also the same.
     *
     * Convenient wrapper for `expectException()` and `expectExceptionMessage()`.
     * @param string $withMessage Optional expected message to check
     * @return void
     * @since 1.5.2
     */
    public function expectAssertionFailed(string $withMessage = ''): void
    {
        $this->expectException(AssertionFailedError::class);
        if ($withMessage) {
            $this->expectExceptionMessage($withMessage);
        }
    }

    /**
     * Skips the test if the condition is `true`
     * @param bool $shouldSkip Whether the test should be skipped
     * @param string $message The message to display
     * @return bool
     */
    public function skipIf(bool $shouldSkip, string $message = ''): bool
    {
        if ($shouldSkip) {
            self::markTestSkipped($message);
        }

        return $shouldSkip;
    }
}
