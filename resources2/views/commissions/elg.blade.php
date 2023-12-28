@extends('base')
@section('css')
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card card-default">
            <div class="card-header border-0">
                <h3 class="card-title">Compte commission ELG</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <i class="fas fa-wallet fa-2x"></i>
                    &nbsp;&nbsp;&nbsp;
                    <h3>{{ $compteElg->solde }} XOF</h3>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title">Mouvement du compte de commission</h3>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped example1">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference BCV</th>
                            <th>Reference GTP</th>
                            <th class="text-capitalize">Type</th>
                            <th>Montant <small>(F cfa)</small></th>
                            <th>Frais <small>(F cfa)</small></th>
                            <th>Commission <small>(F cfa)</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($compteElgOperations as $item)
                            <tr>
                                <td>{{ $item->created_at->format('d/m/Y') }}</td>
                                <td>{{ $item->reference_bcb }}</td>
                                <td>{{ $item->reference_gtp }}</td>
                                <td class="text-capitalize">{{ $item->type_operation }}</td>
                                <td>{{ $item->montant }}</td>
                                <td>{{ $item->frais }}</td>
                                <td>{{ $item->commission }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@section('js')
    <!--This page plugins -->
    <script src="/plugins/select2/js/select2.full.min.js"></script>

    <!-- DataTables  & Plugins -->
    <script src="/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <script src="/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
    <script src="/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
    <script src="/plugins/jszip/jszip.min.js"></script>
    <script src="/plugins/pdfmake/pdfmake.min.js"></script>
    <script src="/plugins/pdfmake/vfs_fonts.js"></script>
    <script src="/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
    <script src="/plugins/datatables-buttons/js/buttons.print.min.js"></script>
    <script src="/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
    <script>
        $(".example1").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
    </script>
@endsection