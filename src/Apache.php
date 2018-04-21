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
 * @since       1.0.2
 */
namespace Tools;

/**
 * Provides some useful methods for interacting with Apache
 */
class Apache
{
    /**
     * Checks if a module is enabled
     * @param string $module Name of the module to be checked
     * @return bool
     */
    public static function isEnabled($module)
    {
        return in_array($module, apache_get_modules());
    }

    /**
     * Gets the version
     * @return string
     */
    public static function version()
    {
        $version = apache_get_version();

        return preg_match('/Apache\/(\d+\.\d+\.\d+)/i', $version, $matches) ? $matches[1] : $version;
    }
}