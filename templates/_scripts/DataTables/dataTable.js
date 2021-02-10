//JQuery-DaraTables common cofig file

$(document).ready(function() {
    var table = $('#daba').DataTable({
        "ordering": true,
        "scrollX": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/French.json"
        }




        /** optional parameters 
         "scrollX": true //horizontal scrollbar
         "dom": '<"top"iflp<"clear">>rt<"bottom"iflp<"clear">>' //multiple paginator (top/bottom)
         "language": {
            "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/French.json"
        }
         "searching": false, //search disabled
        */

    });
    $('button.toggle-vis').on('click', function(e) {
        e.preventDefault();

        // Get the column API object
        var column = table.column($(this).attr('data-column'));

        // Toggle the visibility
        column.visible(!column.visible());


    });
});

/*********** */
/*$(document).ready(function() {
    var table = $('#lstindex').DataTable( {
        "scrollY": "600px",
        "paging": true
    } );
 
    $('a.toggle-vis').on( 'click', function (e) {
        e.preventDefault();
 
        // Get the column API object
        var column = table.column( $(this).attr('data-column') );
 
        // Toggle the visibility
        column.visible( ! column.visible() );
    } );
} );
*/