define([
    'Magento_Customer/js/customer-data',
    'Punchout2Go_Punchout/js/view/is-force-reload',
    'underscore'
], function(customerData, isForceReload, _) {
    'use strict'

    let punchoutData = customerData.get('punchout-session');

    /**
     *
     * @param data
     * @param redirectUrl
     */
    function checkAndRedirect(data, redirectUrl) {
        if (data && data.isRedirect && isNotMatchUrl(redirectUrl)) {
            location.href = redirectUrl;
        }
    }

    /**
     * compare path names of
     * @param redirectUrl
     * @returns {boolean}
     */
    function isNotMatchUrl(redirectUrl) {
        let current = new URL(window.location.href),
            redirect = new URL(redirectUrl);

        if (redirect.pathname.slice(-1) == '/') {
            redirect.pathname = redirect.pathname.slice(0, -1)
        }

        if (current.pathname.slice(-1) == '/') {
            current.pathname = current.pathname.slice(0, -1)
        }

        return current.pathname !== redirect.pathname;
    }

    /**
     * transfer punchout data
     */
    return function(config) {
       punchoutData.subscribe(function (data) {
            checkAndRedirect(data, config.redirectUrl);
        });
        if (isForceReload()) {
            return;
        }
        if (!_.isEmpty(punchoutData())) {
            return checkAndRedirect(punchoutData(), config.redirectUrl);
        }
        return customerData.reload(['punchout-session'], false);
    };
})
