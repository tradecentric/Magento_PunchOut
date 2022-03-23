define([
    'jquery',
    'Punchout2Go_Punchout/js/model/new-address-url',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/cart/cache',
    'mage/storage',
    'underscore'
], function ($, resourceUrl, quote, cartCache, storage, _) {
    'use strict';

    return function () {
        let serviceUrl,
            payload,
            deferred = $.Deferred();
        serviceUrl = resourceUrl(quote);
        payload = {
            addressInformation: {
                address: _.pick(quote.shippingAddress(), cartCache.requiredFields)
            }
        };

        if (quote.shippingMethod() && quote.shippingMethod()['method_code']) {
            payload.addressInformation['shipping_method_code'] = quote.shippingMethod()['method_code'];
            payload.addressInformation['shipping_carrier_code'] = quote.shippingMethod()['carrier_code'];
        }

        storage.post(
            serviceUrl, JSON.stringify(payload), false
        ).done(function (result) {
            deferred.resolve(result);
        }).fail(function (response) {
            deferred.reject(response);
        });
        return deferred.promise();
    };

});
