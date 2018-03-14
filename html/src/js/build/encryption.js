
var HMAC = new function() {

    this.getKey = function() {
        return UserKeychain.getHMACKey();
    };

    /**
     * Returns an Hmac tag
     * @param data (base64 string)
     * @return tag (base64 string)
     */
    this.getTag = function(data) {
        if (!data || !data.length) {
            console.log('Cannot get tag for ', data);
            return false;
        }

        var dataObj = CryptoJS.enc.Base64.parse(data);
        return CryptoJS.HmacSHA256(dataObj, this.getKey()).toString(CryptoJS.enc.Base64);
    };

    /**
     * Verifies a tag against a data/key pair
     * @param data
     * @param tag (base64)
     * @returns {boolean}
     */
    this.verifyTag = function(data, tag) {
        return this.getTag(data) === tag;
    };
};

/**
 * AES class for encrypting and decrypting data.
 * Keys are generated on the server and stored in the browser
 * @type {AES}
 */
var AES = new function() {

    function getKey() {
        return UserKeychain.getContentKey();
    }

    /**
     * Encrypts the data parameter and returns a hex (lowercase) string
     * @param plaintext
     * @param keyObj optional
     */
    this.encrypt = function(plaintext, keyObj) {
        //
        // if (plaintext instanceof Object)
        //     plaintext = json_encode(plaintext);

        // var iv = CryptoJS.enc.Hex.parse('30303030303030303030303030303030');
        var iv = CryptoJS.lib.WordArray.random(16);

        var result = CryptoJS.AES.encrypt(plaintext, keyObj || getKey(), {iv: iv});

        var retObj = {
            cipher: result.ciphertext.toString(CryptoJS.enc.Hex),
            iv:     result.iv.toString(CryptoJS.enc.Hex)
        };

        // retObj.tag = HMAC.getTag(retObj.cipher); // @todo implement HMAC
        return retObj;
    };

    this.decrypt = function(encObj, keyObj) {
        // convert encObj hex string into byte array

        if (!encObj instanceof Object)
            encObj = json_decode(encObj);

        if (!encObj.iv || !encObj.cipher)
            return encObj;

        /** @todo: HMAC tag verification */
        // if (encObj.tag && !HMAC.verifyTag(encObj.cipher, encObj.tag)) {
        //     console.log('Ciphertext Verification failed.');
        //     return '';
        // }

        var cipherParams = CryptoJS.lib.CipherParams.create({ciphertext: CryptoJS.enc.Hex.parse(encObj.cipher)});
        var opts         = {iv: CryptoJS.enc.Hex.parse(encObj.iv)};

        try {
            return CryptoJS.AES.decrypt(cipherParams, keyObj || getKey(), opts);

        } catch (e) {
            if (!result) console.log('Decryption failed.');
            return '';
        }

    };

    /**
     * Accepts a byte array or hex string (lowercase)
     * @param encObj JSON or object
     * @param keyObj optional
     */
    this.decryptToUtf8 = function(encObj, keyObj) {
        return this.decrypt(encObj, keyObj).toString(CryptoJS.enc.Utf8);
    };

};

/**
 * Keychain object used for expanding a user password
 */
var Keychain = function() {
    this.HMACKey       = null; // Hex used for signing requests
    this.PassHash      = null; // Hex hash of the password
    this.ContentKey    = null; // Hex used for decrypting Account data
    this.ContentKeyKey = null; // Hex used for decrypting the contentKey

    /**
     * Saves the object into localStorage
     */
    this.saveToStorage = function() {
        localStorage.setItem('UserKeychain', json_encode({
            HMACKey: this.HMACKey,
            PassHash: this.PassHash,
            ContentKey: this.ContentKey,
            ContentKeyKey: this.ContentKeyKey,
        }));
    };

    /**
     * Deletes the stored ContentKey and metadata
     */
    this.resetStorage = function() {
        localStorage.removeItem('UserKeychain');
    };

    /**
     * loads the object from localStorage
     * @returns {Keychain}
     */
    this.loadFromStorage = function() {
        var storageItem = localStorage.getItem('UserKeychain');
        if (storageItem = json_decode(storageItem)) {
            this.HMACKey       = storageItem.HMACKey || null;
            this.PassHash      = storageItem.PassHash || null;
            this.ContentKey    = storageItem.ContentKey || null;
            this.ContentKeyKey = storageItem.ContentKeyKey || null;
        }
        return this;
    };

    // Returns a CryptoJS instance of the provided keyname
    this.getKey = function(keyname) {
        var keyObjName = '_' + keyname;
        if (this[keyObjName]) return this[keyObjName];
        this[keyObjName] = CryptoJS.enc.Hex.parse(this[keyname]);
        return this[keyObjName];
    };

    // Expands a password into a passkey with the provided CryptoJS PBKDF2 options
    this.expandKey = function(keyname, passObj, opts) {
        this['_' + keyname] = CryptoJS.PBKDF2(passObj, 'Charon.UserKeychain.' + keyname, opts);
        this[keyname] = this['_' + keyname].toString(CryptoJS.enc.Hex);
    };

    this.getHMACKey       = function() { return this.getKey('HMACKey'); };
    this.getPassHash      = function() { return this.getKey('PassHash'); };
    this.getContentKey    = function() { return this.getKey('ContentKey'); };
    this.getContentKeyKey = function() { return this.getKey('ContentKeyKey'); };

    // expands a password into various keys
    this.setPassword = function(pass) {
        var passObj = CryptoJS.enc.Utf8.parse(pass);

        this.expandKey('HMACKey', passObj, {
            iterations: 10,
            hasher: CryptoJS.algo.SHA256,
            keySize: 256/32,
        });

        this.expandKey('PassHash', passObj, {
            iterations: 20,
            hasher: CryptoJS.algo.SHA256,
            keySize: 256/32,
        });

        this.expandKey('ContentKeyKey', passObj, {
            iterations: 15,
            hasher: CryptoJS.algo.SHA256,
            keySize: 256/32,
        });
    };

    // Decrypts the content key and sets it
    this.setContentKey = function(contentKeyEncrypted) {
        console.log('contentKeyEncrypted', contentKeyEncrypted);
        window.decObj = AES.decrypt(contentKeyEncrypted, this.getContentKeyKey());
        console.log('decObj', decObj);
        this.ContentKey = decObj.toString(CryptoJS.enc.Utf8);
        console.log(this.ContentKey);
        this._ContentKey = CryptoJS.enc.Hex.parse(this.ContentKey);
    };

    // returns the encrypted content key
    this.getContentKeyEncrypted = function(pass) {
        if (pass) {
            key = CryptoJS.PBKDF2(CryptoJS.enc.Utf8.parse(pass), 'Charon.UserSQ', {
                iterations: 1030,
                hasher: CryptoJS.algo.SHA256,
                keySize: 256/32,
            });
        } else {
            key = this.getContentKeyKey();
        }
        return AES.encrypt(this.ContentKey, key);
    };

};

/**
 * @param answer
 * @param qIndex
 * @returns {string}
 */
function hashSQ(answer, qIndex) {
    var passObj = CryptoJS.enc.Utf8.parse(answer);
    return CryptoJS.PBKDF2(passObj, 'Charon.UserSQ.' + qIndex, {
        iterations: 1030,
        hasher: CryptoJS.algo.SHA256,
        keySize: 256/32,
    }).toString(CryptoJS.enc.Hex);
}

/**
 * Current authenticated Keychain
 * @type {Keychain}
 */
var UserKeychain = new Keychain().loadFromStorage();