define([
    'jquery',
    'underscore',
    'mage/cookies'
], function($, _) {
    'use strict'

    let checkout;

    /**
     *
     * @param config
     * @returns {*}
     */
    return {
        getSession: function (config) {
            let deferred = $.Deferred();
            if (checkout) {
                return deferred.resolve(checkout);
            }
            this.startSession(config).then(function(session){
                deferred.resolve(session);
            }).fail(function (error) {
                deferred.reject(error);
            });
            return deferred.promise();
        },

        /**
         *
         * @param config
         * @returns {*}
         */
        startSession: function (config) {
            var deferred = $.Deferred();
            require(config.elementsUrl, function() {
                checkout = new Po2go(config.punchoutConfig);
                checkout.init();
                deferred.resolve(checkout);
            });
            return deferred.promise();
        },

        /**
         * fill cart and transport
         * @param session
         * @param checkoutData
         */
        prepareSession: function (session, checkoutData) {
            let data = this.prepareCartData(checkoutData);
            //add cart data
            _.each(data.cart_data, function(value, key) {
                session.cart[key] = value;
            });

            session.cart.items = []; //clear current cart
            //add cart item to cart
            _.each(data.items_data, function(value) {
                session.addItemToCart(value);
            });
        },

        /**
         *
         * @param checkoutData
         * @returns {*}
         */
        prepareCartData: function (checkoutData) {
            // add custom fields
            $.extend(
                checkoutData.cart_data,
                this.prepareCustomFields(checkoutData.cart_data.custom_fields)
            );
            delete (checkoutData.cart_data.custom_fields);

            //add addresses
            if (!_.isEmpty(checkoutData.cart_data.addresses)) {
                checkoutData.cart_data.addresses = _.map(
                    checkoutData.cart_data.addresses,
                    function(value) {
                        return JSON.parse(value);
                    }
                );
            }
            checkoutData.items_data = _.map(
                checkoutData.items_data,
                function(value) {
                    return JSON.parse(value);
                }
            );
            return checkoutData;
        },

        /**
         *
         * @param customFields
         * @returns {{}}
         */
        prepareCustomFields: function(customFields) {
            let result = {};
            if (!_.isEmpty(customFields)) {
                _.each(
                    customFields,
                    function(value) {
                        let obj = JSON.parse(value);
                        result[obj.field] = obj.value;
                    }
                );
            }
            return result;
        },

        /**
         *
         * @param session
         */
        transferCart: function(cart)
        {
            cart.transferCart();
        },

        /**
         *
         * @param config
         * @param checkoutData
         * @returns {*}
         */
        run: function(config, checkoutData) {
            let deferred = $.Deferred();
            this.getSession(config).then(function(session) {
                this.prepareSession(session, checkoutData);
                return deferred.resolve(session);
            }.bind(this)).fail(function (error) {
                deferred.reject(error);
            });
            return deferred.promise();
        }
    }
});
