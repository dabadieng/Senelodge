//JQuery-DaraTables common cofig file

$(document).ready(function() {
    var table = $('#daba').DataTable({
        "ordering": true,
        "scrollX": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/French.json"
        }


    });
    $('a.toggle-vis').on('click', function(e) {
        e.preventDefault();

        // Get the column API object
        var column = table.column($(this).attr('data-column'));

        // Toggle the visibility
        column.visible(!column.visible());


    });
});