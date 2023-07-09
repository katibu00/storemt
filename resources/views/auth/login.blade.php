<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="MyShopMate">
    <meta name="generator" content="Hugo 0.108.0">
    <title>Signin · MyShopMate</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <meta name="theme-color" content="#712cf9">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.css"
    integrity="sha512-Mo79lrQ4UecW8OCcRUZzf0ntfMNgpOFR46Acj2ZtWO8vKhBvD79VCp3VOKSzk6TovLg5evL3Xi3u475Q/jMu4g=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            width: 300px;
            margin: 0 auto;
            margin-top: 100px;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
        }

        .card h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .card form {
            margin-bottom: 20px;
        }

        .card .form-floating {
            margin-bottom: 10px;
        }

        .card .checkbox {
            margin-bottom: 10px;
        }

        .card .btn-primary {
            background-color: #712cf9;
            border-color: #712cf9;
        }

        .card .btn-primary:hover {
            background-color: #5d23d4;
            border-color: #5d23d4;
        }

        .card .btn-primary:focus {
            box-shadow: none;
        }

        .card .mt-5 {
            margin-top: 20px;
        }

        .card input[type="email"],
        .card input[type="password"] {
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #f1f3f5;
        }

        .card input[type="email"]:focus,
        .card input[type="password"]:focus {
            outline: none;
            box-shadow: none;
            background-color: #e9ecef;
        }

        .card label {
            margin-bottom: 5px;
        }

        .card .alert {
            margin-bottom: 10px;
        }
        .card .form-floating {
    position: relative;
}

.card .form-floating .password-toggle {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    cursor: pointer;
    z-index: 100;
}



    </style>
</head>

<body>

    <div class="card">
        <h1 class="h3 mb-3 fw-normal">Please sign in</h1>
        <ul id="error_list"></ul>
        <form id="loginForm">
            <div class="form-floating">
                <input type="text" class="form-control" id="email_or_phone" name="email_or_phone" placeholder="Enter your Email or Phone number">
                <label for="email_or_phone">Email/Phone Number</label>
            </div>
            <div class="form-floating position-relative">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                <label for="password">Password</label>
                <span class="password-toggle" onclick="togglePasswordVisibility()"><i class="fa fa-eye"></i></span>
            </div>
            

            <div class="checkbox mb-3">
                <label>
                    <input type="checkbox" id="remember"> Remember me
                </label>
            </div>
            <button class="w-100 btn btn-lg btn-primary" type="submit" id="submit_btn">Sign in</button>
        </form>
        <p class="mt-5 mb-3 text-muted">&copy; ShopMate 2023</p>
    </div>

    <script src="/jquery-3.6.3.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <script>
        function togglePasswordVisibility() {
            var passwordInput = document.getElementById("password");
            var eyeIcon = document.querySelector(".password-toggle i");
    
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
            } else {
                passwordInput.type = "password";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
            }
        }
    </script>


    <script>
         $(document).ready(function() {

            $('#loginForm').submit(function(event) {
                event.preventDefault();
                var submitButton = $(this).find('button[type="submit"]');
                submitButton.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...'
                );

                var formData = new FormData(this);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: '/login',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        submitButton.prop('disabled', false).text('Login');

                        if (response.success) {
                            toastr.success('Login successful. Redirecting to dashboard...');
                            setTimeout(function() {
                                window.location.href = response.redirect_url;
                            }, 200);
                        } else {
                            toastr.error('Invalid credentials.');
                        }
                    },
                    error: function(xhr, status, error) {
                        submitButton.prop('disabled', false).text('Login');

                        var response = xhr.responseJSON;
                        if (response && response.errors && response.errors.login_error) {
                            toastr.warning(response.errors.login_error[0]);
                        } else if (response && response.message) {
                            toastr.error(response.message);
                        } else {
                            toastr.error('An error occurred. Please try again.');
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>
