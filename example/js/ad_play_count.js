(function ($, Drupal) {
  Drupal.behaviors.ad_play_count = {
    attach: function (context, settings) {      
      $('.allocation-input').on('keyup change',function () {
        var sum = 0;
        var campaign_id_string = jQuery('.hidden-campaign-ids').val();
        var campaign_ids = campaign_id_string.split(" ");
      
        $(campaign_ids).each(function( index, value ) {
          var input_id = "#edit-campaign-data-"+value+"-allocation";
          if(input_id)
            {
              console.log($(input_id).attr('name'));
              sum += parseFloat($(input_id).val());
            }
        });
        if(sum > 100) {
        //  var arrear_allotment = sum - 100;
          $(".exceed-content").show();
          $("button#edit-submit").prop('disabled', true);
        } else if(sum < 100) {
        //  var arrear_allotment =  100 - sum;
          $(".exceed-content").hide();
          $("button#edit-submit").prop('disabled', false);
        } else {
          $(".total-text-column").text("Total");
          $(".exceed-content").hide();
          $("button#edit-submit").prop('disabled', false);
        }
        $(".total-allocation").text(sum + " %");
      });
    },
  };
})(jQuery, Drupal);