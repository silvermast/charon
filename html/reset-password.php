<!DOCTYPE html>
<html lang="en">
<head>
<? core\Template::output('header.php', ['title' => 'Reset Password']); ?>
</head>

<body class="bg-gray">
    <? core\Template::output('nav-bar.php'); ?>

    <div id="page-container" class="container">
        <template>
            <div class="row">
                <div class="col-sm-12 col-md-9 col-md-offset-1">

                    <div v-if="!profile || !profile.id" class="card ph-20">
                        <h1>The Token you are using is not valid.</h1>
                        <p>We recommend requesting another password reset and trying with a fresh token.</p>
                        <br>
                        <p><a href="/"><i class="fa fa-sign-in"></i> Log In</a></p>
                    </div>
                    <div v-else-if="!profile.sq1Q || !profile.sq2Q || !profile.sq3Q" class="card ph-20">
                        <h1>This User has no Security Questions!</h1>
                        <p>Unfortunately, we are unable to help you reset your password.</p>
                        <br>
                        <p><a href="/"><i class="fa fa-sign-in"></i> Log In</a></p>
                    </div>

                    <form v-else class="card ph-20" @submit.prevent="resetPassword">
                        <h1>Please answer the following Security Questions.</h1>
                        <br><br>
                        <ol>
                            <li class="form-group" :class="{'has-error': errors.sq1A}">
                                <div v-text="profile.sq1Q"></div>
                                <input type="text" class="form-control borderless" v-model="sq1A" @change="$set(errors, 'sq1A', null)" required autocomplete="off">
                                <small class="help-block text-danger" v-if="errors.sq1A">Incorrect answer.</small>
                            </li>
                            <li class="form-group" :class="{'has-error': errors.sq2A}">
                                <div v-text="profile.sq2Q"></div>
                                <input type="text" class="form-control borderless" v-model="sq2A" @change="$set(errors, 'sq2A', null)" required autocomplete="off">
                                <small class="help-block text-danger" v-if="errors.sq2A">Incorrect answer.</small>
                            </li>
                            <li class="form-group" :class="{'has-error': errors.sq3A}">
                                <div v-text="profile.sq3Q"></div>
                                <input type="text" class="form-control borderless" v-model="sq3A" @change="$set(errors, 'sq3A', null)" required autocomplete="off">
                                <small class="help-block text-danger" v-if="errors.sq3A">Incorrect answer.</small>
                            </li>
                        </ol>
                        <br>

                        <div class="row" v-if="showPasswords">
                            <div class="col-xs-6 col-xs-offset-3">
                                <h3>You can now enter your new password.</h3>
                                <div class="form-group" :class="{'has-error': !passwordVerify}">
                                    <input type="password" class="form-control borderless" placeholder="Change Password" v-model="changePass1">
                                    <label class="small text-muted">Change Password</label>
                                    <small class="help-block" v-if="!passwordVerify">Password must be at least 12 characters long. Type whatever you'd like, though!</small>
                                </div>
                                <div class="form-group" :class="{'has-error': !passwordsMatch}">
                                    <input type="password" class="form-control borderless" placeholder="Verify Password" v-model="changePass2">
                                    <label class="small text-muted">Verify Password</label>
                                    <small class="help-block" v-if="!passwordsMatch">The passwords you've entered don't match!</small>
                                </div>
                            </div>
                        </div>

                        <br>
                        <div class="row">
                            <div class="col-xs-6 col-xs-offset-3 text-center">
                                <button class="btn btn-primary btn-lg btn-block" v-if="!isBusy"><i class="fa fa-check"></i> Submit</button>
                                <button class="btn btn-primary btn-lg btn-block disabled" v-if="isBusy"><i class="fa fa-cog fa-spin"></i> Please wait</button>
                            </div>
                        </div>
                        <br><br><br>
                    </form>

                </div>
            </div>
        </template>


        <noscript><h1>Javascript is required to use this application.</noscript>
    </div> <!-- /container -->
</body>

<script src="/src/js/build.js"></script>
<script src="/src/js/reset-password.js"></script>

</html>
