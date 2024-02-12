/**
 * @file
 * Contains definition of the behaviour AOS.js.
 */

(function ($, Drupal, drupalSettings, once) {
  "use strict";

  const version = drupalSettings.aosjs.version;
  const library = drupalSettings.aosjs.library;
  const advance = drupalSettings.aosjs.additional;

  Drupal.behaviors.aosJS = {
    attach: function (context, settings) {

      const example = drupalSettings.aosjs.sample;
      const Selector = example.selector;

      console.log(example);

      if (once('aos__sample', Selector).length) {
        let options = {
          selector: Selector,
          animation: example.animation,
          offset: example.offset,
          delay: example.delay,
          duration: example.duration,
          easing: example.easing,
          once: example.once,
          mirror: example.mirror,
          anchorPlacement: example.anchorPlacement,
        };

        // AOS.js preview warning if library version changes.
        $(once('aos__version', '#edit-version', context)).on(
          'change',
          function (event) {
            if (version !== $(this).val()) {
              $('.aos__warning').fadeIn();
            } else {
              $('.aos__warning').fadeOut();
            }
          }
        );

        // AOS.js preview replay.
        $(once('aos__animation', '#edit-animation', context)).on(
          'change',
          function (event) {
            $(Selector).attr('class', 'aos__sample');

            let options = {
              selector: Selector,
              animation: $('#edit-animation').val(),
              offset: $('#edit-offset').val(),
              delay: $('#edit-delay').val(),
              duration: $('#edit-duration').val(),
              easing: $('#edit-easing').val(),
              once: $('#edit-once').is(':checked'),
              mirror: $('#edit-mirror').is(':checked'),
              anchorPlacement: $('#edit-anchor_placement').val(),
            };

            setTimeout(function () {
              new Drupal.aosJSdemo(options);
            }, 10);
          }
        ).trigger('change');

        $(once('aos__replay', '.aos__replay', context)).on(
          'click',
          function (event) {
            $('#edit-animation').trigger('change');
            event.preventDefault();
        });

      }
    }
  };

  Drupal.aosJSdemo = function (options) {
    let animation = options.animation;

    if ( version === 'v3') {
      $( options.selector ).attr( "data-aos-mirror", options.mirror === 1 );

      if ( library === 'animate') {
        if ((animation.indexOf("In") >= 0) || /In/.test(animation)) {
          $( options.selector ).css('visibility', 'hidden');
        } else {
          $( options.selector ).css('visibility', 'visible');
        }

        if ( advance.animatedClassName === 'animate__animated' ) {
          animation = 'animate__' + options.animation
        }
        advance.initClassName = false;
        advance.useClassNames = true;

        document.addEventListener('aos:in', ({ detail }) => {
          if ((animation.indexOf("In") >= 0) || /In/.test(animation)) {
            if ($( options.selector ).hasClass(animation)) {
              $( options.selector ).css('visibility', 'visible');
            }
          }
        });

        document.addEventListener('aos:out', ({ detail }) => {
          if ((animation.indexOf("In") >= 0) || /In/.test(animation)) {
            $( options.selector ).css('visibility', 'hidden');
          }
        });

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

    // Initial AOS.
    AOS.init( advance );
  };

})(jQuery, Drupal, drupalSettings, once);
