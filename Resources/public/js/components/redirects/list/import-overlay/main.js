/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define(['text!./skeleton.html'], function(skeletonTemplate) {

    'use strict';

    var defaults = {
            templates: {
                skeleton: skeletonTemplate
            },
            translations: {
                title: 'sulu_redirect.import.title',
                total: 'sulu_redirect.import.total',
                exceptions: 'sulu_redirect.import.exceptions'
            }
        };

    return {

        defaults: defaults,

        initialize: function() {
            var $container = $('<div/>');
            this.$el.append($container);

            this.sandbox.start([{
                name: 'overlay@husky',
                options: {
                    el: $container,
                    openOnStart: true,
                    removeOnClose: true,
                    skin: 'large',
                    instanceName: 'error-overlay',
                    slides: [
                        {
                            title: this.translations.title,
                            data: this.templates.skeleton({
                                result: this.options.result,
                                translations: this.translations,
                                translate: this.sandbox.translate,
                                sprintf: this.sandbox.util.sprintf
                            }),
                            buttons: [
                                {
                                    type: 'ok',
                                    align: 'center',
                                    callback: function() {
                                        this.sandbox.stop();
                                    }.bind(this)
                                }
                            ]
                        }
                    ]
                }
            }]);

            this.sandbox.on('husky.overlay.error-overlay.opened', function() {
                this.sandbox.dom.on('.result-rows-list-row .header', 'click', function() {
                    $(this).parent().toggleClass('open');
                });
            }.bind(this));
        }
    };
});
