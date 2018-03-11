<?php
$headerOpts = [
    'title' => 'Password Locker',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <? core\Template::output('header.php', $headerOpts) ?>
</head>

<body>
<? core\Template::output('nav-bar.php') ?>
    <!-- Fixes the strange chrome/firefox autocomplete spaz bug -->
    <input type="text" name="user" value="" style="display:none;" />
    <input type="password" name="password" value="" style="display:none;" />

    <div id="page-container">

        <div id="locker-app">

            <nav-bar pageTitle="Password Locker">
                <span class="btn btn-success" v-if="hasChanged & !mergeNeeded" @click="saveObject">Save Pending Changes</span>
                <span class="btn btn-warning" v-if="mergeNeeded" @click="mergeObject">Merge Changes</span>
            </nav-bar>

            <div class="container-fluid">
                <div class="row">

                    <div id="sidebar" class="col-xs-12 col-md-2 sidebar">
                        <div class="hidden-sm hidden-md hidden-lg" style="height: 120px;"></div>

                        <ul class="nav nav-sidebar nav-sidebar-sticky">
                            <li>
                                <input id="search" type="search" class="form-control sidebar-search" placeholder="Search" v-model="query" autofocus style="padding-right:2em;">
                                <span class="search-clear" @click="query = ''" v-show="query"><i class="fa fa-times-circle"></i></span>
                            </li>
                            <li :class="{active: !object.id}">
                                <a class="index-anchor" href="#/" tabindex="-1"><span class="fa fa-plus"></span> Add New Locker</a>
                            </li>
                        </ul>
                        <ul class="nav nav-sidebar nav-sidebar-content">
                            <li v-for="(indexItem, key) in index" :class="{active: indexItem.id == object.id}" v-if="!query || search(key)">
                                <a class="index-anchor" :href="'#/' + indexItem.id" tabindex="-1"><i class="fa fa-book"></i> <span v-text="indexItem.name"></span></a>
                            </li>
                        </ul>

                        <div class="text-center hidden-sm hidden-md hidden-lg">
                            <div class="btn btn-link" data-toggle="collapse" data-target="#navbar, #sidebar">Close</div>
                        </div>
                        <hr class="hidden-sm hidden-md hidden-lg" />
                    </div>

                    <form class="col-xs-12 col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main" autocomplete="off" @submit.prevent="">

                        <div class="text-center" v-show="loader">
                            <img src="/img/loader.svg" width="100%">
                        </div>

                        <div v-show="!loader">

                            <div class="alert alert-success" v-if="success">
                                <button type="button" class="close" @click="clearMessages">&times;</button>
                                <span v-html="success"></span>
                            </div>
                            <div class="alert alert-warning" v-if="warning">
                                <button type="button" class="close" @click="clearMessages">&times;</button>
                                <span v-html="warning"></span>
                            </div>
                            <div class="alert alert-danger" v-if="error">
                                <button type="button" class="close" @click="clearMessages">&times;</button>
                                <span v-html="error"></span>
                            </div>

                            <h1 class="page-header">
                                <input type="text" class="form-control input-lg borderless" v-model="object.name" placeholder="Add New Locker" autofocus>
                            </h1>

                            <br />

                            <div class="row clearfix hidden-xs">
                                <div class="col-sm-3">
                                    <div class="text-muted">Title</div>
                                </div>

                                <div class="col-sm-3">
                                    <div class="text-muted">URL</div>
                                </div>

                                <div class="col-sm-2">
                                    <div class="text-muted">User</div>
                                </div>

                                <div class="col-sm-3">
                                    <div class="text-muted">Password</div>
                                </div>
                            </div>
                            <br />

                            <draggable :list="object.items" :options="{handle:'.sort-handle'}">

                                <div class="row clearfix slide-50" v-if="object.items && object.items.length" v-for="(item, key) in object.items">

                                    <div class="col-sm-3 col-xs-5">
                                        <div class="input-group">
                                            <div class="input-group-addon hidden-xs sort-handle">
                                                <span class="fa fa-ellipsis-v"></span>
                                            </div>

                                            <div class="input-group-addon pointer dropdown">
                                                <i class="fa text-primary dropdown-toggle" :class="item.icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="width:15px;"></i>
                                                <div class="dropdown-menu icon-menu">
                                                    <div class="fa icon" v-for="icon in icons" @click="item.icon = icon" :class="icon + (item.icon != icon ? ' text-primary' : ' text-success')"></div>
                                                </div>
                                            </div>

                                            <input type="text" class="form-control borderless" :class="{'alert-info':fieldMatch(item.title)}" v-model="item.title" placeholder="Title" autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="col-sm-3 col-xs-7">
                                        <div class="input-group">
                                            <input type="text" class="form-control borderless" :class="{'alert-info':fieldMatch(item.url)}" v-model="item.url" @focus="highlight" placeholder="URL" autocomplete="off">

                                            <div class="input-group-addon pointer" v-if="item.url && item.url.length">
                                                <span class="fa fa-sign-in btn-link" @click="loginAttempt(item)" tabindex="-1"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-2 col-xs-5">
                                        <input type="text" class="form-control borderless" :class="{'alert-info':fieldMatch(item.user)}" v-model="item.user" @focus="highlight" placeholder="User" autocomplete="off">
                                    </div>

                                    <div class="col-sm-3 col-xs-7">
                                        <div class="input-group">
                                            <input type="password" class="form-control borderless password-mask" placeholder="Password" autocomplete="off" v-model="item.pass" @focus="highlight">

                                            <div class="input-group-addon pointer" data-toggle="popover" data-content="Generates a new 16-character password">
                                                <span class="fa fa-refresh text-warning" @click="generatePassword(key)" tabindex="-1"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-1 col-xs-12">
                                        <div class="input-group-addon pointer" @click="removeItem(key)" data-toggle="popover" data-content="Deletes the corresponding item entry">
                                            <span class="fa fa-trash text-danger pull-right" tabindex="-1"></span>
                                        </div>
                                    </div>

                                </div>

                            </draggable>

                            <div class="row">
                                <div class="col-xs-12">
                                    <br />
                                    <div class="btn btn-link text-success" @click="addItem" data-toggle="popover" data-content="Adds a new key entry"><span class="fa fa-plus"></span> Add</div>
                                </div>
                            </div>

                            <hr />

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel panel-default" :class="{'panel-info':fieldMatch(object.note)}">
                                        <div class="panel-heading">Note</div>
                                        <div class="panel-body">
                                            <textarea class="form-control" rows="10" v-model="object.note" placeholder="Type text here..." tabindex="-1"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr />

                            <div class="row" v-if="object.name && object.name.length">
                                <div class="col-md-3 col-md-offset-9 col-sm-12 text-right">
                                    <span data-toggle="popover" data-content="Deletes the Group permanently. You will be prompted for confirmation.">
                                        <button type="button" class="btn btn-danger btn-lg" data-toggle="modal" data-target="#confirm-delete" tabindex="-1">Delete</button>
                                    </span>
                                    <span data-toggle="popover" data-content="Saves the Group">
                                        <button type="button" class="btn btn-success btn-lg" @click="saveObject">Save</button>
                                    </span>
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
                                    <h5>Delete <span v-text="object.name"></span> Group?</h5>
                                    <div>This action cannot be undone.</div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-danger" @click="deleteObject" data-dismiss="modal">Delete</button>
                                </div>
                                </div>
                            </div>
                        </div>
                        <!-- Delete Modal -->

                    </form>
                </div>

            </div>
        </div>
        <noscript><h1>Javascript is required to use this application.</noscript>

    </div>
</body>

<script type="text/x-template" id="tmpl-nav-bar"><?php include(ROOT . '/html/templates/nav-bar.php'); ?></script>
<script src="/dist/js/build.js"></script>
<script src="/dist/js/locker.js"></script>

</html>
