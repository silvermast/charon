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
        });
    },
    computed: {},
    methods: {
        hasPermission: function(perms) {
            perms = isArray(perms) ? perms : [perms];
            return (this.user.permLevel == 1 || perms.indexOf(this.user.permLevel) !== -1);
        },
        logout: window.logout,
    }
});
