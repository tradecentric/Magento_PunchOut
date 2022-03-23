define([
    'Magento_Checkout/js/model/resource-url-manager'
], function (resourceUrlManager) {
    'use strict';

    return function(quote) {
        var params = resourceUrlManager.getCheckoutMethod() === 'guest' ?
            {
                cartId: quote.getQuoteId()
            } : {},
            urls = {
                'guest': '/guest-carts/:cartId/set-totals-information',
                'customer': '/carts/mine/set-totals-information'
            };


        return resourceUrlManager.getUrl(urls, params);
    };
});
