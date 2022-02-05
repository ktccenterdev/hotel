$(document).ready(function() {
  return false;
});


$('.datatable').DataTable({
    responsive: false,
  dom: 'Bfrtip',
    buttons: 
    [
      {
      "extend": 'excel',
      "title": 'Liste des Clients',
      "text": 'Exporter en Excel',
      'className': 'btn btn-primary active',
  "paging": false,
      "exportOptions": {
          "columns": ':not(:last-child)',
        }
      },
      {
      "extend": 'csv',
      "title": 'Liste des Clients',
      "text": 'Exporter en csv',
      'className': 'btn btn-teal active',
  "paging": false,
      "exportOptions": {
          "columns": ':not(:last-child)',
        }
      }
    ],
    language: {
        searchPlaceholder: 'Recherche...',
        sSearch: '',
        lengthMenu: '_MENU_ items/page',
    }
  });