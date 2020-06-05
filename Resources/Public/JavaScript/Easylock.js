define([
    'jquery'
], function ($) {
    'use strict';

    let Easylock = {};

    Easylock.generatePwd = function () {
        const minLength = 12;
        const charList = [
   'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9','!','@','#','$','%','&','*','(',')','.',',',';',':','_','-'];
   
       const charListLength = charList.length;
       
       $('.tx-easylock.button').click(function(){
           let generatedPwd = '';
           
           for (let i = 0; i < minLength; i++) {
               generatedPwd += charList[Math.floor(Math.random() * charListLength)];
           }
           
           $('.result').text(generatedPwd);
        });
    };

    // expose to global
    TYPO3.Easylock = Easylock;

    return Easylock;
});