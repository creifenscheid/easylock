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
                           securityLevel.addClass("secure");
                           securityLevel.removeClass("midsecure");
                           securityLevel.removeClass("notsecure");
                           securityLevel.text("Secure");
                       } else if (passwordScore > 50) {
                           securityLevel.addClass("midSecure");
                           securityLevel.removeClass("secure");
                           securityLevel.removeClass("notSecure");
                           securityLevel.text("Could be more secure");
                       } else {
                           securityLevel.addClass("notSecure");
                           securityLevel.removeClass("midSecure");
                           securityLevel.removeClass("secure");
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

        var charLists = [
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'abcdefghijklmnopqrstuvwxyz',
            '0123456789',
            '!@#$%&*()'
        ];

        var uppercase = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        var lowercase = "abcdefghijklmnopqrstuvwxyz";
        var digits = "0123456789";
        var splChars ="!@#$%&*()";
        var minLenght = 8;

        var ucaseFlag = PasswordSecurityDisplay.checkValue(password, uppercase);
        var lcaseFlag = PasswordSecurityDisplay.checkValue(password, lowercase);
        var digitsFlag = PasswordSecurityDisplay.checkValue(password, digits);
        var splCharsFlag = PasswordSecurityDisplay.checkValue(password, splChars);


        // set password score
        if (password.length>=minLenght) {
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

    // trigger init events
    $(PasswordSecurityDisplay.initializeEvents);

    // return method object
    return PasswordSecurityDisplay;
});