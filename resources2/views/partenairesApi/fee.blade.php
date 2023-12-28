@extends('base')
@section('css')
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endsection
@section('page')
    Frais des transactions
@endsection
@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <button type="button" class="btn waves-effect waves-light btn-primary" data-toggle="modal" data-target="#add-fee">Ajouter un frais</button>
                    <br>
                    <br>
                    <div class="card">
                        <div class="card-header border-0">
                        <h3 class="card-title">Liste des frais appliqués</h3>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-bordered table-striped example1">
                                <thead>
                                    <tr>
                                        <th>Type frais</th>
                                        <th>Partenaires</th>
                                        <th>De</th>
                                        <th>A</th>
                                        <th>Valeur</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($fees as $item)
                                        <tr>
                                            <td>{{ $item->type_fee }}</td>
                                            <td>@if($item->api_partenaire_account_id == 0) Tous les partenaires @else {{ $item->apiPartenaireAccount->libelle }} @endif</td>
                                            <td>{{ $item->beguin }}</td>
                                            <td>{{ $item->end }}</td>
                                            <td>{{ $item->value }}</td>                                          
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#edit-frais-{{ $item->id }}"><i class="fa fa-edit"></i>&nbsp;&nbsp;Modifier les informations</a>
                                                        <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#del-frais-{{ $item->id }}"><i class="fa fa-trash"></i>&nbsp;&nbsp;Supprimer le frais</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="edit-frais-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="exampleModalLabel1">Modification de frais </h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <form action="/partenaires/api/fees/edit/{{ $item->id }}" method="POST"> 
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label for="">Type de partenaire</label>
                                                                <select class="form-control select2bs4" name="partenaire" style="width:100%">
                                                                    <option value="">Selectionner un type</option>
                                                                    <option value="0" @if($item->api_partenaire_account_id == 0) selected @endif>Tous les partenaires</option>
                                                                    @foreach ($partenaires as $datum)
                                                                        <option @if($datum->id == $item->api_partenaire_account_id) selected @endif value="{{ $datum->id }}">{{ $datum->libelle }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="">Type de taux</label>
                                                                <select class="form-control select2bs4" name="type_fee" style="width:100%">
                                                                    <option value="">Selectionner un type</option>
                                                                    <option @if($item->type_fee == 'fixe') selected @endif value="Taux fixe">Taux fixe</option>
                                                                    <option @if($item->type_fee == 'pourcentage') selected @endif value="Taux pourcentage">Taux pourcentage</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="">Montant debut</label>
                                                                <input type="text" class="form-control" value="{{ $item->beguin }}" name="beguin" placeholder="Montant debut">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="">Montant fin</label>
                                                                <input type="text" class="form-control" value="{{ $item->end }}" name="end" placeholder="Montant fin">
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

                                        <div class="modal fade" id="del-frais-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="exampleModalLabel1">Suppression de frais </h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <form action="/partenaires/api/fees/delete/{{ $item->id }}" method="POST"> 
                                                        @csrf
                                                        <div class="modal-body">
                                                            <p>Cela implique que toutes opérations dans cet intervalle de montant sera exonérée de frais. <br> Etes vous sur de supprimer ce parametre ?</p>
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
    
        <div class="modal fade" id="add-fee" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel1">Definition des frais d'opérations</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form action="{{ route('partenaire.api.fee.add') }}" id="form-add-card-magasin" method="POST"> 
                        @csrf
                        <div class="modal-body">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="">Partenaire</label>
                                    <select class="form-control select2bs4" required name="partenaire" style="width:100%">
                                        <option value="">Selectionner un type</option>
                                        <option value="0">Tous les partenaires</option>
                                        @foreach ($partenaires as $item)
                                            <option value="{{ $item->id }}">{{ $item->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="">Type de taux</label>
                                    <select class="form-control select2bs4" required name="type_fee" style="width:100%">
                                        <option value="">Selectionner un type</option>
                                        <option value="fixe">Taux fixe</option>
                                        <option value="pourcentage">Taux pourcentage</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="">Montant debut</label>
                                    <input type="text" class="form-control" required name="beguin" placeholder="Montant debut">
                                </div>
                                <div class="form-group">
                                    <label for="">Montant fin</label>
                                    <input type="text" class="form-control" required name="end" placeholder="Montant fin">
                                </div>
                                <div class="form-group">
                                    <label for="">Valeur</label>
                                    <input type="text" class="form-control" required name="value" placeholder="Valeur">
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