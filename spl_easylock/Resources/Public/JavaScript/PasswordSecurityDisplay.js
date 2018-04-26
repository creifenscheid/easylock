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
    var securityElementClass = 'securitylevel-';
    var securityNotes = [
        'No password set',
        'Unsecure',
        'Could be more secure',
        'Ok',
    ];
    var minLength = 8;
    var charLists = [
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'abcdefghijklmnopqrstuvwxyz',
        '0123456789',
        '!@#$%&*().,;:_-'
    ];

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
                           PasswordSecurityDisplay.setSecurityLevel(securityLevel, '3');
                       } else if (passwordScore > 50) {
                           PasswordSecurityDisplay.setSecurityLevel(securityLevel, '2');
                       } else if (passwordScore > 0) {
                           PasswordSecurityDisplay.setSecurityLevel(securityLevel, '1');
                       } else {
                           PasswordSecurityDisplay.setSecurityLevel(securityLevel, '0');
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
     * set security level
     */
    PasswordSecurityDisplay.setSecurityLevel = function (element, level) {

        // remove possible existing level classes
        for (var i = 1; i <= 3; i++) {
            element.removeClass('securitylevel-' + i);
        }

        // add new level class
        element.addClass(securityElementClass + level);

        // change security label
        element.text(securityNotes[level]);
    };

    // trigger init events
    $(PasswordSecurityDisplay.initializeEvents);

    // return method object
    return PasswordSecurityDisplay;
});