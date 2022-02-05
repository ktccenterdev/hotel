
var input = document.querySelector("#phone");
window.intlTelInput(input, {
allowExtensions: true,
autoFormat: false,
autoHideDialCode: false,
autoPlaceholder: false,
defaultCountry: "auto",
ipinfoToken: "yolo",
nationalMode: false,
numberType: "MOBILE",
initialCountry: "cm",
preventInvalidNumbers: true,
utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.3/js/utils.min.js"
});

/*var input = document.querySelector("#phone"),
  errorMsg = document.querySelector(".erreur"),
  validMsg = document.querySelector(".valide");

// here, the index maps to the error code returned from getValidationError - see readme
var errorMap = ["Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];
// initialise plugin
var iti = window.intlTelInput(input, {
  utilsScript: "./build/js/utils.js?1613236686837",*/
  /*utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",

});

var reset = function() {
  input.classList.remove("error");
  errorMsg.innerHTML = "";
  errorMsg.classList.add("hide");
  validMsg.classList.add("hide");
};
// on blur: validate
input.addEventListener('blur', function() {
  reset();
  if (input.value.trim()) {
    console.log('aaa');
    if (iti.isValidNumber()) {
      console.log('bbb');
      console.log(iti);
      console.log('ccc');
      validMsg.classList.remove("hide");
      console.log('dddd');
    } else {
      console.log('eee');
      input.classList.add("error");
      var errorCode = iti.getValidationError();
      errorMsg.innerHTML = errorMap[errorCode];
      errorMsg.classList.remove("hide");
      console.log('fff');
    }
  }
});
console.log('fin');
// on keyup / change flag: reset
input.addEventListener('change', reset);
input.addEventListener('keyup', reset);*/



/*************************COMPABY*******************************************/

var input = document.querySelector("#telephone2");
window.intlTelInput(input, {
allowExtensions: true,
autoFormat: false,
autoHideDialCode: false,
autoPlaceholder: false,
defaultCountry: "auto",
ipinfoToken: "yolo",
nationalMode: false,
numberType: "MOBILE",
initialCountry: "cm",
preventInvalidNumbers: true,
utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.3/js/utils.min.js"
});


		$(document).ready(function() { 
			$('#submit1').click(function() {  
				$(".error").hide();
				var hasError = false;
				var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
                var passReg = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,20})/;
                var phoneReg = /^\d{7,}$/
		
				var email = $("#email").val();
				var name = $("#name").val();
                var town = $("#town").val();
				var diplome = $("#diploma").val();
				var phone = $("#phone").val();
                var password1 = $("#password1").val();
				var confirmpassword1 = $("#confirmpassword1").val();


				if(email == '') {
					$("#email").after("<span class='error'>Merci d'entrer une adresse mail.</span>");
					hasError = true;
				}
		
				else if(!emailReg.test(email)) {
					$("#email").after('<span class="error">Adresse mail non valide.</span>');
					hasError = true;
				}
				else if(name == '') {
					$("#name").after('<span class="error">Entrer votre Nom.</span>');
					hasError = true;
				}
                else if(town == '') {
					$("#town").after('<span class="error">Merci de renseigner votre ville de résidence.</span>');
					hasError = true;
				}
				else if(diplome == '') {
					$("#diploma").after('<span class="error">Merci de renseigner votre diplome le plus élévé.</span>');
					hasError = true;
				}
                else if(phone == '') {
					$("#phone").after('<span class="error">Merci de renseigner votre numero de téléphone.</span>');
					hasError = true;
				}
                else if(!phoneReg.test(phone.replace(/[\s()-\.]|ext/gi, ''))) {
					$("#phone").after('<span class="error">Merci de renseigner votre numero de téléphone valide.</span>');
					hasError = true;
				}
                else if(password1 == '') {
					$("#password1").after('<span class="error">Merci de renseigner un mot de passe.</span>');
					hasError = true;
				}
				else if(password1.length < 8) {
					$("#password1").after('<span class="error">Un mot de passe doit avoir au moins 8 caracteres.</span>');
					hasError = true;
				}
                else if(!password1.test(confirmpassword1)){
					$("#password1").after('<span class="error">Merci de renseigner des mots de passes identique.</span>');
					hasError = true;
				}
		
				if(hasError == true)
					{ return false; }
		
			});
		});



    $(document).ready(function() { 
        $('#submit2').click(function() {
            $(".error").hide();
            var hasError = false;
            var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
            var passReg = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,20})/;
            var phoneReg = /^\d{7,}$/
    
            var name2 = $("#name2").val();
            var category2 = $("#category2").val();
            var telephone2 = $("#telephone2").val();
            var email2 = $("#email2").val();
            var town2 = $("#town2").val();
            var activite2 = $("#activite2").val();
            var postal2 = $("#postal2").val();

            var rccm = $("#rccm").val();
            var identification = $("#identification").val();
            var logo2 = $("#logo2").val();

            var password2 = $("#password2").val();
            var confirmpassword2 = $("#confirmpassword2").val();

            if(email2 == '') {
                $("#email2").after("<span class='error'>Merci d'entrer une adresse mail.</span>");
                hasError = true;
            }
    
            else if(!emailReg.test(email2)) {
                $("#email2").after('<span class="error">Adresse mail non valide.</span>');
                hasError = true;
            }
            else if(!password2.test(confirmpassword2)){
                $("#password2").after('<span class="error">Merci de renseigner des mots de passes identique.</span>');
                hasError = true;
            }
            else if(name2 == '') {
                $("#name2").after('<span class="error">Entrer votre Nom.</span>');
                hasError = true;
            }
            else if(town2 == '') {
                $("#town2").after('<span class="error">Merci de renseigner le siege social de votre entreprise.</span>');
                hasError = true;
            }
            else if(category2 == '') {
                $("#category2").after('<span class="error">Merci de renseigner la catégorie de votre entrprise.</span>');
                hasError = true;
            }
            else if(logo2 == '') {
                $("#logo2").after('<span class="error">Merci de renseigner un logo.</span>');
                hasError = true;
            }
            else if(rccm == '') {
                $("#rccm").after('<span class="error">Merci de renseigner RCCM.</span>');
                hasError = true;
            }
            else if(activite2 == '') {
                $("#activite2").after("<span class='error'>Renseignez l'activité principale de votre entreprise.</span>");
                hasError = true;
            }
            else if(postal2 == '') {
                $("#postal2").after("<span class='error'>Merci de renseigner l'adresse postale de votre entreprise.</span>");
                hasError = true;
            }

            else if(!phoneReg.test(telephone2.replace(/[\s()-\.]|ext/gi, ''))) {
                $("#telephone2").after('<span class="error">Merci de renseigner votre numero de téléphone valide.</span>');
                console.log(phone)
                hasError = true;
            }
            else if(telephone2 == '') {
                $("#telephone2").after('<span class="error">Merci de renseigner votre numero de téléphone.</span>');
                hasError = true;
            }
            else if(identification == '') {
                $("#identification").after("<span class='error'>Mercir de fournir l'Identification nationale de votre entreprise.</span>");
                hasError = true;
            }
            else if(password2 == '') {
                $("#password2").after('<span class="error">Merci de renseigner un mot de passe.</span>');
                hasError = true;
            }
            else if(confirmpassword2 == '') {
                $("#confirmpassword2").after('<span class="error">Merci de confirmer votre mot de passe.</span>');
                hasError = true;
            }
            else if(password2.length < 8) {
                $("#password2").after('<span class="error">Un mot de passe doit avoir au moins un 8 caracteres.</span>');
                hasError = true;
            }
            
            if(hasError == true)
                { return false; }
    
        });
    });
