@extends('base')
@section('css')
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <style>
        .img-flag {
            width: 25px;
            height: 12px;
            margin-top: -4px;
        }
    </style>
@endsection
@section('page')
    Liste des operations clients en attentes
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
                        <h3 class="card-title">Liste des operations clients en attentes</h3>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-bordered table-striped example1">
                                <thead>
                                    <tr>
                                        <th style="width:15%">Date</th>
                                        <th style="width:15%">Client</th>
                                        <th style="width:10%">Type</th>
                                        <th style="width:10%">Montant</th>
                                        <th style="width:10%">Frais</th>
                                        <th style="width:10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>        
                                @forelse($transactions as $item)
                                <tr>
                                    <td>{{ $item['date'] }}</td>
                                    <td>{{ $item['partenaire']['libelle'] }}</td>
                                    <td>{{ $item['type'] }}</td>
                                    <td>{{ $item['montant'] }}</td>
                                    <td>{{ $item['frais_bcb'] }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                Actions
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#validation-client-{{ $item['id'] }}"><i class="fa fa-eye"></i>&nbsp;&nbsp;Voir plus</a>
                                                <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#edit-client-{{ $item['id'] }}"><i class="fa fa-times"></i>&nbsp;&nbsp;Annuler la transaction</a>
                                                <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#reset-password-client-{{ $item['id'] }}"><i class="fa fa-check"></i>&nbsp;&nbsp;Finaliser la transaction</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10">Pas de données</td>
                                </tr>
                                @endforelse
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
    <script>
        function formatState (state) {
            if (!state.id) { 
                return state.text; 
            }
            var $state = $(
                '<span><img src="/assets/images/flags/' +  state.element.dataset.flag.toLowerCase() +
                '.svg" class="img-flag" /> ' +
                state.text +  '</span>'
            );
            return $state;
        };

        $(".select2-flag-search").select2({
            templateResult: formatState,
            templateSelection: formatState,
            escapeMarkup: function(m) { return m; },
            width: '100%',
            dropdownParent: $("#add-client")
        });

        $('#code').on('keyup',function (e) {
            var code = $(this).val();
            $('#name').val("")
            $('#lastname').val("")
            $('#last').val("")
            if(code.length >= 8){
                $('#loader').show()
                $.ajax({
                    url: "/search/client",
                    data: {
                        code : code
                    },
                    method: "post",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(data){
                        let res = JSON.parse(data)
                        $('#loader').hide()
                        $('#name').val(res.firstName)
                        $('#lastname').val(res.lastname)
                        $('#last').val(res.lastFourDigits)
                    }
                }); 
            }
        })
        
    </script>
@endsection