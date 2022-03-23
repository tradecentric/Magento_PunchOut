define([
    'jquery',
    'Punchout2Go_Punchout/js/model/session-transfer-url-resolver',
    'mage/storage'
], function ($, urlResolver, storage) {
    'use strict';

    return function () {
        let serviceUrl = urlResolver(),
            deferred = $.Deferred();
        if (!serviceUrl) {
            deferred.reject(new Error('Transfer error.'));
            return deferred.promise();
        }
        storage
            .get(serviceUrl)
            .done(function (data) {
                deferred.resolve(data);
            }).fail(function(error) {
                deferred.reject(error);
        });
        return deferred.promise();
    };
});
