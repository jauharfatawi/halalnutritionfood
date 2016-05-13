//List
var foodProductTable = $('#foodProduct-table').DataTable({
    ajax : laroute.route('api.foodproduct.data'),
    columns: [
        { data: 'fCode', name: 'fCode'},
        { data: 'fName', name: 'fName' },
        { data: 'fManufacture', name: 'fManufacture' },
    ],
    rowId: 'id'
});
$('#foodProduct-table tbody').on( 'click', 'tr', function () {
    var id = foodProductTable.row( this ).id();
    window.location.href = laroute.route('foodproduct.show', { foodproduct: id});
});
//Set plug in form
$('.fManufacture').typeahead({
    ajax: {
        url: laroute.route('api.manufacture.list'),
        triggerLength: 3
    }
});
$('.halalcertorg').typeahead({
    ajax: {
        url: laroute.route('api.certOrg.list'),
        triggerLength: 3
    }
});

$('.ingredient').select2({
    width: '100%',
    multiple: true,
    tags: true,
    theme: "bootstrap",
    tokenSeparators: [","],
    placeholder: "Click here and start typing to search.",
    minimumInputLength: 2,
    ajax: {
        url: laroute.route('api.ingredient.list'),
        dataType: 'json',
        delay: 250,
        data: function (params) {
            return {
                q: params.term,
            };
        },
        processResults: function (data) {
            return {
                results: data
            };
        },
        cache: true
    }
});
