define([
    'Magento_Customer/js/customer-data',
    'Punchout2Go_Punchout/js/view/is-force-reload'
], function(customerData, isForceReload) {
    'use strict'

    /**
     * transfer punchout data
     */
    return function(config) {
        if (isForceReload()) {
            let sections = config.jsReloadSections.split(",");
            customerData.invalidate(sections);
            customerData.reload(sections, true);
        }
    };
})
