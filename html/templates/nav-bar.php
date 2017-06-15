<script type="text/x-template" id="tmpl-nav-bar">
    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar, #sidebar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="/#/" tabindex="-1"><?=models\Account::current()->name?></a><span v-if="pageTitle"> - </span><span v-text="pageTitle"></span>
            </div>

            <div class="navbar-collapse collapse">
                <ul class="nav navbar-nav navbar-right hidden-xs">
                    <li class="dropdown">
                        <a class="btn btn-link" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span v-text="'Hi, ' + user.name"></span>
                            <i class="fa fa-gear"></i>
                        </a>

                        <ul class="dropdown-menu" aria-labelledby="dLabel">
                            <li :class="{disabled: pagename == '/locker/'}"><a href="/locker/">Password Locker</a></li>
                            <li role="separator" class="divider"></li>
                            <li :class="{disabled: pagename == '/profile/'}"><a href="/profile/">My Profile</a></li>
                            <li :class="{disabled: pagename == '/users/'}" v-if="hasPermission(10)"><a href="/users/">Manage Users</a></li>
                            <!--<li :class="{disabled: pagename == '/account/'}" v-if="hasPermission(10)"><a href="/account/">Manage Account</a></li>-->
                            <li role="separator" class="divider"></li>
                            <li><a class="pointer" @click="logout" tabindex="-1"><i class="fa fa-sign-out"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
                <div class="navbar-form navbar-right hidden-xs" style="margin-right:100px;">
                    <slot></slot>
                </div>
            </div>

        </div>
    </nav>
</script>