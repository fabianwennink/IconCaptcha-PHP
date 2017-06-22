/**
 * Icon Captcha Plugin: v2.0.2
 * Copyright © 2017, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */
(function($){

    $.fn.extend({
        iconCaptcha: function(options) {

            // Default plugin options, will be ignored if not set
            var defaults = {
                captchaTheme: "",
                captchaFontFamily: "",
                captchaClickDelay: 1000,
                captchaHoverDetection: true,
                showBoxShadow: true,
                showCredits: true,
                enableLoadingAnimation: false,
                loadingAnimationDelay: 2000,
                captchaAjaxFile: "../php/captcha-request.php",
                captchaMessages: {
                    header: "Select the image that does not belong in the row",
                    correct: {
                        top: "Great!",
                        bottom: "You do not appear to be a robot."
                    },
                    incorrect: {
                        top: "Oops!",
                        bottom: "You've selected the wrong image."
                    }
                }
            };

            var $options =  $.extend(defaults, options);

            // Loop through all the captcha holder.
            // Note: You can only have 1 captcha per page!
            return this.each(function(id) {

                var $holder = $(this);

                var form_built = false; // Building the form, false if not build yet
                var build_time = 0; // Timestamp of when the form was generated.
                var hovering = false;

                // Build the captcha
                buildCaptcha();

                // Create random form
                function buildCaptcha() {
                    var captchaTheme = "light";

                    if($options.captchaTheme && ($options.captchaTheme === "dark" || $options.captchaTheme === "light")) {
                        $holder.addClass('captcha-theme-' + $options.captchaTheme);
                        captchaTheme = $options.captchaTheme.toLowerCase();
                    }

                    $.ajax({
                        url: $options.captchaAjaxFile,
                        type: "post",
                        data: {rT : 1, tM: captchaTheme},
                        success: function (data) {
                            if(data) {
                                $data = JSON.parse(data);

                                build_time = new Date();

                                // Build the captcha if it hasn't been build yet
                                if(!form_built) {
                                    _buildCaptcha();

                                    // Event: init
                                    $holder.trigger('init');
                                }

                                $('.captcha-image').each(function(i, obj) {
                                    $(this).css('background-image', 'url(' + $options.captchaAjaxFile + '?hash=' + $data[i] + ')');
                                    $(this).attr('icon-hash', $data[i]);
                                });
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                           console.log(textStatus, errorThrown);
                           showError();
                        }
                    });
                }

                // Build the form
                function _buildCaptcha() {
                    if($options.captchaFontFamily) {
                        $holder.css('font-family', $options.captchaFontFamily);
                    } else {
                        $('body').append('<!-- Icon Captcha default font --><link href="https://fonts.googleapis.com/css?family=Roboto:400,500" rel="stylesheet">');
                    }

                    $holder.html("\
                        <div id='captcha-modal' >\
                            <div id='captcha-modal__header'>\
                                <span>" + (($options.captchaMessages.header && $options.captchaMessages.header) ? $options.captchaMessages.header : "Select the image that does not belong in the row") + "</span>\
                            </div>\
                            <div id='captcha-modal__icons'>\
                                <div class='captcha-image'></div>\
                                <div class='captcha-image'></div>\
                                <div class='captcha-image'></div>\
                                <div class='captcha-image'></div>\
                                <div class='captcha-image'></div>\
                            </div>\
                            <div id='captcha-modal__credits' alt='Captcha provided by Fabian Wennink'>\
                                Captcha provided by <a href='https://www.fabianwennink.nl/projects/IconCaptcha/v2/' target='_blank' rel='follow'>Fabian Wennink</a> ©\
                            </div>\
                            <input type='hidden' name='captcha-hidden-field' required />\
                        </div>"
                    );

                    if($options.showBoxShadow) {
                        $holder.addClass('captcha-boxshadow');
                    }

                    if($options.showCredits) {
                        $holder.addClass('captcha-credits');
                    }

                    form_built = true;
                }

                // Submit the captcha
                function submitCaptcha(captcha) {
                    var clicked_class = captcha.attr('icon-hash');

                    if(clicked_class) {
                        $holder.find('input').attr('value', clicked_class);

                        $.ajax({
                            url: $options.captchaAjaxFile,
                            type: "post",
                            data: {pC: clicked_class, rT : 2},
                            success: function (data) {
                                (data === '1') ? showSuccess() : showError();
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                               showError();
                            }
                        });
                    }
                }

                // Show the success popup
                function showSuccess() {
                    $("#captcha-modal__icons").empty();

                    $holder.addClass('captcha-success');
                    $("#captcha-modal__icons").html('<div id="captcha-modal__icons-title">' + (($options.captchaMessages.correct && $options.captchaMessages.correct.top) ? $options.captchaMessages.correct.top : "Great!") + '</div><div id="captcha-modal__icons-subtitle">' + (($options.captchaMessages.correct && $options.captchaMessages.correct.bottom) ? $options.captchaMessages.correct.bottom : "You do not appear to be a robot.") + '</div>');

                    // Trigger: success
                    $holder.trigger('success');
                }

                // Show the error popup
                function showError() {
                    $("#captcha-modal__icons").empty();

                    $holder.addClass('captcha-error');
                    $("#captcha-modal__icons").html('<div id="captcha-modal__icons-title">' + (($options.captchaMessages.incorrect && $options.captchaMessages.incorrect.top) ? $options.captchaMessages.incorrect.top : "Oops!") + '</div><div id="captcha-modal__icons-subtitle">' + (($options.captchaMessages.incorrect && $options.captchaMessages.incorrect.bottom) ? $options.captchaMessages.incorrect.bottom : "You've selected the wrong image.") + '</div>');

                    // Trigger: error
                    $holder.trigger('error');

                    setTimeout(resetCaptcha, 3000);
                }

                // Reset the captcha
                function resetCaptcha() {
                    $holder.removeClass('captcha-error');
                    $holder.find('input').attr('value', null);

                    $("#captcha-modal__icons").html("\
                        <div class='captcha-image'></div>\
                        <div class='captcha-image'></div>\
                        <div class='captcha-image'></div>\
                        <div class='captcha-image'></div>\
                        <div class='captcha-image'></div>\
                    ").removeClass('captcha-opacity');;

                    $("#captcha-modal__icons > .captcha-image").attr('icon-hash', null);

                    // Rebuild the captcha
                    buildCaptcha();

                    // Trigger: refreshed
                    $holder.trigger('refreshed');
                }

                // On icon click
                $(document).on('click', '#captcha-modal__icons > .captcha-image', function() {

                    // Only allow a user to click after 1.5 seconds
                    if((new Date() - build_time) <= $options.captchaClickDelay) return;

                    // if the cursor is not hovering over the element, return
                    if($options.captchaHoverDetection && !hovering) return;

                    var $form = $(this);
                    var $icon_holder = $("#captcha-modal__icons");

                    // If an image is clicked, do not allow clicking again until the form has reset
                    if($icon_holder.hasClass('captcha-opacity')) return;

                    // Trigger: selected
                    $holder.trigger('selected');

                    if($options.enableLoadingAnimation === true) {
                        $icon_holder.addClass('captcha-opacity');
                        $icon_holder.prepend('<div class="captcha-loader"></div>');

                        setTimeout(function() {
                            submitCaptcha($form);
                        }, $options.loadingAnimationDelay);
                    } else {
                        submitCaptcha($form);
                    }
                }).on({
                    mouseenter:function() { if(!hovering) hovering = true },
                    mouseleave:function(){ if(hovering) hovering = false },
                    }, '#captcha-holder'
                );
            });
        }
    });
})(jQuery);