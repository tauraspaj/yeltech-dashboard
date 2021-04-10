$(document).ready(function () {
    // Toggle icon highlight
    $('#login_email, #login_pwd').on('focus blur', function () {
        $(this).prev().toggleClass('text-lightblue-500 scale-125');
    })

    $('#forgotPassword').on('click', function() {
        $('#forgotPasswordDiv').toggleClass('hidden');
    })

    // Submit form
    $('form').on('submit', function (e) {
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
})
