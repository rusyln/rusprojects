/**
 * @file
 * Contains definition of the behaviour AOS.js.
 */

(function ($, Drupal, drupalSettings, once) {
  "use strict";

  const version = drupalSettings.aosjs.version;
  const library = drupalSettings.aosjs.library;

  Drupal.behaviors.aosJS = {
    attach: function (context, settings) {

      const example = drupalSettings.aosjs.sample;
      const Selector = example.selector;

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
          // AOS.js additional configuration.
          disable: example.disable,
          startEvent: example.startEvent,
          initClassName: example.initClassName,
          animatedClassName: example.animatedClassName,
          useClassNames: example.useClassNames,
          disableMutationObserver: example.disableMutationObserver,
          debounceDelay: example.debounceDelay,
          throttleDelay: example.throttleDelay,
        };

        //new Drupal.aosJSdemo(options);
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
              library: $('.aos-library').val() ? $('.aos-library').val() : example.library,
              animation: $('#edit-animation').val(),
              offset: $('#edit-offset').val(),
              delay: $('#edit-delay').val(),
              duration: $('#edit-duration').val(),
              easing: $('#edit-easing').val(),
              once: $('#edit-once').is(':checked'),
              mirror: $('#edit-mirror').is(':checked'),
              anchorPlacement: $('#edit-anchor_placement').val(),
              // AOS.js additional configuration.
              disable: $('#edit-disable').is(':checked'),
              startEvent: $('#edit-start-event').val(),
              initClassName: $('#edit-init-class-name').val(),
              animatedClassName: $('#edit-animated-class-name').val(),
              useClassNames: $('#edit-use-class-names').is(':checked'),
              disableMutationObserver: $('#edit-disable-mutation-observer').is(':checked'),
              debounceDelay: $('#edit-debounce-delay').val(),
              throttleDelay: $('#edit-throttle-delay').val(),
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
    let animation = options.animation
      , additional = {
      disable: options.disable,
      startEvent: options.startEvent,
    }

    if ( version === 'v3') {
      $( options.selector ).attr( "data-aos-mirror", options.mirror === 1 );

      // Set additional advanced setting for version 3.
      additional.initClassName = options.initClassName;
      additional.animatedClassName = options.animatedClassName;
      additional.useClassNames = options.useClassNames;
      additional.disableMutationObserver = options.disableMutationObserver;
      additional.debounceDelay = options.debounceDelay;
      additional.throttleDelay = options.throttleDelay;

      if ( library === 'animate') {
        if ((animation.indexOf("In") >= 0) || /In/.test(animation)) {
          $( options.selector ).css('visibility', 'hidden');
        } else {
          $( options.selector ).css('visibility', 'visible');
        }

        if ( additional.animatedClassName === 'animate__animated' ) {
          animation = 'animate__' + options.animation
        }
        additional.initClassName = false;
        additional.useClassNames = true;

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

    console.log(additional);

    // Initial AOS.
    AOS.init(additional);
  };

})(jQuery, Drupal, drupalSettings, once);
