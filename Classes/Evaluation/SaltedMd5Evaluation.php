<?php
namespace ChristianReifenscheid\Easylock\Evaluation;

/**
 * Class for field value validation/evaluation to be used in 'eval' of TCA
 */
class SaltedMd5Evaluation {
    /**
     * JavaScript code for client side validation/evaluation
     *
     * @return string JavaScript code for client side validation/evaluation
     */
    public function returnFieldJS() {
        return '
         return value;
      ';
    }

    /**
     * Server-side validation/evaluation on saving the record
     *
     * @param string $value The field value to be evaluated
     * @param string $is_in The "is_in" value of the field configuration from TCA
     * @param bool $set Boolean defining if the value is written to the database or not. Must be passed by reference and changed if needed.
     * @return string Evaluated field value
     */
    public function evaluateFieldValue($value, $is_in, &$set) {
        // hash value if not empty
        if (strval($value) !== '') {

            // check salting utility
            if (\TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility::isUsageEnabled ('BE')) {

                // if enable for be - get instance of salt factory object
                $saltObj = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance ();

                // check salt factory objects
                if (is_object($saltObj)) {
                    // salt the password
                    $value = $saltObj->getHashedPassword($value);
                }
            }
        }

        return $value;
    }

    /**
     * Server-side validation/evaluation on opening the record
     *
     * @param array $parameters Array with key 'value' containing the field value from the database
     * @return string Evaluated field value
     */
    public function deevaluateFieldValue(array $parameters) {
        return $parameters['value'];
    }
}