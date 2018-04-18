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

        $attributes = [
            'value' => '',
            'id' => StringUtility::getUniqueId('formengine-input-'),
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
        ];

        $mainFieldHtml = [];
        $mainFieldHtml[] = '<div class="form-control-wrap" style="max-width: ' . $width . 'px">';
        $mainFieldHtml[] =  '<div class="form-wizards-wrap">';
        $mainFieldHtml[] =      '<div class="form-wizards-element">';
        $mainFieldHtml[] =          '<input type="text"' . GeneralUtility::implodeAttributes($attributes, true) . ' onChange="checkPwd()" />';
        $mainFieldHtml[] =          '<input type="hidden" name="' . $parameterArray['itemFormElName'] . '" value="' . htmlspecialchars($itemValue) . '" />';
        $mainFieldHtml[] =          '<script type="text/javascript">';
        $mainFieldHtml[] =          '
                                        function checkPwd() {
                                            var password = 0;
                                            
                                            if (password) {                                                
                                                var uppercase = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                                                var lowercase = "abcdefghijklmnopqrstuvwxyz";
                                                var digits = "0123456789";
                                                var splChars ="!@#$%&*()";
                                                
                                                var ucaseFlag = contains(password, uppercase);
                                                var lcaseFlag = contains(password, lowercase);
                                                var digitsFlag = contains(password, digits);
                                                var splCharsFlag = contains(password, splChars);
                                                
                                                if(password.length>=8 && ucaseFlag && lcaseFlag && digitsFlag && splCharsFlag) {
                                                    return true;
                                                } else {
                                                    return false;
                                                }
                                            } else {
                                                return false;
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
        $mainFieldHtml[] =          '<div class="securityLevel" data-element="' . $parameterArray['itemFormElID'] . '" style="background-color: deeppink; height: 25px; width: 100%; margin-top: 5px;"></div>';
        $mainFieldHtml[] =      '</div>';
        $mainFieldHtml[] =  '</div>';
        $mainFieldHtml[] = '</div>';
        $mainFieldHtml = implode(LF, $mainFieldHtml);

        $resultArray['html'] = '<div class="formengine-field-item t3js-formengine-field-item">' . $mainFieldHtml . '</div>';

        DebuggerUtility::var_dump ($resultArray);
        DebuggerUtility::var_dump ($parameterArray);

        return $resultArray;
    }
}