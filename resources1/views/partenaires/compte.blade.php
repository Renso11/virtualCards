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
        <div class="row">
            <div class="col-md-6">
                <div class="card card-default">
                    <div class="card-header border-0">
                        <h3 class="card-title">Compte commission</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <i class="fas fa-wallet fa-2x"></i>
                            <h3 style="margin-left: 3%">{{ $compteCommission->solde }} XOF</h3>
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
                                    <th style="width: 10px">#</th>
                                    <th>Libelle</th>
                                    <th class="text-capitalize">Type</th>
                                    <th>Date</th>
                                    <th>Solde avant</th>
                                    <th>Montant</th>
                                    <th>Solde apres</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($operationsCompteCommission as $item)
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td>{{ $item->libelle }}</td>
                                        <td>{{ $item->type }}</td>
                                        <td>{{ $item->created_at->format('d/m/Y') }}</td>
                                        <td>{{ $item->solde_avant }} F CFA</td>
                                        <td>{{ $item->montant }} F CFA</td>
                                        <td>{{ $item->solde_apres }} F CFA</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card card-default">
                    <div class="card-header border-0">
                        <h3 class="card-title">Compte distribution</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <i class="fas fa-wallet fa-2x"></i>
                            <h3 style="margin-left: 3%">{{ $compteDistribution->solde }} XOF</h3>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">Mouvement du compte de distribution</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped example1">
                            <thead>
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Libelle</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Solde avant</th>
                                    <th>Montant</th>
                                    <th>Solde apres</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($operationsCompteDistribution as $item)
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td>{{ $item->libelle }}</td>
                                        <td class="text-capitalize">{{ $item->type }}</td>
                                        <td>{{ $item->created_at->format('d/m/Y') }}</td>
                                        <td>{{ $item->solde_avant }} F CFA</td>
                                        <td>{{ $item->montant }} F CFA</td>
                                        <td>{{ $item->solde_apres }} F CFA</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
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