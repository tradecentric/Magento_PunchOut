define([
    'jquery',
    'underscore',
    'Magento_Customer/js/customer-data',
    'Punchout2Go_Punchout/js/model/destroy-session',
], function($, _, customerData, destroySession) {
    'use strict'

    let punchoutSession = customerData.get('punchout-session');
    function toggleLink(elem, punchoutId) {
        if (_.isEmpty(punchoutId)) {
            $(elem).hide();
        } else {
            $(elem).show();
        }
    }
    return function (config, elem) {
        toggleLink(elem, punchoutSession().punchoutId);
        punchoutSession.subscribe(function (data) {
            toggleLink(elem, data.punchoutId);
        });
        $(elem).click(function() {
            destroySession();
            $.mage.redirect(config.redirectUrl);
        });
    }
})
