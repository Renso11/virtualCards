@extends('base')
@section('css')
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endsection
@section('page')
    Liste des gammes
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @if (hasPermission('gamme.add'))
                    <button type="button" class="btn waves-effect waves-light btn-primary" data-toggle="modal" data-target="#add-gamme">Ajouter une gamme</button>
                @endif
                <br>
                <br>
                <div class="card">
                    <div class="card-header border-0">
                    <h3 class="card-title">Liste des gammes</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped example1">
                            <thead>
                                <tr>
                                    <th>Libelle</th>
                                    <th>Description</th>
                                    <th>Prix</th>
                                    <th>Status</th>
                                    @if (hasPermission('gamme.edit') || hasPermission('gamme.delete')|| hasPermission('gamme.activation')|| hasPermission('gamme.desactivation'))
                                        <th>Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($gammes as $item)
                                    <tr>
                                        <td>{{ $item->libelle }}</td>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ $item->prix }} F CFA</td>
                                        <td> @if($item->status == 0) <span class="label label-danger">Inactif</span> @else <span class="label label-success">Actif</span> @endif </td>
                                        @if (hasPermission('gamme.edit') || hasPermission('gamme.delete')|| hasPermission('gamme.activation')|| hasPermission('gamme.desactivation'))
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        @if (hasPermission('gamme.edit'))
                                                            <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#edit-gamme-{{ $item->id }}"><i class="fa fa-edit"></i>&nbsp;&nbsp;Modifier les informations</a>
                                                        @endif
                                                        @if (hasPermission('gamme.delete'))
                                                            <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#del-gamme-{{ $item->id }}"><i class="fa fa-trash"></i>&nbsp;&nbsp;Supprimer la gamme</a>
                                                        @endif
                                                        @if($item->status == 0)
                                                            @if (hasPermission('gamme.activation'))
                                                                <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#activation-gamme-{{ $item->id }}"><i class="fa fa-check"></i>&nbsp;&nbsp;Activer la gamme</a>
                                                            @endif
                                                        @else
                                                            @if (hasPermission('gamme.desactivation'))
                                                                <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#desactivation-gamme-{{ $item->id }}"><i class="fa fa-times"></i>&nbsp;&nbsp;Désactiver la gamme</a>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>

                                    <div class="modal fade" id="edit-gamme-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="exampleModalLabel1">Modification de {{ $item->libelle }}</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <form action="/gamme/edit/{{ $item->id }}" method="POST"> 
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="form-group">  
                                                            <label for="recipient-name" class="control-label">Libelle de la gamme:</label>
                                                            <input type="text" autocomplete="off" value="{{ $item->libelle }}" class="form-control" name="libelle">
                                                        </div>
                                                        <div class="form-group">  
                                                            <label for="recipient-name" class="control-label">Prix de la carte (F CFA):</label>
                                                            <input type="number" value="{{ $item->prix }}" autocomplete="off" class="form-control" name="prix">
                                                        </div>
                                                        <div class="form-group">  
                                                            <label for="">Type de carte</label>
                                                            <select class="form-control select2bs4 type" name="type" id="" style="width:100%">
                                                                <option value="">Selectionner un type</option>
                                                                <option @if($item->type == "physique") selected @endif value="physique">Physique</option>
                                                                <option @if($item->type == "virtuelle") selected @endif value="virtuelle">Virtuelle</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">  
                                                            <label for="recipient-name" class="control-label">Description:</label>
                                                            <textarea name="description" class="form-control" id="" cols="30" rows="5" placeholder="Description de la gamme de carte">{{ $item->description }}</textarea>
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

                                    <div class="modal fade" id="del-gamme-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="exampleModalLabel1">Suppression de la gamme {{ $item->libelle }}</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <form action="/gamme/delete/{{ $item->id }}" method="POST"> 
                                                    @csrf
                                                    <div class="modal-body">
                                                        <p>Etes vous sur de supprimer cette gamme?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Non</button>
                                                        <button type="submit" class="btn btn-primary">Oui</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="activation-gamme-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="exampleModalLabel1">Activation de la gamme {{ $item->libelle }}</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <form action="/gamme/activation/{{ $item->id }}" method="POST"> 
                                                    @csrf
                                                    <div class="modal-body">
                                                        <p>Etes vous sur d'activer cette gamme?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Non</button>
                                                        <button type="submit" class="btn btn-primary">Oui</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="desactivation-gamme-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="exampleModalLabel1">Désactivation de la gamme {{ $item->libelle }}</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <form action="/gamme/desactivation/{{ $item->id }}" method="POST"> 
                                                    @csrf
                                                    <div class="modal-body">
                                                        <p>Etes vous sur de désactiver  cette gamme?</p>
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
                    <h4 class="modal-title" id="exampleModalLabel1">Nouvelle gamme</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form action="/gamme/add" method="POST"> 
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">  
                            <label for="recipient-name" class="control-label">Libelle de la gamme:</label>
                            <input type="text" autocomplete="off" class="form-control" name="libelle">
                        </div>
                        <div class="form-group">  
                            <label for="recipient-name" class="control-label">Prix de la carte (F CFA):</label>
                            <input type="number" autocomplete="off" class="form-control" name="prix">
                        </div>
                        <div class="form-group">  
                            <label for="">Type de carte</label>
                            <select class="form-control select2bs4 type" name="type" id="" style="width:100%">
                                <option value="">Selectionner un type</option>
                                <option value="physique">Physique</option>
                                <option value="virtuelle">Virtuelle</option>
                            </select>
                        </div>
                        <div class="form-group">  
                            <label for="recipient-name" class="control-label">Description:</label>
                            <textarea name="description" class="form-control" id="" cols="30" rows="5" placeholder="Description de la gamme de carte"></textarea>
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