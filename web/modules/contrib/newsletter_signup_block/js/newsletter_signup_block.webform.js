(function (Drupal) {
  Drupal.behaviors.NewsletterSignupBlock.Webform = {
    attach: function (context, settings) {
      var dynamicEmail = context.querySelector('.js-dynamic-email');
      var storedEmail = sessionStorage.getItem('NewsletterSignupBlock.Email');
      if (dynamicEmail && storedEmail) {
        dynamicEmail.value = storedEmail;
      }
    }
  }
})(Drupal);
