
<table class="table table-bordered table-striped example1">
    <thead>
        <tr>
            <th>Type</th>
            <th>Partenaire</th>
            <th>Date</th>
            <th>Montant</th>
            <th>Frais</th>
            <th>Solde avant</th>
            <th>Solde apres</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($transactions as $item)
            <tr>
                <td @if($item->type == 'Appro') class="text-success" @else class="text-danger" @endif>{{ $item->type }}</td>
                <td>{{ $item->apiPartenaireAccount->libelle }}</td>
                <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>
                <td>{{ $item->montant }}</td>
                <td>{{ $item->frais }}</td>
                <td>{{ $item->solde_avant }}</td>                                          
                <td>{{ $item->solde_apres }}</td>                                          
                <td>
                    @if ($item->status == 0)
                        <span class="badge badge-danger">Echec</span>
                    @elseif ($item->status == 1)
                        <span class="badge badge-success">Succes</span>
                    @elseif ($item->status == 2)
                        <span class="badge badge-default">Ristourne</span>
                    @else
                        <span class="badge badge-warning">En attente</span>
                    @endif                                                
                </td>                                          
                <td>
                    <div class="btn-group">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Actions
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#edit-frais-{{ $item->id }}"><i class="fa fa-edit"></i>&nbsp;&nbsp;Détails de la transaction</a>
                            <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#edit-frais-{{ $item->id }}"><i class="fa fa-edit"></i>&nbsp;&nbsp;Ristourne</a>
                        </div>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>