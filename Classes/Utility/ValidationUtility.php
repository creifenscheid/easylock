<?php

declare(strict_types=1);

namespace ChristianReifenscheid\Easylock\Utility;

/**
 * *************************************************************
 *
 * Copyright notice
 *
 * (c) 2020 Christian Reifenscheid <christian.reifenscheid.2112@gmail.com>
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 * *************************************************************
 */

/**
 * Class ValidationUtility
 *
 * @package ChristianReifenscheid\Easylock\Utility
 * @author  Christian Reifenscheid
 */
class ValidationUtility
{
    /**
     * Compare value after it'been salted with expected value
     * 
     * @param string $value
     * @param string $expectedValue
     * @param boolean $strict
     * @return bool|NULL
     */
    public static function validate (string $value, string $expectedValue, $strict = true) : ?bool
    {
        if ($value) {
            
            /** @var \TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory $hashFactory */
            $hashFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory::class);
            $mode = 'BE';
            
            try {
                $hashFactory->get($value, $mode);
            } catch (\TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException $e) {
                $newHashInstance = $hashFactory->getDefaultHashInstance($mode);
                $value = $newHashInstance->getHashedPassword($value);
            }
        
            if ($strict) {
                if ($value === $expectedValue) {
                    return true;
                }
            } else {
                if ($value == $expectedValue) {
                    return true;
                }
            }
        
            return false;
        }
        
        return null;
    }
}