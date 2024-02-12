/**
 * @file
 * Default JavaScript file for Modal Page.
 */

(function ($, cookies, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.modalPage = {
    attach: function (context, settings) {

      // Get Modals to Show.
      var modals = $('.js-modal-page-show', context);

      // Verify if there is Modal.
      if (!modals.length) {
        return false;
      }

      // Verify if this project should load Bootstrap automatically.
      var verify_load_bootstrap_automatically = true;
      if (typeof settings.modal_page != 'undefined' && settings.modal_page.verify_load_bootstrap_automatically != 'undefined') {
        verify_load_bootstrap_automatically = settings.modal_page.verify_load_bootstrap_automatically;
      }

      // If Bootstrap is automatic enable it only if its necessary.
      if (!$.fn.modal && !window.bootstrap && verify_load_bootstrap_automatically) {
        $.ajax({url: "/modal-page/ajax/enable-bootstrap", success: function(result){
          location.reload();
        }});
      }

      // Foreach in all Modals.
      $(modals).each(function (index) {

        // Get default variables.
        var modal = $(this);
        var checkbox_please_do_not_show_again = $('.modal-page-please-do-not-show-again', modal);
        var id_modal = $('#modal_id', modal).val();

        var show_once = $(modal).find('#show_once').val();

        // Remove the cookie if show only once option is disabled.
        if (show_once != "1") {
          cookies.remove('hide_modal_id_' + id_modal);
        }

        // Get cookies for do not show again option and show only once.
        var hide_modal_cookie = cookies.get('hide_modal_id_' + id_modal) || cookies.get('please_do_not_show_again_modal_id_' + id_modal);

        // Verify don't show again and show only once options.
        if (hide_modal_cookie) {
          return;
        }

        // Verify auto-open.
        var auto_open = true;

        if (typeof modal.data('modal-options').auto_open != 'undefined' && typeof modal.data('modal-options').auto_open != 'undefined') {
          auto_open = modal.data('modal-options').auto_open;
        }


        modal.on('shown.bs.modal', function () {
          $(this).find(".js-modal-page-ok-buttom").first().focus();
          var auto_hide = $(modal).find('#auto_hide').val();
          var auto_hide_delay = $(modal).find('#auto_hide_delay').val();
          if (auto_hide == "1") {
            setTimeout(function () {
              $(modal).modal('hide');
            }, auto_hide_delay * 1000);
          }
        });

        modal.on('hide.bs.modal', function () {
          if (show_once == "1") {
            cookies.set('hide_modal_id_' + id_modal, true, { expires: 365 * 20, path: '/' });
          }
        });

        modal.on('keydown', function (e) {
          var eventKey = e.key || e.which;
          var lastElement = $(this).find('.js-modal-page-ok-buttom').last().is(':focus');
          var firstElement = $(this).find(".js-modal-page-ok-buttom").first().is(':focus');

          if (eventKey === 'Escape' && !e.shiftKey && lastElement) {
            e.preventDefault();
            $(this).find(".js-modal-page-ok-buttom").first().focus();
          } else if (eventKey === 'Escape' && e.shiftKey && firstElement) {
            e.preventDefault();
            $(this).find(".js-modal-page-ok-buttom").last().focus();
          }
        });

        // Open Modal on Auto Open.
        if (auto_open == true) {

          // Verify if the modal should be trigged by height instead of time
          var offsetHeight = $(modal).find('#height_offset').val();

          if (offsetHeight) {

            // If page has no scrollbar show the modal.
            if ($(document).height() == $(window).height()) {
              modal.modal();
              $(document).off(namespace);
              return;
            }

            // Namespace guarantee we are only working with events of one modal, even on the DOM document.
            var namespace = '.' + $(modal).attr('aria-describedby');
            // Check if is using pixels or percentage, if using percentage
            // remove the % symbol, if using pixels convert to percentage.
            if (!$.isNumeric(offsetHeight)) {
              offsetHeight = offsetHeight.slice(0, -1);
            } else {
              offsetHeight = (offsetHeight / $(document).height()) * 100;
            }
            $(document).on('scroll' + namespace, function () {
              // Account for the offset touch option
              // 0 to start, 0.5 to center, 1 to end.
              var offsetTouch = $(modal).find('#height_offset').attr('offset-type');

              // Getting the position of the scroll on the page according to
              // the page total size and window size.
              var positionY = Math.round(
                ($(window).scrollTop() / ($(document).height() - $(window)
                  .height())) * 100
              );

              var windowPercentage = Math.round(($(window).height() / $(document).height()) * 100);

              // Adjusting the scroll position by the offsetTouch.
              positionY = positionY + (offsetTouch * windowPercentage);

              if (positionY >= offsetHeight) {
                modal.modal();
                $(document).off(namespace);
              }
            });
          } else {
            // Verify if there is a delay to show Modal.
            var delay = $(modal).find('#delay_display').val() * 1000;

            setTimeout(function () {
              if (drupalSettings.modal_page.bootstrap_version == "3x") {
                modal.modal();
              } else {
                modal.modal('show');
              }
            }, delay);
          }
        }

        // Open Modal Page clicking on "open-modal-page" class.
        $('.open-modal-page', modal).on('click', function () {
          if (drupalSettings.modal_page.bootstrap_version == "3x") {
            modal.modal();
          } else {
            modal.modal('show');
          }
        });

        // Open Modal Page clicking on user custom element.
        if (typeof modal.data('modal-options').open_modal_on_element_click != 'undefined' && modal.data('modal-options').open_modal_on_element_click) {

          var link_open_modal = modal.data('modal-options').open_modal_on_element_click;

          $(link_open_modal).on('click', function () {
            if (drupalSettings.modal_page.bootstrap_version == "3x") {
              modal.modal();
            } else {
              modal.modal('show');
            }
          });
        }

        var ok_button = $('.js-modal-page-ok-button', modal);

        ok_button.on('click', function () {

          if (checkbox_please_do_not_show_again.is(':checked')) {
            var cookieTime = $(modal).find('#cookie_expiration').val();
            var cookieSettings = { path: '/' };
            // If not set at all, uses an arbitraty time (never show up again)
            if (!cookieTime) {
              cookieSettings.expires = 10000;
            } else {
              // If it's 0, don't add expire date. Expire at end of session.
              if (cookieTime > 0) {
                cookieSettings.expires = parseInt(cookieTime);
              }
            }

            cookies.set('please_do_not_show_again_modal_id_' + id_modal, true, cookieSettings);

          }

          modal.modal('hide');

          var modalElement = $('.js-modal-page-ok-button').parents('#js-modal-page-show-modal');

          // URL to send data.
          var urlModalSubmit = "/modal/ajax/hook-modal-submit";

          // Get Modal Options.
          var modalOptions = modalElement.data('modal-options');

          // Get Modal ID.
          var modalId = modalOptions.id;

          var dontShowAgainOption = modalElement.find('.modal-page-please-do-not-show-again').is(':checked');

          var modalState = new Object();

          modalState.dont_show_again_option = dontShowAgainOption;

          // Params to be sent.
          var params = new Object();

          // Send Modal ID.
          params.id = modalId;

          // Send Modal State.
          params.modal_state = modalState;

          $.post(urlModalSubmit, params, function (result) { });

          var redirect = $(this).attr('data-redirect');
          if (typeof redirect != 'undefined' && redirect.length > 0) {
            window.location.replace(redirect);
          }

        });
      });
    }
  };
})(jQuery, window.Cookies, Drupal, drupalSettings);
