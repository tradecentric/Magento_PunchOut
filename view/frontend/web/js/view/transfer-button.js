define([
    'jquery',
    'Punchout2Go_Punchout/js/model/save-address-data',
    'Punchout2Go_Punchout/js/model/close-session',
    'Punchout2Go_Punchout/js/model/get-punchout-data',
    'Punchout2Go_Punchout/js/model/punchout-checkout',
    'Punchout2Go_Punchout/js/model/destroy-session',
    'Magento_Ui/js/modal/alert',
    'Punchout2Go_Punchout/js/view/debug-popup'
], function($, addressDataSaver, closeSession, punchoutDataHandler, punchoutCheckout, destroySession, alert, debugPopup) {
    'use strict'

    /**
     * transfer punchout data
     */
    return function(config) {
        function log(string, data) {
            if (config.js_logging) {
                console.log(string,data);
            }
        }
        $(config.elementId).click(function() {
            $("body").trigger('processStart');
            log("Save address data");
            return addressDataSaver()
                .then(function() {
                    log("Load punchout data");
                    return punchoutDataHandler();
                })
                .then(function(punchoutData) {
                    log("Run punchout checkout session");
                    return punchoutCheckout.run(config.checkoutConfig, punchoutData);
                })
                .then(function(session) {
                    let deferred = $.Deferred();
                    if (!config.debug) {
                        return deferred.resolve(session);
                    }
                    debugPopup(function() {
                        return deferred.resolve(session);
                    });
                    session.debug();
                    return deferred.promise();
                })
                .then(function(session) {
                    log("Run transfer cart");
                    return punchoutCheckout.transferCart(session);
                })
                .then(function () {
                    log("Close punchout session");
                    return closeSession(config.closeSessionUrl);
                })
                .then(function() {
                    log("Destroy punchout session");
                    return destroySession();
                })
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
});
