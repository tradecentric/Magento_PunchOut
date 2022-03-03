define([
    'jquery',
    'mage/storage',
    'underscore'
], function ($, storage, _) {
    'use strict';

    return function (closeUrl) {
        let deferred = $.Deferred();
        storage.post(
            closeUrl, [], false
        ).done(function (result) {
            deferred.resolve(result);
        }).fail(function (response) {
            deferred.reject(response);
        });
        return deferred.promise();
    };

});
