$(function () {
    var ajaxGetLastReadingUrl = '/ajax/excavator-reading/last';

    calculateTotalRent();

    $('body').on("change", "#excavator_id", function (evt) {
        var excavatorId = $(this).val();
        
        var defaultBucketRate  = $(this).find(':selected').data('bucket-rate');
        var defaultBreakerRate = $(this).find(':selected').data('breaker-rate');
        
        $('#customer_account_id').val('');
        $('#site_id').val('');
        $('#employee_id').val('');
        $('#bucket_rate').val('');
        $('#breaker_rate').val('');

        if(excavatorId && excavatorId != 'undefined') {
            $.ajax({
                url: ajaxGetLastReadingUrl,
                method: "get",
                data: {
                    'excavator_id' : excavatorId,
                },
                success: function(result) {
                    
                    if(result && result.flag) {
                        var customerAccountId = result.lastReading.transaction.credit_account_id;
                        var siteId            = result.lastReading.site_id;
                        var employeeId        = result.lastReading.operator_id;
                        var bucketRate        = result.lastReading.bucket_rate;
                        var breakerRate       = result.lastReading.breaker_rate;

                        $('#customer_account_id').val(customerAccountId);
                        $('#site_id').val(siteId);
                        $('#employee_id').val(employeeId);
                        $('#bucket_rate').val(bucketRate);
                        $('#breaker_rate').val(breakerRate);

                        $('#customer_account_id').trigger('change');
                        $('#site_id').trigger('change');
                        $('#employee_id').trigger('change');
                    } else {
                        $('#bucket_rate').val(defaultBucketRate);
                        $('#breaker_rate').val(defaultBreakerRate);

                        $('#customer_account_id').trigger('change');
                        $('#site_id').trigger('change');
                        $('#employee_id').trigger('change');
                    }
                    calculateTotalRent();
                },
                error: function (err) {
                    calculateTotalRent();
                }
            });
        }
    });

    $('body').on("change keyup", "#bucket_hour", function (evt) {
        calculateTotalRent();
    });

    $('body').on("change keyup", "#bucket_rate", function (evt) {
        calculateTotalRent();
    });

    $('body').on("change keyup", "#breaker_hour", function (evt) {
        calculateTotalRent();
    });

    $('body').on("change keyup", "#breaker_rate", function (evt) {
        calculateTotalRent();
    });
});

function calculateTotalRent() {
    var totalRentBucket = 0, totalRentBreaker = 0, totalRent = 0, bucketRate = 0, breakerRate = 0, bucketHour = 0, breakerHour = 0;

    bucketRate  = $('#bucket_rate').val();
    bucketHour  = $('#bucket_hour').val();
    breakerRate = $('#breaker_rate').val();
    breakerHour = $('#breaker_hour').val();

    if(!bucketRate || bucketRate == 'undefined' || bucketRate < 0) {
        bucketRate = 0;
    }
    if(!bucketHour || bucketHour == 'undefined' || bucketHour < 0) {
        bucketHour = 0;
    }
    if(!breakerRate || breakerRate == 'undefined' || breakerRate < 0) {
        breakerRate = 0;
    }
    if(!breakerHour || breakerHour == 'undefined' || breakerHour < 0) {
        breakerHour = 0;
    }

    totalRentBucket  = bucketRate * bucketHour;
    totalRentBreaker = breakerRate * breakerHour;

    totalRent = totalRentBucket + totalRentBreaker;

    $('#total_rent_bucket').val(totalRentBucket);
    $('#total_rent_breaker').val(totalRentBreaker);
    $('#total_rent').val(totalRent);
}