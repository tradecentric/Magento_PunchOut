define([
    'jquery',
    'Punchout2Go_Punchout/js/model/destroy-session',
], function($, destroySession) {
    'use strict'

    return function (config, elem) {
        $(elem).click(function() {
            destroySession();
            $.mage.redirect(config.redirectUrl);
        });
    }
})
