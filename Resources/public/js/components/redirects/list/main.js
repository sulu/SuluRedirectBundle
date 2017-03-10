/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'underscore',
    'services/suluredirect/redirect-manager',
    'services/suluredirect/redirect-router'
], function(_, manager, router) {

    'use strict';

    var defaults = {
        options: {},

        templates: {
            list: [
                '<div class="list-toolbar-container"></div>',
                '<div class="list-info"></div>',
                '<div class="datagrid-container"></div>',
                '<div class="dialog"></div>'
            ].join('')
        },

        translations: {
            headline: 'sulu_redirect.title'
        }
    };

    return {

        defaults: defaults,

        header: {
            noBack: true,

            toolbar: {
                buttons: {
                    add: {
                        options: {
                            callback: function() {
                                router.toAdd();
                            }
                        }
                    },
                    deleteSelected: {}
                }
            }
        },

        layout: {
            content: {
                width: 'max'
            }
        },

        initialize: function() {
            this.render();
        },

        render: function() {
            this.$el.html(this.templates.list());

            this.sandbox.sulu.initListToolbarAndList.call(this,
                'redirect-routes',
                '/admin/api/redirect-routes/fields',
                {
                    el: this.$find('.list-toolbar-container'),
                    instanceName: 'redirect-routes',
                    template: this.sandbox.sulu.buttons.get({
                        settings: {
                            options: {
                                dropdownItems: [
                                    {
                                        type: 'columnOptions'
                                    }
                                ]
                            }
                        }
                    })
                },
                {
                    el: this.sandbox.dom.find('.datagrid-container'),
                    url: '/admin/api/redirect-routes?sortBy=created&sortOrder=desc',
                    searchInstanceName: 'redirect-routes',
                    searchFields: ['source', 'target'],
                    resultKey: 'redirect-routes',
                    instanceName: 'redirect-routes',
                    actionCallback: function(id) {
                        router.toEdit(id);
                    }.bind(this),
                    viewOptions: {
                        table: {
                            actionIconColumn: 'source'
                        }
                    }
                }
            );
        }
    };
});
