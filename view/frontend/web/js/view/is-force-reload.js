define([
    'ko',
], function(ko) {
    'use strict'

    return ko.observable(document.location.href.indexOf(window.reloadParam) > 1);
})
