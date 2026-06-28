<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <title>e-regio</title>
    <link rel="icon" type="image/x-icon" href="{{asset('assets/img/favicon.ico')}}"/>
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <style>body{font-family:'Segoe UI',system-ui,-apple-system,BlinkMacSystemFont,'Helvetica Neue',Arial,sans-serif}</style>
    <link href="{{asset('bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/plugins.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/authentication/form-2.css')}}" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/forms/theme-checkbox-radio.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/forms/switches.css')}}">
</head>
<style>
.error{
    color:red  !important;
}
</style>
<body class="form">

@yield('content')

    <!-- BEGIN GLOBAL MANDATORY SCRIPTS -->
    <script src="{{asset('assets/js/libs/jquery-3.1.1.min.js')}}"></script>
    <script src="{{asset('bootstrap/js/popper.min.js')}}"></script>
    <script src="{{asset('bootstrap/js/bootstrap.min.js')}}"></script>
    
    <!-- END GLOBAL MANDATORY SCRIPTS -->
    <script src="{{asset('assets/js/authentication/form-2.js')}}"></script>

    <script src="{{asset('assets/js/jquery.validate.min.js')}}"></script>

    @yield ('javascript')  
    <script type="text/javascript">
    $(document).ready(function () {
        $(document).on('click', '.submit_login_form', function (e) {
            e.preventDefault();
            $("form#login_form").validate({
                rules: {
                    username: { required: true },
                    password: { required: true},
                },
                messages: {
                    username: { 
                        required: "Please enter username",
                    },
                    password: { required: "Please enter password"},
                },
                focusInvalid: true,
                invalidHandler: function () {
                    $(this).find(":input.error:first").focus();
                }
            });
            if ($("form#login_form").valid()) {
                $("form#login_form").submit();
            }
        });
    });
    //close the alert after 3 seconds.
    $(document).ready(function(){
            setTimeout(function() {
                $(".alert").alert('close');
            }, 3000);
    	});
</script>
</body>
</html>