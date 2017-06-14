/**
 * Stores a cookie
 * @param string key
 * @param mixed val
 * @param int days (ttl)
 */
function set_cookie(key, val, days)
{
    var exdate = new Date();
    days = days !== undefined ? days : 7;
    exdate.setDate(exdate.getDate() + days);
    document.cookie = key + "=" + encodeURIComponent(val) + "; expires=" + exdate.toUTCString() + ";path=/";
}

/**
 * Retrieves a cookie
 * @param  string key_param
 * @return mixed
 */
function get_cookie(key)
{
    var cookies = document.cookie.split(';'), i;
    for (i in cookies) {
        var split = cookies[i].split('=');
        if (split[0].trim() == key) {
            return decodeURIComponent(split[1]);
        }
    }
    return null;
}

function md5(string) {
    return CryptoJS.MD5(string).toString();
}

/**
 * Returns an object or the param as it was passed in
 * @param param
 * @returns {*}
 */
function json_decode(param) {
    if (typeof(param) == 'object')         return param;
    if (!param.length || param.length < 2) return param;

    try {
        return JSON.parse(param);
    } catch (e) {
        return param;
    }
}

function json_encode(param) {
    return JSON.stringify(param);
}

function clone(obj) {
    return json_decode(json_encode(obj));
}

function runCallback(fn) {
    if (fn instanceof Function) fn.call();
}

/**
 * Generates a unique id
 * @returns {number}
 */
function unique_id() {
    return _uid++;
}
var _uid = new Date().getTime();

// Logs out
function logout() {
    $.get('/logout', function() {
        localStorage.clear();
        location.reload();
    });
}


/**
 * bootstrap modal for confirmation
 * @param opts
 */
function bsConfirm(opts) {
    // set option defaults
    opts = $.extend({
        title: 'Are you sure?',
        text: 'This action cannot be undone.',
        confirm: function() {},
        cancel: function() {},
    }, opts);

    // select the modal
    var $modal = $('#bs-confirm-modal');
    // if it doesn't exist, create it
    if (!$modal.length) {
        $('body').append(
            '<div id="bs-confirm-modal" class="modal fade">' +
                '<div class="modal-dialog">' +
                    '<div class="modal-content">' +
                        '<div class="modal-header">' +
                            '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                            '<h4 class="modal-title">' + opts.title + '</h4>' +
                        '</div>' +
                        '<div class="modal-body">' +
                            '<p class="modal-body-text">' + opts.text + '</p>' +
                        '</div>' +
                        '<div class="modal-footer">' +
                            '<button type="button" class="btn btn-default bsConfirm-btn-cancel" data-dismiss="modal">Close</button>' +
                            '<button type="button" class="btn btn-primary bsConfirm-btn-confirm" data-dismiss="modal">Confirm</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>'
        );
        // select it again
        $modal = $('#bs-confirm-modal');
    }

    // attach the cancel and confirmation actions
    $modal.find('.bsConfirm-btn-cancel').off().on('click', opts.cancel);
    $modal.find('.bsConfirm-btn-confirm').off().on('click', opts.confirm);

    $modal.modal('show');
}

$.ajaxSetup({
    beforeSend: function(jqXHR, settings) {
        if (settings.data instanceof Object)
            settings.data = json_encode(settings.data);
    },
    // contentType: 'application/json',
    // processData: false,
});