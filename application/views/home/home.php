<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>FMS</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <!-- <link href="img/favicon.ico" rel="icon"> -->

    <!-- Google Web Fonts -->
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@200;300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <link href="css/style.css" rel="stylesheet">
    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

</head>
<style>
    .dropbtn {
        background-color: #ffffff;
        color: #dc3545;
        padding: 16px;
        font-size: 16px;
        border: none;
        border-radius: 10px;
    }

    .dropdown {
        position: relative;
        display: inline-block;
        margin-top: 22px;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #f1f1f1;
        min-width: 160px;
        box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
        z-index: 1;
    }

    .dropdown-content a {
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
    }

    .dropdown-content a:hover {
        background-color: #ddd;
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }

    .nav-item {
        margin-top: 30px;
    }

    /* .dropdown:hover .dropbtn {background-color: #3e8e41;} */
</style>

<body style="background-image: url('./img/background_image.png');  background-size: cover;">

    <!-- Navbar Start -->
    <div class="container-fluid">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-dark p-0">

            </nav>
        </div>
    </div>
    <!-- Navbar End -->


    <!-- Hero Start -->
    <div class="container-fluid hero-header">
        <div class="container pt-1">

            <div class="row">
                <div class="col-md-8">
                    <img class="img-fluid" src="img/Shasan.png" alt="" style="max-height: 100px;">
                </div>
                <div class="col-md-4 d-flex">
                    <a href="index.html" class="nav-item nav-link text-danger"><b>Home</b></a>
                    <a href="index.html" class="nav-item nav-link text-danger"><b>CONTACT</b></a>
                    <a href="index.html" class="nav-item nav-link text-danger"><b>ABOUT</b></a>
                    <div class="dropdown">
                        <button class="dropbtn"><b>LIST</b></button>
                        <div class="dropdown-content">
                            <a href="#">Link 1</a>
                            <a href="#">Link 2</a>
                            <a href="#">Link 3</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-8">
            <div class="container-fluid  p-5">
                <div class="row" style="box-shadow: 0 1px 1px rgba(0,0,0,0.19), 0 1px 1px rgba(0,0,0,0.23);
                border-radius: 12px;
                padding: 20px 10px 12px; margin-top: -15px;
                background: #f9f9fe;">
                    <h4>PERSONAL DETAILS</h4>
                    <form class="form" id="gst_form">
                        <div class="row text-center">
                            <div class="col-md-1  mt-2">
                                <label class="" for=""><b>GST</b></label>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <input type="text" class="form-control" id="" aria-describedby=""
                                        placeholder="Enter GST ">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary mb-2">Submit</button>
                            </div>
                            <!-- <div class="col-md-1">
                                <button type="submit" class="btn btn-primary mb-2" id="" data-toggle="modal" data-target="#gst_info">Submit</button>
                            </div> -->
                        </div>
                    </form>

                    <form class="form">
                        <div class="row text-center">
                            <div class="col-md-1  mt-2">
                                <label class="" for=""><b>PAN</b></label>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <input type="text" class="form-control" id="" aria-describedby=""
                                        placeholder="Enter PAN ">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary mb-2">Submit</button>
                            </div>
                        </div>
                    </form>

                    <form class="form">
                        <div class="row text-center">
                            <div class="col-md-1  mt-2">
                                <label class="" for=""><b>Account</b></label>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <input type="text" class="form-control" id="" aria-describedby=""
                                        placeholder="Enter Account no. ">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <input type="text" class="form-control" id="" aria-describedby=""
                                        placeholder="Enter IFSC ">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary mb-2">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal" id="myModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">GST DETAILS</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5">
                            <label class="" for=""><b>Company Name :</b></label>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group">
                                <label class="" for="">safex chemical</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <label class="" for=""><b>GST ID :</b></label>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group">
                                <label class="" for="">123456789</label>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function () {
            $("#gst_form").submit(function (event) {
                event.preventDefault();
                $("#myModal").show();
            });

            $(".close").click(function () {
                $("#myModal").hide();
            });
        });

    </script>


</body>

</html>