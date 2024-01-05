@extends('base')
@section('css')
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endsection
@section('page')
    Commande de cartes virtuelles finalisées
@endsection
@section('content')
<section class="content"> 
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-0">
                    <h3 class="card-title">Liste des commandes de carte virtuelles finalisées</h3>
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
                                                    <h4 class="modal-title" id="exampleModalLabel1">Détails de la commande</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
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
                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label for="">CustomerID</label>
                                                            <p>{{ $item->carteVirtuelle->code }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Annuler</button>
                                                </div>
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