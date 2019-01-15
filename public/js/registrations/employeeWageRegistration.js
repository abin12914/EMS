$(function () {
    var ajaxGetNextWageDateUrl = '/ajax/employee-wage/next-wage-date';

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

    $('body').on("change", "#employee_id", function (evt) {
        var employeeId = $(this).val();
        $('#from_date').val('');
        $('#to_date').val('');

        if(employeeId && employeeId != 'undefined') {
            $.ajax({
                url: ajaxGetNextWageDateUrl,
                method: "get",
                data: {
                    'employee_id' : employeeId,
                },
                success: function(result) {
                    
                    if(result && result.flag) {
                        var nextWageDate = result.nextWageDate;
                        console.log('c');
                        if(nextWageDate) {
                            var nextWageDateField = new Date(nextWageDate);
                            if(nextWageDateField < new Date()) {
                                var day     = nextWageDateField.getDate();
                                var month   = nextWageDateField.getMonth()+1;
                                var year    = nextWageDateField.getFullYear();
                                nextWageDate  = day+'-'+month+'-'+year;

                                $('#from_date').datepicker('setDate', nextWageDate);
                                $('#to_date').datepicker('setDate', nextWageDate);
                            }
                        }
                    } else {
                        $('#from_date').val('');
                        $('#to_date').val('');
                    }
                },
                error: function (err) {
                    $('#from_date').val('');
                    $('#to_date').val('');
                }
            });
        } else {
            $('#from_date').val('');
            $('#to_date').val('');
        }
    });
});