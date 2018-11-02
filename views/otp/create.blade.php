<!DOCTYPE html>
<html>
    <head>
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
        <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
    </head>
</html>

<body>
    <div class="container be-detail-container">
        <div class="row">
            <div class="col-sm-6 col-sm-offset-3">
                <br>
                <img src="https://cdn2.iconfinder.com/data/icons/luchesa-part-3/128/SMS-512.png" class="img-responsive" style="width:200px; height:200px;margin:0 auto;"><br>

                <h1 class="text-center">SMS Password</h1><br>
                <p class="lead" style="align:center"></p><p>A pasword has been sent to your mobile number. Please enter the password below to proceed.</p>
                <p></p>
                <br>

                <form method="post" id="veryfyotp" action="">
                    <div class="row">
                        <div class="form-group col-sm-8">
                            <span style="color:red;"></span>
                            <input type="text" class="form-control" name="otp" placeholder="Enter your OTP number" required>
                        </div>
                        <button type="submit" class="btn btn-primary  pull-right col-sm-3">Verify</button>
                    </div>
                </form>
                <br><br>
            </div>
        </div>
    </div>
</body>