/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define(['services/husky/mediator'], function(Mediator) {

    'use strict';

    var constant = {
            defaultContent: 'details',
            baseRoute: 'redirects'
        },

        goto = function(postFix) {
            postFix = postFix || '';
            Mediator.emit('sulu.router.navigate', constant.baseRoute + (postFix.length > 0 ? '/' : '') + postFix);
        };

    return {
        toEdit: function(id, content) {
            goto('edit:' + id + '/' + (content || constant.defaultContent));
        },

        toAdd: function(content) {
            goto('add/' + (content || constant.defaultContent));
        },

        toList: function() {
            goto();
        }
    }
});
