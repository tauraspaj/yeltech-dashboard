$(document).ready(function () {
    // Toggle icon highlight
    $('#login_email, #login_pwd').on('focus blur', function () {
        $(this).prev().toggleClass('text-lightblue-500 scale-125');
    })

    // Checks:
    // Empty fields
    // Email that doesn't exist
    // Incorrect password
    var successChecks = {
        loginCheck: false,
        passwordCheck: false
    }

    // Process email input field
    $('#login_email').on('blur', function () {
        var enteredVal = $(this).val();
        var parent = $(this).parent();
        if (enteredVal == '') {
            parent.addClass('border-red-500');
            successChecks.loginCheck = false;
        } else {
            parent.removeClass('border-red-500');

            $.ajax({
                url: './includes/login.inc.php',
                type: 'POST',
                data: {
                    email: enteredVal,
                    function: 'checkEmail'
                },
                success: function (data) {
                    if (data != 1) {
                        parent.addClass('border-red-500');
                        successChecks.loginCheck = false;
                    } else {
                        parent.removeClass('border-red-500');
                        successChecks.loginCheck = true;
                    }

                }
            })
        }
    })

    // Process password field
    $('#login_pwd').on('blur', function () {
        if ($(this).val() == '') {
            $(this).parent().addClass('border-red-500');
            successChecks.passwordCheck = false;
        } else {
            $(this).parent().removeClass('border-red-500');
            successChecks.passwordCheck = true;
        }
    })

    $('#login_email, #login_pwd').keypress(function(e) {
        console.log(e);
        if(e.which == 13) {
            $('form').submit();
        }
    });

    // Submit form
    $('form').on('submit', function (e) {
        e.preventDefault();
        if (!(Object.values(successChecks).indexOf(false) > -1)) {
            var email = $('#login_email').val();
            var password = $('#login_pwd').val();
            var rmbMe = $('#remember_me:checked').val();

            $.ajax({
                url: './includes/login.inc.php',
                type: 'POST',
                data: {
                    email: email,
                    password: password,
                    rmbMe: rmbMe,
                    function: 'loginUser'
                },
                success: function (data) {
                    if (data == 'Success') {
                        window.location.href = "index.php";
                    } else if (data == 'Wrong Password') {
                        $('#login_pwd').parent().addClass('border-red-500');
                    }
                }
            })
        }
    })
})
