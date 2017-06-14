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
            <form class="form-signin" @submit="loginAttempt">
                <div class="alert alert-danger" v-if="error.length" v-html="error"></div>

                <h2 class="form-signin-heading">Please sign in</h2>

                <label>Email:</label>
                <input type="email" class="form-control" placeholder="john.smith@example.io" v-model="email" tabindex="1" required autofocus>
                <br />

                <label>Password:</label>
                <input type="password" class="form-control" placeholder="somepass123" v-model="pass" required>
                <br />

                <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
            </form>
        </script>
        <login-form></login-form>

        <noscript><h1>Javascript is required to use this application.</noscript>

    </div> <!-- /container -->
</body>

<script src="/dist/js/build.js"></script>
<script src="/dist/js/login.js"></script>

</html>
