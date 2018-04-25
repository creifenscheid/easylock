/**
 * Module: TYPO3/CMS/SplEasylock/PasswordSecurityDisplay;
 *
 * xxx
 * @exports TYPO3/CMS/SplEasylock/PasswordSecurityDisplay
 */
define(function () {
    'use strict';

    /**
     * @exports TYPO3/CMS/SplEasylock/PasswordSecurityDisplay
     */
    var PasswordSecurityDisplay = {};
    var initCounter = 0;

    /**
     * initialize events
     */
    PasswordSecurityDisplay.initializeEvents = function() {

       if ($('.tx-spl-easylock-element-container').length) {

           // loop through every security level element to connect the corresponding input field
           $('.tx-spl-easylock-element-container').each(function(){

               // define objects
               var passwordField = $(this).find('.tx-spl-easylock-input');
               var securityLevel = $(this).find('.tx-spl-easylock-securitylevel');

               // bind needed events to the input field
               passwordField.on('change paste keyup', function(){
                   var passwordFieldValue = passwordField.val();

                   if (passwordFieldValue) {

                       var passwordScore = PasswordSecurityDisplay.checkPasswordComplexity(passwordFieldValue);

                       if (passwordScore > 75) {
                           securityLevel.addClass("securitylevel-3");
                           securityLevel.text("Secure");
                       } else if (passwordScore > 50) {
                           securityLevel.addClass("securitylevel-2");
                           securityLevel.text("Could be more secure");
                       } else {
                           securityLevel.addClass("securitylevel-1");
                           securityLevel.text("Unsecure");
                       }
                   }
               });
           });
       }

       else {

           if (initCounter < 3) {

               // call function again with delay
               setTimeout(function(){
                   $(PasswordSecurityDisplay.initializeEvents);
               }, 1500);

               // increase initCounter
               initCounter++;
           }
       }
    };

    /**
     * password complexity check
     */
    PasswordSecurityDisplay.checkPasswordComplexity = function (password) {

        // init password score
        var pwdScore = 0;

        // define min length
        var minLength = 8;

        var charLists = [
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'abcdefghijklmnopqrstuvwxyz',
            '0123456789',
            '!@#$%&*().,;:_-'
        ];

        for(var i = 0; i < charLists.length;  i++) {
            if (PasswordSecurityDisplay.checkValue(password, charLists[i])) {
                pwdScore += 15;
            }
        }

        // set password score
        if (password.length>=minLength) {
            pwdScore += 40;
        }

        return pwdScore;
    };

    /**
     * check value against predefined character list
     */
    PasswordSecurityDisplay.checkValue = function (value, charList) {

        // loop through and compare every character of given value against the character list
        for (var i = 0; i < value.length; i++) {
            var char = value.charAt(i);
            if (charList.indexOf(char) >= 0) {
                return true;
            }
        }

        return false;
    };

    /**
     * clear css class
     */
    PasswordSecurityDisplay.clearCssClasses = function (element) {
        for (var i = 1; i <= 3; i++) {
            element.removeClass('securitylevel-' + i);
        }
    };

    // trigger init events
    $(PasswordSecurityDisplay.initializeEvents);

    // return method object
    return PasswordSecurityDisplay;
});