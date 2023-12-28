@extends('base')
@section('css')
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endsection
@section('page')
    Commissions sur transactions
@endsection
@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <button type="button" class="btn waves-effect waves-light btn-primary" data-toggle="modal" data-target="#add-gamme">Ajouter une commission</button>
                    <br>
                    <br>
                    <div class="card">
                        <div class="card-header border-0">
                        <h3 class="card-title">Liste des commissions</h3>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-bordered table-striped example1">
                                <thead>
                                    <tr>
                                        <th>Type d'operation</th>
                                        <th>Type de Taux</th>
                                        <th>Valeur</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($commissions as $item)
                                        <tr>
                                            <td class="text-capitalize">{{ $item->type_operation }}</td>
                                            <td>{{ $item->type }}</td>
                                            <td>{{ $item->value }} @if($item->type == 'Taux pourcentage') % @else F CFA @endif</td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#edit-commissions-{{ $item->id }}"><i class="fa fa-edit"></i>&nbsp;&nbsp;Modifier les informations</a>
                                                        <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#del-commissions-{{ $item->id }}"><i class="fa fa-trash"></i>&nbsp;&nbsp;Supprimer la commission</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="edit-commissions-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="exampleModalLabel1">Modification de commission </h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <form action="/commissions/edit/{{ $item->id }}" method="POST"> 
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label for="">Type d'operation</label>
                                                                <select class="form-control select2bs4" name="type_operation" style="width:100%">
                                                                    <option value="">Selectionner un type</option>
                                                                    <option @if($item->type_operation == 'depot') selected @endif value="depot">Depot</option>
                                                                    <option @if($item->type_operation == 'retrait') selected @endif value="retrait">Retrait</option>
                                                                    <option @if($item->type_operation == 'transfert') selected @endif value="transfert">Transfert</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="">Type de taux</label>
                                                                <select class="form-control select2bs4" name="type" style="width:100%">
                                                                    <option value="">Selectionner un type</option>
                                                                    <option @if($item->type == 'Taux fixe') selected @endif value="Taux fixe">Taux fixe</option>
                                                                    <option @if($item->type == 'Taux pourcentage') selected @endif value="Taux pourcentage">Taux pourcentage</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="">Valeur</label>
                                                                <input type="text" class="form-control" value="{{ $item->value }}" name="value" placeholder="Valeur">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary">Modifier</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="del-commissions-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="exampleModalLabel1">Suppression de commission </h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <form action="/commissions/delete/{{ $item->id }}" method="POST"> 
                                                        @csrf
                                                        <div class="modal-body">
                                                            <p>Cela implique que toutes opérations dans cet intervalle de montant sera exonérée de commissions. <br> Etes vous sur de supprimer ce parametre ?</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-danger" data-dismiss="modal">Non</button>
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
    
        <div class="modal fade" id="add-gamme" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel1">Ajout d'une commission d'opération</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form action="/commissions/add" id="form-add-card-magasin" method="POST"> 
                        @csrf
                        <div class="modal-body">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="">Type d'operation</label>
                                    <select class="form-control select2bs4" name="type_operation" style="width:100%">
                                        <option value="">Selectionner un type</option>
                                        <option value="depot">Depot</option>
                                        <option value="retrait">Retrait</option>
                                        <option value="transfert">Transfert</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="">Type de taux</label>
                                    <select class="form-control select2bs4" name="type" style="width:100%">
                                        <option value="">Selectionner un type</option>
                                        <option value="Taux fixe">Taux fixe</option>
                                        <option value="Taux pourcentage">Taux pourcentage</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="">Valeur</label>
                                    <input type="text" class="form-control" name="value" placeholder="Valeur">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
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