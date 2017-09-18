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
                headline: 'sulu_redirect.title',
                enabled: 'sulu_redirect.enabled',
                enable: 'sulu_redirect.enable',
                disable: 'sulu_redirect.disable',
                status301: 'sulu_redirect.status-code.301',
                status302: 'sulu_redirect.status-code.302'
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
                            parent: 'saveWithOptions'
                        },
                        enabled: {
                            parent: !!this.data.enabled ? 'toggler-on' : 'toggler',
                            options: {
                                title: !!this.data.enabled ? this.translations.disable : this.translations.enable
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
                        },
                        edit: {
                            options: {
                                dropdownItems: {
                                    delete: {
                                        options: {
                                            disabled: !this.options.id,
                                            callback: this.delete.bind(this)
                                        }
                                    }
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
            this.sandbox.on('sulu.toolbar.save', this.save.bind(this));
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
            this.sandbox.emit(
                'sulu.header.toolbar.button.set',
                'enabled',
                {title: !!enabled ? this.translations.disable : this.translations.enable}
            );

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

        delete: function() {
            this.sandbox.sulu.showDeleteDialog(function(wasConfirmed) {
                if (wasConfirmed) {
                    manager.delete(this.options.id).then(router.toList);
                }
            }.bind(this));
        },

        loadComponentData: function() {
            if (!this.options.id) {
                return {enabled: true, statusCode: 301};
            }

            return manager.load(this.options.id);
        }
    };
});
