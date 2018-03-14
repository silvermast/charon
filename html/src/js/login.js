
Vue.component('login-form', {
    template: '#login-form-template',
    data: function() {
        return {
            isBusy: false,
            page: 'login',

            email: '',
            pass: '',
        };
    },
    methods: {

        /**
         * Attempts to log in
         * @param e
         */
        loginAttempt: function() {
            var vm = this;

            UserKeychain.resetStorage();
            UserKeychain.setPassword(vm.pass);

            var ajaxData = {
                email: vm.email,
                passhash: UserKeychain.PassHash,
            };

            $.post({
                url: '/login',
                data: json_encode(ajaxData),
                dataType: 'json',
                success: function(result) {
                    UserKeychain.setContentKey(result.contentKeyEncrypted);
                    if (UserKeychain.ContentKey) {
                        UserKeychain.saveToStorage();
                        location.reload();
                    } else {
                        Alerts.error('Error decrypting ContentKey', {layout: 'topRight'});
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    Alerts.error(jqXHR.responseText, {layout: 'topRight'});
                }
            });

        },

        /**
         * Requests a password reset
         */
        requestPasswordReset: function() {
            var vm = this;
            vm.isBusy = true;
            $.post({
                url: '/request-password-reset',
                data: json_encode({email: vm.email}),
                dataType: 'text',
                success: function(result) {
                    Alerts.success(result);
                    vm.isBusy = false;
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    Alerts.error(jqXHR.responseText, {layout: 'topRight'});
                    vm.isBusy = false;
                }
            });
        },
    }
});

var vue_container = new Vue({
    el: '#page-container',
});