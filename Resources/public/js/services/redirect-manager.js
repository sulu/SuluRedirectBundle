/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define(['services/husky/util'], function(Util) {

    'use strict';

    var baseUrl = '/admin/api/redirect-routes';

    return {
        load: function(id) {
            return Util.load(baseUrl + '/' + id);
        },

        save: function(data) {
            return Util.save(baseUrl + (!!data.id ? '/' + data.id : ''), !!data.id ? 'PUT' : 'POST', data);
        },

        delete: function(id) {
            return Util.save(baseUrl + '/' + id, 'DELETE');
        }
    }
});
