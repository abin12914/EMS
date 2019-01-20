$(function () {
    var ajaxGetNextRentDateUrl = '/ajax/excavator-reading/last';

    $('#from_date').datepicker({
        format: 'dd-mm-yyyy',
        endDate: '+0d',
        autoclose: true,
    }).on('changeDate', function(e) {
            var selectedDate = new Date(e.date);
            var msecsInADay = 86400000;
            var endDate = new Date(selectedDate.getTime() + msecsInADay);
            
           //Set Minimum Date of EndDatePicker After Selected Date of StartDatePicker
            $("#to_date").datepicker("setStartDate", endDate );
        });

    $('#to_date').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        endDate: '+0d',
    });

    $('body').on("change keypress", "#from_date", function (evt) {
        $('#to_date').datepicker('setDate', '');
    });

    /*$('body').on("change", "#excavator_id", function (evt) {
        var excavatorId = $(this).val();
        $('#from_date').val('');
        $('#to_date').val('');

        if(excavatorId && excavatorId != 'undefined') {
            $.ajax({
                url: ajaxGetNextRentDateUrl,
                method: "get",
                data: {
                    'excavator_id' : excavatorId,
                },
                success: function(result) {
                    if(result && result.flag) {
                        var nextRentDate = result.nextRentDate;
                        if(nextRentDate) {
                            var nextRentDateField = new Date(nextRentDate);
                            if(nextRentDateField < new Date()) {
                                var day     = nextRentDateField.getDate();
                                var month   = nextRentDateField.getMonth()+1;
                                var year    = nextRentDateField.getFullYear();
                                nextRentDate  = day+'-'+month+'-'+year;

                                $('#from_date').datepicker('setDate', nextRentDate);
                            }
                        }
                    } else {
                        $('#from_date').val('');
                    }
                },
                error: function (err) {
                    $('#from_date').val('');
                }
            });
        } else {
            $('#from_date').val('');
        }
    });*/
});