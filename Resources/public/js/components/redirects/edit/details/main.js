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
                target: 'sulu_redirect.target'
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
            this.$el.html(this.templates.form({translations: this.translations}));

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
        },

        save: function(data) {
            if (!this.sandbox.form.validate(constants.formSelector)) {
                return;
            }

            var newData = this.sandbox.util.extend(false, {}, data, this.sandbox.form.getData(constants.formSelector));

            return manager.save(newData).then(function(response) {
                this.sandbox.emit('sulu.tab.saved', response);
            }.bind(this));
        },

        loadComponentData: function() {
            return this.options.data();
        }
    };
});
