$(document).ready(function() {
    $('#booking_startDate, #booking_endDate').datepicker({
        format: 'dd/mm/yyyy',
        datesDisabled: [
            { % for day in ad.notAvailableDays % }
            "{{ day.format('d/m/Y') }}",
            { % endfor % }
        ]
    });
});