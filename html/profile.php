<!DOCTYPE html>
<html lang="en">
<head>
<? core\Template::output('header.php', ['title' => 'Profile']); ?>
</head>

<body>
    <? core\Template::output('nav-bar.php'); ?>

    <!-- Fixes the strange chrome/firefox autocomplete spaz bug -->
    <input type="text" name="user" value="" style="display:none;" />
    <input type="password" name="password" value="" style="display:none;" />

    <div id="page-container">

        <div id="locker-app">

            <nav-bar pageTitle="Profile"></nav-bar>

            <div class="container">

                <div class="text-center" v-show="loader">
                    <img src="/img/loader.svg" width="100%">
                </div>

                <br v-if="success.length || error.length">
                <div class="alert alert-success" v-if="success.length">
                    <button type="button" class="close" @click="clearMessages">&times;</button>
                    <span v-text="success"></span>
                </div>
                <div class="alert alert-danger" v-if="error.length">
                    <button type="button" class="close" @click="clearMessages">&times;</button>
                    <span v-text="error"></span>
                </div>

                <h1 class="page-header">My Profile</h1>

                <hr />

                <div class="row">
                    <div class="col-xs-12 col-sm-8 col-md-6 col-lg-4">
                        <div class="form-group">
                            <input type="text" class="form-control borderless" v-model="profile.id" readonly>
                            <label class="small text-muted">User ID</label>
                        </div>

                        <div class="form-group">
                            <input type="text" class="form-control borderless" id="<?=$u?>" placeholder="Kylo" v-model="profile.name" required>
                            <label class="small text-muted" for="<?=$u=uniqid('label')?>">User Name</label>
                        </div>

                        <div class="form-group">
                            <input type="email" class="form-control borderless" id="<?=$u?>" placeholder="kylo.ren@republic.co" v-model="profile.email" required>
                            <label class="small text-muted" for="<?=$u=uniqid('label')?>">Email Address</label>
                        </div>

                        <div class="form-group" :class="{'has-error': !passwordVerify}">
                            <input type="password" class="form-control borderless" id="<?=$u?>" placeholder="Change Password" v-model="changePass1">
                            <label class="small text-muted" for="<?=$u=uniqid('label')?>">Change Password</label>
                            <small class="help-block" v-if="!passwordVerify">Password must be at least 12 characters long. Type whatever you'd like, though!</small>
                        </div>
                        <div class="form-group" :class="{'has-error': !passwordsMatch}">
                            <input type="password" class="form-control borderless" placeholder="Verify Password" v-model="changePass2">
                            <label class="small text-muted" for="<?=$u=uniqid('label')?>">Verify Password</label>
                            <small class="help-block" v-if="!passwordsMatch">The passwords you've entered don't match!</small>
                        </div>

                        <br /><br />
                        <div class="form-group">
                            <button class="btn btn-success btn-lg" @click="saveObject">Save</button>
                        </div>

                    </div>
                </div>
            </div>

        </div>
        <noscript><h1>Javascript is required to use this application.</h1></noscript>

    </div>
</body>

<script src="/src/js/build.js"></script>
<script src="/src/js/profile.js"></script>

</html>
