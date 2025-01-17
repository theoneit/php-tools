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
 * @since       1.5.12
 */
namespace Tools\TestSuite\Console;

use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester as BaseCommandTester;

/**
 * CommandTester.
 *
 * This class overrides the one provided by the Symfony console component,
 *  offering additional methods.
 */
class CommandTester extends BaseCommandTester
{
    /**
     * Asserts that the output generated by executing the command contains `$needle`
     * @param string $needle String you want to check
     * @param string $message The failure message that will be appended to the
     *  generated message
     * @return void
     */
    public function assertOutputContains(string $needle, string $message = ''): void
    {
        Assert::assertStringContainsString($needle, $this->getDisplay(), $message ?: 'The output does not contain the string `' . $needle . '`');
    }

    /**
     * Asserts that the output generated by executing the command does not contain `$needle`
     * @param string $needle String you want to check
     * @param string $message The failure message that will be appended to the
     *  generated message
     * @return void
     */
    public function assertOutputNotContains(string $needle, string $message = ''): void
    {
        Assert::assertStringNotContainsString($needle, $this->getDisplay(), $message ?: 'The output contains the string `' . $needle . '`');
    }

    /**
     * Asserts that the command fails
     * @param string $message The failure message that will be appended to the
     *  generated message
     * @return void
     */
    public function assertCommandIsFailure(string $message = ''): void
    {
        Assert::assertSame(Command::FAILURE, $this->getStatusCode(), $message ?: 'The command did not fail');
    }
}
