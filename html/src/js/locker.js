// check location and redirect
if (location.pathname != '/locker/') location.pathname = '/locker/';

// returns the locker ID from the location hash
function getLockerId() {
    return location.hash.replace(/^[#!\/]*/g, '');
}

// default Locker Object schema
function getBlankLocker() {
    return {
        id: '',
        name: '',
        note: '',
        items: [getBlankItem()],
    }
}

// default Item schema
function getBlankItem() {
    return {
        _id: unique_id(), // unique id to prevent sorting collisions
        icon: 'fa-key',
        title: '',
        url: '',
        user: '',
        pass: '',
        note: '',
    };
}

/**
 * Main Vue component
 */
var lockerApp = new Vue({
    el: '#locker-app',
    data: {

        // display messages
        loader: true,
        success: '',
        error: '',
        warning: '',
        objectHash: false,
        object: getBlankLocker(),
        mergeNeeded: false,

        // icon options
        icons: [
            'fa-key',
            'fa-terminal',
            'fa-database',
            'fa-lock',
            'fa-rocket',
            'fa-truck',

            'fa-envelope-square',
            'fa-book',
            'fa-heartbeat',
            'fa-certificate',
            'fa-expeditedssl',
            'fa-slack',

            'fa-wordpress',
            'fa-linux',
            'fa-apple',
            'fa-android',
            'fa-amazon',
            'fa-windows',

            'fa-instagram',
            'fa-dropbox',
            'fa-google-plus-square',
            'fa-facebook-square',
            'fa-twitter',
            'fa-yelp',

            'fa-ban',
        ],

        /**
         * Timeouts
         */
        timeouts: {},
        durations: {
            loadIndex: 30 * 1000, // 30 seconds
            checkForChanges: 60 * 1000, // 1 minute
        },

        // search query
        query: '',

        /**
         * Index
         * @type {Array}
         */
        index: {},

        user: {},
    },
    created: function() {
        var vm = this;
        vm.loadIndex();
        vm.loadObject();

        vm.timeouts.loadIndex       = setInterval(vm.loadIndex, vm.durations.loadIndex);
        vm.timeouts.checkForChanges = setInterval(vm.checkForChanges, vm.durations.checkForChanges);
    },

    computed: {
        hasChanged: function() {
            return this.objectHash !== this.hashObject(this.object);
        }
    },

    methods: {

        // hashes the object
        hashObject: function(obj) {
            return md5(json_encode(obj))
        },

        // Sets the object as a blank object
        resetObject: function() {
            this.object      = getBlankLocker();
            // this.hasChanged = false;
            this.objectHash  = this.hashObject(this.object);
        },

        /// Adds a blank item to the items array
        addItem: function() {
            if (!this.object.items) this.object.items = [];
            this.object.items.push(getBlankItem());
        },

        // Removes a key row from the group
        removeItem: function(key) {
            this.object.items.splice(key, 1);
        },

        sortItemUpdate: function(event) {
            this.object.items.splice(event.newIndex, 0, this.object.items.splice(event.oldIndex, 1)[0])
        },

        // Function for highlighting an element
        highlight: function(e) {
            // use setTimeout to circumvent safari bug
            setTimeout(function() {
                $(e.target).select();
            }, 10);
        },

        // parses a response string into an object
        getObjectFromResponse: function(obj) {
            if (typeof obj === "string")
                obj = json_decode(obj);

            if (obj.items.iv !== undefined)
                obj.items = AES.decryptToUtf8(obj.items);

            if (typeof obj.items === "string")
                obj.items = json_decode(obj.items);

            // make sure each object has a unique ID before setting
            if (obj.items && obj.items.map) {
                obj.items.map(function(item) {
                    if (item._id === undefined) {
                        delete item.$$hashKey;
                        item._id = unique_id();
                    }
                    item.icon = (item.icon && item.icon.length) ? item.icon : 'fa-key';
                });
            }

            return obj
        },

        // decrypts, formats, and sets the Locker object
        setObject: function(obj) {
            obj             = this.getObjectFromResponse(obj);
            this.objectHash = this.hashObject(obj);
            this.object     = obj;
        },

        // Loads the index on to the sidebar
        loadIndex: function() {
            var self = this;
            $.get({
                url: '/locker/_index',
                success: function(result) {
                    self.index = json_decode(result);
                },
                error: function(jqXHR) {
                    Alerts.error(jqXHR.responseText);
                    if (jqXHR.status === 401)
                        window.logout();
                }
            });
        },

        // Loads a Locker object from the server
        loadObject: function() {
            var self = this;
            self.toggleLoader(true);

            var lockerId = getLockerId();

            // if we're adding a new group, just
            if (!lockerId.length) {
                self.toggleLoader(false);
                self.resetObject();
                return;
            }

            // send or pull the object
            $.ajax({
                method: 'get',
                url: '/locker/' + lockerId,
                success: function(result) {
                    if (result === 'null') {
                        Alerts.warning("Object Not Found.");
                    } else {
                        self.setObject(result);
                    }
                    self.toggleLoader(false);

                },
                error: function(jqXHR) {
                    if (code == 401) {
                        location.reload();
                        return;
                    }
                    Alerts.error(jqXHR.responseText);
                    self.toggleLoader(false);
                    self.resetObject();
                }
            });

        },

        // checks for changes
        checkForChanges: function(callback) {
            var lockerId = getLockerId(),
                self     = this;

            // if we're adding a new group, just return
            if (!lockerId.length) {
                runCallback(callback);
                return;
            }

            // send or pull the object
            $.ajax({
                method: 'get',
                url: '/locker/' + lockerId,
                success: function(result) {
                    result = self.getObjectFromResponse(result);
                    if (self.hasChanged) {
                        // current locker has changed, and remote locker has changed
                        if (self.objectHash !== self.hashObject(result)) {
                            Alerts.warning('This Locker has changed since it was loaded.');
                            self.mergeNeeded = true;
                        }

                    } else {
                        // current locker has not changed. Overload the object for a fresh copy
                        self.setObject(result);
                    }

                    runCallback(callback);
                },
                error: function(jqXHR) {
                    if (code == 401) {
                        location.reload();
                        return;
                    }
                    Alerts.error(jqXHR.responseText);
                }
            });

        },

        // merges two objects together
        mergeObject: function(callback) {
            var self = this;
            self.toggleLoader(true);

            var lockerId = getLockerId();

            // if we're adding a new group, just
            if (!lockerId.length) {
                self.toggleLoader(false);
                runCallback(callback);
                return;
            }

            // returns the item index with the associated _id
            function _find_item_key(_id, items) {
                for (var i in items) if (items[i]._id === _id) return i;
                return false;
            }

            // generates a unique hash of the item object
            function _hash_item(item) {
                // var $$hashKey = item.$$hashKey;
                delete item.$$hashKey;
                var hash = self.hashObject(item);
                // item.$$hashKey = $$hashKey;
                return hash;
            }

            clearInterval(self.timeouts.checkForChanges);

            // send or pull the object
            $.ajax({
                method: 'get',
                url: '/locker/' + lockerId,
                success: function(result) {
                    var localObj  = clone(self.object);
                    var remoteObj = self.getObjectFromResponse(result);

                    // only worry about merging if the DB object has actually changed
                    if (self.hashObject(remoteObj) !== self.objectHash) {

                        // loop through remoteObj items.
                        for (var i in remoteObj.items) {
                            var item           = remoteObj.items[i];
                            var local_item_key = _find_item_key(item._id, localObj.items);

                            if (local_item_key === false) {
                                // If _id does not exist, append.
                                localObj.items.push(item);

                            } else if (_hash_item(localObj.items[local_item_key]) !== _hash_item(item)) {
                                // If _id does exist and _hash is different, put beneath
                                localObj.items.splice(local_item_key, 0, item);

                            }
                        }

                        if (localObj.note.trim() !== remoteObj.note.trim())
                            localObj.note += "\n====================MERGE====================\n" + remoteObj.note;

                        self.object      = localObj;
                        self.objectHash  = self.hashObject(remoteObj); // set as remoteObj so we know not to merge again

                    }

                    self.mergeNeeded = false;
                    self.toggleLoader(false);
                    self.timeouts.checkForChanges = setInterval(self.checkForChanges, self.durations.checkForChanges);
                    runCallback(callback);
                },
                error: function(jqXHR) {
                    if (code == 401) {
                        location.reload();
                        return;
                    }
                    Alerts.error(jqXHR.responseText);
                    self.toggleLoader(false);
                    self.timeouts.checkForChanges = setInterval(self.checkForChanges, self.durations.checkForChanges);
                    runCallback(callback);
                }
            });
        },

        // Saves the Locker object
        saveObject: function() {
            var vm = this;

            // perform merge first in acse there are any outstanding changes that need to be loaded
            vm.mergeObject(function() {

                vm.toggleLoader(true);

                // encrypt
                var ajaxData = $.extend(true, clone(vm.object), {
                    items: AES.encrypt(json_encode(vm.object.items))
                });

                console.log(ajaxData);

                $.ajax({
                    method: 'post',
                    url: '/locker/' + vm.object.id,
                    data: json_encode(ajaxData),
                    success: function(result) {
                        // Set the data into the object
                        vm.setObject(result);

                        // set the hash id
                        location.hash = '#/' + vm.object.id;

                        vm.loadIndex();
                        vm.toggleLoader(false);

                        // set success message
                        Alerts.success('Successfully saved the object', {layout: 'bottomCenter'});

                    },
                    error: function(jqXHR) {
                        if (jqXHR.status == 401) {
                            location.reload();
                            return;
                        }

                        Alerts.error(jqXHR.responseText);
                        vm.toggleLoader(false);
                    }

                });
            });
        },

        // Permanently deletes the entire Locker object
        deleteObject: function() {
            var vm = this;

            bsConfirm({
                text: 'Are you sure you want to delete this Locker? This action cannot be undone.',
                confirm: function() {

                    vm.toggleLoader(true);

                    // send or pull the object
                    $.ajax({
                        method: 'delete',
                        url: '/locker/' + vm.object.id,
                        success: function(result) {
                            Alerts.success(result, {layout: 'bottomCenter'});
                            vm.resetObject();
                            vm.loadIndex();
                            vm.toggleLoader(false);
                        },
                        error: function(jqXHR) {
                            if (jqXHR.status == 401) {
                                location.reload();
                                return;
                            }

                            Alerts.error(jqXHR.responseText);
                            vm.toggleLoader(false);
                        }

                    });

                },
            });

        },

        // Turns the loader on after a slight delay Or turns it off and clears the timeout
        toggleLoader: function(toggle) {
            var self = this;
            if (toggle) {
                self.timeouts.loader = setTimeout(function() {
                    self.loader = true;
                }, 200);

            } else {
                self.loader = false;
                clearTimeout(self.timeouts.loader);
                window.scrollTo(0, 0);
            }
        },

        // generates a random password for the given item index
        generatePassword: function(index) {
            var self = this;
            if (!self.object.items || !self.object.items[index]) return;

            var chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*_-?",
                pass  = "";
            for (var i = 0; i < 16; i++) {
                var key = Math.floor(Math.random() * chars.length);
                pass += chars[key];
            }
            self.object.items[index].pass = pass;
        },


        // Filters the index set according to the query
        search: function(id) {

            // only search if scope query is more than 3
            if (this.query && this.query.length < 3) return true;

            var regexp = new RegExp(this.query.replace(' ', '.*'), 'i');

            // first check the group name for a match
            if (this.index[id].name.match(regexp) !== null) return true;

            // if it's not a match, try decrypting and checking
            if (this.index[id].items.iv !== undefined)
                this.index[id].items = AES.decryptToUtf8(this.index[id].items);

            return this.index[id].items.match(regexp) !== null;

        },

        // exports the entire account locker as CSV
        exportCSV: function() {
            var lockerList = [], i, j;

            for (i in this.index) {
                var locker = this.index[i];
                var items  = locker.items && locker.items.iv ? json_decode(AES.decryptToUtf8(locker.items)) : [];

                for (j in items) {
                    var item = items[j];

                    lockerList.push({
                        'Locker': locker.name || '',
                        'Item Title': item.title || '',
                        'Item URL': item.url || '',
                        'Item User': item.user || '',
                        'Item Pass': item.pass || '',
                    });
                }

                if (locker.note) {
                    lockerList.push({
                        'Locker': locker.name || '',
                        'Item Title': locker.note,
                        'Item URL': '',
                        'Item User': '',
                        'Item Pass': '',
                    });
                }
            }

            console.log(lockerList);
            console.log(Papa.unparse(lockerList));

            var $a = document.createElement('a');
            $a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(Papa.unparse(lockerList));
            $a.target = '_blank';
            $a.download = 'Lockers.csv';
            $a.click();
        },

        // Determines whether the field matches the query string
        fieldMatch: function(value) {
            if (value === undefined || !this.query || !this.query.length)
                return false;

            var regexp = new RegExp(this.query.replace(' ', '.*'), 'i');
            return value.match(regexp) !== null;
        },

        /**
         * Attempts to log the user in
         * @param item
         */
        loginAttempt: function(item) {
            var url = item.url.indexOf('//') === -1 ? 'https://' + item.url : item.url;
            window.open(url, '_blank');
            return;

            // if non-http or https, pass through.
            if (url.indexOf('http') !== 0)
                window.open(url, '_blank');

            if (url.match(/wp-admin|wp-login\.php/)) {
                var auth_url = url.replace(/wp-admin\/?/, 'wp-login.php');
                postNewWindow(auth_url, {
                    log: item.user,
                    pwd: item.pass
                });

            } else {
                // by default, just try to open the link.
                window.open(url, '_blank');
            }
        },

    }
});

/**
 * jQuery based keymap
 */
$(document).on('keyup', function(e) {
    if (e.target.value) {
        return;
    }

    switch (e.keyCode) {

        case 27: // "escape"
            if (document.activeElement)
                document.activeElement.blur();
            break;

        case 191: // "/"
            $('#search').focus();
            break;

    }

});

/**
 * Search keypress event
 */
$(document).on('keyup', '#search', function(e) {
    if (e.keyCode === 13) {
        var hash = $('.nav-sidebar').eq(1).find('a[href]:visible').attr('href');
        if (hash && hash.length > 3)
            location.hash = hash;
    }
});

/**
 *
 */
$(window).on('hashchange', function() {
    lockerApp.loadObject();
});
