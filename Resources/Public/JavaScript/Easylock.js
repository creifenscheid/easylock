define([
    'jquery'
], function ($) {
    'use strict';

    let Easylock = {};
    
    Easylock.validatePwd = function () {
        const minLength = 12;
  const charLists = [
    ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'],
    ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'],
    ['0','1','2','3','4','5','6','7','8','9'],
    ['!','@','#','$','%','&','*','(',')','.',',',';',':','_','-']
  ];

  // SecurityCheck
  $('.input').on('change paste keyup', function(){
    let pwd = $(this).val();
    let secLvl = 0;
    let tmpCharLists = charLists;
   
    for (let i = 0; i < pwd.length; i++) {
      let char = pwd.charAt(i);
            
      for (let j = 0; j < tmpCharLists.length; j++) {
        if (tmpCharLists[j].indexOf(char) >= 0) {
          tmpCharLists.splice(j,1);
        }
      }
    }

    secLvl += tmpCharLists.length;

    if (pwd.length < minLength) {
        secLvl++;
    }

    $(this).attr('data-lvl',secLvl);
  }); 
    };

    Easylock.generatePwd = function () {
        const minLength = 12;
        const charLists = [
            ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'],
            ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'],
            ['0','1','2','3','4','5','6','7','8','9'],
            ['!','@','#','$','%','&','*','(',')','.',',',';',':','_','-']
        ];
        
        const charListsLength = charLists.length;
        
        $('.tx-easylock.button').click(function(){
            let generatedPwd = '';
            
            for (let i = 0; i < minLength; i++) {
                let charListId = Math.floor(Math.random() * charListsLength)
                
                let charListLength = charLists[charListId].length;
                generatedPwd += charLists[charListId][Math.floor(Math.random() * charListLength)];
            }
            
            $('.result').text(generatedPwd);
        });
    };

    // expose to global
    TYPO3.Easylock = Easylock;

    return Easylock;
});