<?php
$headerOpts = [
    'title' => 'Login',
    'css' => ['/src/css/login.css']
];
?>
<!doctype html>
<html lang="en">
<head>
    <? core\Template::output('header.php', $headerOpts) ?>
</head>

<!-- App body -->
<body>

    <div id="page-container" class="container">

        <!-- Login Form template -->
        <script type="text/x-template" id="login-form-template">
            <div>

                <form class="form-signin" @submit.prevent="loginAttempt" v-if="page == 'login'">

                    <h2 class="form-signin-heading">Please sign in</h2>
                    <br>

                    <label>Email:</label>
                    <input type="email" class="form-control" placeholder="john.smith@example.io" v-model="email" tabindex="1" required autofocus>
                    <br />

                    <label>Password:</label>
                    <input type="password" class="form-control" placeholder="somepass123" v-model="pass" required>
                    <br />
                    <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>

                    <br><br>
                    <div class="text-center">
                        <button type="button" class="btn btn-link text-primary" @click="page = 'request-password-reset'">Forgot your password?</button>
                    </div>
                </form>

                <form class="form-signin" @submit.prevent="requestPasswordReset" v-else-if="page == 'request-password-reset'">
                    <h2 class="form-signin-heading">Forgot your password?</h2>
                    <br>

                    <label>What's your Email?</label>
                    <input type="email" class="form-control" placeholder="john.smith@example.io" v-model="email" tabindex="1" required autofocus>
                    <br />

                    <button class="btn btn-lg btn-primary btn-block" type="submit" v-if="!isBusy"><i class="fa fa-check"></i> Submit Request</button>
                    <button class="btn btn-lg btn-primary btn-block disabled" type="button" v-if="isBusy"><i class="fa fa-cog fa-spin"></i> Please wait</button>

                    <br><br>
                    <div class="text-center">
                        <button type="button" class="btn btn-link text-primary" @click="page = 'login'">Log In</button>
                    </div>
                </form>

            </div>
        </script>
        <login-form></login-form>

        <noscript><h1>Javascript is required to use this application.</noscript>

    </div> <!-- /container -->
</body>

<script src="/dist/js/build.js"></script>
<script src="/dist/js/login.js"></script>

</html>
