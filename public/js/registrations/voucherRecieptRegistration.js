$(function () {
    //append to main registratin number textbox
    $('body').on("click", ".transaction_type", function (evt) {
        if($('#transaction_type_credit').is(':checked')) {console.log('in');
            $('#account_label').html('Reciever');
        } else {console.log('else');
            $('#account_label').html('Giver');
        }
    });
});