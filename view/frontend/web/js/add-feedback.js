define([
    'Magento_Ui/js/modal/modal',
    'uiComponent',
    "jquery",
    'Magento_Ui/js/modal/alert',
    "jquery/ui",
], function (modal, Component, $, alert) {
    'use strict';

    $.widget('mage.addfeedback', {
        options: {
            confirmMsg: ('divElement is removed.')
        },
        _create: function () {

            this.getOrderData();
            
             
        },
        getOrderData: function () {
            var self = this;
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: 'Give Feedback',
                buttons: [],
                open: function( event, ui ) {
                    debugger;
                }

            };
            var popup = modal(options, $('#testdiv'));

            $(".add_feedback" ).each(function(index) {
                $(this).on("click", function($){
                    var orderId = this.getAttribute('order-id');
                    jQuery.ajax({
                        url: '/feedback/feedback/orderinfo',
                        type: 'post',
                        dataType: 'html',
                        showLoader: true ,
                        data: {
                            orderdata: orderId
                        },
                        complete: function (output) {
                            var res = output.responseText;
                            jQuery('#testdiv').html(res);
                            jQuery('#testdiv').modal('openModal');
                            self.feedbackFormSubmit();
                        }
                    });
                });
            });
        },
        feedbackFormSubmit: function () {
            $("#feedback_form" ).on('submit', function (e) {
                e.preventDefault();
                var formData = jQuery('#feedback_form').serialize();
                jQuery.ajax({
                    url: '/feedback/feedback/submit',
                    type: 'post',
                    dataType: 'json',
                    showLoader: true ,
                    data: formData,
                    complete: function (output) {
                         var res = jQuery.parseJSON(output.responseText);
                         if(res.status == 'success'){
                            jQuery("#testdiv").modal("closeModal");

                         }
                    }
                });
            });
        }

        

    });
    return $.mage.addfeedback;



});