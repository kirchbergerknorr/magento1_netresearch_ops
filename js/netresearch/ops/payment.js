Event.observe(window, 'load', function () {


    if (typeof checkout != 'undefined') {
        payment.switchMethod = payment.switchMethod.wrap(function (originalMethod, method) {
            if (method && typeof window[method] != 'undefined') {
                payment.currentMethodObject = window[method];
                if (payment.isInline() && !payment.opsAliasSuccess) {
                    payment.toggleContinue(false);
                } else {
                    payment.toggleContinue(true);
                }
            } else {
                if (typeof checkout != 'undefined') {
                    payment.toggleContinue(true);
                } else {
                    toggleOrderSubmit(true);
                }
            }
            originalMethod(method);
        });
    }


    if (payment.onSave) {
        payment.onSave = payment.onSave.wrap(function (original, transport) {
            var response = null;
            if (transport && transport.responseText) {
                try {
                    response = eval('(' + transport.responseText + ')');
                }
                catch (e) {
                    response = {};
                }
            }

            var addValidationErrors = function (pair) {
                var element;
                if (element = $(pair.key)) {
                    Validation.ajaxError(element, pair.value);
                }

            };
            /*
             * if there is an error in payment, need to show error message
             */
            if (response.opsError) {
                $H(response.fields).each(function (pair) {
                    addValidationErrors(pair);
                });
                checkout.gotoSection(response.goto_section);

            }
            original(transport);
        });
    }

    if (payment.save) {
        payment.save = payment.save.wrap(function (originalSaveMethod) {
            payment.originalSaveMethod = originalSaveMethod;
            if ($('ops-retry-form')) {
                checkout.setLoadWaiting('payment');
                if (paymentForm.validator && paymentForm.validator.validate()) {
                    $('ops-retry-form').submit();
                } else {
                    checkout.setLoadWaiting(false);
                    return false;
                }
            } else {
                originalSaveMethod();
            }
        });
    }


    payment.getSelectedAliasElement = function () {
        return $$('input[name="payment[' + payment.currentMethod + '_data][alias]"]:checked')[0];
    };

    payment.isStoredAliasSelected = function () {
        return payment.getSelectedAliasId() != 'new_alias_' + payment.currentMethod;
    };

    payment.getSelectedAlias = function () {
        return payment.getSelectedAliasElement().value;
    };

    payment.getSelectedAliasId = function () {
        return payment.getSelectedAliasElement().id;
    };

    payment.toggleCCInputfields = function (element) {
        if (element.id.indexOf('new_alias') != -1) {

            var currentMethod = element.id.replace('new_alias_', '');
            var currentMethodUC = currentMethod.toUpperCase();

            var paymenDetailsId = $('insert_payment_details_' + currentMethod).id;
            if ($(currentMethod + '_stored_alias_brand') != null) {
                $(currentMethod + '_stored_alias_brand').disable();
            }

            if ($(currentMethod + '_stored_country_id') != null) {
                $(currentMethod + '_stored_country_id').disable();
            }

            var selector = $(currentMethodUC + '_BRAND') || $(currentMethod + '_country_id');
            selector.enable();

            $(paymenDetailsId).show();


            $$('input[type="text"][name="payment[' + currentMethod + '_data][cvc]"]').each(function (cvcEle) {
                cvcEle.up('li').hide();
                cvcEle.disable();
            });
            $$('#' + paymenDetailsId + ' input,#' + paymenDetailsId + ' select').each(function (element) {
                element.enable();
            });
            if(payment.currentMethodObject && payment.currentMethodObject.tokenizationFrame.src != 'about:blank'){
                payment.toggleContinue(false);
            }
        }
        else {
            var currentMethod = element.up('ul').id.replace('payment_form_', '');
            var currentMethodUC = currentMethod.toUpperCase();
            var paymenDetailsId = $('insert_payment_details_' + currentMethod).id;
            if ($(currentMethod + '_stored_alias_brand') != null) {
                $(currentMethod + '_stored_alias_brand').enable();
                $(currentMethod + '_stored_alias_brand').value = element.dataset.brand;
            }

            if ($(currentMethod + '_stored_country_id') != null) {
                $(currentMethod + '_stored_country_id').enable();
                $(currentMethod + '_stored_country_id').value = element.dataset.countryid;
            }

            var selector = $(currentMethodUC + '_BRAND') || $(currentMethod + '_country_id');
            selector.disable();


            $$('input[type="text"][name="payment[' + currentMethod + '_data][cvc]"]').each(function (cvcEle) {
                if ($(currentMethodUC + '_CVC_' + element.id) != null
                    && $(currentMethodUC + '_CVC_' + element.id).id == cvcEle.id
                ) {
                    cvcEle.up('li').show();
                    cvcEle.enable();
                } else {
                    cvcEle.up('li').hide();
                    cvcEle.disable();
                }
            });

            $$('#' + paymenDetailsId + ' input,#' + paymenDetailsId + ' select').each(function (element) {
                element.disable();
            });
            $(paymenDetailsId).hide();
            payment.toggleContinue(true);
        }
    };

    if (typeof accordion != 'undefined') {
        accordion.openSection = accordion.openSection.wrap(function (originalOpenSectionMethod, section) {
            if (section.id == 'opc-payment' || section == 'opc-payment') {

                payment.registerAliasEventListeners();
            }

            originalOpenSectionMethod(section);
        });
    }

    payment.registerAliasEventListeners = function () {
        var aliasMethods = ['ops_cc', 'ops_dc', 'ops_directDebit'];

        aliasMethods.each(function (method) {
            if (typeof  $('p_method_' + method) != 'undefined') {
                $$('input[type="radio"][name="payment[' + method + '_data][alias]"]').each(function (element) {
                    element.observe('click', function (event) {
                        payment.toggleCCInputfields(this);
                    })
                });
            }
            if ($('new_alias_' + method)
                && $$('input[type="radio"][name="payment[' + method + '_data][alias]"]').size() == 1
            ) {
                payment.toggleCCInputfields($('new_alias_' + method));
            }
        });
    };

    payment.jumpToLoginStep = function () {
        if (typeof accordion != 'undefined') {
            accordion.openSection('opc-login');
            $('login:register').checked = true;
        }
    };
});
