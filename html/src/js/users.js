if (location.pathname != '/users/') location.pathname = '/users/';

// returns the locker ID from the location hash
function getUserId() {
    return location.hash.replace(/^[#!\/]*/g, '');
}

// default Locker Object schema
function getBlankUser() {
    return {
        id: '',
        name: '',
        email: '',
        permLevel: 20,
        changePass1: '',
        changePass2: '',
    }
}

/**
 * Main Vue component
 */
var usersApp = new Vue({
    el: '#users-app',
    data: {

        // display messages
        loader: true,
        query: '',
        users: [],
        me: {},

        object: getBlankUser(),
        objectHash: false,
        changingPassword: false,
        changePass1: null,
        changePass2: null,

        userLevels: {
            1: 'Owner',
            10: 'Admin',
            20: 'Member',
        },

        timeouts: {},
    },
    created: function() {
        var vm = this;
        vm.loadIndex();
        vm.loadObject();

        $.get('/profile', function(result) {
            vm.me = $.extend(vm.me, json_decode(AES.decrypt(result)));
            vm.me.permLevel = parseInt(vm.me.permLevel);
            vm.$forceUpdate();
        });
    },
    computed: {
        hasChanged: function() {
            return this.objectHash !== md5(json_encode(this.object));
        },
        passwordChange: function() {
            return this.changePass1.length > 0 && this.changePass2.length > 0;
        },
        passwordVerify: function() {
            return this.changePass1.length === 0 || this.changePass1.length > 12;
        },
        passwordsMatch: function() {
            return this.changePass1.length === 0 || this.changePass1 === this.changePass2;
        },
        canEdit: function() {
            if (this.me.id === this.object.id)
                return true;

            switch (this.me.permLevel) {
                case 1: // owners can edit anybody
                    return true;

                case 10: // admins can edit members and other admins
                    return !this.object.id || parseInt(this.object.permLevel) !== 1;

                case 20:
                    return false;

                default:
                    return false;
            }
        },
        objectIsMe: function() {
            return this.me && this.object && this.me.id === this.object.id;
        },
    },

    methods: {

        // clears & resets messages
        clearMessages: function() {
            this.error = this.success = '';
        },

        // Sets the object as a blank object
        resetObject: function() {
            this.object           = getBlankUser();
            this.hasChanged       = false;
            this.objectHash       = md5(json_encode(this.object));
            this.changingPassword = true;
        },

        loadIndex: function() {
            var scope = this;
            $.get({
                url: '/users',
                success: function(result) {
                    scope.users = json_decode(result);
                    scope.toggleLoader(false);
                }
            });
        },

        loadObject: function() {

            var scope = this;
            scope.toggleLoader(true);
            scope.clearMessages();
            scope.cancelChangePassword();

            var userId = getUserId();

            // if we're adding a new user
            if (!userId.length) {
                scope.toggleLoader(false);
                scope.resetObject();
                scope.changingPassword = true;
                return;
            }

            // send or pull the object
            $.get({
                url: '/users/' + userId,
                success: function(data) {
                    // no user was found
                    if (!data) {
                        location.hash = '#/';
                        scope.loadObject();
                        return;
                    }

                    scope.object = json_decode(data);

                    scope.cancelChangePassword();
                    scope.objectHash = md5(json_encode(scope.object));
                    scope.hasChanged = false;
                    scope.toggleLoader(false);

                },
                error: function(jqXHR) {
                    if (code == 401) {
                        location.reload();
                        return;
                    }
                    scope.error = jqXHR.responseText;
                    scope.toggleLoader(false);
                    scope.resetObject();
                }
            });

        },

        // Saves the Locker object
        saveObject: function() {
            var scope = this;
            scope.toggleLoader(true);
            scope.clearMessages();

            if (scope.passwordChange) {
                // passwords must match
                if (!scope.passwordsMatch) {
                    scope.error = 'Passwords do not match.';
                    return;
                }

                UserKeychain.setPassword(scope.changePass1);
                scope.object.passhash            = UserKeychain.PassHash;
                scope.object.contentKeyEncrypted = UserKeychain.getContentKeyEncrypted();
            }


            $.post({
                url: '/users/' + scope.object.id,
                data: json_encode(scope.object),
                success: function(result) {
                    // Set the data into the object
                    scope.object = json_decode(result);

                    // set the hash id
                    location.hash = '#/' + scope.object.id;

                    scope.loadIndex();
                    scope.toggleLoader(false);
                    scope.cancelChangePassword();
                    // scope.hasChanged = false;
                    scope.objectHash = md5(json_encode(scope.object));

                    // set success message
                    Alerts.success('Successfully saved the user');

                },
                error: function(jqXHR) {
                    if (jqXHR.status == 401) {
                        location.reload();
                        return;
                    }

                    scope.error = jqXHR.responseText;
                    scope.toggleLoader(false);
                    console.log(scope.error);
                }

            });
        },

        // deletes the provided user
        deleteObject: function() {
            var scope = this;

            $.ajax({
                method: 'delete',
                url: '/users/' + scope.object.id,
                success: function(result) {
                    // set success message
                    scope.resetObject();
                    location.hash = '#/';
                    scope.loadIndex();

                    Alerts.success('Successfully deleted the user');
                },
                error: function(jqXHR) {
                    if (jqXHR.status == 401) {
                        location.reload();
                        return;
                    }

                    scope.error = jqXHR.responseText;
                    scope.toggleLoader(false);
                }
            });
        },

        startChangePassword: function() {
            this.changePass1 = '';
            this.changePass2 = '';
            this.changingPassword   = true;
        },
        cancelChangePassword: function() {
            this.changePass1 = '';
            this.changePass2 = '';
            this.changingPassword   = false;
        },

        // Turns the loader on after a slight delay Or turns it off and clears the timeout
        toggleLoader: function(toggle) {
            var scope = this;
            if (toggle) {
                scope.timeouts.loader = setTimeout(function() {
                    scope.loader = true;
                }, 200);

            } else {
                scope.loader = false;
                clearTimeout(scope.timeouts.loader);
                window.scrollTo(0, 0);
            }
        },


        // Filters the index set according to the query
        search: function(id) {

            // only search if scope query is more than 3
            if (this.query.length < 3) return true;

            var regexp = new RegExp(this.query.replace(' ', '.*'), 'i');

            // first check the group name for a match
            if (this.users[id].name.match(regexp) !== null) return true;
            else if (this.users[id].email.match(regexp) !== null) return true;

            return false;
        },
    }
});

/**
 * Load user on hashchange
 */
$(window).on('hashchange', function() {
    usersApp.loadObject();
});