<!DOCTYPE html>
<html lang="en">
<head>
<? core\Template::output('header.php', ['title' => 'Manage Users']); ?>
</head>

<body>
    <? core\Template::output('nav-bar.php'); ?>
    <noscript><h1>Javascript is required to use this application.</h1></noscript>
    <!-- Fixes the strange chrome/firefox autocomplete spaz bug -->
    <input type="text" name="user" value="" style="display:none;" />
    <input type="password" name="password" value="" style="display:none;" />

    <div id="page-container">

        <div id="users-app">

            <nav-bar pageTitle="Manage Users">
                <span class="btn btn-success" v-if="hasChanged" @click="saveObject()">Save Pending Changes</span>
            </nav-bar>

            <div class="container-fluid">
                <div class="row">

                    <div id="sidebar" class="col-xs-12 col-md-2 sidebar">
                        <div class="hidden-sm hidden-md hidden-lg" style="height: 120px;"></div>

                        <ul class="nav nav-sidebar">
                            <li>
                                <input id="search" type="search" class="form-control sidebar-search" placeholder="Search" v-model="query" autofocus style="padding-right:2em;">
                                <span class="search-clear" @click="query = ''" v-show="query.length !== 0"><i class="fa fa-times-circle"></i></span>
                            </li>
                            <li :class="{active: !object.id}">
                                <a class="index-anchor" href="#/" tabindex="-1"><span class="fa fa-plus"></span> Add New User</a>
                            </li>
                            <hr />
                            <li v-for="(indexUser, key) in users" :class="{active: indexUser.id == object.id}" v-if="query.length === 0 || search(key)">
                                <a class="index-anchor" :href="'#/' + indexUser.id" tabindex="-1"><i class="fa fa-book"></i> <span v-text="indexUser.name"></span></a>
                            </li>
                        </ul>

                        <div class="text-center hidden-sm hidden-md hidden-lg">
                            <div class="btn btn-link" data-toggle="collapse" data-target="#navbar, #sidebar">Close</div>
                        </div>
                        <hr class="hidden-sm hidden-md hidden-lg" />
                    </div>

                    <div class="col-xs-12 col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

                        <div class="text-center" v-show="loader">
                            <img src="/img/loader.svg" width="100%">
                        </div>

                        <div class="alert alert-success" v-if="success.length">
                            <button type="button" class="close" @click="clearMessages">&times;</button>
                            <span v-text="success"></span>
                        </div>
                        <div class="alert alert-danger" v-if="error.length">
                            <button type="button" class="close" @click="clearMessages">&times;</button>
                            <span v-text="error"></span>
                        </div>

                        <h3 v-if="object.id">Editing User <samp v-text="object.id"></samp></h3>
                        <h3 v-else>New User</h3>
                        <hr />

                        <div class="col-xs-12 col-sm-8 col-md-6 col-lg-4">

                            <div class="form-group">
                                <input type="text" class="form-control borderless" v-model="object.name" />
                                <label class="small text-muted">Name</label>
                            </div>

                            <div class="form-group">
                                <input type="email" class="form-control borderless" v-model="object.email" />
                                <label class="small text-muted">Email</label>
                            </div>

                            <div class="form-group" :class="{'has-error': !passwordVerify || !passwordsMatch}">
                                <button class="btn btn-primary btn-sm" v-if="!changingPassword" @click="startChangePassword()">Change Password</button>
                                <div v-if="changingPassword">
                                    <input type="password" class="form-control borderless" v-model="changePass1" />
                                    <label class="small text-muted">New Password</label>
                                    <small class="help-block" v-if="!passwordVerify">Password must be at least 12 characters long. Type whatever you'd like, though!</small>

                                    <input type="password" class="form-control borderless" v-model="changePass2" />
                                    <label class="small text-muted">Verify Password</label>
                                    <small class="help-block" v-if="!passwordsMatch">The passwords you've entered don't match!</small>

                                    <br /><br />
                                    <button class="btn btn-default btn-sm" v-if="object.id" @click="cancelChangePassword()">Cancel</button>
                                </div>
                                <br />
                            </div>

                            <div class="form-group">
                                <select class="form-control borderless pointer" v-model="object.permLevel">
                                    <option v-for="(optionName, optionId) in userLevels" :value="optionId" v-text="optionName"></option>
                                </select>
                                <label class="small text-muted">Permission Level</label>
                            </div>

                            <hr />
                            <button class="btn btn-success" @click="saveObject()">Save</button>
                            <button class="btn btn-danger" data-toggle="modal" data-target="#confirm-delete" v-if="object.id">Delete</button>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Delete Modal -->
            <div class="modal" id="confirm-delete">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">Are you sure?</h4>
                        </div>
                        <div class="modal-body">
                            <h5>Delete <span v-text="object.name"></span> User?</h5>
                            <div>This action cannot be undone.</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger" @click="deleteObject" data-dismiss="modal">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Delete Modal -->


        </div>

    </div>
</body>

<script src="/dist/js/build.js"></script>
<script src="/dist/js/users.js"></script>

</html>
