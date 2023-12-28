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
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped example1">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Partenaire</th>
                                            <th>Date</th>
                                            <th>Montant (XOF)</th>
                                            <th>Frais (XOF)</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($appros as $item)
                                            <tr>
                                                <td @if($item->type == 'Appro') class="text-success" @else class="text-danger" @endif>{{ $item->type }}</td>
                                                <td>{{ $item->apiPartenaireAccount->libelle }}</td>
                                                <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>
                                                <td>{{ $item->montant }}</td>
                                                <td>{{ $item->frais }}</td>                                       
                                                <td>
                                                    <span class="badge badge-warning">En attente</span>
                                                </td>                                          
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            Actions
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#validate-appro-{{ $item->id }}"><i class="fa fa-check"></i>&nbsp;&nbsp;Valider l'appro</a>
                                                            <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#unvalidate-appro-{{ $item->id }}"><i class="fa fa-times"></i>&nbsp;&nbsp;Rejeter l'appro</a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <div class="modal fade" id="validate-appro-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title" id="exampleModalLabel1">Validation d'approvisionnement </h4>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                        </div>
                                                        <form action="/partenaires/api/recharge/validate/{{ $item->id }}" method="POST"> 
                                                            @csrf
                                                            <div class="modal-body">
                                                                <p class="text-center">Validation d'approvisionnement de {{ $item->montant }} XOF. <br> Etes vous sur de valider cet approvisionnement ?</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-danger pull-left" data-dismiss="modal">Non</button>
                                                                <button type="submit" class="btn btn-primary">Oui</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="unvalidate-appro-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title" id="exampleModalLabel1">Rejet d'approvisionnement </h4>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                        </div>
                                                        <form action="/partenaires/api/recharge/unvalidate/{{ $item->id }}" method="POST"> 
                                                            @csrf
                                                            <div class="modal-body">
                                                                <label for="unvalidateComment">Motif du rejet</label>
                                                                <textarea name="comment" id="unvalidateComment" cols="30" rows="5" class="form-control"></textarea>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-danger pull-left" data-dismiss="modal">Non</button>
                                                                <button type="submit" class="btn btn-primary">Oui</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </tbody>
                                </table>
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