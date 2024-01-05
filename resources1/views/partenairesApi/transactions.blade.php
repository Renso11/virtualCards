@extends('base')
@section('css')
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endsection
@section('page')
    Liste des transactions
@endsection
@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <br>
                    <br>
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="card-title">Liste des transactions</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="filterPartner">Partenaire</label>
                                    <select name="" id="filterPartner" class="form-control select2bs4 filter">
                                        <option value="all" selected>Tous</option>
                                        @foreach ($partners as $partner)
                                            <option value="{{ $partner->id }}">{{ $partner->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="filterType">Type</label>
                                    <select name="" id="filterType" class="form-control select2bs4 filter">
                                        <option value="all" selected>Tous</option>
                                        <option value="Appro">Appro</option>
                                        <option value="Debit">Debit</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="filterStatus">Status</label>
                                    <select name="" id="filterStatus" class="form-control select2bs4 filter">
                                        <option value="all" selected>Tous</option>
                                        <option value="1">Succes</option>
                                        <option value="null">En attente</option>
                                        <option value="0">Echec</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="filterDate">Date</label>
                                    <input type="date" id="filterDate" class="form-control filter">
                                </div>
                            </div>
                            <div class="table-responsive" id="filterResult" style="margin-top: 5%">
                                <table class="table table-bordered table-striped example1">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Partenaire</th>
                                            <th>Libelle</th>
                                            <th>Date</th>
                                            <th>Montant</th>
                                            <th>Frais</th>
                                            <th>Solde avant</th>
                                            <th>Solde apres</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($transactions as $item)
                                            <tr>
                                                <td @if($item->type == 'Appro') class="text-success" @else class="text-danger" @endif>{{ $item->type }}</td>
                                                <td>{{ $item->apiPartenaireAccount->libelle }}</td>
                                                <td>{{ $item->libelle }}</td>
                                                <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>
                                                <td>{{ $item->montant }}</td>
                                                <td>{{ $item->frais }}</td>
                                                <td>{{ $item->solde_avant }}</td>                                          
                                                <td>{{ $item->solde_apres }}</td>                                          
                                                <td>
                                                    @if ($item->status == 0)
                                                        <span class="badge badge-danger">Echec</span>
                                                    @elseif ($item->status == 1)
                                                        <span class="badge badge-success">Succes</span>
                                                    @elseif ($item->status == 2)
                                                        <span class="badge badge-default">Ristourne</span>
                                                    @else
                                                        <span class="badge badge-warning">En attente</span>
                                                    @endif                                                
                                                </td>     
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center" id="filterLoader" style="margin-top: 5%;display:none">
                                <i class="fa fa-spin fa-spinner fa-4x"></i>
                            </div>
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
            "dom": 'Bfrtip',
            "buttons": ["csv", "excel", "pdf"]
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        $('.filter').on('change',function(e) {
            e.preventDefault()
            $('#filterResult').hide()
            $('#filterLoader').show('slow')

            var partner = $('#filterPartner').val()
            var type = $('#filterType').val()
            var status = $('#filterStatus').val()
            var date = $('#filterDate').val()

            $.ajax({
                url: "/partenaires/api/filter/transactions",
                data: {
                    partner : partner,
                    type : type,
                    status : status,
                    date : date,
                },
                method: "post",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(data){
                    $('#filterLoader').hide()
                    $('#filterResult').html(data)
                    $('#filterResult').show()
                    
                    $(".example1").DataTable({
                        "responsive": true,
                        "lengthChange": false,
                        "autoWidth": false,
                        "dom": 'Bfrtip',
                        "buttons": ["csv", "excel", "pdf"]
                    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
                }
            }); 
        })
    </script>
@endsection