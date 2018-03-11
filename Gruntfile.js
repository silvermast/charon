module.exports = function(grunt) {

    require('load-grunt-tasks')(grunt); // npm install --save-dev load-grunt-tasks

    grunt.initConfig({
        concat: {
            options: {
                separator: "\n",
            },
            css: {
                src: [
                    'bower_components/bootstrap/dist/css/bootstrap.css',
                    'bower_components/font-awesome/css/font-awesome.css',
                    'bower_components/roboto-condensed/css/roboto-condensed.css',
                    'bower_components/noty/lib/noty.css',
                    'html/src/css/bootstrap-flatly.min.css',
                    'html/src/css/main.css',
                ],
                dest: 'html/dist/css/build.css',
            },
            js: {
                src: [
                    // jquery & addons
                    'bower_components/jquery/dist/jquery.js',

                    // vuejs & addons
                    'bower_components/vue/dist/vue.js',
                    'bower_components/Sortable/Sortable.js',
                    'bower_components/vue.draggable/dist/vuedraggable.min.js',

                    // bootstrap & addons
                    'bower_components/bootstrap/dist/js/bootstrap.js',
                    'bower_components/noty/lib/noty.js',

                    // CryptoJS utilities (AES & HMAC)
                    'html/lib/js/cryptojs/rollups/aes.js',
                    'html/lib/js/cryptojs/components/mode-ctr-min.js',
                    'html/lib/js/cryptojs/rollups/md5.js',
                    'html/lib/js/cryptojs/rollups/hmac-sha256.js',
                    'html/lib/js/cryptojs/rollups/pbkdf2.js',

                    // JSEncrypt utilities (RSA)
                    // 'html/lib/js/jsencrypt/jsencrypt.js',

                    // finally, all custom-written build files
                    'html/src/js/build/*.js',
                ],
                dest: 'html/src/js/build.js',
            }
        },
        uglify: {
            options: {
                mangle: false
            },
            dist: {
                files: [{
                    expand: true,
                    cwd: 'html/src/js',
                    src: '*.js',
                    dest: 'html/dist/js'
                }]
            }
        },
        copy: {
            fontawesome: {
                expand: true,
                cwd: 'bower_components/font-awesome/fonts',
                src: '**',
                dest: 'html/dist/fonts/',
            },
            roboto: {
                expand: true,
                cwd: 'bower_components/roboto-condensed/fonts',
                src: '**',
                dest: 'html/dist/fonts/',
            },
            css: {
                expand: false,
                src: ['bower_components/bootstrap/dist/css/bootstrap.css.map'],
                dest: 'html/dist/css/bootstrap.css.map',
            }
        },
        watch: {
            scripts: {
                files: [
                    'Gruntfile.js',
                    'html/src/js/*.js',
                    'html/src/js/build/*.js',
                    '<%= concat.css.src %>',
                ],
                tasks: ['concat', 'uglify'],
                options: {
                    spawn: true,
                }
            }
        }
    });

    // grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-concat');

    grunt.registerTask('default', ['concat', 'uglify', 'copy']);

};
