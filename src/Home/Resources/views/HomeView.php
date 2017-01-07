<?php
namespace Home\Resources\Views;

class HomeView
{
    public function head($basePath = "", $cssPath = "", $vendorPath = "")
    {
    ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Simulation-A Biometric Based Three Factor Remote User Authentication Using Smart Card</title>
            <link rel="stylesheet" href="<?php echo $vendorPath; ?>bootstrap/css/bootstrap.min.css" />
            <link rel="stylesheet" href="<?php echo $vendorPath; ?>fontawesome/css/font-awesome.min.css" />
            <link rel="stylesheet" href="<?php echo $cssPath; ?>custom.css">
            <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
            <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
            <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
            <![endif]-->

        </head>
        <body>

        <div class="container-fluid top-bar">

            <nav class="navbar navbar-default navbar-fixed-top">
                <div class="container-fluid">
                    <!-- Brand and toggle get grouped for better mobile display -->
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                    </div>

                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                        <ul class="nav navbar-nav">
                            <li class="active"><a href="<?php echo $basePath; ?>?route=home">Home <span class="sr-only">(current)</span></a></li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Create <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?php echo $basePath; ?>?route=create-server">Server</a></li>
                                    <li><a href="<?php echo $basePath; ?>?route=create-user">User</a></li>
                                </ul>
                            </li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Registration <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?php echo $basePath; ?>?route=register-server">Server</a></li>
                                    <li><a href="<?php echo $basePath; ?>?route=user-registration">User</a></li>
                                </ul>
                            </li>
                            <li><a href="<?php echo $basePath; ?>?route=login">Login & Authentication</a></li>
                            <li><a href="<?php echo $basePath; ?>?route=password-change">Password Change</a></li>

                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Attacks<span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?php echo $basePath; ?>?route=impersonation-attack">Impersonation Attack</a></li>
                                    <li><a href="<?php echo $basePath; ?>?route=server-masquerading-attack">Server Masquerading Attack</a></li>
                                    <li><a href="<?php echo $basePath; ?>?route=reply-attack">Reply Attack</a></li>
                                </ul>
                            </li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Recovery <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?php echo $basePath; ?>?route=password-recovery">Password</a></li>
                                    <li><a href="<?php echo $basePath; ?>?route=smart-card-recovery">Smart Card</a></li>
                                </ul>
                            </li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Registration Center<span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?php echo $basePath; ?>?route=registered-servers">Registered Server</a></li>
                                    <li><a href="<?php echo $basePath; ?>?route=registered-users">Registered User</a></li>
                                </ul>
                            </li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Server<span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?php echo $basePath; ?>?route=login-log">Login Log</a></li>
                                </ul>
                            </li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Attacker<span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?php echo $basePath; ?>?route=data-center">Data Center</a></li>
                                </ul>
                            </li>

                        </ul>

                    </div><!-- /.navbar-collapse -->
                </div><!-- /.container-fluid -->
            </nav>&nbsp;

        </div>
    <?php
    }

    public function body()
    {
    ?>
        <div class="container-fluid" id="main-window">

            <div class="container">

                <h2>Simulation</h2><br/><h3>A Biometric Based Three Factor Remote User Authentication Using Smart Card</h3>

            </div>

        </div>

        <?php
    }


    public function foot($jsPath = "", $vendorPath = "")
    {
    ?>
                <script type="text/javascript" src="<?php echo $vendorPath; ?>jquery/jquery.js"></script>
                <script type="text/javascript" src="<?php echo $vendorPath; ?>bootstrap/js/bootstrap.min.js"></script>
                <script type="text/javascript" src="<?php echo $jsPath; ?>custom.js"></script>

            </body>

        </html>
    <?php
    }
}
