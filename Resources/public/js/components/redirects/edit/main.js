/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'jquery',
    'services/suluredirect/redirect-manager',
    'services/suluredirect/redirect-router'
], function($, manager, router) {

    'use strict';

    return {

        defaults: {
            translations: {
                headline: 'sulu_redirect.headline',
                enabled: 'sulu_redirect.enabled',
                status301: 'sulu_redirect.status.301',
                status302: 'sulu_redirect.status.302'
            }
        },

        header: function() {
            return {
                title: function() {
                    return !!this.data.source ? this.data.source : this.translations.headline;
                }.bind(this),

                tabs: {
                    url: '/admin/content-navigations?alias=redirect-routes',
                    options: {
                        data: function() {
                            return this.sandbox.util.extend(false, {}, this.data);
                        }.bind(this)
                    },
                    componentOptions: {
                        values: this.data
                    }
                },

                toolbar: {
                    buttons: {
                        save: {
                            parent: 'saveWithOptions',
                            options: {
                                callback: this.save.bind(this)
                            }
                        },
                        enabled: {
                            parent: !!this.data.enabled ? 'toggler-on' : 'toggler',
                            options: {
                                title: this.translations.enabled
                            }
                        },
                        statusCode: {
                            parent: 'template',
                            options: {
                                title: !!this.data.statusCode ? this.translations['status' + this.data.statusCode] : this.translations.status301,
                                icon: 'external-link',
                                dropdownItems: [
                                    {
                                        id: 301,
                                        title: this.translations.status301
                                    },
                                    {
                                        id: 302,
                                        title: this.translations.status302
                                    }
                                ],
                                dropdownOptions: {
                                    callback: function(item) {
                                        this.changeStatusCode(item.id);
                                    }.bind(this)
                                }
                            }
                        }
                    }
                }
            };
        },

        initialize: function() {
            this.bindCustomEvents();
        },

        bindCustomEvents: function() {
            this.sandbox.on('sulu.header.back', router.toList);
            this.sandbox.on('sulu.tab.dirty', this.enableSave.bind(this));
            this.sandbox.on('sulu.tab.data-changed', this.setData.bind(this));
            this.sandbox.on('husky.toggler.sulu-toolbar.changed', this.changeEnabled.bind(this));
        },

        save: function(action) {
            this.loadingSave();

            this.saveTab().then(function(data) {
                this.afterSave(action, data);
            }.bind(this));
        },

        setData: function(data) {
            this.data = data;
        },

        changeEnabled: function(enabled) {
            this.data.enabled = enabled;
            this.enableSave();
        },

        changeStatusCode: function(statusCode) {
            this.data.statusCode = statusCode;
            this.enableSave();
        },

        saveTab: function() {
            var promise = $.Deferred();

            this.sandbox.once('sulu.tab.saved', function(savedData) {
                this.setData(savedData);

                promise.resolve(savedData);
            }.bind(this));

            this.sandbox.emit('sulu.tab.save', this.data);

            return promise;
        },

        enableSave: function() {
            this.sandbox.emit('sulu.header.toolbar.item.enable', 'save', false);
        },

        loadingSave: function() {
            this.sandbox.emit('sulu.header.toolbar.item.loading', 'save');
        },

        afterSave: function(action, data) {
            this.sandbox.emit('sulu.header.toolbar.item.disable', 'save', true);
            this.sandbox.emit('sulu.header.saved', data);

            if (action === 'back') {
                router.toList();
            } else if (action === 'new') {
                router.toAdd();
            } else if (!this.options.id) {
                router.toEdit(data.id);
            }
        },

        loadComponentData: function() {
            if (!this.options.id) {
                return {enabled: true, statusCode: 301};
            }

            return manager.load(this.options.id);
        }
    };
});
