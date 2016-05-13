//List
var additiveTable = $('#additive-table').DataTable({
    ajax : laroute.route('api.additive.data'),
    columns: [
        { data: 'eNumber', name: 'eNumber'},
        { data: 'iName', name: 'iName' }
    ],
    rowId: 'id'
});
$('#additive-table tbody').on( 'click', 'tr', function () {
    var id = additiveTable.row( this ).id();
    window.location.href = laroute.route('additive.show', { additive: id});
});
//Set plug in form
$(".eNumber").inputmask({mask: "E[9]{3,5}", greedy: false });
// $(".url").inputmask({alias: 'url', greedy: false});

$('.hOrganization').typeahead({
    ajax: {
        url: laroute.route('api.halalOrg.list'),
        triggerLength: 3
    }
});
