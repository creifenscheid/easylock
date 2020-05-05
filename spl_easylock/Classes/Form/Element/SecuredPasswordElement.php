<?php
namespace ChristianReifenscheid\Easylock\Form\Element;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

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
                'tx-easylock-input',
            ]),
            'data-formengine-validation-rules' => $this->getValidationDataAsJsonString($config),
            'data-formengine-input-params' => json_encode([
                'field' => $parameterArray['itemFormElName'],
                'evalList' => implode(',', $evalList),
                'is_in' => trim($config['is_in'])
            ]),
            'data-formengine-input-name' => $parameterArray['itemFormElName'],
            //'onkeyup' => 'checkPwd("' . $uniqueFieldId . '")',
        ];

        $mainFieldHtml = [];
        $mainFieldHtml[] = '<div class="form-control-wrap" style="max-width: ' . $width . 'px">';
        $mainFieldHtml[] =  '<div class="form-wizards-wrap">';
        $mainFieldHtml[] =      '<div class="form-wizards-element tx-spl-easylock-element-container">';
        $mainFieldHtml[] =          '<input type="text"' . GeneralUtility::implodeAttributes($attributes, true) .'/>';
        $mainFieldHtml[] =          '<input type="hidden" name="' . $parameterArray['itemFormElName'] . '" value="' . htmlspecialchars($itemValue) . '" />';
        $mainFieldHtml[] =          '<div class="tx-spl-easylock-securitylevel" id="tx-easylock-securitylevel_' . $uniqueFieldId . '"></div>';
        $mainFieldHtml[] =      '</div>';
        $mainFieldHtml[] =  '</div>';
        $mainFieldHtml[] = '</div>';
        $mainFieldHtml = implode(LF, $mainFieldHtml);

        // todo: add as backend skin
        // add backend css
        $resultArray['stylesheetFiles'] = array(
            'EXT:easylock/Resources/Public/Css/tx-easylock-backend.css'
        );

        // add js module
        $resultArray['requireJsModules'] = array(
            'TYPO3/CMS/Easylock/PasswordSecurityDisplay'
        );

        $resultArray['html'] = '<div class="formengine-field-item t3js-formengine-field-item">' . $mainFieldHtml . '</div>';

        return $resultArray;
    }
}