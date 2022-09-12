$(document).ready(function() {
    if($('#form-payplus')) {
        $('#form-payplus').submit();
        console.log('Submitting Payment Form')
    }
    $('head').append('<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">');
    $("#DISPLAY").on('change', function(e) {
        var opt = jQuery("#DISPLAY").val();
    });

});

   /* var iframes = document.getElementsByTagName('iframe');
    if(iframes.length == 1) {
      window.pp_iframe = iframes[0]
    } else if(iframes.length > 1) {
      window.pp_iframe = document.getElementById('payplusPaymentIframe')
    } else {
      console.log('no-iframe-payplus-!')
      return
    }

       window.addEventListener("message", function(e) {
        console.log('eventeventevent', event)
         var data = e.data
         if(window.pp_iframe) {
             if(!window.pp_iframe) {
                   console.log('error id iframe !')
                    return
             }

            if(data.canMakePaymentsPayPlus == 'start') {
             let applePaySession = {}

              applePaySession = new ApplePaySession(3, data.data.request)
              window.a_session = applePaySession //put in global

              applePaySession.onvalidatemerchant = function(event) {
                  const validationUrlApplePayPayPlus = event.validationURL
                  window.pp_iframe.contentWindow.postMessage({onmerchantPayPlusAppPay:'success', urlValidationAP:validationUrlApplePayPayPlus}, '*')
                };

              window.a_session.onpaymentauthorized = function(event) {
                 const token = event.payment.token.paymentData
                 window.pp_iframe.contentWindow.postMessage({onpaymentauthorizedPayPlusAppPay:'success', token:token}, '*')
             }

                applePaySession.begin()
            }


            if(data.returnValidateMerchantPayPlusAppPay == 'success') {
                console.log(data.data.session)
                window.a_session.completeMerchantValidation(data.data.session)
            }

            if(data.returnRespTransactionPayPlusAppPay == 'success') {
                   if (data.data.response.status === 'success') {
               window.a_session.completePayment({
                  status: window.ApplePaySession.STATUS_SUCCESS
                })
              } else {
                window.a_session.completePayment({
                  status: window.ApplePaySession.STATUS_FAILURE
                })
              }
            }

             window.a_session.oncancel = function(event) {
                 window.a_session = null
                 window.pp_iframe.contentWindow.postMessage({oncancelPayPlusAppPay:'success'}, '*')
             }
         }
    });


    /*
       window.addEventListener('message', receiveMessageIframePayPlusAppPay, false)

   function receiveMessageIframePayPlusAppPay(e) {

         var data = e.originalEvent.data
         let iframePP = document.getElementById('payplusPaymentIframe')
         if(!iframePP) {
              console.log('do nothig !')
              return
         }

        if(data.canMakePaymentsPayPlus == 'start') {
         let applePaySession = {}

          applePaySession = new ApplePaySession(3, data.data.request)
          window.a_session = applePaySession //put in global

          applePaySession.onvalidatemerchant = function(event) {
              const validationUrlApplePayPayPlus = event.validationURL
              iframePP.contentWindow.postMessage({onmerchantPayPlusAppPay:'success', urlValidationAP:validationUrlApplePayPayPlus}, '*')
            };

          window.a_session.onpaymentauthorized = function(event) {
             const token = event.payment.token.paymentData
             iframePP.contentWindow.postMessage({onpaymentauthorizedPayPlusAppPay:'success', token:token}, '*')
         }

            applePaySession.begin()
        }


        if(data.returnValidateMerchantPayPlusAppPay == 'success') {
            window.a_session.completeMerchantValidation(data.data.session)
        }

        if(data.returnRespTransaction == 'success') {
               if (data.data.response.status === 'success') {
           window.a_session.completePayment({
              status: window.ApplePaySession.STATUS_SUCCESS
            })
          } else {
            window.a_session.completePayment({
              status: window.ApplePaySession.STATUS_FAILURE
            })
          }
        }

         window.a_session.oncancel = function(event) {
             window.a_session = null
             iframePP.contentWindow.postMessage({oncancelPayPlusAppPay:'success'}, '*')
         }
   }
    */

