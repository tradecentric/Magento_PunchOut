define([
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/url-builder'
], function(customerData, urlBuilder) {
    'use strict'

    /**
     * transfer punchout data
     */
    return function() {
        let punchoutSession = customerData.get('punchout-session');
        if (!punchoutSession || !punchoutSession().punchoutId) {
            return null;
        }
        return urlBuilder.createUrl('/punchout-quote/:punchoutQuoteId/transfer', {'punchoutQuoteId': punchoutSession().punchoutId});
    };
})
