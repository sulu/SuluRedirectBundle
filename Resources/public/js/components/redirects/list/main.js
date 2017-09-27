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
    'config',
    'services/suluredirect/redirect-manager',
    'services/suluredirect/redirect-router'
], function(_, Config, manager, router) {

    'use strict';

    var defaults = {
            options: {},

            templates: {
                list: [
                    '<div class="dropzone-container"/>',
                    '<div class="list-toolbar-container"></div>',
                    '<div class="list-info"></div>',
                    '<div class="datagrid-container"></div>',
                    '<div class="dialog"></div>'
                ].join('')
            },

            translations: {
                headline: 'sulu_redirect.title',
                success: 'sulu_redirect.import.success'
            }
        },

        parseFiles = function(files) {
            var result = {total: 0, exceptions: []};

            for (var i in files) {
                for (var j in files[i].exceptions) {
                    files[i].exceptions[j].fileName = files[i].fileName;
                }

                result.total += files[i].total;
                result.exceptions = result.exceptions.concat(files[i].exceptions)
            }

            return result;
        };

    return {

        defaults: defaults,

        header: function() {
            return {
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
                        deleteSelected: {},
                        import: {
                            options: {
                                icon: 'cloud-upload',
                                title: 'sulu_redirect.import',
                                callback: function() {
                                    this.sandbox.emit('husky.dropzone.redirects.show-popup');
                                }.bind(this)
                            }
                        }
                    }
                }
            };
        },

        layout: {
            content: {
                width: 'max'
            }
        },

        initialize: function() {
            this.render();

            this.bindCustomEvents();
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
                    storageName: 'redirect-routes',
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

            this.sandbox.start([{
                name: 'dropzone@husky',
                options: {
                    el: this.sandbox.dom.find('.dropzone-container'),
                    url: '/admin/redirects/import',
                    method: 'POST',
                    paramName: 'redirectRoutes',
                    instanceName: 'redirects'
                }
            }]);
        },

        bindCustomEvents: function() {
            this.sandbox.on('husky.dropzone.redirects.files-added', this.filesAddedHandler.bind(this));

            this.sandbox.on('husky.datagrid.redirect-routes.number.selections', function(number) {
                var postfix = number > 0 ? 'enable' : 'disable';
                this.sandbox.emit('sulu.header.toolbar.item.' + postfix, 'deleteSelected', false);
            }.bind(this));

            this.sandbox.on('sulu.toolbar.delete', function() {
                this.sandbox.emit('husky.datagrid.redirect-routes.items.get-selected', this.deleteItems.bind(this));
            }.bind(this));
        },

        filesAddedHandler: function(files) {
            this.sandbox.emit('husky.datagrid.redirect-routes.update');

            var result = parseFiles(files);
            if (0 === result.exceptions.length) {
                return this.sandbox.emit(
                    'sulu.labels.success.show',
                    this.sandbox.util.sprintf(this.translations.success, result)
                );
            }

            var $container = $('<div/>');
            this.$el.append($container);
            this.sandbox.start([{
                name: 'redirects/list/import-overlay@suluredirect',
                options: {
                    el: $container,
                    result: result
                }
            }]);
        },

        deleteItems: function(ids) {
            this.sandbox.emit('sulu.header.toolbar.item.loading', 'deleteSelected');
            manager.deleteMultiple(ids).then(function() {
                _.each(ids, function(id) {
                    this.sandbox.emit('husky.datagrid.redirect-routes.record.remove', id);
                }.bind(this));

                this.sandbox.emit('sulu.header.toolbar.item.enabled', 'deleteSelected');
            }.bind(this));
        }
    };
});
