<?php
namespace SPL\SplEasylock\Form\Element;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Class of renderType securedPassword to display security level of chosen password
 */
class SecuredPasswordElement extends \TYPO3\CMS\Backend\Form\Element\AbstractFormElement {

    /**
     * This will render a single-line input form field with security level display
     *
     * @return array As defined in initializeResultArray() of AbstractNode
     */
    public function render() {
        $table = $this->data['tableName'];
        $fieldName = $this->data['fieldName'];
        $row = $this->data['databaseRow'];
        $parameterArray = $this->data['parameterArray'];
        $resultArray = $this->initializeResultArray();

        $itemValue = $parameterArray['itemFormElValue'];
        $config = $parameterArray['fieldConf']['config'];
        $evalList = GeneralUtility::trimExplode(',', $config['eval'], true);
        $size = MathUtility::forceIntegerInRange($config['size'] ?? $this->defaultInputWidth, $this->minimumInputWidth, $this->maxInputWidth);
        $width = (int)$this->formMaxWidth($size);

        foreach ($evalList as $func) {
            if (isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][$func])) {
                if (class_exists($func)) {
                    $evalObj = GeneralUtility::makeInstance($func);
                    if (method_exists($evalObj, 'deevaluateFieldValue')) {
                        $_params = [
                            'value' => $itemValue
                        ];
                        $itemValue = $evalObj->deevaluateFieldValue($_params);
                    }
                    if (method_exists($evalObj, 'returnFieldJS')) {
                        $resultArray['additionalJavaScriptPost'][] = 'TBE_EDITOR.customEvalFunctions[' . GeneralUtility::quoteJSvalue($func) . ']'
                            . ' = function(value) {' . $evalObj->returnFieldJS() . '};';
                    }
                }
            }
        }

        $uniqueFieldId = StringUtility::getUniqueId('formengine-input-');

        $attributes = [
            'value' => '',
            'id' => $uniqueFieldId,
            'class' => implode(' ', [
                'form-control',
                't3js-clearable',
                'hasDefaultValue',
            ]),
            'data-formengine-validation-rules' => $this->getValidationDataAsJsonString($config),
            'data-formengine-input-params' => json_encode([
                'field' => $parameterArray['itemFormElName'],
                'evalList' => implode(',', $evalList),
                'is_in' => trim($config['is_in'])
            ]),
            'data-formengine-input-name' => $parameterArray['itemFormElName'],
            'onkeyup' => 'checkPwd("' . $uniqueFieldId . '")',
        ];

        $mainFieldHtml = [];
        $mainFieldHtml[] = '<div class="form-control-wrap" style="max-width: ' . $width . 'px">';
        $mainFieldHtml[] =  '<div class="form-wizards-wrap">';
        $mainFieldHtml[] =      '<div class="form-wizards-element">';
        $mainFieldHtml[] =          '<input type="text"' . GeneralUtility::implodeAttributes($attributes, true) .'/>';
        $mainFieldHtml[] =          '<input type="hidden" name="' . $parameterArray['itemFormElName'] . '" value="' . htmlspecialchars($itemValue) . '" />';
        $mainFieldHtml[] =          '<script type="text/javascript">';
        $mainFieldHtml[] =          '
                                        function checkPwd(pwdFieldId) {
                                            var password = $("#" + pwdFieldId).val();
                                            var passwordSecurityLevel = $("#tx-spl-easylock-securitylevel_" + pwdFieldId);
                                            
                                            if (password) {                                                
                                                var pwdScore = 0;
                                                
                                                var uppercase = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                                                var lowercase = "abcdefghijklmnopqrstuvwxyz";
                                                var digits = "0123456789";
                                                var splChars ="!@#$%&*()";
                                                
                                                var ucaseFlag = contains(password, uppercase);
                                                var lcaseFlag = contains(password, lowercase);
                                                var digitsFlag = contains(password, digits);
                                                var splCharsFlag = contains(password, splChars);
                                                
                                                
                                                // set password score
                                                if (password.length>=8) {
                                                    pwdScore += 40;
                                                }
                                                
                                                if (ucaseFlag) {
                                                    pwdScore += 15;
                                                }
                                                
                                                if (lcaseFlag) {
                                                    pwdScore += 15;
                                                }
                                                
                                                if (digitsFlag) {
                                                    pwdScore += 15;
                                                }
                                                
                                                if (splCharsFlag) {
                                                    pwdScore += 15;
                                                }
                                                
                                                if (pwdScore > 75) {
                                                    passwordSecurityLevel.addClass("secure");
                                                    passwordSecurityLevel.removeClass("midsecure");
                                                    passwordSecurityLevel.removeClass("notsecure");
                                                    passwordSecurityLevel.text("Secure");
                                                } else if (pwdScore > 50) {
                                                    passwordSecurityLevel.addClass("midSecure");
                                                    passwordSecurityLevel.removeClass("secure");
                                                    passwordSecurityLevel.removeClass("notSecure");
                                                    passwordSecurityLevel.text("Could be more secure");
                                                } else {
                                                    passwordSecurityLevel.addClass("notSecure");
                                                    passwordSecurityLevel.removeClass("midSecure");
                                                    passwordSecurityLevel.removeClass("secure");
                                                    passwordSecurityLevel.text("Unsecure");
                                                }
                                            }
                                        }
                                        
                                        function contains(password, allowedChars) {
                                            for (i = 0; i < password.length; i++) {
                                                var char = password.charAt(i);
                                                if (allowedChars.indexOf(char) >= 0) {
                                                    return true;
                                                }
                                            }
                                            return false;
                                        }
                                    ';
        $mainFieldHtml[] =          '</script>';
        $mainFieldHtml[] =          '<div class="tx-spl-easylock-securitylevel" id="tx-spl-easylock-securitylevel_' . $uniqueFieldId . '"></div>';
        $mainFieldHtml[] =      '</div>';
        $mainFieldHtml[] =  '</div>';
        $mainFieldHtml[] = '</div>';
        $mainFieldHtml = implode(LF, $mainFieldHtml);

        // add backend css
        $resultArray['stylesheetFiles'] = array(
            'EXT:spl_easylock/Resources/Public/Css/tx-spleasylock-backend.css'
        );

        DebuggerUtility::var_dump ($resultArray);

        $resultArray['html'] = '<div class="formengine-field-item t3js-formengine-field-item">' . $mainFieldHtml . '</div>';

        return $resultArray;
    }
}