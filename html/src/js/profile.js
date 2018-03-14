/**
 * Main Vue component
 */
var profileApp = new Vue({
    el: '#locker-app',
    data: {
        loader: true,

        changePass1: '',
        changePass2: '',

        changingSQ: false,
        changeSQ: {
            q1: {q: null, a: null},
            q2: {q: null, a: null},
            q3: {q: null, a: null},
        },
        sqOptions: [
            "What high-school clique did your dad belong to?",
            "What high-school clique did your mom belong to?",
            "What is your fondest childhood memory?",
            "What word best describes yourself as a whole?",
            "What is the name of the last movie you tried to sneak into?",
            "What is your favorite type of wood?",
            "In one or two words, explain your opinion of Karma.",
            "What typical human quality do you feel like you have in excess?",
            "What typical human quality do you feel like you completely lack?",
            "What is the cutest baby animal?",
            "In your opinion, what is the ugliest vegetable?",
            "What book do you wish was never turned into a movie?",
            "What villain do you really feel for?",
            "If you were challenged to a duel, what weapons would you choose?",
            "What was your favorite childhood toy?",
            "What is the first name of the teacher who gave you your first failing grade?",
            "What is the last name of the teacher who gave you your first failing grade?",
            "What is your favorite organism?",
            "What is your favorite astronomical body?",
            "What is your favorite number?",
            "What are you really happy about being terrible at?",
            "As a child, what was your favorite reward for being good?",
            "What is the genus of your favorite organism?",
            "What grade were you in when you first realized you had strong feelings about mathematics?",
            "What is the term for the infant version of your favorite animal?",
            "What is the first name of your celebrity look-alike?",
            "What is the last name of your celebrity look-alike?",
            "As a child, what was your least-favorite punishment?",
            "What conspiracy theory do you actually believe?",
            "Which appliance can you not live without?",
            "What was the make and model of your first smartphone?",
            "What is the first name of your high-school teacher whom likely had questionable morals?",
            "What is the last name of your high-school teacher whom likely had questionable morals?",
            "What is the first book you read that completely changed your life?",
            "Who is the author or the first book you read that completely changed your life?",
            "As a child, what was your favorite activity?",
            "As a child, what was something you were obsessed with?",
            "What was your most memorable vacation in your childhood?",
            "Who had the biggest impact on the person you have become?",
        ],

        profile: {},

        timeouts: {},
    },
    watch: {
        changingSQ: function(newVal) {
            if (newVal) {
                this.changeSQ.q1.q = this.changeSQ.q1.q || this.getRandomQuestion();
                this.changeSQ.q2.q = this.changeSQ.q2.q || this.getRandomQuestion();
                this.changeSQ.q3.q = this.changeSQ.q3.q || this.getRandomQuestion();
            }
        },
    },
    created: function() {
        var vm = this;
        $.get({
            url: "/profile",
            success: function(result) {
                vm.profile = $.extend(vm.profile, json_decode(result));
                vm.toggleLoader(false);

                if (!vm.profile || !vm.profile.sqLastUpdate) {
                    vm.changingSQ    = true;
                    vm.changeSQ.q1.q = vm.getRandomQuestion();
                    vm.changeSQ.q2.q = vm.getRandomQuestion();
                    vm.changeSQ.q3.q = vm.getRandomQuestion();
                }

                vm.$forceUpdate();
            }
        });
    },

    computed: {
        passwordChange: function() {
            return this.changePass1.length > 0 && this.changePass2.length > 0;
        },
        passwordVerify: function() {
            return this.changePass1.length === 0 || this.changePass1.length >= 12;
        },
        passwordsMatch: function() {
            return this.changePass1.length === 0 || this.changePass1 === this.changePass2;
        },
        sqChanged: function() {
            return this.changingSQ && this.changeSQ.q1.a && this.changeSQ.q2.a && this.changeSQ.q3.a;
        }
    },

    methods: {
        getRandomQuestion: function() {
            var i = parseInt(CryptoJS.lib.WordArray.random(4).toString(CryptoJS.enc.Hex).replace(/\D/g, ''));
            return this.sqOptions[i % this.sqOptions.length];
        },

        saveObject: function() {
            var vm = this;

            if (vm.passwordChange) {
                // passwords must match
                if (!vm.passwordsMatch) {
                    Alerts.error('Passwords do not match.', {layout: 'bottomCenter'});
                    return;
                }

                UserKeychain.setPassword(vm.changePass1);
                vm.profile.passhash            = UserKeychain.PassHash;
                vm.profile.contentKeyEncrypted = UserKeychain.getContentKeyEncrypted();
            }

            // have the security questions changed?
            if (vm.sqChanged) {
                var passphrase = (this.changeSQ.q1.a + '|' + this.changeSQ.q2.a + '|' + this.changeSQ.q3.a).toLocaleLowerCase();

                vm.profile.sq1Q                  = vm.changeSQ.q1.q;
                vm.profile.sq1A                  = hashSQ(vm.changeSQ.q1.a, 1);
                vm.profile.sq2Q                  = vm.changeSQ.q2.q;
                vm.profile.sq2A                  = hashSQ(vm.changeSQ.q2.a, 2);
                vm.profile.sq3Q                  = vm.changeSQ.q3.q;
                vm.profile.sq3A                  = hashSQ(vm.changeSQ.q3.a, 3);
                vm.profile.sqContentKeyEncrypted = UserKeychain.getContentKeyEncrypted(passphrase);
                vm.profile.sqLastUpdate          = new Date().toJSON();
            }

            vm.toggleLoader(true);

            $.post({
                url: '/profile',
                data: json_encode(vm.profile),
                success: function(result) {

                    Alerts.success("Successfully updated your profile!", {layout: 'bottomCenter'});
                    vm.profile     = $.extend(vm.profile, json_decode(result));
                    vm.changePass1 = '';
                    vm.changePass2 = '';
                    vm.changingSQ  = false;
                    vm.toggleLoader(false);

                    UserKeychain.saveToStorage(); // overwrite storage with the new keychain

                },
                error: function(jqXHR) {
                    Alerts.error(jqXHR.responseText, {layout: 'bottomCenter'});
                    vm.toggleLoader(false);
                }
            });
        },

        // Turns the loader on after a slight delay Or turns it off and clears the timeout
        toggleLoader: function(toggle) {
            var vm = this;
            if (toggle) {
                vm.timeouts.loader = setTimeout(function() {
                    vm.loader = true;
                }, 200);

            } else {
                vm.loader = false;
                clearTimeout(vm.timeouts.loader);
                window.scrollTo(0, 0);
            }
        },
    }
});