Event.observe(window, 'load', function() {

    // check if we are dealing with OneStepCheckout
    payment.isOneStepCheckout = $$('.onestepcheckout-place-order');
    payment.formOneStepCheckout = $('onestepcheckout-form');
    payment.holdOneStepCheckout = false;

    if(payment.isOneStepCheckout){

        window.get_separate_save_methods_function = window.get_separate_save_methods_function.wrap(function (originalCall, url, update_payments) {

            var aliasMethods = ['ops_cc', 'ops_dc'];

            aliasMethods.each(function (method) {
                if (typeof  $('p_method_' + method) != 'undefined') {
                    $$('input[type="radio"][name="payment[' + method + '_data][alias]"]').each(function (element) {
                        element.observe('click', function () {
                            payment.toggleCCInputfields(this);
                        })
                    });
                }
                var newAliasElement = $('new_alias_' + method);
                if (newAliasElement
                    && $$('input[type="radio"][name="payment[' + method + '_data][alias]"]').size() == 1
                ) {
                    payment.toggleCCInputfields(newAliasElement);
                }
            });

            return originalCall(url, update_payments);

        });
        //set the form element
        payment.form = payment.formOneStepCheckout;

         //bind event handlers to buttons
        payment.isOneStepCheckout.each(function(elem){
            elem.observe('click', function(e){

                Event.stop(e);
                if(!payment.holdOneStepCheckout){
                    return;
                }

                if ('ops_directDebit' == payment.currentMethod && payment.holdOneStepCheckout) {
                    window.already_placing_order = true;
                }

                if ('ops_cc' == payment.currentMethod && payment.holdOneStepCheckout) {
                    window.already_placing_order = true;
                }
                //normally this is not called
                payment.save();
            });
        });


         //add new method to restore the place order state when failure
        payment.toggleOneStepCheckout =  function(action){
            var submitelement = $('onestepcheckout-place-order');
            var loaderelement = $$('.onestepcheckout-place-order-loading');

            if(action === 'payment'){

                window.already_placing_order = true;
                /* Disable button to avoid multiple clicks */
                submitelement.removeClassName('orange').addClassName('grey');
                submitelement.disabled = true;
                payment.holdOneStepCheckout = true;
            }

            if(action === 'remove'){

                submitelement.removeClassName('grey').addClassName('orange');
                submitelement.disabled = false;

                if(loaderelement){
                    loaderelement = loaderelement[0];
                    if(loaderelement){
                        loaderelement.remove();
                    }
                }

                window.already_placing_order = false;
                payment.holdOneStepCheckout = false;
            }
        };

        //wrap this to toggle the buttons in OneStepCheckout.
        payment.opcToggleContinue = function (active) {

            if (!active) {
                payment.toggleOneStepCheckout('payment');
            } else {
                payment.toggleOneStepCheckout('remove');
            }
        };
    }
    // check if we are dealing with OneStepCheckout end

});
