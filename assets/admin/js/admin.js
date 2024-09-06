jQuery(document).ready( function($) {
   // Initially hide both sections
   $('.am-custom-product-price').hide();
   $('.am-custom-product-price-haube').hide();

   $('.am-ratio-section').hide();
   $('.am-limit-section').hide();

   // Showing input field based on the selected category
   $('#custom_meta_key').change(function() {
      let selected_cat = $(this).val();

      if( selected_cat === 'zuschnitt' ) {
         $('.am-ratio-section').show();
         $('.am-limit-section').show();
         $('.am-height-limit').hide();
         $('.am-custom-product-price').show();
         $('.am-custom-product-price-haube').hide();
      } else if (selected_cat === 'haube') {
         $('.am-ratio-section').hide();
         $('.am-limit-section').hide();
         $('.am-custom-product-price').hide();
         $('.am-custom-product-price-haube').show();
      } else {
         $('.am-ratio-section').hide();
         $('.am-limit-section').hide();
         $('.am-custom-product-price').hide();
         $('.am-custom-product-price-haube').hide();
      }
      $('.am-current-category').val(selected_cat);
   }).change();

});
