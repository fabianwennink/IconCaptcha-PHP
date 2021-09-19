/**
 * IconCaptcha Plugin: v3.0.0
 * Copyright © 2021, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */

// Theme selector.
document.addEventListener('DOMContentLoaded', function() {
    const elements = document.querySelectorAll('.theme > span:first-child');
    for (let element of elements) {
        element.addEventListener('click', function (e) {
            const theme = e.target.getAttribute('data-theme');
            const holder = document.querySelector('.iconcaptcha-holder');

            // Set the theme attribute.
            holder.setAttribute('data-theme', theme);

            // Filter out the theme class.
            const classes = holder.className.split(" ").filter(function(c) {
                return !c.includes('theme');
            });
            holder.className = classes.join(" ").trim();

            // Set the new theme class.
            holder.classList.add('iconcaptcha-theme-' + theme);
        });
    }
});