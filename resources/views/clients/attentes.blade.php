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
    Liste des clients en attentes
@endsection
@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    @if (hasPermission('client.add'))
                        <button type="button" class="btn waves-effect waves-light btn-primary" data-toggle="modal" data-target="#add-client">Ajouter un compte client</button>
                    @endif
                    <br>
                    <br>
                    <div class="card">
                        <div class="card-header border-0">
                        <h3 class="card-title">Liste des clients en attentes</h3>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-bordered table-striped example1">
                                <thead>
                                    <tr>
                                        <th>Nom et prénoms</th>
                                        <th>Telephone</th>
                                        <th>Status</th>
                                        <th>Verification</th>
                                        @if (hasPermission('client.edit') || hasPermission('client.delete') || hasPermission('client.details')|| hasPermission('client.activation')|| hasPermission('client.desactivation') || hasPermission('client.reset.password'))
                                            <th>Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($userClients as $item)
                                        <tr>
                                            <td>{{ $item->lastname.' '.$item->name }}</td>
                                            <td>{{ $item->username }}</td>
                                            <td>@if($item->status == 0) <span class="badge bg-danger">Inactif</span> @else <span class="badge bg-success">Actif</span> @endif</td>
                                            <td>@if($item->verification == 0) <span class="badge bg-danger">Non vérifié</span> @else <span class="badge bg-success">Vérifié</span> @endif</td>
                                            @if (hasPermission('client.edit') || hasPermission('client.details') || hasPermission('client.delete')|| hasPermission('client.activation')|| hasPermission('client.desactivation')|| hasPermission('client.reset.password'))
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            Actions
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            @if($item->kyc_client_id)
                                                                <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#validation-client-{{ $item->id }}"><i class="fa fa-check"></i>&nbsp;&nbsp;Valider / Rejeter</a>
                                                            @endif
                                                            @if (hasPermission('client.edit'))
                                                                <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#edit-client-{{ $item->id }}"><i class="fa fa-edit"></i>&nbsp;&nbsp;Modifier les informations</a>
                                                            @endif
                                                            @if (hasPermission('client.reset.password'))
                                                                <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#reset-password-client-{{ $item->id }}"><i class="fa fa-spinner"></i>&nbsp;&nbsp;Reinitialisater le mot de passe</a>
                                                            @endif
                                                            @if($item->status == 0)
                                                                @if (hasPermission('client.activation'))
                                                                    <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#activation-client-{{ $item->id }}"><i class="fa fa-check"></i>&nbsp;&nbsp;Activer le compte</a>
                                                                @endif
                                                            @else
                                                                @if (hasPermission('client.desactivation'))
                                                                    <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#desactivation-client-{{ $item->id }}"><i class="fa fa-times"></i>&nbsp;&nbsp;Désactiver le compte</a>
                                                                @endif
                                                            @endif
                                                            @if (hasPermission('client.delete'))
                                                                <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#del-client-{{ $item->id }}"><i class="fa fa-trash"></i>&nbsp;&nbsp;Supprimer le compte</a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>

                                        <div class="modal fade" id="edit-client-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="exampleModalLabel1">Modification de {{ $item->lastname.' '.$item->name }}</h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <form action="/client/edit/{{ $item->id }}" method="POST"> 
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label for="recipient-name" class="control-label">Nom:</label>
                                                                <input type="text" value="{{ $item->name }}" autocomplete="off" class="form-control" name="name">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="message-text" class="control-label">Prenom:</label>
                                                                <input type="text" value="{{ $item->lastname }}" autocomplete="off" class="form-control" name="lastname">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="recipient-name" class="control-label">Code:</label>
                                                                <input type="text" value="{{ $item->code }}" autocomplete="off" class="form-control" name="code">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="message-text" class="control-label">4 dernier chiffres:</label>
                                                                <input type="text" value="{{ $item->last }}" autocomplete="off" class="form-control" name="last">
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

                                        <div class="modal fade" id="del-client-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="exampleModalLabel1">Suppression de {{ $item->lastname.' '.$item->name }}</h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <form action="/client/delete/{{ $item->id }}" method="POST"> 
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

                                        <div class="modal fade" id="reset-password-client-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="exampleModalLabel1">Reinitialisation du mot de passe de {{ $item->lastname.' '.$item->name }}</h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <form action="/client/reset/password/{{ $item->id }}" method="POST"> 
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

                                        <div class="modal fade" id="activation-client-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="exampleModalLabel1">Activation de {{ $item->lastname.' '.$item->name }}</h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <form action="/client/activation/{{ $item->id }}" method="POST"> 
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

                                        <div class="modal fade" id="desactivation-client-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="exampleModalLabel1">Désactivation de {{ $item->lastname.' '.$item->name }}</h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <form action="/client/desactivation/{{ $item->id }}" method="POST"> 
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

                                        <div class="modal fade" id="valid-conf-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel1">Validation du dossier de {{ $item->lastname.' '.$item->name }}</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <form action="/client/validation/{{ $item->id }}" method="POST"> 
                                                        @csrf
                                                        <div class="modal-body">
                                                            <p>Etes vous sur de valider le compte de ce client?</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-danger" data-dismiss="modal">Non</button>
                                                            <button type="submit" class="btn btn-primary">Oui</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="rejet-conf-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel1">Rejet du dossier de {{ $item->lastname.' '.$item->name }}</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <form action="/client/rejet/{{ $item->id }}" method="POST"> 
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label for="recipient-name" class="control-label">Niveau du rejet</label>
                                                                <select class="form-control select2bs4" required name="niveau" id="niveau"  data-placeholder="Selectionner le niveau">
                                                                    <option value="">Selectionner le motif du rejet</option>
                                                                    <option value="2">Information incorrecte</option>
                                                                    <option value="3">Pieces ou photo non valide</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="recipient-name" class="control-label">Description:</label>
                                                                <textarea class="form-control" name="description" id="" rows="5"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-danger" data-dismiss="modal">Non</button>
                                                            <button type="submit" class="btn btn-primary">Oui</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        @if($item->kyc_client_id)
                                            <div class="modal fade" id="validation-client-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                                <div class="modal-dialog modal-lg" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title" id="exampleModalLabel1">KYC de {{ $item->kycClient->lastname.' '.$item->kycClient->name }} | Demande du {{ $item->updated_at->format('d-M-Y') }}</h4>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                        </div>
                                                        <form action="/client/edit/{{ $item->id }}" method="POST"> 
                                                            @csrf
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <label for="">Nom et prénoms</label>
                                                                        <p>{{ $item->kycClient->name.' '.$item->kycClient->lastname }}</p>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label for="">Email</label>
                                                                        <p>{{ $item->kycClient->email }}</p>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label for="">Telephone</label>
                                                                        <p>{{ $item->kycClient->telephone }}</p>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label for="">Naissance</label>
                                                                        <p>{{ $item->kycClient->birthday }}</p>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label for="">Departement</label>
                                                                        <p class="text-capitalize">{{ $item->kycClient->departement }}</p>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label for="">Ville</label>
                                                                        <p>{{ $item->kycClient->city }}</p>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label for="">Adresse</label>
                                                                        <p>{{ $item->kycClient->address }}</p>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label for="">Type de piece</label>
                                                                        <p>@if($item->kycClient->piece_type == 1) Passeport @elseif($item->piece == 2) CNI @elseif($item->piece == 3) Permis de conduire @else Autres @endif</p>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label for="">Numero de la piece</label>    
                                                                        <p>{{ $item->kycClient->piece_id }}</p>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label for="">Piece du client</label> <br>
                                                                        <a href="{{ $item->kycClient->piece_file }}" target="_blank" class="btn btn-primary">
                                                                            Voir la piece
                                                                        </a>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label for="">Client avec la piece</label> <br>
                                                                        <a href="{{ $item->kycClient->user_with_piece }}" target="_blank" class="btn btn-primary">
                                                                            Voir le client avec la piece
                                                                        </a>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label for="">Verification numero</label>
                                                                        <p>@if($item->verification_step_one == 0) <span class="badge bg-danger">Non vérifié</span> @else <span class="badge bg-success">Vérifié</span> @endif</p>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label for="">Verification informations</label>
                                                                        <p>@if($item->verification_step_two == 0) <span class="badge bg-danger">Non vérifié</span> @else <span class="badge bg-success">Vérifié</span> @endif</p>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label for="">Verification identité</label>
                                                                        <p>@if($item->verification_step_three == 0) <span class="badge bg-danger">Non vérifié</span> @else <span class="badge bg-success">Vérifié</span> @endif</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                @if($item->verification_step_one == 1 && $item->verification_step_two == 1 && $item->verification_step_three == 1 && $item->verification == 0)
                                                                    <button type="button" data-dismiss="modal" data-toggle="modal" data-target="#valid-conf-{{ $item->id }}" class="btn btn-success">Valider le compte</button>
                                                                @endif
                                                                <button data-dismiss="modal" data-toggle="modal" data-target="#rejet-conf-{{ $item->id }}" class="btn btn-primary">Rejeter le KYC</button>
                                                                <button type="button" class="btn btn-danger" data-dismiss="modal">Annuler</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="add-client" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
            <div class="modal-dialog" role="document">
                <form action="/client/add" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="exampleModalLabel1">Nouveau client</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">  
                                <label for="recipient-name" class="control-label">Taper le numero de telephone:</label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <select class="form-control select2bs4" required name="codePays" id="code-pays"  data-placeholder="Selectionner le pays">
                                            <option value=""></option>
                                            @foreach ($countries as $key => $item)
                                                <option value="{{ $item['code'] }}" data-flag="{{ $key }}">{{ '(+'.$item['code'].') -'.$item['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="telephone" required autocomplete="off" class="form-control" id="telephone">
                                        @error('telephone') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="recipient-name" class="control-label">Code:</label>
                                <input type="text" required  autocomplete="off" class="form-control" name="code" id="code">
                            </div>
                            <div class="form-group text-center" style="display: none " id="loader">
                                <i class="fa fa-spin fa-spinner"></i>
                            </div>
                            <div class="form-group">
                                <label for="recipient-name" class="control-label">Nom:</label>
                                <input type="text" required readonly  autocomplete="off" class="form-control" name="name" id="name">
                            </div>
                            <div class="form-group">
                                <label for="message-text" class="control-label">Prenom:</label>
                                <input type="text" required readonly  autocomplete="off" class="form-control" name="lastname" id="lastname">
                            </div>
                            <div class="form-group">
                                <label for="message-text" class="control-label">4 dernier chiffres:</label>
                                <input type="text" required readonly  autocomplete="off" class="form-control" name="last" id="last">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </div>
                </form>
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