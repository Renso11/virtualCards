@extends('base')
@section('css')
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endsection
@section('page')
    Détails de l'utilisateur
@endsection
@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Informations</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <strong><i class="fas fa-user mr-1"></i> Nom et prénoms</strong>

                            <p class="text-muted">
                                {{ $user->name . ' ' . $user->lastname }}
                            </p>

                            <hr>

                            <strong><i class="fas fa-at mr-1"></i> Username</strong>

                            <p class="text-muted">{{ $user->username }}</p>

                            <hr>

                            <strong><i class="fas fa-lock mr-1"></i> Derniere connexion</strong>

                            <p class="text-muted">
                                <span class="tag tag-danger">{{ $user->lastconnexion }}</span>
                            </p>
                            <br/>
                            @if (hasPermission('user.edit'))
                                <button type="button" data-toggle="modal" data-target="#edit-user-{{ $user->id }}" class="btn btn-success"><i class="fa fa-edit"></i></button>
                            @endif
                            @if (hasPermission('user.delete'))
                                <button type="button" data-toggle="modal" data-target="#del-user-{{ $user->id }}" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                            @endif
                            @if (hasPermission('user.reset.password'))
                                <button type="button" data-toggle="modal" data-target="#reset-password-user-{{ $user->id }}" class="btn btn-primary"><i class="fa fa-spinner"></i></button>
                            @endif
                            @if($user->status == 0)
                                @if (hasPermission('user.activation'))
                                <button type="button" data-toggle="modal" data-target="#activation-user-{{ $user->id }}" class="btn btn-success"><i class="fa fa-check"></i></button>
                                @endif
                            @else
                                @if (hasPermission('user.desactivation'))
                                <button type="button" data-toggle="modal" data-target="#desactivation-user-{{ $user->id }}" class="btn btn-default"><i class="fa fa-times"></i></button>
                                @endif
                            @endif
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header p-2">
                            <h5>Activité de l'utilisateur</h3>
                        </div><!-- /.card-header -->
                        <div class="card-body">
                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-striped example1">
                                    <thead>
                                        <tr>
                                            <th>Partenaire</th>
                                            <th>Libellé</th>
                                            <th>Montant</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.col -->
                </div>
            </div>
        </div>
        

        <div class="modal fade" id="edit-user-{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel1">Modification de {{ $user->name.' '.$user->lastname }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form action="/user/edit/{{ $user->id }}" method="POST"> 
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">  
                                <label for="recipient-name" class="control-label">Nom de l'Utilisateur:</label>
                                <input type="text" value="{{ $user->name }}" autocomplete="off" class="form-control" name="name">
                            </div>
                            <div class="form-group">  
                                <label for="recipient-name" class="control-label">Prenom de l'utilisateur:</label>
                                <input type="text" value="{{ $user->lastname }}" autocomplete="off" class="form-control" name="lastname">
                            </div>
                            <div class="form-group">  
                                <label for="">Role</label>
                                <select class="form-control select2bs4 type" name="role" id="" style="width:100%">
                                    <option value="">Selectionner un role</option>
                                    @foreach ($roles as $value)
                                        <option @if($value->id == $user->role_id) selected @endif value="{{ $value->id }}">{{ $value->libelle }}</option>
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

        <div class="modal fade" id="del-user-{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel1">Suppression de {{ $user->name.' '.$user->lastname }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form action="/user/delete/{{ $user->id }}" method="POST"> 
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
        
        <div class="modal fade" id="reset-password-user-{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel1">Reinitialisation du mot de passe de {{ $user->lastname.' '.$user->name }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form action="/user/reset/password/{{ $user->id }}" method="POST"> 
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

        <div class="modal fade" id="activation-user-{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel1">Activation de {{ $user->lastname.' '.$user->name }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form action="/user/activation/{{ $user->id }}" method="POST"> 
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

        <div class="modal fade" id="desactivation-user-{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel1">Desactivation du mot de passe de {{ $user->lastname.' '.$user->name }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form action="/user/desactivation/{{ $user->id }}" method="POST"> 
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