/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require.config({
    paths: {
        suluredirect: '../../suluredirect/js',
        suluredirectcss: '../../suluredirect/css',

        'services/suluredirect/redirect-manager': '../../suluredirect/js/services/redirect-manager',
        'services/suluredirect/redirect-router': '../../suluredirect/js/services/redirect-router'
    }
});

define(['css!suluredirectcss/main'], function() {

    'use strict';

    return {

        name: 'Sulu Redirect Bundle',

        initialize: function(app) {
            app.components.addSource('suluredirect', '/bundles/suluredirect/js/components');

            app.sandbox.mvc.routes.push({
                route: 'redirects/add/:content',
                callback: function(content) {
                    return '<div data-aura-component="redirects/edit@suluredirect" data-aura-content="' + content + '"/>';
                }
            });

            app.sandbox.mvc.routes.push({
                route: 'redirects/edit::id/:content',
                callback: function(id, content) {
                    return '<div data-aura-component="redirects/edit@suluredirect" data-aura-id="' + id + '" data-aura-content="' + content + '"/>';
                }
            });

            app.sandbox.mvc.routes.push({
                route: 'redirects',
                callback: function() {
                    return '<div data-aura-component="redirects/list@suluredirect"/>';
                }
            });
        }
    }
});
