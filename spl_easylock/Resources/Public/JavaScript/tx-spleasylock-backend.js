function checkPwd(pwdFieldId) {
    var password = $('#' + pwdFieldId).val();
    var passwordSecurityLevel = $('#tx-spl-easylock-securitylevel_' + pwdFieldId);

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
    for (var i = 0; i < password.length; i++) {
        var char = password.charAt(i);
        if (allowedChars.indexOf(char) >= 0) {
            return true;
        }
    }
    return false;
}