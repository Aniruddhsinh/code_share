(function ($, Drupal) {
  Drupal.behaviors.advertiser = {
    attach: function (context, settings) {

      //Show or Hide columns toggle JS
      $(".view-ad-listing .form-row legend span").on('click', function(){
        $(".view-ad-listing .form-row .fieldset-wrapper").toggleClass('open'); 
      });
      $(".view-sku-listing .form-row legend").on('click', function(){
        $(".view-sku-listing .form-row .fieldset-wrapper").toggleClass('open'); 
      });

      //Save filter and my filter button toggle JS
      $(".view-ad-listing .header-save-links-span").on('click', function(){
        $(".view-ad-listing .save-filter-links").slideToggle();
      });
      $(".view-sku-listing .header-save-links-span").on('click', function(){
        $(".view-sku-listing .save-filter-links").slideToggle();
      });

    },
  };
})(jQuery, Drupal);