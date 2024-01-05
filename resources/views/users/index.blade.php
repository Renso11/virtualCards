@extends('base')
@section('css')
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endsection
@section('page')
    Liste des utilisateurs
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @if (hasPermission('user.add'))
                    <button type="button" class="btn waves-effect waves-light btn-primary" data-toggle="modal" data-target="#add-user">Ajouter un utilisateur</button>
                @endif
                <br>
                <br>
                <div class="card">
                    <div class="card-header border-0">
                    <h3 class="card-title">Liste des utilisateurs</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped example1">
                            <thead>
                                <tr>
                                    <th>Nom et prenoms</th>
                                    <th>Username</th>
                                    <th>Status</th>
                                    <th>Role</th>
                                    @if (hasPermission('user.edit') || hasPermission('user.delete') || hasPermission('user.details') || hasPermission('user.activation') || hasPermission('user.desactivation') || hasPermission('user.reset.password'))
                                        <th>Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $item)
                                    <tr>
                                        <td>{{ $item->name.' '.$item->lastname }}</td>
                                        <td>{{ $item->username }}</td>
                                        <td>@if($item->status == 0) <span class="label label-danger">Inactif</span> @else <span class="label label-success">Actif</span> @endif</td>
                                        <td>{{ $item->role->libelle }}</td>                                            
                                        @if (hasPermission('user.edit') || hasPermission('user.delete') || hasPermission('user.details') || hasPermission('user.activation') || hasPermission('user.desactivation') || hasPermission('user.reset.password'))
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        @if (hasPermission('user.details'))
                                                            <a class="dropdown-item" href="/user/details/{{ $item->id }}"><i class="fa fa-eye"></i> Détails sur l'utilisateur</a>
                                                        @endif
                                                        @if (hasPermission('user.reset.password'))
                                                            <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#reset-password-user-{{ $item->id }}"><i class="fa fa-spinner"></i>&nbsp;&nbsp;Reinitialiser le mot de passe</a>
                                                        @endif
                                                        @if (hasPermission('user.edit'))
                                                            <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#edit-user-{{ $item->id }}"><i class="fa fa-edit"></i>&nbsp;&nbsp;Modifier les informations</a>
                                                        @endif
                                                        @if (hasPermission('user.delete'))
                                                            <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#del-user-{{ $item->id }}"><i class="fa fa-trash"></i>&nbsp;&nbsp;Supprimer l'utilisateur</a>
                                                        @endif
                                                        @if($item->status == 0)
                                                            @if (hasPermission('user.activation'))
                                                                <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#activation-user-{{ $item->id }}"><i class="fa fa-check"></i>&nbsp;&nbsp;Activer l'utilisateur</a>
                                                            @endif
                                                        @else
                                                            @if (hasPermission('user.desactivation'))
                                                                <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#desactivation-user-{{ $item->id }}"><i class="fa fa-times"></i>&nbsp;&nbsp;Désactiver l'utilisateur</a>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>

                                    <div class="modal fade" id="edit-user-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="exampleModalLabel1">Modification de {{ $item->name.' '.$item->lastname }}</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <form action="/user/edit/{{ $item->id }}" method="POST"> 
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="form-group">  
                                                            <label for="recipient-name" class="control-label">Nom de l'Utilisateur:</label>
                                                            <input type="text" value="{{ $item->name }}" autocomplete="off" class="form-control" name="name">
                                                        </div>
                                                        <div class="form-group">  
                                                            <label for="recipient-name" class="control-label">Prenom de l'utilisateur:</label>
                                                            <input type="text" value="{{ $item->lastname }}" autocomplete="off" class="form-control" name="lastname">
                                                        </div>
                                                        <div class="form-group">  
                                                            <label for="">Role</label>
                                                            <select class="form-control select2bs4 type" name="role" id="" style="width:100%">
                                                                <option value="">Selectionner un role</option>
                                                                @foreach ($roles as $value)
                                                                    <option @if($value->id == $item->role_id) selected @endif value="{{ $value->id }}">{{ $value->libelle }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-primary">Modifier</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="del-user-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="exampleModalLabel1">Suppression de {{ $item->name.' '.$item->lastname }}</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <form action="/user/delete/{{ $item->id }}" method="POST"> 
                                                    @csrf
                                                    <div class="modal-body">
                                                        <p>Etes vous sur de supprimer cet utilisateur?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Non</button>
                                                        <button type="submit" class="btn btn-primary">Oui</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="reset-password-user-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="exampleModalLabel1">Reinitialisation du mot de passe de {{ $item->lastname.' '.$item->name }}</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <form action="/user/reset/password/{{ $item->id }}" method="POST"> 
                                                    @csrf
                                                    <div class="modal-body">
                                                        <p>Etes vous sur de réinitialiser le mot de passe de?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Non</button>
                                                        <button type="submit" class="btn btn-primary">Oui</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="activation-user-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="exampleModalLabel1">Activation de {{ $item->lastname.' '.$item->name }}</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <form action="/user/activation/{{ $item->id }}" method="POST"> 
                                                    @csrf
                                                    <div class="modal-body">
                                                        <p>Etes vous sur d'activer le compte de cet utilisateur?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Non</button>
                                                        <button type="submit" class="btn btn-primary">Oui</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                
                                    <div class="modal fade" id="desactivation-user-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="exampleModalLabel1">Desactivation du mot de passe de {{ $item->lastname.' '.$item->name }}</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <form action="/user/desactivation/{{ $item->id }}" method="POST"> 
                                                    @csrf
                                                    <div class="modal-body">
                                                        <p>Etes vous sur de désactiver le compte de cet utilisateur?</p>
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

    <div class="modal fade" id="add-user" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel1">Nouvel utilisateur</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form action="/user/add" method="POST"> 
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">  
                            <label for="recipient-name" class="control-label">Nom :</label>
                            <input type="text" required autocomplete="off" class="form-control" name="name">
                        </div>
                        <div class="form-group">  
                            <label for="recipient-name" class="control-label">Prénom :</label>
                            <input type="text" required autocomplete="off" class="form-control" name="lastname">
                        </div>
                        <div class="form-group">
                            <label for="">Role :</label>
                            <select required class="form-control select2bs4 type" name="role" id="" style="width:100%">
                                <option value="">Selectionner un role</option>
                                @foreach ($roles as $item)
                                    <option value="{{ $item->id }}">{{ $item->libelle }}</option>
                                @endforeach
                            </select>
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
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
            "language": {
                "search": "Rechercher:"
            }
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
    </script>
@endsection