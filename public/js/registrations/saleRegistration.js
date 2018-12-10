$(function () {
    siblingsHandling();
    var ajaxAccountDetailUrl    = '/ajax/account/details/';

    $('body').on("click", "#sale_submit_button", function (evt) {
        evt.preventDefault();
        var taxFlag         = false;
        var cutomTitle      = 'Are you sure to save the sale?';
        var customButton    = 'Yes, Save it!';

        swal({
          title: cutomTitle,
          type: 'warning',
          showCancelButton: true,
          focusCancel : true,
          confirmButtonColor: '#d33',
          confirmButtonText: customButton
        }).then((result) => {
          if (result.value) {
            $(this).attr('disabled', true);
            //submit form on confirmation
            $(this).parents('form:first').submit();
          }
        })
    });

    //to invoke weighment modal
    $('body').on("click", ".add_note", function (evt) {
        var selectBox   = $(this).closest('tr').find('.products_combo');
        var fieldValue  = selectBox.val();
        var rowId       = selectBox.data('index-no');
        var productName = selectBox.find(':selected').text();

        if(fieldValue && fieldValue != '' && fieldValue != 'undefined') {
            var defaultWeighmentWastage = selectBox.find(':selected').data('weighment_wastage');
            if(defaultWeighmentWastage == '' || defaultWeighmentWastage == 'undefined') {
                defaultWeighmentWastage = 0;
            }
            $('#modal_row_id').val(rowId);
            $('#modal_product').val(productName);
            $('#modal_gross_quatity').val('');
            $('#modal_numbers').val('');
            $('#modal_unit_wastage').val(defaultWeighmentWastage);
            $('#modal_total_wastage').val('');
            $('#modal_net_quantity').val('');
            $('#weighment_modal').modal('show');
            $('#modal_gross_quatity').focus();
        }
    });

    //customer details
    $('body').on("change", "#customer_account_id", function (evt) {
        var oldBalanceAmount    = 0;
        var customerAccountId   = $(this).val();
        $('#ob_info').html('');
        $('#old_balance').val(0);

        if(customerAccountId && customerAccountId != -1) {
            var selectedOption = $(this).find(':selected');
            
            $.ajax({
                url: ajaxAccountDetailUrl + customerAccountId,
                method: "get",
                data: {},
                success: function(result) {
                    
                    if(result && result.flag) {
                        var account     = result.account;
                        var obDebit     = result.oldBalance.oldDebit;
                        var obCredit    = result.oldBalance.oldCredit;

                        if(account.type == 3) {
                            $('#customer_name').val(account.name);
                            $('#customer_phone').val(account.phone);
                        }

                        if(obDebit != 'undefined' && obCredit != 'undefined') {
                            oldBalanceAmount = obDebit - obCredit;
                        }

                        if(oldBalanceAmount < 0) {
                            //debit < credit => company owes customer
                            $('#ob_info').html(' (Payable To Customer)');
                        } else {
                            //debit > credit => customer owes company
                            $('#ob_info').html(' (Receivable From Customer)');
                        }
                        $('#old_balance').val(oldBalanceAmount);
                        calculateTotalSaleBill();
                    } else {
                        $('#customer_name').val('');
                        $('#customer_phone').val('');
                    }
                },
                error: function (err) {
                    $('#customer_name').val('');
                    $('#customer_phone').val('');
                }
            });
        } else {
            $('#customer_name').val('');
            $('#customer_phone').val('');
        }
        //calculate total sale bill
        calculateTotalSaleBill();
    });

    //
    $('body').on("keyup", "#customer_phone", function (evt) {
        var input               = $(this).val();
        var selectedAccountId   = $('#customer_account_id').val();

        if(input.length > 9) {
            accountId   = $('#customer_account_id').find(`[data-phone='${input}']`).val();
            accountName = $('#customer_account_id').find(`[data-phone='${input}']`).text();

            if(selectedAccountId != -1) {
                if(accountId && accountId > 0 && accountId != selectedAccountId) {
                    if(confirm("Found an account related to the entered phone number. Do you want to change the 'customer account' field to " + accountName + "?")) {
                        $('#customer_account_id').val(accountId);
                        $('#customer_account_id').trigger('change');
                        $('#customer_parent_div').addClass('has-warning');
                        $('#description').focus();
                    }
                }
            } else if(accountId && accountId > 0) {
                $('#customer_account_id').val(accountId);
                $('#customer_account_id').trigger('change');
                $('#customer_parent_div').addClass('has-warning');
                $('#description').focus();
                alert('Found an account related to the entered phone number. Sale would credited to '+ accountName);
            }
        }
    });

    //product change event
    $('body').on("change", ".products_combo", function (evt) {
        var fieldValue  = $(this).val();
        var rowId       = $(this).data('index-no');

        if(fieldValue && fieldValue != '' && fieldValue != 'undefined') {
            /*var defaultWeighmentWastage = $(this).find(':selected').data('weighment_wastage');
            if(defaultWeighmentWastage) {
                $('#modal_row_id').val(rowId);
                $('#modal_product').val($(this).find(':selected').text());
                $('#modal_gross_quatity').val('');
                $('#modal_numbers').val('');
                $('#modal_unit_wastage').val(defaultWeighmentWastage);
                $('#modal_total_wastage').val('');
                $('#modal_net_quantity').val('');
                $('#weighment_modal').modal('show');
            }*/

            //enabling quantity & rate in same column
            $(this).closest('tr').find('.net_quantity').attr('disabled', false);
            $(this).closest('tr').find('.sale_rate').attr('disabled', false);
            $(this).closest('tr').find('.sub_bill').attr('disabled', false);
            //enabling weighment fields
            $(this).closest('tr').find('.gross_quantity').attr('disabled', false);
            $(this).closest('tr').find('.product_number').attr('disabled', false);
            $(this).closest('tr').find('.unit_wastage').attr('disabled', false);
            $(this).closest('tr').find('.total_wastage').attr('disabled', false);

            //enabling next combo box
            $('#product__row_'+(rowId+1)).find('.products_combo').attr('disabled', false);
            //show more row
            $('#product__row_'+(rowId+3)).show();
            //focus to same row quantity
            $('#net_quantity_'+rowId).focus();
        } else {
            //disabling quantity & rate in same column
            $(this).closest('tr').find('.net_quantity').attr('disabled', true);
            $(this).closest('tr').find('.sale_rate').attr('disabled', true);
            $(this).closest('tr').find('.sub_bill').attr('disabled', true);
            //enabling weighment fields
            $(this).closest('tr').find('.gross_quantity').attr('disabled', true);
            $(this).closest('tr').find('.product_number').attr('disabled', true);
            $(this).closest('tr').find('.unit_wastage').attr('disabled', true);
            $(this).closest('tr').find('.total_wastage').attr('disabled', true);
            
            //setting empty values for deselected product
            $('#sale_notes'+rowId).val('');
            $('#net_quantity_'+rowId).val('');
            $('#sale_rate_'+rowId).val('');

            $('#product__row_'+(rowId+1)).find('.products_combo').val('');
            //disabling next combo box

            $('#product__row_'+(rowId+1)).find('.products_combo').attr('disabled', true);
            //hide more row
            $('#product__row_'+(rowId+3)).hide();
        }

        //disabiling same value selection in 2 product combo boxes
        siblingsHandling();
        initializeSelect2();
        //calculate total sale bill
        //calculateTotalSaleBill(); //wont work

    });

    $('body').on("click", "#btn_modal_weighment_submit", function (evt) {
        //calculate total quantity
        calculateQuantity();

        var rowId = $('#modal_row_id').val();
        var grossQuantity = $('#modal_gross_quatity').val();
        var productNumber = $('#modal_numbers').val();
        var unitWastage   = $('#modal_unit_wastage').val();
        var totalWastage  = $('#modal_total_wastage').val();
        var netQuantity   = $('#modal_net_quantity').val();

        if(rowId && rowId != 'undefined' && grossQuantity && productNumber && unitWastage && totalWastage && netQuantity) {
            if(grossQuantity <= 0) {
                alert("Invalid value in gross quantity.");
                $('#modal_gross_quatity').focus();
            } else if(productNumber <= 0) {
                alert("Invalid value in number of items.");
                $('#modal_numbers').focus();
            } else if(unitWastage <= 0) {
                alert("Invalid value in unit wastage.");
                $('#modal_unit_wastage').focus();
            } else if(netQuantity <= 0) {
                alert("Invalid value in net quantity.");
                $('#modal_unit_wastage').focus();
            } else {
                $('#gross_quantity_'+rowId).val(grossQuantity);
                $('#product_number_'+rowId).val(productNumber);
                $('#unit_wastage_'+rowId).val(unitWastage);
                $('#total_wastage_'+rowId).val(totalWastage);
                $('#net_quantity_'+rowId).val(netQuantity);
                $('#notes_'+rowId).val(grossQuantity + ' - (' + productNumber + ' nos x ' + unitWastage + ') = ' + netQuantity);
                
                $('#weighment_modal').modal('hide');
                $('#sale_rate_'+rowId).focus();
            }
        } else {
            alert("Fill all fields!");
        }
    });

    //modal weighment calc event
    $('body').on("change keyup", "#modal_gross_quatity", function (evt) {
        //calculate total quantity
        calculateQuantity();
    });
    //modal weighment calc event
    $('body').on("change keyup", "#modal_numbers", function (evt) {
        //calculate total quantity
        calculateQuantity();
    });
    //modal weighment calc event
    $('body').on("change keyup", "#modal_unit_wastage", function (evt) {
        //calculate total quantity
        calculateQuantity();
    });

    //sale quantity event actions
    $('body').on("change keyup", ".net_quantity", function (evt) {
        //calculate total sale bill
        calculateTotalSaleBill();
    });

    //sale rate event actions
    $('body').on("change keyup", ".sale_rate", function (evt) {
        //calculate total sale bill
        calculateTotalSaleBill();
    });

    //sale rate event actions
    $('body').on("change keyup", "#discount", function (evt) {
        //calculate total sale bill
        calculateTotalSaleBill();
    });

    //sale rate event actions
    $('body').on("change keyup", "#cash_received", function (evt) {
        //calculate total sale bill
        calculateTotalSaleBill();
    });
});

