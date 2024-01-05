
@if(count($retraits))
<div class="row">
    <button type="button" class="btn btn-info exportToExcel">
        Exporter en Excel
    </button>
    &nbsp;&nbsp;&nbsp;
    <a class="btn btn-info" href="/download/rapport/retraits">Exporter en PDF</a>
</div>   
@endif
<div id="table">
<br>
<table class="table table-bordered">
    <thead>
        <tr>
            <td>Date de l'operation</td>
            <td>Partenaire</td>
            <td>Client</td>
            <td>Libelle</td>
            <td>Montant (FCFA)</td>
        </tr>
    </thead>
    <tbody>        
        @forelse($retraits as $item)
            <tr>
                <td>{{ $item->created_at->format('d-m-Y à h:i:s') }}</td>
                <td>{{ $item->partenaire->libelle }}</td>
                <td>{{ $item->userClient->name.' '.$item->userClient->lastname }}</td>
                <td>{{ $item->libelle }}</td>
                <td>{{ $item->montant }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5">Pas de données</td>
            </tr>
        @endforelse
    </tbody>
</table>
</div>