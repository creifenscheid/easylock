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
     * @param bool $strict
     *
     * @return null|bool
     */
    public static function validate (string $value, string $expectedValue, $strict = true) : ?bool
    {
        if ($value) {
            // todo: implement salting factory
        
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
    
    
    ############
    
    
    /**
     * validate the user password (session data or form input)
     *
     * @param $notValidatedPassword
     * @return bool
     */
    protected function validatePassword($notValidatedPassword, $saltedMode = FALSE) : bool {

        if ($saltedMode) {
            $success = FALSE;

            // check salting utility
            if (\TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility::isUsageEnabled ('BE')) {

                // if enable for be - get instance of salt factory object
                $saltObj = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance ();

                // check salt factory objects
                if (is_object($saltObj)) {
                    $success = $saltObj->checkPassword($notValidatedPassword, $this->password);
                }
            }

            if ($success) {
                return 1;
            }

            else {
                return 0;
            }
        }

        else {
            // validate password
            if ($this->password === $notValidatedPassword) {
                return 1;
            }

            else {
                return 0;
            }
        }
    }
}