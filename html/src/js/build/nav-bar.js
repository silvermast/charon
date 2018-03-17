Vue.component('nav-bar', {
    template: '#tmpl-nav-bar',
    props: {
        pageTitle: String
    },
    data: function() {
        return {
            pagename: location.pathname,
            appname: 'SilverMast',
            user: {
                name: '',
                permLevel: 0,
            }
        };
    },
    created: function() {
        var vm = this;
        $.get('/profile', function(result) {
            vm.user = $.extend(vm.user, json_decode(AES.decrypt(result)));
            vm.user.permLevel = parseInt(vm.user.permLevel);

            vm.showSecurityQuestionModal();
        });
    },
    computed: {},
    methods: {
        hasPermission: function(perms) {
            perms = isArray(perms) ? perms : [perms];
            return (this.user.permLevel == 1 || perms.indexOf(this.user.permLevel) !== -1);
        },

        showSecurityQuestionModal: function() {
            if (location.pathname.indexOf('/profile') !== -1) {
                console.log('not showing modal. on /profile');
                return; // user is already on the profile page. They see it.
            }

            if (this.user.sq1A && this.user.sq2A && this.user.sq3A) {
                console.log('not showing modal. sq questions set: ', this.user.sq1A, this.user.sq2A, this.user.sq3A);
                return; // user has already set their security questions
            }

            if (sessionStorage.getItem('ignoreSQModal')) {
                console.log('not showing modal. ignore flag set');
                return; // User clicked ignore
            }


            var msg = "<h2>Complete your Security Questions</h2>";
            msg += '<p>Worried you might forget your password? Answer some security questions so that we can decrypt your data if it ever happens.</p>';

            var alert = Alerts.warning(msg, {
                layout: 'center',
                timeout: 0,
                buttons: [
                    Noty.button("I'll take the risk. <i class='fa fa-close'></i>", 'btn btn-default', function () {
                        sessionStorage.setItem('ignoreSQModal', true);
                        alert.close();
                    }),
                    Noty.button("OK, I'll do it. <i class='fa fa-arrow-right'></i>", 'btn btn-success', function () {
                        location.href = '/profile/';
                    }),
                ],
            });
        },

        logout: window.logout,
    }
});
