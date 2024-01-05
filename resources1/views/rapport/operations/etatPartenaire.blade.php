
<div class="card">
    <div class="card-body">
        <div class="row">
            <button type="button" class="btn btn-info exportToExcel">
                <i class="far fa-file-excel" aria-hidden="true"></i> Exporter en Excel
            </button>
            &nbsp;&nbsp;&nbsp;
            <button type="button" class="btn btn-info exportToExcel">
                <i class="far fa-file-excel" aria-hidden="true"></i> Exporter en Csv
            </button>
            &nbsp;&nbsp;&nbsp;
            <a class="btn btn-info" href="/download/rapport/depots">
                <i class="far fa-file-pdf" aria-hidden="true"></i> Exporter en PDF
            </a>
        </div>   
        <br>
        <br>

        <div id="table" class="table-responsive">
            <div class="row">
                <div class="col-md-4">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th colspan="4">Total Opérations</th>
                            </tr>
                            <tr>
                                <th>Depots</th>
                                <th>Retraits</th>
                            </tr>
                        </thead>
                        <tbody>        
                            <tr>
                                <td>{{ $statNb['depot'] }}</td>
                                <td>{{ $statNb['retrait'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <br>
                <div class="col-md-4">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th colspan="4">Montant Opérations (XOF)</th>
                            </tr>
                            <tr>
                                <th>Depots</th>
                                <th>Retraits</th>
                            </tr>
                        </thead>
                        <tbody>        
                            <tr>
                                <td>{{ $statSum['depot'] }}</td>
                                <td>{{ $statSum['retrait'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <br>
                <div class="col-md-4">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th colspan="4">Total frais (XOF)</th>
                            </tr>
                            <tr>
                                <th>Depots</th>
                                <th>Retraits</th>
                            </tr>
                        </thead>
                        <tbody>        
                            <tr>
                                <td>{{ $statFrais['depot'] }}</td>
                                <td>{{ $statFrais['retrait'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width:15%">Date</th>
                        <th style="width:15%">Partenaire</th>
                        <th style="width:10%">Type</th>
                        <th style="width:10%">Montant</th>
                        <th style="width:10%">Frais</th>
                        <th style="width:15%">Reference BCC</th>
                        <th style="width:10%">Reference GTP</th>
                    </tr>
                </thead>
                <tbody>        
                @forelse($transactions as $item)
                <tr>
                    <td>{{ $item['date'] }}</td>
                    <td>{{ $item['partenaire']['libelle'] }}</td>
                    <td>{{ $item['type'] }}</td>
                    <td>{{ $item['montant'] }}</td>
                    <td>{{ $item['frais_bcb'] }}</td>
                    <td>{{ $item['reference_bcb'] }}</td>
                    <td>
                        @if (array_key_exists('reference_gtp', $item))
                            {{ $item['reference_gtp'] }}
                        @else
                            {{ $item['reference_gtp_debit'] }}
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10">Pas de données</td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>