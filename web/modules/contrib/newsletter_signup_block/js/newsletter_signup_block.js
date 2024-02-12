(function (Drupal) {
  Drupal.behaviors.NewsletterSignupBlock = {
    attach: function (context, settings) {
      var form = context.querySelector('.js-newsletter-signup-form');
      var email = form.querySelector('.js-newsletter-signup-email');
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var emailAddress = email.value;
        if (emailAddress) {
          if (typeof (Storage) !== "undefined") {
            sessionStorage.setItem("NewsletterSignupBlock.Email", emailAddress);
          }
        }
        form.submit();
      });
    }
  }
})(Drupal);
