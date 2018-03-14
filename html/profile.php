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

                <h1 class="page-header">My Profile</h1>

                <hr />

                <div class="row">
                    <div class="col-sm-12 col-md-4">
                        <div class="form-group">
                            <input type="text" class="form-control borderless" v-model="profile.id" readonly>
                            <label class="small text-muted">User ID</label>
                        </div>

                        <div class="form-group">
                            <input type="text" class="form-control borderless" placeholder="Kylo" v-model="profile.name" required>
                            <label class="small text-muted">User Name</label>
                        </div>

                        <div class="form-group">
                            <input type="email" class="form-control borderless" placeholder="kylo.ren@republic.co" v-model="profile.email" required>
                            <label class="small text-muted">Email Address</label>

                        </div>

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
                    <div class="col-sm-12 col-md-8">

                        <template v-if="changingSQ">

                            <ol>
                                <li class="form-group">
                                    <div>
                                        <i class="fa fa-refresh text-success pointer mh-10" @click="$set(changeSQ.q1, 'q', getRandomQuestion())"></i>
                                        <span class="pointer" data-toggle="dropdown" data-target="#sqDropdown1">
                                            <span v-text="changeSQ.q1.q"></span>
                                            <i class="fa fa-caret-down"></i>
                                        </span>
                                        <div id="sqDropdown1" class="dropdown">
                                            <ul class="dropdown-menu">
                                                <li v-for="sqOpt in sqOptions"><a v-text="sqOpt" @click="$set(changeSQ.q1, 'q', sqOpt)"></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <input type="text" class="form-control borderless" v-model="changeSQ.q1.a" required>
                                </li>
                                <li class="form-group">
                                    <div>
                                        <i class="fa fa-refresh text-success pointer mh-10" @click="$set(changeSQ.q2, 'q', getRandomQuestion())"></i>
                                        <span class="pointer" data-toggle="dropdown" data-target="#sqDropdown2">
                                            <span v-text="changeSQ.q2.q"></span>
                                            <i class="fa fa-caret-down"></i>
                                        </span>
                                        <div id="sqDropdown2" class="dropdown">
                                            <ul class="dropdown-menu">
                                                <li v-for="sqOpt in sqOptions"><a v-text="sqOpt" @click="$set(changeSQ.q2, 'q', sqOpt)"></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <input type="text" class="form-control borderless" v-model="changeSQ.q2.a" required>
                                </li>
                                <li class="form-group">
                                    <div>
                                        <i class="fa fa-refresh text-success pointer mh-10" @click="$set(changeSQ.q3, 'q', getRandomQuestion())"></i>
                                        <span class="pointer" data-toggle="dropdown" data-target="#sqDropdown3">
                                            <span v-text="changeSQ.q3.q"></span>
                                            <i class="fa fa-caret-down"></i>
                                        </span>
                                        <div id="sqDropdown3" class="dropdown">
                                            <ul class="dropdown-menu">
                                                <li v-for="sqOpt in sqOptions"><a v-text="sqOpt" @click="$set(changeSQ.q3, 'q', sqOpt)"></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <input type="text" class="form-control borderless" v-model="changeSQ.q3.a" required>
                                </li>
                            </ol>

                            <br>
                            <button type="button" class="btn btn-default" @click="changingSQ = false"><i class="fa fa-ban"></i> Cancel changing my Security Questions</button>
                        </template>

                        <button type="button" class="btn btn-info" v-if="!changingSQ" @click="changingSQ = true"><i class="fa fa-lock"></i> Change my Security Questions</button>
                        <span class="btn btn-link text-warning" data-toggle="modal" data-target="#why-sq-modal"><i class="fa fa-info-circle"></i> Why do I need security questions, anyway?</span>

                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 text-right">
                        <div class="form-group">
                            <button class="btn btn-success btn-lg" @click="saveObject" style="width: 300px;">Save</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <noscript><h1>Javascript is required to use this application.</h1></noscript>

    </div>

    <div id="why-sq-modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Why do I need security questions?</h4>
                </div>
                <div class="modal-body">
                    <p class="text-info">SilverMast is built in such a way that all private data is only accessible through authenticated users. As a result, <u>we will not be able to reset your password in the event it is forgotten.</u> To alleviate this, we've built a recovery procedure which will help you recover forgotten passwords.</p>
                    <br>
                    <ul>
                        <li>Don't worry about capitalization.</li>
                        <li>Make sure you choose answers that you will remember.</li>
                        <li>It's better if you lie. But don't forget that you've lied!</li>
                        <li>We don't store your answers. They're <a href="https://en.wikipedia.org/wiki/Cryptographic_hash_function" target="_blank">securely hashed</a> and stored away for safe-keeping.</li>
                        <li><strong>Tip:</strong> Don't make things topical. You probably won't remember you put "demagorgon" down for your favorite animal 2 or 3 years from now.</li>
                        <li><strong>Tip:</strong> Try to keep your answer at 3 words or fewer.</li>
                        <li><strong>Tip:</strong> Don't do too much research for your answer. If you've never thought about your favorite astronomical body before, pick a different question!</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
</body>

<script src="/src/js/build.js"></script>
<script src="/src/js/profile.js"></script>

</html>
