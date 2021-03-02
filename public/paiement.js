 type = "text/javascript"
 src = "https://js.stripe.com/v2/" > < /script> <
 script src = "https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js" >

     Stripe.setPublishableKey('pk_test_Et9GdgxRPH9oBRXGCfMKX6RK')
 var $form = $('#payment_form')
 $form.submit(function(e) {
     e.preventDefault()
     $form.find('.button').attr('disabled', true)
     Stripe.card.createToken($form, function(status, response) {
         if (response.error) {
             $form.find('.message').remove();
             $form.prepend('<div class="ui negative message"><p>' + response.error.message + '</p></div>');
             $form.find('.button').attr('disabled', false)
         } else {
             var token = response.id
             $form.append($('<input type="hidden" name="stripeToken">').val(token))
             $form.get(0).submit()
         }
     })
 })