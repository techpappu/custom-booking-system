jQuery(document).ready(function ($) {
    $('#booking-date').on('change', function () {
        const selectedDate = $(this).val();
        if (!selectedDate) return;

        $.ajax({
            url: bookingAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'get_time_slots',
                date: selectedDate,
            },
            success: function (response) {
                let timeSlotHtml = '<h3>Available Time Slots</h3>';
                if (response.length === 0) {
                    timeSlotHtml += '<p>No time slots available for this date.</p>';
                } else {
                    response.forEach((slot) => {
                        timeSlotHtml += `
                            <button class="time-slot" data-slot="${slot}">
                                ${slot}
                            </button>
                        `;
                    });
                }
                $('#time-slots').html(timeSlotHtml);

                // Show booking form when a time slot is clicked
                $('.time-slot').on('click', function () {
                    $('.time-slot').removeClass('selected');
                    $(this).addClass('selected');
                    const selectedSlot = $(this).data('slot');
                    $('#selected-time-slot').val(selectedSlot);
                    $('#booking-form').show();
                });
            },
        });
    });

    
    $('#booking-form-element').on('submit', function (e) {
        e.preventDefault();

        const bookingData = {
            action: 'process_booking',
            date: $('#booking-date').val(),
            time_slot: $('#selected-time-slot').val(),
            name: $('#name').val(),
            email: $('#email').val(),
            message: $('#message').val(),
        };

        $.ajax({
            url: bookingAjax.ajax_url,
            method: 'POST',
            data: bookingData,
            success: function (response) {
                if (response.success) {
                    alert(response.data.message);
                    $('#booking-form').hide();
                    $('#time-slots').html('');
                    $('#booking-date').val('');
                } else {
                    alert(response.data.message);
                }
            },
        });
    });
});
