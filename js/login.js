$(document).ready(function () {
    // Toggle icon highlight
    $('#login_email, #login_pwd').on('focus blur', function () {
        $(this).prev().toggleClass('text-lightblue-500 scale-125');
    })

    // Login form
    $('#login-form').on('submit', function (e) {
        e.preventDefault();

        var email = $('#login_email').val();
        var password = $('#login_pwd').val();
        var rmbMe = $('#remember_me:checked').val();
        
        if (email != '' && password != '') {
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
                    var response = JSON.parse(data);
                    // console.log(data);
                    if (response.status == 'OK') {
                        window.location.href = "index.php";
                    } else {
                        // Display error message
                        $('#errorMessage').removeClass('hidden');
                        $('#errorMessage').html(response.message);

                        // Highlight error fields
                        $('#login_email, #login_pwd').parent().removeClass('border-red-500');
                        if (response.fields != null) {
                            $('#'+response.fields).parent().addClass('border-red-500');
                        }
                    }
                }
            })
        }
    })

    // Rest pwd form
    $('#resetpwd-form').on('submit', function (e) {
        e.preventDefault();
        
        var email = $.trim($('#resetpwd_email').val());
        
        if (email != '') {
            $.ajax({
                url: './includes/reset-pwd.php',
                type: 'POST',
                data: {
                    email: email,
                    function: 'request_passwordReset'
                },
                success: function (data) {
                    response = JSON.parse(data);
                    if (response.status == 200) {alert (response.message)}
                    if (response.status == 404) {alert (response.message)}
                }
            })
        }
    })

    // Change pwd form
    $('#changepwd-form').on('submit', function (e) {
        e.preventDefault();
        
        var selector = $.trim($('#selector').val());
        var validator = $.trim($('#validator').val());
        var new_password = $.trim($('#reset_newpwd').val());
        var confirm_password = $.trim($('#reset_confirm_newpwd').val());

        // alert(selector)
        
        if (new_password != '' || confirm_password != '') {
            $.ajax({
                url: './includes/reset-pwd.php',
                type: 'POST',
                data: {
                    selector: selector,
                    validator: validator,
                    new_password: new_password,
                    confirm_password: confirm_password,
                    function: 'new_password'
                },
                success: function (data) {
                    response = JSON.parse(data);
                    if (response.status == 200) {
                        alert(response.message)
                        window.location.replace('https://yelcloud.com/');
                    } else {
                        alert(response.message)
                    }
                }
            })
        }
    })
})