//method for quantity calculation
function calculateQuantity() {
    var grossQuantity = $('#modal_gross_quatity').val();
    var productNumber = $('#modal_numbers').val();
    var unitWastage   = $('#modal_unit_wastage').val();
    var totalWastage  = 0;
    var netQuantity   = 0;

    if(grossQuantity && grossQuantity != 'undefined' && productNumber && productNumber != 'undefined' && unitWastage && unitWastage != 'undefined') {
        totalWastage = unitWastage * productNumber;
        netQuantity  = grossQuantity - totalWastage;
    }

    $('#modal_total_wastage').val(totalWastage);
    $('#modal_net_quantity').val(netQuantity);
}

//method for total bill calculation of sale
function calculateTotalSaleBill() {
    var bill              = 0;
    var totalBill         = 0;
    var billPlusObAmount  = 0;
    var outstandingAmount = 0;
    var discount          = parseFloat($('#discount').val() > 0 ? $('#discount').val() : 0 );
    var oldBalance        = parseFloat((($('#old_balance').val() != 'undefined') && ($('#old_balance').val() != '')) ? $('#old_balance').val() : 0 );
    var cashReceived      = parseFloat((($('#cash_received').val() != 'undefined') && ($('#cash_received').val() != '')) ? $('#cash_received').val() : 0 );
    $('#bill_plus_ob_amount').val(0);
    $('#outstanding_amount').val(0);

    $('.products_combo').each(function(index) {
        var productId   = $(this).val();
        var rowId       = $(this).data('index-no');
        var quantity    = $('#net_quantity_'+rowId).val();
        var rate        = $('#sale_rate_'+rowId).val();

        if(productId && productId != '' && quantity && quantity != '' && rate && rate != '') {
            $('#sub_bill_'+rowId).val((quantity * rate));
            bill = bill + (quantity * rate);
        } else {
            $('#sub_bill_'+rowId).val('');
        }
    });
    
    if(bill > 0) {
        $('#total_amount').val(bill);
        if((bill - discount) > 0) {
            totalBill = bill - discount;
        } else {
            $('#discount').val(0);
            totalBill = bill;
        }
        $('#total_bill').val(totalBill);
    } else {
        $('#total_amount').val(0);
        $('#discount').val(0);
        $('#total_bill').val(0);
    }

    billPlusObAmount = oldBalance + totalBill;
    $('#bill_plus_ob_amount').val(billPlusObAmount);
    outstandingAmount = billPlusObAmount - cashReceived;
    $('#outstanding_amount').val(outstandingAmount);

    $('#cash_received').val(cashReceived);
}

function siblingsHandling() {
    var selectedOptions = [];

    //getting all selected option values with unique select element index number
    $('.products_combo').each(function() {
        fieldValue = $(this).val();
        indexNo    = $(this).data('index-no');

        if(fieldValue && fieldValue != '') {
            //selectedOptions hold selected option values using select's data-index-no as index
            selectedOptions[parseInt(indexNo)] = parseInt(fieldValue);
        }
    });

    //traversing each selects
    $('.products_combo').each(function() {
        //traversing through every select elements
        $(this).children('option').each(function() {
            optionValue = parseInt($(this).val());
            indexNo     = $(this).parent().data('index-no');
            
            //if current option is in the selectedOptions
            if(selectedOptions.includes(optionValue)) {
                //if index number of the current select and index of the selectedOptions match leave it from disabling
                if(indexNo == selectedOptions.indexOf(optionValue)) {
                    $(this).attr('disabled', false);
                    return;
                }
                //else disable the option
                $(this).attr('disabled', true);
            } else {
                //else enable
                $(this).attr('disabled', false);
            }
        });
    });
}