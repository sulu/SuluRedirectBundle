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
    'jquery',
    'services/suluredirect/redirect-manager',
    'text!./form.html'
], function(_, $, manager, form) {

    var constants = {
        formSelector: '#redirect-routes-form'
    };

    return {

        defaults: {
            templates: {
                form: form
            },
            translations: {
                source: 'sulu_redirect.source',
                sourceHost: 'sulu_redirect.source-host',
                target: 'sulu_redirect.target',
                conflict: 'sulu_redirect.errors.conflict',
            }
        },

        layout: {
            content: {
                width: 'fixed',
                leftSpace: true,
                rightSpace: true
            }
        },

        initialize: function() {
            this.render();

            this.bindDomEvents();
            this.bindCustomEvents();
        },

        render: function() {
            this.$el.html(this.templates.form({translations: this.translations, data: this.data}));

            this.form = this.sandbox.form.create(constants.formSelector);
            this.form.initialized.then(function() {
                this.sandbox.form.setData(constants.formSelector, this.data || {});
            }.bind(this));
        },

        bindDomEvents: function() {
            this.$el.find('input, textarea').on('keypress', function() {
                this.sandbox.emit('sulu.tab.dirty');
            }.bind(this));
        },

        bindCustomEvents: function() {
            this.sandbox.on('sulu.tab.save', this.save.bind(this));
            this.sandbox.on('sulu_redirect.statusCode.changed', this.statusCodeChanged.bind(this));
        },

        save: function(data) {
            if (!this.sandbox.form.validate(constants.formSelector)) {
                return;
            }

            var newData = this.sandbox.util.extend(false, {}, data, this.sandbox.form.getData(constants.formSelector));

            return manager.save(newData).then(function(response) {
                this.sandbox.emit('sulu.tab.saved', response);
            }.bind(this)).fail(function(jqXHR) {
                switch (jqXHR.status) {
                    case 409:
                        this.sandbox.emit('sulu.labels.error.show', this.translations.conflict);

                        break;
                    default:
                        this.sandbox.emit('sulu.labels.error.show');

                        break;
                }

                this.sandbox.emit('sulu.tab.dirty');
            }.bind(this));
        },

        statusCodeChanged: function(data) {
            this.data = this.sandbox.util.extend(false, {}, data, this.sandbox.form.getData(constants.formSelector));
            this.render();
        },

        loadComponentData: function() {
            return this.options.data();
        }
    };
});
