jQuery(document).ready(function($) {

   $('.product .price').remove();

   $('form.cart').on('submit', function(e) {
      // ... (existing code)
  
      let customPrice = parseFloat($('.am-custom-field-price-input').val());
      if (!isNaN(customPrice)) {
          $('<input>').attr({
              type: 'hidden',
              name: 'custom_price',
              value: customPrice
          }).appendTo('form.cart');
      }
  
      // ... (rest of the existing code)
  });

   function priceChange() {
      var selectedThickness =  $('#am_thickness').val();
      AM_ARR.price = selectedThickness;
      $('.am-haube-square_meter').text( `CHF ${AM_ARR.price}` );
   }

   priceChange();

   $('#am_thickness').change(function() {
      priceChange();
   });
   
   //preventing form submission
   $('form.cart').on('submit', function(e) {
      console.log('entered');
      let customFieldSelect = $('#custom_field_select').val();
      let thicknessVal      = $('#am_thickness').val();
      let customLength      = $('#custom_length').val();
      let customWidth       = $('#custom_width').val();
      let customHaubeHeight = $('#haube_custom_height').val();
      let customHaubewidth  = $('#haube_custom_width').val();
      let customHaubelength = $('#haube_custom_length').val();
      let customPriceHaube  = $('.am-custom-field-price-input').val();
      console.log(customPriceHaube);

      if( AM_ARR.category == 'haube' ) {
         if ( customHaubelength === '' || customHaubewidth === '' || customHaubeHeight === '' || thicknessVal == '' ) {
            alert('Please fill in all required fields.');
            e.preventDefault();
            return false;
        } 
      } else if ( customPriceHaube === '' ) {
         e.preventDefault();
         return false;
     } else {
         if ( customFieldSelect === '' || customLength === '' || customWidth === '' || thicknessVal === '' ) {
            alert('Please fill in all required fields.');
            e.preventDefault();
            return false;
        }
      }
   });

   // Function to perform calculation
   function performCalculation() {
      let customFieldSelect    = $('#custom_field_select').val();
      let customLength = parseFloat($('#custom_length').val());
      let customWidth = parseFloat($('#custom_width').val());

      let quantityValue = $('.quantity .input-text').val();

      if (customFieldSelect !== '' && customLength > 0 && customWidth > 0) {
         // calculation for roh category field
          if (customFieldSelect == "roh") {
              let result = ( 5 / quantityValue ) + (customLength + customWidth) * 2 / 900 + 0.75;
              let formattedResult = result.toFixed(2);

              $('.am_custom_field_category_text h2').text(`CHF ${formattedResult} Preis`);
          }
          // calculation for gebrochen category field
          if (customFieldSelect == "gebrochen") {
            let roh_category = ( 5 / quantityValue ) + (customLength + customWidth) * 2 / 900 + 0.75 ;
            let gebrochen_category = ( customLength + customWidth ) * 2 / 1000 + 0.25 ;
            let result = roh_category + gebrochen_category;
            let formattedResult = result.toFixed(2);

            $('.am_custom_field_category_text h2').text(`CHF ${formattedResult} Preis`);
         }
         // calculation for gebrochen und poliert categpry field
         if (customFieldSelect == "gebrochen und poliert") {
            let roh_category = ( 5 / quantityValue ) + (customLength + customWidth) * 2 / 900 + 0.75 ;
            let gebrochen_category = ( customLength + customWidth ) * 2 / 1000 + 0.25 ;
            let gebrochen_poliert_category = ( customLength + customWidth + 600 ) * 2 / 1000 + 1;
            let result = roh_category + gebrochen_category + gebrochen_poliert_category;
            let formattedResult = result.toFixed(2);
            
            $('.am_custom_field_category_text h2').text(`CHF ${formattedResult} Preis`);
         }
      }
   }

   //function to calculate area 
   function calculateArea() {
      let length = $('#custom_length').val();
      let width  = $('#custom_width').val();
      let areaSqmm = length * width ;
      let areaSqm = areaSqmm / 1000000;

      $('.am-custom-field .am-sqm-area').text(`${areaSqm}`);
   }

   // calculating the total price and updating in frontend
   function totalPriceCalculate(){
      let length = $('#custom_length').val();
      let width = $('#custom_width').val();
      let sqmPrice = parseFloat(AM_ARR.price);
      var category = $('.am_custom_field_category_text h2').text();
      var matches = category.match(/CHF (\d+\.\d+)/);

      if ( matches ) {
         var number = parseFloat( matches[1] );
      }

      let calculatedPrice = ( ( length * width / 1000000 ) * sqmPrice * 2 + ( number * 2.35  ) ).toFixed(2);

      if ( calculatedPrice !== null && !isNaN(calculatedPrice) ){
         $('.am-custom-field-price').text(`CHF ${calculatedPrice}`);
         $('am-custom-field-price-input').attr( 'value', `CHF ${calculatedPrice}`);
          // Remove the old price if it exists
         $('.new-price').remove();
      
         // Add the new price
         var newPrice = `<h2 class="new-price">CHF ${calculatedPrice}</h2>`; 
         $('.product .product_title').after(newPrice);
      }      
   }

   function sending_data_zuschnitt(){
      let customFieldSelect = $('#custom_field_select').val();
      let customLength = parseFloat($('#custom_length').val());
      let customWidth = parseFloat($('#custom_width').val());

      if ( AM_ARR.category == 'zuschnitt' ) {
        
         if ( customFieldSelect !== '' && customLength !== '' && customWidth !== '' ){
            let finalPrice =  $('.am-custom-field-price').text();
            if ( finalPrice !== '' ){
               finalPrice = finalPrice.match(/CHF (\d+\.\d+)/)[1];
               var updatedPrice = parseFloat(finalPrice);
               var quantity = jQuery('.input-text.qty').val(); 
               var elementId = $('.product').attr('id');
               var product_id = elementId.split('-')[1];

               function ajax_call(){

                  let quantity_updated = jQuery('.input-text.qty').val(); 

                  $.ajax({
                     url:  AM_ARR.admin_url,
                     type: 'POST',
                     data: {
                           action: 'update_haube_custom_price',
                           price: updatedPrice,
                           quantity: quantity_updated,
                           product_id: product_id,
                           nonce: AM_ARR.nonce
                     },
                     success: function(response) {
                           console.log('Price updated', response);
                     },
                     error: function(response) {
                        console.log(response);
                     }
                  });
               }

               if (quantity == 1){
                  ajax_call();
               }
               $('.input-text.qty').change(function(){
                  ajax_call();
               });
            }
         }     
      } 
   }

   // ratio 1:5

   function checkLengthWidthRatio() {
      let length = parseInt($('#custom_length').val()) || 0;
      let width = parseInt($('#custom_width').val()) || 0;

      let lengthRatio = AM_ARR.length_ratio;
      let widthRatio = AM_ARR.width_ratio;

      // let maxWidth = length * 5;
      let maxWidth = length * (widthRatio / lengthRatio);
      if (width > maxWidth) {
         if (width > maxWidth) {
            alert(`Das Verhältnis darf nicht größer als ${widthRatio} zu ${lengthRatio} von Breite zu Länge sein.`);
            $('#custom_width').val(maxWidth);
        }
      }
   }

   function categoryValidateDimensions() {
      let length = parseFloat($('#custom_length').val());
      let width = parseFloat($('#custom_width').val());

     // Validate minimum and maximum values
      if (length < AM_ARR.min_length || length > AM_ARR.max_length) {
          alert('Die Länge muss zwischen ' + AM_ARR.min_length + ' mm und ' + AM_ARR.max_length + ' mm liegen.');
          $('#custom_length').val(AM_ARR.min_length);
      }
      if (width < AM_ARR.min_width || width > AM_ARR.max_width) {
          alert('Die Breite muss zwischen ' + AM_ARR.min_width + ' mm und ' + AM_ARR.max_width + ' mm liegen.');
          $('#custom_width').val(AM_ARR.min_width);
      }
   }

   // haube category width,length,height range

   function haube_min_max_measurements() {
      var selectedOptionText = $('#am_thickness').find("option:selected").text();
      var matches = selectedOptionText.match(/^(\d+)mm/);
      var thickness = matches ? matches[1] : '';
      if ( thickness && AM_ARR.haube_data[thickness] ) {
        var haube_minLength = parseFloat(AM_ARR.haube_data[thickness].min_length);

        var haube_maxLength = parseFloat(AM_ARR.haube_data[thickness].max_length);
        var haube_minWidth  = parseFloat(AM_ARR.haube_data[thickness].min_width);
        var haube_maxWidth  = parseFloat(AM_ARR.haube_data[thickness].max_width);
        var haube_minHeight = parseFloat(AM_ARR.haube_data[thickness].min_height);
        var haube_maxHeight = parseFloat(AM_ARR.haube_data[thickness].max_height);
      }


      $( '#haube_custom_length, #haube_custom_width, #haube_custom_height' ).on( 'change', function(){
         let length = parseFloat($('#haube_custom_length').val());
         let width = parseFloat($('#haube_custom_width').val());
         let height = parseFloat($('#haube_custom_height').val());  // Assuming there's a height input

         // Validate minimum and maximum values for length
         if (length < haube_minLength || length > haube_maxLength) {
            alert('Die Länge muss zwischen ' +  haube_minLength + ' mm und ' + haube_maxLength + ' mm liegen.');
            $('#haube_custom_length').val( haube_minLength );
         }
         // Validate minimum and maximum values for width
         if (width < haube_minWidth || width > haube_maxWidth) {
            alert('Die Breite muss zwischen ' + haube_minWidth + ' mm und ' + haube_maxWidth + ' mm liegen.');
            $(' #haube_custom_width').val(haube_minWidth);
         }
         // Validate minimum and maximum values for height
         if (height < haube_minHeight || height > haube_maxHeight) {
            alert('Die Höhe muss zwischen ' + haube_minHeight + ' mm und ' +haube_maxHeight + ' mm liegen.');
            $('#haube_custom_height').val(haube_minHeight);
         }
      } );
   }

   haube_min_max_measurements();
   
   $('.am_haube_thickness').change(function() {
      haube_min_max_measurements();
   });


   $('#custom_field_select, #custom_length, #custom_width , .quantity .input-text, #am_thickness').change(  performCalculation );
   $('#custom_length, #custom_width').change(function(){
      calculateArea();
      checkLengthWidthRatio();
      categoryValidateDimensions();
   });
   $(' #custom_field_select, #custom_length, #custom_width, #am_thickness').change(function(){
      totalPriceCalculate();
      sending_data_zuschnitt();
      // showingTotalPrice();
   });


   // Haube category

   // calculationg square meters
   function calculateSquareMeter(){
      let customHaubeHeight = $('#haube_custom_height').val();
      let customHaubewidth = $('#haube_custom_width').val();
      let customHaubelength = $('#haube_custom_length').val();
      let firstpart = customHaubelength /1000 + 40 / 1000 ;
      let secondpart = ( customHaubeHeight /1000 + 40 / 1000 ) * 2;
      let thirdpart = customHaubewidth / 1000;
      let fourthpart = ( customHaubeHeight / 1000 + 40 / 1000 ) * 2;
      let fifthpart = customHaubelength / 1000 * customHaubewidth / 1000;
      if ( customHaubeHeight !== '' && customHaubewidth !== '' && customHaubelength !== '' ){
         /*
         let squareMeter = ( customHaubelength + 40 ) / 1000 * (customHaubeHeight + 40 ) / 1000 * 2 + customHaubewidth / 1000 * ( customHaubeHeight + 40 ) / 1000 * 2 + customHaubelength / 1000 * customHaubewidth / 1000 ; */

         
            let squareMeter =  firstpart * secondpart + thirdpart * fourthpart + fifthpart;

         $('.am-haube-sqm-area').text( squareMeter.toFixed(4) );
      }
   }

   // calculating material cost
   function calculateMaterialCost(){
      let customHaubeHeight = $('#haube_custom_height').val();
      let customHaubewidth = $('#haube_custom_width').val();
      let customHaubelength = $('#haube_custom_length').val();

      let sqareMetre = $('.am-haube-sqm-area').text();
      if ( customHaubeHeight !== '' && customHaubewidth !== '' && customHaubelength !== '' ){
         // console.log( haube_price );
         let materialCost = ( sqareMetre * AM_ARR.price * 1.85 ).toFixed(2) ;
         console.log( AM_ARR.price );
         $('.am-haube-material-cost').text(`CHF ${materialCost}`);
      }
   }

   // calculating workload cost
   function workloadCost(){
      let customHaubeHeight = $('#haube_custom_height').val();
      let customHaubewidth  = $('#haube_custom_width').val();
      let customHaubelength = $('#haube_custom_length').val();
      if ( customHaubeHeight !== '' && customHaubewidth !== '' && customHaubelength !== '' ){
         let workloadCost = ( customHaubelength / 1000 * 2 + customHaubewidth / 1000 * 2 + customHaubeHeight / 1000 * 4 ) * 38 * 2.35 ;
         $('.am-haube-workload-cost').text(`CHF ${workloadCost.toFixed(2)}`);
      }
   }

   // calculating final price
   function finalPrice(){
      let customHaubeHeight = $('#haube_custom_height').val();
      let customHaubewidth  = $('#haube_custom_width').val();
      let customHaubelength = $('#haube_custom_length').val();
      if ( customHaubeHeight !== '' && customHaubewidth !== '' && customHaubelength !== '' ){
         let materialCost = $('.am-haube-material-cost').text();
         let workLoadCost = $('.am-haube-workload-cost').text();
         materialCost = materialCost.match(/CHF (\d+\.\d+)/);
         workLoadCost = workLoadCost.match(/CHF (\d+\.\d+)/);
         let finalPrice = ( parseFloat(materialCost[1] ) + parseFloat(workLoadCost[1]) );
         let roundedPrice = finalPrice.toFixed(2);

         $('.am-haube-final-price').text( `CHF ${roundedPrice}` );
         $('.am-custom-field-price-input').val( roundedPrice );
         $('.new-price').remove();
         var newPrice = `<h2 class="new-price">CHF ${roundedPrice}</h2>`; 
         $('.product .product_title').after(newPrice);
      }
   }

   function sendingDataToajax(){
      let customHaubeHeight = $('#haube_custom_height').val();
      let customHaubewidth  = $('#haube_custom_width').val();
      let customHaubelength = $('#haube_custom_length').val();

      if ( AM_ARR.category == 'haube' ) {
         if ( customHaubeHeight !== '' && customHaubewidth !== '' && customHaubelength !== '' ){

            let finalPrice = $('.am-haube-final-price').text();
            if (finalPrice !== ''){
               finalPrice = finalPrice.match(/CHF (\d+\.\d+)/)[1];
               var updatedPrice = parseFloat(finalPrice);
               var quantity = jQuery('.input-text.qty').val(); 
               // var product_id = $("input[name='product_id']").val();
               var elementId = $('.product').attr('id');
               var product_id = elementId.split('-')[1];
               
               function ajax_call(){

                  let quantity_updated = jQuery('.input-text.qty').val(); 
                  
                  $.ajax({
                     url:  AM_ARR.admin_url,
                     type: 'POST',
                     data: {
                           action: 'update_haube_custom_price',
                           price: updatedPrice,
                           quantity: quantity_updated,
                           product_id: product_id,
                           nonce: AM_ARR.nonce
                     },
                     success: function(response) {
                           console.log('Price updated', response);
                     },
                     error: function(response) {
                        console.log(response);
                     }
                  });
               }

               if (quantity == 1){
                  ajax_call();
               }
               $('.input-text.qty').change(function(){
                  ajax_call();
               });
            }  
         }
      }
   }

   $('#haube_custom_length, #haube_custom_width, #haube_custom_height, .am_haube_thickness').change(function() {
      calculateSquareMeter();
      calculateMaterialCost();
      workloadCost();
      finalPrice();
      sendingDataToajax();
  });
   // $('#custom_length, #custom_width, .quantity .input-text').change(updateCalculations);

   // Reset fields for "zuschnitt" category

   $('.reset_zuschnitt_fields').click(function() {
      $('#custom_field_select').val('');
      $('#custom_length').val('');
      $('#custom_width').val('');
      $('.am-sqm-area').text('0.00');
      $('.am-custom-field-price').text('');
   });

   // Reset fields for "haube" category
   $('.reset_haube_fields').click(function() {
      $('#haube_custom_length').val('');
      $('#haube_custom_width').val('');
      $('#haube_custom_height').val('');
      // Reset calculated areas and prices as well
      $('.am-haube-sqm-area').text('0.00');
      $('.am-haube-material-cost').text('');
      $('.am-haube-workload-cost').text('');
      $('.am-haube-final-price').text('');
      // Repeat for any other fields or text elements you want to reset
   }); 

   function thickness_value() {
      var selectedOption = $('#am_thickness').find('option:selected');
      var thickness = selectedOption.text().replace('mm', '').trim();
      $('#thickness_value').val(thickness);
   }
   thickness_value();
   // sent thickness value 
   $('#am_thickness').change(function() {
      thickness_value();
  });
});

