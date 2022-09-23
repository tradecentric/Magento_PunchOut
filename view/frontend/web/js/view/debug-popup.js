define([
    'jquery',
    'underscore',
    'Magento_Ui/js/modal/confirm'
], function($, _, confirm) {
    'use strict'

    return function (callback) {
        $("body").trigger('processStop');
        confirm({
            content: '<div id="logwindow" class="punchout-debug-window"></div>',
            buttons: [{
                text: $.mage.__('Transfer'),
                'class': 'action-primary action-accept',

                /** @inheritdoc */
                click: function (event) {
                    $("body").trigger('processStart');
                    this.closeModal(event, true);
                    callback(event);
                }
            }]
        });
    }
})
