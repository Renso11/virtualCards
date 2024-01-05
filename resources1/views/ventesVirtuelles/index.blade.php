@extends('base')
@section('css')
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endsection
@section('page')
    Commande de cartes virtuelles en attentes
@endsection
@section('content')
<section class="content">    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-0">
                    <h3 class="card-title">Liste des commandes de carte virtuelles en attentes</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped example1">
                            <thead>
                                <tr>
                                    <th>Nom et prenoms</th>
                                    <th>Email</th>
                                    <th>Telephone</th>
                                    <th>Reference</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ventes as $item)
                                    <tr>
                                        <td>{{ $item->kycClient->name.' '.$item->kycClient->lastname }} </td>
                                        <td>{{ $item->kycClient->email }}</td>
                                        <td>{{ $item->kycClient->telephone }}</td>
                                        <td>@if($item->reference) <span class="text-success">{{ $item->reference }}</span>@else <span class="text-danger">Pas de reference</span> @endif</td>
                                        <td>                                                
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#view-vente-{{ $item->id }}"><i class="fa fa-eye"></i>&nbsp;&nbsp;Détails sur l'achat</a>
                                                    @if (hasPermission('edit.kyc'))
                                                        <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#edit-kyc-{{ $item->id }}"><i class="fa fa-edit"></i>&nbsp;&nbsp;Modifier le KYC</a>
                                                    @endif
                                                    @if (hasPermission('vente.virtuelles.attentes.validation'))
                                                        <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#validation-vente-{{ $item->id }}"><i class="fa fa-check"></i>&nbsp;&nbsp;Valider la commande</a>
                                                    @endif
                                                    @if (hasPermission('vente.virtuelles.attentes.rejet'))
                                                        <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#rejet-vente-{{ $item->id }}"><i class="fa fa-times"></i>&nbsp;&nbsp;Rejeter la commande</a>
                                                    @endif
                                                    @if (hasPermission('vente.virtuelles.delete'))
                                                        <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#del-vente-{{ $item->id }}"><i class="fa fa-trash"></i>&nbsp;&nbsp;Supprimer la commande</a>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="view-vente-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="exampleModalLabel1">KYC de {{ $item->kycClient->lastname.' '.$item->kycClient->name }} | Commande du {{ $item->created_at->format('d-M-Y') }}</h4>
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
                                                                <label for="">Profession</label>
                                                                <p>{{ $item->kycClient->profession }}</p>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label for="">Revenu mensuel</label>
                                                                <p>{{ $item->kycClient->revenu }}</p>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label for="">Type de piece</label>
                                                                <p>@if($item->kycClient->piece_type == 1) Passeport @elseif($item->piece == 2) CNI @elseif($item->piece == 3) Permis de conduire @else Autres @endif</p>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label for="">Numero de la piece</label>
                                                                <p>{{ $item->kycClient->piece_id }}</p>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <a href="{{ $item->kycClient->piece_file }}" target="_blank">
                                                                    <img src="{{ $item->kycClient->piece_file }}" width="40%" alt="Image du client">
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Annuler</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="modal fade" id="edit-kyc-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                        <div class="modal-dialog modal-xl" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="exampleModalLabel1">Modification du kyc de {{ $item->kycClient->lastname.' '.$item->kycClient->name }}</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <form action="/kyc/edit/{{ $item->kycClient->id }}" method="POST" enctype="multipart/form-data"> 
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="recipient-name" class="control-label">Nom:</label>
                                                                    <input type="text" value="{{ $item->kycClient->name }}" autocomplete="off" class="form-control" name="name">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="message-text" class="control-label">Prenom:</label>
                                                                    <input type="text" value="{{ $item->kycClient->lastname }}" autocomplete="off" class="form-control" name="lastname">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="recipient-name" class="control-label">Email:</label>
                                                                    <input type="text" value="{{ $item->kycClient->email }}" autocomplete="off" class="form-control" name="email">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="message-text" class="control-label">Telephone:</label>
                                                                    <div class="row">
                                                                        <div class="col-md-4">
                                                                            <select class="form-control" name="code_pays" id="">
                                                                                <option value=""></option>
                                                                                @foreach ($countries as $value)
                                                                                    <option value="{{ $value['code'] }}" @if($value['code'] == explode(' ',$item->kycClient->telephone)[0]) selected @endif>{{ $value['name'] }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-md-8">
                                                                            <input type="text" autocomplete="off" class="form-control" name="telephone" value="{{ explode(' ',$item->kycClient->telephone)[1] }}">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="message-text" class="control-label">Date de naissance:</label>
                                                                    <input type="date" value="{{ date('Y-m-d', strtotime($item->kycClient->birthday)) }}" autocomplete="off" class="form-control" name="birthday">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="message-text" class="control-label">Departement:</label>
                                                                    <select id="dep" required name="departement" class="form-control" style="width:100%">
                                                                        <option selected="selected" value="">Sélectionnez un departement...</option>
                                                                        <option  @if($item->kycClient->departement == 'AL') selected @endif value="AL">Alibori</option>
                                                                        <option  @if($item->kycClient->departement == 'AK') selected @endif value="AK">Atacora</option>
                                                                        <option  @if($item->kycClient->departement == 'AQ') selected @endif value="AQ">Atlantique</option>
                                                                        <option  @if($item->kycClient->departement == 'BO') selected @endif value="BO">Borgou</option>
                                                                        <option  @if($item->kycClient->departement == 'CO') selected @endif value="CO">Collines</option>
                                                                        <option  @if($item->kycClient->departement == 'KO') selected @endif value="KO">Couffo</option>
                                                                        <option  @if($item->kycClient->departement == 'DO') selected @endif value="DO">Donga</option>
                                                                        <option  @if($item->kycClient->departement == 'LI') selected @endif value="LI">Littoral</option>
                                                                        <option  @if($item->kycClient->departement == 'MO') selected @endif value="MO">Mono</option>
                                                                        <option  @if($item->kycClient->departement == 'OU') selected @endif value="OU">Ouémé</option>
                                                                        <option  @if($item->kycClient->departement == 'PL') selected @endif value="PL">Plateau</option>
                                                                        <option  @if($item->kycClient->departement == 'ZO') selected @endif value="ZO">Zou</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="message-text" class="control-label">Ville:</label>
                                                                    <input type="text" value="{{ $item->kycClient->city }}" autocomplete="off" class="form-control" name="city">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="message-text" class="control-label">Adresse:</label>
                                                                    <input type="text" value="{{ $item->kycClient->address }}" autocomplete="off" class="form-control" name="address">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="message-text" class="control-label">Profession:</label>
                                                                    <input type="text" value="{{ $item->kycClient->profession }}" autocomplete="off" class="form-control" name="profession">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="message-text" class="control-label">Revenu:</label>
                                                                    <select class="form-control" name="revenu" required id="revenu" style="width:100%">
                                                                        <option selected="selected" value="">Sélectionnez le revenu..</option>
                                                                        <option @if($item->kycClient->revenu == '< 250 000') selected @endif value="< 250 000"> < 250 000 </option>
                                                                        <option @if($item->kycClient->revenu == '< 500 000') selected @endif value="< 500 000"> < 500 000 </option>
                                                                        <option @if($item->kycClient->revenu == '< 1 000 000') selected @endif value="< 1 000 000"> < 1 000 000 </option>
                                                                        <option @if($item->kycClient->revenu == '> 1 000 000') selected @endif value="> 1 000 000"> > 1 000 000 </option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="message-text" class="control-label">Type de piece:</label>
                                                                    <select required class="form-control"  name="piece_type" id="piece" style="width:100%">
                                                                        <option selected="selected" value="">Sélectionnez le type de piece</option>
                                                                        <option @if($item->kycClient->piece_type == 1) selected @endif value="1">Passeport</option>
                                                                        <option @if($item->kycClient->piece_type == 2) selected @endif value="2">CNI</option>
                                                                        <option @if($item->kycClient->piece_type == 3) selected @endif value="3">Permis de conduire</option>
                                                                        <option @if($item->kycClient->piece_type == 4) selected @endif value="4">Autres</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="message-text" class="control-label">Numero de la piece:</label>
                                                                    <input type="text" value="{{ $item->kycClient->piece_id }}" autocomplete="off" class="form-control" name="piece_id">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="message-text" class="control-label">Fichier de la piece:</label>
                                                                    <input type="file" accept=".pdf,image/png, image/jpeg" name="piece_file" placeholder="Cliquer pour choisir" />
                                                                </div>
                                                            </div>
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

                                    <div class="modal fade" id="del-vente-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="exampleModalLabel1">Suppression de la commande</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <form action="/ventes/virtuelles/delete/{{ $item->id }}" method="POST"> 
                                                    @csrf
                                                    <div class="modal-body">
                                                        <p>Etes vous sur de supprimer cette commande?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Non</button>
                                                        <button type="submit" class="btn btn-primary">Oui</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="validation-vente-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="exampleModalLabel1">Validation de la commande</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <form action="/ventes/virtuelles/attentes/validation/{{ $item->id }}" method="POST"> 
                                                    @csrf
                                                    <div class="modal-body">
                                                        <p>Etes vous sur de valider cette commande? Une carte sera attribué au client automatiquement.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Non</button>
                                                        <button type="submit" class="btn btn-primary">Oui</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="rejet-vente-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="exampleModalLabel1">Rejet de la commande</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <form action="/ventes/virtuelles/attentes/rejet/{{ $item->id }}" method="POST"> 
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label for="recipient-name" class="control-label">Entrez le motif du rejet</label>
                                                            <textarea name="motif_rejet" id="" cols="30" rows="5" class="form-control"></textarea>                                                           </div>
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