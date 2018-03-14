/**
 * Main Vue component
 */
var App = new Vue({
    el: '#page-container',
    data: {
        isBusy: true,
        sq1A: null,
        sq2A: null,
        sq3A: null,

        showPasswords: false,
        changePass1: '',
        changePass2: '',

        errors: {},

        profile: {
            id: null,
            sq1Q: null,
            sq2Q: null,
            sq3Q: null,
        },
        timeouts: {},
    },
    created: function() {
        var vm = this;
        $.getJSON({
            url: location.href,
            success: function(result) {
                vm.profile = result;
                vm.isBusy = false;
            },
        });
    },

    computed: {
        passwordVerify: function() {
            return this.showPasswords && (this.changePass1.length === 0 || this.changePass1.length >= 12);
        },
        passwordsMatch: function() {
            return this.showPasswords && this.changePass1 === this.changePass2;
        },
    },

    methods: {
        resetPassword: function() {
            var vm = this;

            vm.isBusy = true;

            if (vm.showPasswords && !vm.passwordVerify) {
                Alerts.error("Please ensure your new password is at least 12 characters.");
                return;
            }

            $.post({
                url: location.href,
                dataType: 'json',
                data: {
                    sq1A: vm.sq1A,
                    sq2A: vm.sq2A,
                    sq3A: vm.sq3A,
                    changePass1: vm.changePass1,
                    changePass2: vm.changePass2,
                },
                success: function(result) {
                    vm.isBusy = false;

                    vm.errors.sq1A = result.sq1Error;
                    vm.errors.sq2A = result.sq2Error;
                    vm.errors.sq3A = result.sq3Error;

                    if (result.pwSuccess) {
                        Alerts.success("We've successfully reset your password. Please log in.");
                        setTimeout(function() {
                            location.href = '/';
                        }, 2000);
                        return;
                    } else if (result.sqSuccess && vm.showPasswords) {
                        Alerts.error("There was an issue decrypting your user data. Please contact support.");
                    }

                    if (result.sqSuccess && !vm.showPasswords) {
                        Alerts.info("Your answers are correct. Please choose a new password!");
                        vm.showPasswords = true;
                    }

                    vm.$forceUpdate();
                },
                error: function(jqXHR) {
                    Alerts.error(jqXHR.responseText);
                    vm.isBusy = false;
                }
            });
        },

    }
});