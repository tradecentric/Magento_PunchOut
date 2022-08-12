define([
    'jquery',
    'Punchout2Go_Punchout/js/model/save-address-data',
    'Punchout2Go_Punchout/js/model/close-session',
    'Punchout2Go_Punchout/js/model/get-punchout-data',
    'Punchout2Go_Punchout/js/model/punchout-checkout',
    'Punchout2Go_Punchout/js/model/destroy-session',
    'Magento_Ui/js/modal/alert'
], function($, addressDataSaver, closeSession, punchoutDataHandler, punchoutCheckout, destroySession, alert) {
    'use strict'

    /**
     * transfer punchout data
     */
    return function(config) {
        $(config.elementId).click(function() {
            $("body").trigger('processStart');
            return addressDataSaver()
                .then(punchoutDataHandler)
                .then(function(punchoutData) {
                    return punchoutCheckout.run(config.checkoutConfig, punchoutData);
                })
                .then(function () {
                    closeSession(config.closeSessionUrl);
                })
                .then(destroySession)
                .done(function() {
                    $("body").trigger('processStop');
                })
                .fail(function() {
                    $("body").trigger('processStop');
                    alert({
                        content: $.mage.__('Transfer error. Please, try again later')
                    });
                });
        });
    };
})
