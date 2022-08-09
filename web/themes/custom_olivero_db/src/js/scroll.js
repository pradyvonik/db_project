(function($, Drupal) {
  // I want some code to run on page load, so I use Drupal.behaviors
  Drupal.behaviors.scroll = {
    attach(context, settings) {
      window.onscroll = function() {
        let btn = document.getElementById("scroll-to-top");
        if (!btn) {
          btn = document.createElement("button");
          btn.id = "scroll-to-top";
          document.body.appendChild(btn);
        }
        btn.style.display =
          document.documentElement.scrollTop > 300 ? "block" : "none";
        btn.onclick = function() {
          document.body.scrollTop = 0;
          document.documentElement.scrollTop = 0;
        };
      };
    }
  };
})(jQuery, Drupal);
