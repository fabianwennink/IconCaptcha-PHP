/**
 * Icon Captcha Plugin: v2.3.0
 * Copyright © 2017, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */
(function($){

    $.fn.extend({
        iconCaptcha: function(options) {

            // Default plugin options, will be ignored if not set
            var defaults = {
                captchaTheme: [''],
                captchaFontFamily: '',
                captchaClickDelay: 1000,
                captchaHoverDetection: true,
                showCredits: 'show',
                enableLoadingAnimation: false,
                loadingAnimationDelay: 2000,
                requestIconsDelay: 1500,
                captchaAjaxFile: '../php/captcha-request.php',
                captchaMessages: {
                    header: 'Select the image that does not belong in the row',
                    correct: {
                        top: 'Great!',
                        bottom: 'You do not appear to be a robot.'
                    },
                    incorrect: {
                        top: 'Oops!',
                        bottom: 'You\'ve selected the wrong image.'
                    }
                }
            };

            var $options =  $.extend(defaults, options);

            // Loop through all the captcha holder.
            return this.each(function(id) {

                var $holder = $(this);
                var $captcha_id = id;

                var build_time = 0;
                var hovering = false;
                var generated = false;
                var images_ready = 0;

                // Build the captcha
                buildCaptcha(false);

                // Create random form
                function buildCaptcha(loaderActive) {
                    var captchaTheme = 'light';

                    if($options.captchaTheme[$captcha_id] !== undefined && ($options.captchaTheme[$captcha_id] === 'dark' || $options.captchaTheme[$captcha_id] === 'light')) {
                        captchaTheme = $options.captchaTheme[$captcha_id].toLowerCase();
                    }

                    // Reset image loading count
                    images_ready = 0;

                    $holder.addClass('captcha-theme-' + captchaTheme);

                    // Build the captcha if it hasn't been build yet
                    if(!generated)
                        _buildCaptchaHolder();

                    var $icon_holder = $holder.find('.captcha-modal__icons');

                    // If the requestIconsDelay has been set and is not 0, add the loading delay.
                    // The loading delay will (possibly) prevent high CPU usage when a page displaying
                    // one or more captchas gets constantly refreshed during a DDoS attack.
                    if(($options.requestIconsDelay && $options.requestIconsDelay > 0) && !generated) {

                        // Add the loading animation
                        if(!loaderActive)
                            addLoader($icon_holder);

                        // Set the timeout
                        setTimeout(function() {
                            loadCaptcha(captchaTheme, $icon_holder, true);
                        }, $options.requestIconsDelay)
                    } else {
                        loadCaptcha(captchaTheme, $icon_holder, loaderActive);
                    }
                }

                function loadCaptcha(captchaTheme, iconHolder, loadDelay) {
                    $.ajax({
                        url: $options.captchaAjaxFile,
                        type: 'post',
                        data: {cID: $captcha_id, rT : 1, tM: captchaTheme},
                        success: function (data) {
                            if(data) {
                                $data = JSON.parse(data);

                                // Add the loading animation
                                if(!loadDelay)
                                    addLoader(iconHolder);

                                build_time = new Date();

                                $holder.find('.captcha-image').each(function(i) {
                                    $(this).css('background-image', 'url(' + $options.captchaAjaxFile + '?cid=' + $captcha_id + '&hash=' + $data[i] + ')');
                                    $(this).attr('icon-hash', $data[i]);

                                    loadImage($(this), iconHolder);
                                });

                                // Event: init
                                if(!generated)
                                    $holder.trigger('init', [{captcha_id: id}]);

                                generated = true;
                            }
                        },
                        error: function() {
                            console.log(textStatus, errorThrown);
                            showError();
                        }
                    });
                }

                // Build the form
                function _buildCaptchaHolder() {
                    if($options.captchaFontFamily) {
                        $holder.css('font-family', $options.captchaFontFamily);
                    } else {
                        $('body').append('<!-- Icon Captcha default font --><link href="https://fonts.googleapis.com/css?family=Roboto:400,500" rel="stylesheet">');
                    }

                    var captchaHTML = [];

                    // Adds the first portion of the HTML to the array.
                    captchaHTML.push(
                        "<div class='captcha-modal'>",
                        "<div class='captcha-modal__header'>",
                        "<span>" + (($options.captchaMessages.header && $options.captchaMessages.header) ? $options.captchaMessages.header : "Select the image that does not belong in the row") + "</span>",
                        "</div>",
                        "<div class='captcha-modal__icons'>",
                        "<div class='captcha-image'></div>",
                        "<div class='captcha-image'></div>",
                        "<div class='captcha-image'></div>",
                        "<div class='captcha-image'></div>",
                        "<div class='captcha-image'></div>",
                        "</div>"
                    );

                    // If the credits option is enabled, push the HTML to the array.
                    if($options.showCredits === 'show' || $options.showCredits === 'hide') {
                        var className = 'captcha-modal__credits' + (($options.showCredits === 'hide') ? ' captcha-modal__credits--hide' : '');

                        captchaHTML.push(
                            "<div class='" + className + "' title='IconCaptcha by Fabian Wennink'>",
                            "<a href='https://www.fabianwennink.nl/projects/IconCaptcha/v2/' target='_blank' rel='follow'>IconCaptcha</a> &copy;",
                            "</div>"
                        );
                    }

                    // Adds the last portion of the HTML to the array.
                    captchaHTML.push(
                        "<input type='hidden' name='captcha-hf' required />",
                        "<input type='hidden' name='captcha-idhf' value='" + $captcha_id + "' required />",
                        "</div>"
                    );

                    $holder.html(captchaHTML.join('')).attr('data-captcha-id', $captcha_id);
                }

                // Submit the captcha
                function submitCaptcha(captcha) {
                    var clicked_class = captcha.attr('icon-hash');

                    if(clicked_class) {
                        $holder.find('input[name="captcha-hf"]').attr('value', clicked_class);
                        $holder.find('input[name="captcha-idhf"]').attr('value', $captcha_id);

                        $.ajax({
                            url: $options.captchaAjaxFile,
                            type: 'post',
                            data: {cID: $captcha_id, pC: clicked_class, rT : 2},
                            success: function (data) {
                                (data === '1') ? showSuccess() : showError();
                            },
                            error: function() {
                                showError();
                            }
                        });
                    }
                }

                // Show the success popup
                function showSuccess() {
                    $holder.find('.captcha-modal__icons').empty();

                    $holder.addClass('captcha-success');
                    $holder.find('.captcha-modal__icons').html('<div class="captcha-modal__icons-title">' + (($options.captchaMessages.correct && $options.captchaMessages.correct.top) ? $options.captchaMessages.correct.top : 'Great!')
                        + '</div><div class="captcha-modal__icons-subtitle">' + (($options.captchaMessages.correct && $options.captchaMessages.correct.bottom) ? $options.captchaMessages.correct.bottom : 'You do not appear to be a robot.') + '</div>');

                    // Trigger: success
                    $holder.trigger("success", [{captcha_id: $captcha_id}]);
                }

                // Show the error popup
                function showError() {
                    $holder.find('.captcha-modal__icons').empty();

                    $holder.addClass('captcha-error');
                    $holder.find('.captcha-modal__icons').html('<div class="captcha-modal__icons-title">' + (($options.captchaMessages.incorrect && $options.captchaMessages.incorrect.top) ? $options.captchaMessages.incorrect.top : 'Oops!')
                        + '</div><div class="captcha-modal__icons-subtitle">' + (($options.captchaMessages.incorrect && $options.captchaMessages.incorrect.bottom) ? $options.captchaMessages.incorrect.bottom : 'You\'ve selected the wrong image.') + '</div>');

                    // Trigger: error
                    $holder.trigger('error', [{captcha_id: $captcha_id}]);

                    setTimeout(resetCaptcha, 3000);
                }

                // Reset the captcha
                function resetCaptcha() {
                    $holder.removeClass('captcha-error');
                    $holder.find("input[name='captcha-hf']").attr('value', null);

                    $holder.find('.captcha-modal__icons').html([
                        "<div class='captcha-loader'></div>",
                        "<div class='captcha-image'></div>",
                        "<div class='captcha-image'></div>",
                        "<div class='captcha-image'></div>",
                        "<div class='captcha-image'></div>",
                        "<div class='captcha-image'></div>"
                    ].join('\n'));

                    $holder.find('.captcha-modal__icons > .captcha-image').attr('icon-hash', null);

                    // Rebuild the captcha
                    buildCaptcha(true);

                    // Trigger: refreshed
                    $holder.trigger('refreshed', [{captcha_id: $captcha_id}]);
                }

                // Wait for the icon to fully load.
                // When all 5 icons are loaded, remove the loading animation.
                function loadImage(image, iconHolder) {
                    var url = image.css('background-image').match(/\((.*?)\)/)[1].replace(/('|")/g,'');
                    var img = new Image();

                    // Listen to the image loading event
                    img.onload = function() {
                        images_ready += 1;

                        // Fire when all icons are ready
                        if(images_ready === 5) {

                            // Remove the preloader
                            if(iconHolder)
                                removeLoader(iconHolder);
                        }
                    };

                    // Workaround for IE (IE sometimes doesn't fire onload)
                    img.src = url;
                    if (img.complete) img.onload();
                }

                // Add the loading animation to the captcha holder
                function addLoader(iconHolder) {
                    iconHolder.addClass('captcha-opacity');
                    iconHolder.prepend('<div class="captcha-loader"></div>');
                }

                // Remove the loading animation from the captcha holder
                function removeLoader(iconHolder) {
                    iconHolder.removeClass('captcha-opacity');
                    iconHolder.find('.captcha-loader').remove();
                }

                // On icon click
                $holder.on('click', '.captcha-modal__icons > .captcha-image', function(e) {

                    // Only allow a user to click after 1.5 seconds
                    if((new Date() - build_time) <= $options.captchaClickDelay)
                        return;

                    // if the cursor is not hovering over the element, return
                    if($options.captchaHoverDetection && !hovering)
                        return;

                    // Detect if the click coordinates. If not present, it's not a real click.
                    var _x = (e.pageX - $(e.target).offset().left),
                        _y = (e.pageY - $(e.target).offset().top);
                    if(!_x || !_y) return;

                    var $form = $(this);
                    var $icon_holder = $holder.find('.captcha-modal__icons');

                    // If an image is clicked, do not allow clicking again until the form has reset
                    if($icon_holder.hasClass('captcha-opacity')) return;

                    // Trigger: selected
                    $holder.trigger('selected', [{captcha_id: $captcha_id}]);

                    if($options.enableLoadingAnimation === true) {
                        addLoader($icon_holder);

                        setTimeout(function() {
                            submitCaptcha($form);
                        }, $options.loadingAnimationDelay);
                    } else {
                        submitCaptcha($form);
                    }
                }).on({
                        mouseenter: function() {
                            if(!hovering)
                                hovering = true
                        },
                        mouseleave: function() {
                            if(hovering)
                                hovering = false
                        }
                    }, $holder
                );
            });
        }
    });
})(jQuery);