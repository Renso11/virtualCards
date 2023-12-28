@extends('base')
@section('css')
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endsection
@section('page')
    Détails du client
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
                                {{ $userClient->name . ' ' . $userClient->lastname }}
                            </p>

                            <hr>

                            <strong><i class="fas fa-phone mr-1"></i> Telephone</strong>

                            <p class="text-muted">+{{ $userClient->username }}</p>

                            <hr>

                            <strong><i class="fas fa-credit-card mr-1"></i> Custumer ID</strong>

                            <p class="text-muted">
                                <span class="tag tag-danger">{{ $userClient->code }}</span>
                            </p>

                            <hr>

                            <strong><i class="fas fa-credit-card mr-1"></i> 4 dernier chiffres</strong>

                            <p class="text-muted">
                                <span class="tag tag-danger">{{ $userClient->last }}</span>
                            </p>

                            <hr>

                            <strong><i class="fas fa-lock mr-1"></i> Derniere connexion</strong>

                            <p class="text-muted">
                                <span class="tag tag-danger">{{ $userClient->lastconnexion }}</span>
                            </p>
                            <br />
                            @if (hasPermission('client.edit'))
                                <button type="button" data-toggle="modal" data-target="#edit-client-{{ $userClient->id }}"
                                    class="btn btn-success"><i class="fa fa-edit"></i></button>
                            @endif
                            @if (hasPermission('client.delete'))
                                <button type="button" data-toggle="modal" data-target="#del-client-{{ $userClient->id }}"
                                    class="btn btn-danger"><i class="fa fa-trash"></i></button>
                            @endif
                            @if (hasPermission('client.reset.password'))
                                <button data-toggle="modal" data-target="#reset-password-client-{{ $userClient->id }}"
                                    type="button"class="btn btn-default"><i class="fa fa-spinner"></i></button>
                            @endif
                            @if ($userClient->status == 0)
                                @if (hasPermission('client.activation'))
                                    <button type="button" data-toggle="modal"
                                        data-target="#activation-client-{{ $userClient->id }}" class="btn btn-success"><i
                                            class="fa fa-check"></i></button>
                                @endif
                            @else
                                @if (hasPermission('client.desactivation'))
                                    <button type="button" data-toggle="modal"
                                        data-target="#desactivation-client-{{ $userClient->id }}" class="btn btn-danger"><i
                                            class="fa fa-times"></i></button>
                                @endif
                            @endif
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#depot">Depots</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#retrait">Retraits</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#transfert">Transferts</a>
                                </li>
                            </ul>
                        </div><!-- /.card-header -->
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="active tab-pane" id="depot">
                                    <div class="card">
                                        <div class="card-header border-0">
                                            <h3 class="card-title">Liste des depots</h3>
                                        </div>
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
                                                    @foreach ($depots as $item)     
                                                        <tr>
                                                            <td>{{ $item->partenaire->libelle }}</td>
                                                            <td>{{ $item->libelle }}</td>
                                                            <td>{{ $item->montant }} F CFA</td>
                                                            <td>@if($item->status == 0) <span class="label label-warning">En cours</span> @else <span class="label label-success">Effectue</span> @endif</td>
                                                            <td>
                                                                <button type="button" data-toggle="modal" class="btn btn-danger"><i class="fa fa-times"></i> Annuler </button>
                                                            </td>
                                                        </tr>          
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.tab-pane -->
                                <div class="tab-pane" id="retrait">
                                    <div class="card">
                                        <div class="card-header border-0">
                                            <h3 class="card-title">Liste des retraits</h3>
                                        </div>
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
                                                    @foreach ($retraits as $item)
                                                        <tr>
                                                            <td>{{ $item->partenaire->libelle }}</td>
                                                            <td>{{ $item->libelle }}</td>
                                                            <td>{{ $item->montant }} F CFA</td>
                                                            <td>
                                                                @if ($item->status == 0)
                                                                    <span class="label label-warning">En cours</span>
                                                                @else
                                                                    <span class="label label-success">Effectue</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <button type="button" data-toggle="modal"
                                                                    class="btn btn-danger"><i class="fa fa-times"></i> Annuler
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.tab-pane -->

                                <div class="tab-pane" id="transfert">
                                    <div class="card">
                                        <div class="card-header border-0">
                                            <h3 class="card-title">Liste des transferts</h3>
                                        </div>
                                        <div class="card-body table-responsive">
                                            <table class="table table-bordered table-striped example1">
                                                <thead>
                                                    <tr>
                                                        <th>Receveur</th>
                                                        <th>Libellé</th>
                                                        <th>Montant</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($transferts as $item)
                                                        <tr>
                                                            <td>{{ $item->receveur->name . ' ' . $item->receveur->lastname }}</td>
                                                            <td>{{ $item->libelle }}</td>
                                                            <td>{{ $item->montant }} F CFA</td>
                                                            <td>
                                                                @if ($item->status == 0)
                                                                    <span class="label label-warning">En cours</span>
                                                                @else
                                                                    <span class="label label-success">Effectue</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <button type="button" data-toggle="modal"
                                                                    class="btn btn-danger"><i class="fa fa-times"></i> Annuler
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.tab-content -->
                            </div><!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </div><!-- /.container-fluid -->
        </div><!-- /.container-fluid -->

        <div class="modal fade" id="edit-client-{{ $userClient->id }}" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalLabel1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel1">Modifiacation de
                            {{ $userClient->lastname . ' ' . $userClient->name }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    </div>
                    <form action="/client/edit/{{ $userClient->id }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="recipient-name" class="control-label">Nom:</label>
                                <input type="text" value="{{ $userClient->name }}" autocomplete="off"
                                    class="form-control" name="name">
                            </div>
                            <div class="form-group">
                                <label for="message-text" class="control-label">Prenom:</label>
                                <input type="text" value="{{ $userClient->lastname }}" autocomplete="off"
                                    class="form-control" name="lastname">
                            </div>
                            <div class="form-group">
                                <label for="recipient-name" class="control-label">Code:</label>
                                <input type="text" value="{{ $userClient->code }}" autocomplete="off"
                                    class="form-control" name="code">
                            </div>
                            <div class="form-group">
                                <label for="message-text" class="control-label">4 dernier chiffres:</label>
                                <input type="text" value="{{ $userClient->last }}" autocomplete="off"
                                    class="form-control" name="last">
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
    
        <div class="modal fade" id="del-client-{{ $userClient->id }}" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalLabel1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel1">Suppression de
                            {{ $userClient->lastname . ' ' . $userClient->name }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    </div>
                    <form action="/client/delete/{{ $userClient->id }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p>Etes vous sur de supprimer ce client?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Non</button>
                            <button type="submit" class="btn btn-primary">Oui</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    
        <div class="modal fade" id="reset-password-client-{{ $userClient->id }}" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalLabel1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel1">Reinitialisation du mot de passe de
                            {{ $userClient->lastname . ' ' . $userClient->name }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    </div>
                    <form action="/client/reset/password/{{ $userClient->id }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p>Etes vous sur de réinitialiser le mot de passe de client?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Non</button>
                            <button type="submit" class="btn btn-primary">Oui</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    
        <div class="modal fade" id="activation-client-{{ $userClient->id }}" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalLabel1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel1">Activation de
                            {{ $userClient->lastname . ' ' . $userClient->name }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    </div>
                    <form action="/client/activation/{{ $userClient->id }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p>Etes vous sur d'activer le compte de ce client?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Non</button>
                            <button type="submit" class="btn btn-primary">Oui</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    
        <div class="modal fade" id="desactivation-client-{{ $userClient->id }}" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalLabel1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel1">Desactivation du mot de passe de
                            {{ $userClient->lastname . ' ' . $userClient->name }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    </div>
                    <form action="/client/desactivation/{{ $userClient->id }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p>Etes vous sur de désactiver le compte de ce client?</p>
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