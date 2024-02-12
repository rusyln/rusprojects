/**
 * @file
 * Contains definition of the behaviour AOS.js.
 */

(function ($, Drupal, drupalSettings, once) {
  "use strict";

  const version = drupalSettings.aosjs.version;
  const library = drupalSettings.aosjs.library;

  let additional = drupalSettings.aosjs.additional;

  Drupal.behaviors.aosInit = {
    attach: function (context, settings) {

      const elements = drupalSettings.aosjs.elements;
      let isItemInit = false;

      $.each(elements, function (index, element) {
        let options = {
          selector: element.selector,
          animation: element.animation,
          offset: element.offset,
          delay: element.delay,
          duration: element.duration,
          easing: element.easing,
          once: element.once,
          mirror: element.mirror,
          anchor: element.anchor,
          anchorPlacement: element.anchorPlacement,
        };

        if (once('aosjs', options.selector).length) {
          new Drupal.aosJS(options);
          // If there is even a selector then
          // the init value must be set to true.
          isItemInit = true;
        }
      });

      // Check is there item to init first.
      if ( isItemInit ) {
        if ( library === 'animate') {
          additional.initClassName = false;
          //animatedClassName: (compat ? '' : 'animate__') + 'animated',
          animatedClassName: 'animate__animated',
            additional.useClassNames = true;
        }
        // Initial AOS now.
        AOS.init( additional );
      }

    }
  };

  Drupal.aosJS = function (options) {
    let animation = options.animation;

    if ( version === 'v2') {
      if (options.anchor !== null && options.anchor.length) {
        $( options.selector ).attr( "data-aos-anchor", options.anchor ? options.anchor : '');
      }
    }
    else {
      $( options.selector ).attr( "data-aos-mirror", options.mirror === 1 );

      if ( library === 'animate') {
        if ((animation.indexOf("In") >= 0) || /In/.test(animation)) {
          $( options.selector ).css('visibility', 'hidden');
        } else {
          $( options.selector ).css('visibility', 'visible');
        }

        if ( additional.animatedClassName === 'animate__animated' ) {
          animation = 'animate__' + options.animation
        }

        // Add Animate.css custom properties.
        if ( options.delay ) {
          $( options.selector ).css({
            '-webkit-animation-delay': options.delay + 'ms',
            '-moz-animation-delay': options.delay + 'ms',
            '-ms-animation-delay': options.delay + 'ms',
            '-o-animation-delay': options.delay + 'ms',
            'animation-delay': options.delay + 'ms',
            '--animate-delay': options.delay + 'ms',
          });
        }
        if ( options.duration ) {
          $( options.selector ).css({
            '-webkit-animation-duration': options.duration + 'ms',
            '-moz-animation-duration': options.duration + 'ms',
            '-ms-animation-duration': options.duration + 'ms',
            '-o-animation-duration': options.duration + 'ms',
            'animation-duration': options.duration + 'ms',
            '--animate-duration': options.duration + 'ms',
          });
        }

        document.addEventListener('aos:in', ({ detail }) => {
          if ((animation.indexOf("In") >= 0) || /In/.test(animation)) {
            $( options.selector ).css('visibility', 'visible');
          }
        });

        document.addEventListener('aos:out', ({ detail }) => {
          if ((animation.indexOf("In") >= 0) || /In/.test(animation)) {
            $( options.selector ).css('visibility', 'hidden');
          }
        });
      }
    }

    // Build AOS.js from global settings.
    $( options.selector ).attr( "data-aos", animation );
    $( options.selector ).attr( "data-aos-offset", options.offset );
    $( options.selector ).attr( "data-aos-delay", options.delay );
    $( options.selector ).attr( "data-aos-duration", options.duration );
    $( options.selector ).attr( "data-aos-easing", options.easing );
    $( options.selector ).attr( "data-aos-once", options.once === 1 );
    $( options.selector ).attr( "data-aos-anchor-placement", options.anchorPlacement );
  };

})(jQuery, Drupal, drupalSettings, once);
