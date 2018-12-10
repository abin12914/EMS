$(function () {
    siblingsHandling();
    var ajaxAccountDetailUrl    = '/ajax/account/details/';

    $('body').on("click", "#purchase_submit_button", function (evt) {
        evt.preventDefault();
        var taxFlag         = false;
        var cutomTitle      = 'Are you sure to save the purchase?';
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
            if(!defaultWeighmentWastage) {
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

    //supplier details
    $('body').on("change", "#supplier_account_id", function (evt) {
        var oldBalanceAmount    = 0;
        var supplierAccountId   = $(this).val();
        $('#ob_info').html('');
        $('#old_balance').val(0);

        if(supplierAccountId && supplierAccountId != -1) {
            var selectedOption = $(this).find(':selected');
            
            $.ajax({
                url: ajaxAccountDetailUrl + supplierAccountId,
                method: "get",
                data: {},
                success: function(result) {
                    
                    if(result && result.flag) {
                        var account     = result.account;
                        var obDebit     = result.oldBalance.oldDebit;
                        var obCredit    = result.oldBalance.oldCredit;

                        if(account.type == 3) {
                            $('#supplier_name').val(account.name);
                            $('#supplier_phone').val(account.phone);
                        }

                        if(obDebit != 'undefined' && obCredit != 'undefined') {
                            oldBalanceAmount = obDebit - obCredit;
                        }

                        if(oldBalanceAmount < 0) {
                            //debit < credit => company owes supplier
                            $('#ob_info').html(' (Payable To Supplier)');
                        } else {
                            //debit > credit => supplier owes company
                            $('#ob_info').html(' (Receivable From Supplier)');
                        }
                        $('#old_balance').val(oldBalanceAmount);
                        calculateTotalPurchaseBill();
                    } else {
                        $('#supplier_name').val('');
                        $('#supplier_phone').val('');
                    }
                },
                error: function (err) {
                    $('#supplier_name').val('');
                    $('#supplier_phone').val('');
                }
            });
        } else {
            $('#supplier_name').val('');
            $('#supplier_phone').val('');
        }
        //calculate total purchase bill
        calculateTotalPurchaseBill();
    });

    //
    $('body').on("keyup", "#supplier_phone", function (evt) {
        var input               = $(this).val();
        var selectedAccountId   = $('#supplier_account_id').val();

        if(input.length > 9) {
            accountId   = $('#supplier_account_id').find(`[data-phone='${input}']`).val();
            accountName = $('#supplier_account_id').find(`[data-phone='${input}']`).text();

            if(selectedAccountId != -1) {
                if(accountId && accountId > 0 && accountId != selectedAccountId) {
                    if(confirm("Found an account related to the entered phone number. Do you want to change the 'supplier account' field to " + accountName + "?")) {
                        $('#supplier_account_id').val(accountId);
                        $('#supplier_account_id').trigger('change');
                        $('#supplier_parent_div').addClass('has-warning');
                        $('#description').focus();
                    }
                }
            } else if(accountId && accountId > 0) {
                $('#supplier_account_id').val(accountId);
                $('#supplier_account_id').trigger('change');
                $('#supplier_parent_div').addClass('has-warning');
                $('#description').focus();
                alert('Found an account related to the entered phone number. Purchase would credited to '+ accountName);
            }
        }
    });

    //product change event
    $('body').on("change", ".products_combo", function (evt) {
        var fieldValue  = $(this).val();
        var rowId       = $(this).data('index-no');

        if(fieldValue && fieldValue != '' && fieldValue != 'undefined') {
            //enabling quantity & rate in same column
            $(this).closest('tr').find('.net_quantity').attr('disabled', false);
            $(this).closest('tr').find('.purchase_rate').attr('disabled', false);
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
            $(this).closest('tr').find('.purchase_rate').attr('disabled', true);
            $(this).closest('tr').find('.sub_bill').attr('disabled', true);
            //enabling weighment fields
            $(this).closest('tr').find('.gross_quantity').attr('disabled', true);
            $(this).closest('tr').find('.product_number').attr('disabled', true);
            $(this).closest('tr').find('.unit_wastage').attr('disabled', true);
            $(this).closest('tr').find('.total_wastage').attr('disabled', true);
            
            //setting empty values for deselected product
            $('#purchase_notes'+rowId).val('');
            $('#net_quantity_'+rowId).val('');
            $('#purchase_rate_'+rowId).val('');

            $('#product__row_'+(rowId+1)).find('.products_combo').val('');
            //disabling next combo box

            $('#product__row_'+(rowId+1)).find('.products_combo').attr('disabled', true);
            //hide more row
            $('#product__row_'+(rowId+3)).hide();
        }

        //disabiling same value selection in 2 product combo boxes
        siblingsHandling();
        initializeSelect2();
        //calculate total purchase bill
        //calculateTotalPurchaseBill(); //wont work

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
                $('#purchase_rate_'+rowId).focus();
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

    //purchase quantity event actions
    $('body').on("change keyup", ".net_quantity", function (evt) {
        //calculate total purchase bill
        calculateTotalPurchaseBill();
    });

    //purchase rate event actions
    $('body').on("change keyup", ".purchase_rate", function (evt) {
        //calculate total purchase bill
        calculateTotalPurchaseBill();
    });

    //purchase rate event actions
    $('body').on("change keyup", "#discount", function (evt) {
        //calculate total purchase bill
        calculateTotalPurchaseBill();
    });

    //purchase rate event actions
    $('body').on("change keyup", "#cash_paid", function (evt) {
        //calculate total purchase bill
        calculateTotalPurchaseBill();
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

//method for total bill calculation of purchase
function calculateTotalPurchaseBill() {
    var bill              = 0;
    var totalBill         = 0;
    var billPlusObAmount  = 0;
    var outstandingAmount = 0;
    var discount          = parseFloat($('#discount').val() > 0 ? $('#discount').val() : 0 );
    var oldBalance        = parseFloat((($('#old_balance').val() != 'undefined') && ($('#old_balance').val() != '')) ? $('#old_balance').val() : 0 );
    var cashPaid          = parseFloat((($('#cash_paid').val() != 'undefined') && ($('#cash_paid').val() != '')) ? $('#cash_paid').val() : 0 );
    $('#bill_plus_ob_amount').val(0);
    $('#outstanding_amount').val(0);

    $('.products_combo').each(function(index) {
        var productId   = $(this).val();
        var rowId       = $(this).data('index-no');
        var quantity    = $('#net_quantity_'+rowId).val();
        var rate        = $('#purchase_rate_'+rowId).val();

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

    billPlusObAmount = oldBalance - totalBill;
    $('#bill_plus_ob_amount').val(billPlusObAmount);
    outstandingAmount = billPlusObAmount + cashPaid;
    $('#outstanding_amount').val(outstandingAmount);
    $('#cash_paid').val(cashPaid);
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