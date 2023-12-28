@extends('base')
@section('css')
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endsection
@section('page')
    Détails du partenaire
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
                        <strong><i class="fas fa-user mr-1"></i> Libellé</strong>

                        <p class="text-muted">
                            {{ $partenaire->libelle }}
                        </p>

                        <hr>

                        <strong><i class="fas fa-credit-card mr-1"></i> Custumer ID</strong>

                        <p class="text-muted">
                            <span class="tag tag-danger">{{ $partenaire->code }}</span>
                        </p>

                        <hr>

                        <strong><i class="fas fa-credit-card mr-1"></i> 4 dernier chiffres</strong>

                        <p class="text-muted">
                            <span class="tag tag-danger">{{ $partenaire->last }}</span>
                        </p>

                        <hr>

                        <strong><i class="fas fa-money-bill mr-1"></i> Compte distribution</strong>

                        <p class="text-muted">
                            <span class="tag tag-danger">{{ $partenaire->accountDistribution->solde }}</span>
                        </p>

                        <hr>

                        <strong><i class="fas fa-money-bill mr-1"></i> Compte commission</strong>

                        <p class="text-muted">
                            <span class="tag tag-danger">{{ $partenaire->accountCommission->solde }}</span>
                        </p>
                        <br/>
                        @if(hasPermission('partenaire.edit'))
                            <button type="button" data-toggle="modal" data-target="#edit-partenaire-{{ $partenaire->id }}" class="btn btn-success"><i class="fa fa-edit"></i> Modifier</button>
                        @endif
                        @if(hasPermission('partenaire.delete'))
                            <button type="button" data-toggle="modal" data-target="#del-partenaire-{{ $partenaire->id }}" class="btn btn-danger"><i class="fa fa-trash"></i> Supprimer</button>
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
                                <a class="nav-link" data-toggle="tab" href="#utilisateur">Utilisateurs</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#distribution">Compte Distribution</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#commission">Compte Commission</a>
                            </li>
                        </ul>
                    </div><!-- /.card-header -->
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="active tab-pane" id="depot">
                                <div class="card">
                                    <div class="card-header border-0">
                                        <h3 class="card-title">Liste des depots</h3>
                                        <div class="card-tools">
                                            <a href="#" class="btn btn-tool btn-sm">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="#" class="btn btn-tool btn-sm">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body table-responsive">
                                        <table class="table table-bordered table-striped example1">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Client</th>
                                                    <th>Telephone</th>
                                                    <th>Montant</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($depots as $item)     
                                                    <tr>
                                                        <td>{{ $item->created_at->format('d-M-Y') }}</td>
                                                        <td>{{ $item->userClient->name.' '.$item->userClient->lastname }}</td>
                                                        <td>{{ $item->userClient->username }}</td>
                                                        <td>{{ $item->montant }} F CFA</td>
                                                        <td>@if(!isset($item->status)) <span class="label label-danger">Annuler</span> @else @if($item->status == 0) <span class="label label-warning">En cours</span> @else <span class="label label-success">Effectue</span> @endif  @endif</td>
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
                                        <div class="card-tools">
                                            <a href="#" class="btn btn-tool btn-sm">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="#" class="btn btn-tool btn-sm">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body table-responsive">
                                        <table class="table table-bordered table-striped example1">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Client</th>
                                                    <th>Telephone</th>
                                                    <th>Montant</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($retraits as $value)     
                                                    <tr>
                                                        <td>{{ $value->created_at->format('d-M-Y') }}</td>
                                                        <td>{{ $value->userClient->name.' '.$value->userClient->lastname }}</td>
                                                        <td>{{ $value->userClient->username }}</td>
                                                        <td>{{ $value->montant }} F CFA</td>
                                                        <td>@if(!isset($value->status)) <span class="label label-danger">Annuler</span> @else @if($value->status == 0) <span class="label label-warning">En cours</span> @else <span class="label label-success">Effectue</span> @endif  @endif</td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                    Actions
                                                                </button>
                                                                <div class="dropdown-menu">
                                                                    <a class="dropdown-item" href="javascript:void(0)"><i class="fa fa-download"></i>&nbsp;&nbsp;Telecharger </a>
                                                                    @if(!isset($value->status))
                                                                        <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#motif-retrait-{{ $value->id }}"><i class="fa fa-eye"></i>&nbsp;&nbsp;Motif de l'annulation</a>
                                                                    @else
                                                                        @if($value->status == 0)
                                                                            @if(hasPermission('partenaire.cancel.retrait'))
                                                                                <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#cancel-retrait-{{ $value->id }}"><i class="fa fa-times"></i>&nbsp;&nbsp;Annuler</a>
                                                                            @endif
                                                                        @endif
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>  
            
                                                    @if(!isset($value->status))
                                                        <div class="modal fade" id="motif-retrait-{{ $value->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h4 class="modal-title" id="exampleModalLabel1">Motif de l'annulation</h4>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <p>
                                                                            {{ $value->motif_rejet }}
                                                                        </p>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Fermer</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>    
                                                    @else
                                                        @if($value->status == 0)
                                                            <div class="modal fade" id="cancel-retrait-{{ $value->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                                                <div class="modal-dialog" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h4 class="modal-title" id="exampleModalLabel1">Annulation de retrait</h4>
                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                                        </div>
                                                                        <form action="/partenaire/cancel/retrait/{{ $value->id }}" method="POST"> 
                                                                            @csrf
                                                                            <div class="modal-body">
                                                                                <div class="form-group">
                                                                                    <label for="recipient-name" class="control-label">Motif d'annulation du retrait</label>
                                                                                    <textarea name="motif" id="" cols="30" rows="5" required class="form-control"></textarea>
                                                                                </div>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button" class="btn btn-danger" data-dismiss="modal">Annuler</button>
                                                                                <button type="submit" class="btn btn-primary">Valider</button>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- /.tab-pane -->

                            <div class="tab-pane" id="utilisateur">
                                <div class="card">
                                    <div class="card-header border-0">
                                        <h3 class="card-title">Liste des transferts</h3>
                                        <div class="card-tools">
                                            <a href="#" class="btn btn-tool btn-sm">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="#" class="btn btn-tool btn-sm">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body table-responsive">
                                        <table class="table table-bordered table-striped example1">
                                            <thead>
                                                <tr>
                                                    <th>Nom et prenoms</th>
                                                    <th>Username</th>
                                                    <th>Status</th>
                                                    <th>Role</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($users as $value)
                                                    <tr>
                                                        <td>{{ $value->name.' '.$value->lastname }}</td>
                                                        <td>{{ $value->username }}</td>
                                                        <td>@if($value->status == 0) <span class="label label-danger">Inactif</span> @else <span class="label label-success">Actif</span> @endif</td>
                                                        <td>{{ $value->rolePartenaire->libelle }}</td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                    Actions
                                                                </button>
                                                                <div class="dropdown-menu">
                                                                    <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#reset-password-user-{{ $value->id }}"><i class="fa fa-spinner"></i>&nbsp;&nbsp;Reinitialisater le mot de passe</a>
                                                                    <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#edit-user-{{ $value->id }}"><i class="fa fa-edit"></i>&nbsp;&nbsp;Modifier les informations</a>
                                                                    <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#del-user-{{ $value->id }}"><i class="fa fa-trash"></i>&nbsp;&nbsp;Supprimer l'utilisateur</a>
                                                                    @if($value->status == 0)
                                                                        <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#activation-client-{{ $value->id }}"><i class="fa fa-check"></i>&nbsp;&nbsp;Activer l'utilisateur</a>
                                                                    @else
                                                                        <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#desactivation-client-{{ $value->id }}"><i class="fa fa-times"></i>&nbsp;&nbsp;Désactiver l'utilisateur</a>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>       
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane" id="distribution">
                                <div class="card">
                                    <div class="card-header border-0">
                                        <h3 class="card-title">Détails compte distribution</h3>
                                        <div class="card-tools">
                                            <a href="#" class="btn btn-tool btn-sm">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="#" class="btn btn-tool btn-sm">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body table-responsive">
                                        <table class="table table-bordered table-striped example1">
                                            <thead>
                                                <tr>
                                                    <th style="width: 10px">#</th>
                                                    <th>Libelle</th>
                                                    <th class="text-capitalize">Type</th>
                                                    <th>Date</th>
                                                    <th>Solde avant</th>
                                                    <th>Montant</th>
                                                    <th>Solde apres</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($partenaire->accountDistribution->accountDistributionOperations as $item)
                                                    <tr>
                                                        <td>{{ $item->id }}</td>
                                                        <td>{{ $item->libelle }}</td>
                                                        <td>{{ $item->type }}</td>
                                                        <td>{{ $item->created_at->format('d/m/Y') }}</td>
                                                        <td>{{ $item->solde_avant }} F CFA</td>
                                                        <td>{{ $item->montant }} F CFA</td>
                                                        <td>{{ $item->solde_apres }} F CFA</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane" id="commission">
                                <div class="card">
                                    <div class="card-header border-0">
                                        <h3 class="card-title">Détails compte commission</h3>
                                        <div class="card-tools">
                                            <a href="#" class="btn btn-tool btn-sm">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="#" class="btn btn-tool btn-sm">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body table-responsive">
                                        <table class="table table-bordered table-striped example1">
                                            <thead>
                                                <tr>
                                                    <th style="width: 10px">#</th>
                                                    <th>Libelle</th>
                                                    <th>Type</th>
                                                    <th>Date</th>
                                                    <th>Solde avant</th>
                                                    <th>Montant</th>
                                                    <th>Solde apres</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($partenaire->accountCommission->accountCommissionOperations as $item)
                                                    <tr>
                                                        <td>{{ $item->id }}</td>
                                                        <td>{{ $item->libelle }}</td>
                                                        <td class="text-capitalize">{{ $item->type }}</td>
                                                        <td>{{ $item->created_at->format('d/m/Y') }}</td>
                                                        <td>{{ $item->solde_avant }} F CFA</td>
                                                        <td>{{ $item->montant }} F CFA</td>
                                                        <td>{{ $item->solde_apres }} F CFA</td>
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


        <div class="modal fade" id="edit-partenaire-{{ $partenaire->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel1">Modifiacation de {{ $partenaire->libelle }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form action="/partenaire/edit/{{ $partenaire->id }}" method="POST"> 
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">  
                                <label for="recipient-name" class="control-label">Libelle du partenaire:</label>
                                <input type="text" value="{{ $partenaire->libelle }}" autocomplete="off" class="form-control" name="libelle">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">  
                                        <label for="recipient-name" class="control-label">CustomerID</label>
                                        <input type="text" value="{{ $partenaire->code }}" autocomplete="off" class="form-control" name="code">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">  
                                        <label for="recipient-name" class="control-label">4 derniers chiffres:</label>
                                        <input type="text" value="{{ $partenaire->last }}" autocomplete="off" class="form-control" name="last">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">  
                                <label for="recipient-name" class="control-label"> Fichier du RCCM <small>(seulement pour changer)</small></label>
                                <input type="file" autocomplete="off" required class="form-control" name="rccm">
                            </div>
                            <div class="form-group">  
                                <label for="recipient-name" class="control-label">Fichier du IF<small>(seulement pour changer)</small></label>
                                <input type="file" autocomplete="off" required class="form-control" name="ifu">
                            </div>
                            <div class="row">
                                <table class="table table-bordered table-striped example1">
                                    <thead>
                                        <tr>
                                            <th>Type fichier</th>
                                            <th>Fichier</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>RCCM</td>
                                            <td>
                                                <a target="_blank" href="{{ asset($partenaire->rccm) }}" class="btn btn-primary">
                                                    <i class="fa fa-eye"></i> Voir le fichier
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>IFU</td>
                                            <td>
                                                <a target="_blank" href="{{ asset($partenaire->ifu) }}" class="btn btn-primary">
                                                    <i class="fa fa-eye"></i> Voir le fichier
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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

        <div class="modal fade" id="del-partenaire-{{ $partenaire->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel1">Suppression de {{ $partenaire->libelle }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form action="/partenaire/delete/{{ $partenaire->id }}" method="POST"> 
                        @csrf
                        <div class="modal-body">
                            <p>Etes vous sur de supprimer ce partenaire?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Non</button>
                            <button type="submit" class="btn btn-primary">Oui</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @foreach ($users as $value)    
            <div class="modal fade" id="edit-user-{{ $value->id }}">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="exampleModalLabel1">Modification de {{ $value->name.' '.$value->lastname }}</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form action="/partenaire/user/edit/{{ $value->id }}" method="POST"> 
                            @csrf
                            <div class="modal-body">
                                <div class="form-group">  
                                    <label for="recipient-name" class="control-label">Nom de l'Utilisateur:</label>
                                    <input type="text" value="{{ $value->name }}" required autocomplete="off" class="form-control" name="name">
                                </div>
                                <div class="form-group">  
                                    <label for="recipient-name" class="control-label">Prenom de l'utilisateur:</label>
                                    <input type="text" value="{{ $value->lastname }}" required autocomplete="off" class="form-control" name="lastname">
                                </div>
                                <div class="form-group">
                                    <label for="">Role</label>
                                    <select class="form-control select2bs4 type" name="role" required id="" style="width:100%">
                                        <option value="">Selectionner un role</option>
                                        @foreach ($roles as $itemo)
                                            <option @if($value->role_partenaire_id = $itemo->id) selected  @endif value="{{ $itemo->id }}">{{ $itemo->libelle }}</option>
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
        
            <div class="modal fade" id="del-user-{{ $value->id }}">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="exampleModalLabel1">Suppression de {{ $value->name.' '.$value->lastname }}</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form action="/partenaire/user/delete/{{ $value->id }}" method="POST"> 
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
        
            <div class="modal fade" id="reset-password-user-{{ $value->id }}"> 
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="exampleModalLabel1">Reinitialisation du mot de passe de {{ $value->lastname.' '.$value->name }}</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form action="/partenaire/user/reset/password/{{ $value->id }}" method="POST"> 
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
        
            <div class="modal fade" id="activation-client-{{ $value->id }}">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="exampleModalLabel1">Activation de {{ $value->lastname.' '.$value->name }}</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form action="/partenaire/user/activation/{{ $value->id }}" method="POST"> 
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
        
            <div class="modal fade" id="desactivation-client-{{ $value->id }}">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="exampleModalLabel1">Desactivation du mot de passe de {{ $value->lastname.' '.$value->name }}</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <form action="/partenaire/user/desactivation/{{ $value->id }}" method="POST"> 
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