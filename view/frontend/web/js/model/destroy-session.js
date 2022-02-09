define([
    'jquery'
], function($) {
    'use strict'

    return function () {
        $.cookieStorage.set('section_data_ids', '{}')
        localStorage.clear();
        sessionStorage.clear();
    }
})
