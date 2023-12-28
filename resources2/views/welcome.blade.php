@extends('base')
@section('css')
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <link href="/assets/libs/chartist/dist/chartist.min.css" rel="stylesheet">
    <link href="/dist/js/pages/chartist/chartist-init.css" rel="stylesheet">
    <style>
        .img-flag {
            width: 25px;
            height: 12px;
            margin-top: -4px;
        }
    </style>
@endsection
@section('page')
    Accueil
@endsection
@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-default">
                        <div class="card-header border-0">
                            <h3 class="card-title">Solde GTP</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <i class="fas fa-wallet fa-2x"></i>
                                <h3 style="margin-left: 3%">{{ $balance }} XOF</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-default">
                        <div class="card-header border-0">
                            <h3 class="card-title">Monnaie en circulation</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <i class="fas fa-wallet fa-2x"></i>
                                <h3 style="margin-left: 3%">{{ $sommeDistribution }} XOF</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $nbClients }}</h3>

                            <p>Client(s)</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-users"></i>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $nbPartenaires }}</h3>

                            <p>Partenaire(s)</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-home"></i>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $totalDepots }}</h3>
                            <p>Total depots</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-plus"></i>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $totalRetraits }}</h3>

                            <p>Total retraits</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-minus"></i>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
            </div>
            <!-- /.row -->
            <!-- Main row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header border-0">
                            <div class="d-flex justify-content-between">
                                <h3 class="card-title">Evolution des operations par type les 6 derniers mois</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <p class="d-flex flex-column">
                                        <span style="width: 15px;height:15px;background-color:#56C128"></span>
                                        <span>Depot(s)</span>
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <p class="d-flex flex-column">
                                        <span style="width: 15px;height:15px;background-color:#ED1428"></span>
                                        <span>Retrait(s)</span>
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <p class="d-flex flex-column">
                                        <span style="width: 15px;height:15px;background-color:#3353CB"></span>
                                        <span>Transfert(s)</span>
                                    </p>
                                </div>
                            </div>

                            <div class="position-relative mb-4">
                                <canvas id="visitors-chart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                      <div class="card-header border-0">
                        <h3 class="card-title">Les 5 derniers retraits</h3>
                      </div>
                      <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped example1">
                          <thead>
                          <tr>
                            <th>Date</th>
                            <th>Telephone</th>
                            <th>Montant</th>
                            <th>Status</th>
                          </tr>
                          </thead>
                          <tbody>
                            @foreach ($lastRetraits as $item)
                                <tr>
                                    <td>{{ $item->created_at->format('d-M-Y') }}</td>
                                    <td>{{ $item->userClient->username }}</td>
                                    <td>{{ $item->montant }} F CFA</td>
                                    <td>
                                        @if (!isset($item->status))
                                            <span class="label label-danger">Annuler</span>
                                        @else
                                            @if ($item->status == 0)
                                                <span class="label label-warning">En cours</span>
                                            @else
                                                <span class="label label-success">Effectue</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                      <div class="card-header border-0">
                        <h3 class="card-title">Les 5 derniers depots</h3>
                      </div>
                      <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped example1">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Telephone</th>
                                    <th>Montant</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lastDepots as $item)
                                    <tr>
                                        <td>{{ $item->created_at->format('d-M-Y') }}</td>
                                        <td>{{ $item->userClient->username }}</td>
                                        <td>{{ $item->montant }} F CFA</td>
                                        <td>
                                            @if (!isset($item->status))
                                                <span class="label label-danger">Annuler</span>
                                            @else
                                                @if ($item->status == 0)
                                                    <span class="label label-warning">En cours</span>
                                                @else
                                                    <span class="label label-success">Effectue</span>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                          </tbody>
                        </table>
                      </div>
                    </div>
                </div>
            </div>
            <!-- /.row (main row) -->
        </div><!-- /.container-fluid -->
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
    <script>

        $(function () {
        var ticksStyle = {
            fontColor: '#495057',
            fontStyle: 'bold'
        }

        var mode = 'index'
        var intersect = true

        var $visitorsChart = $('#visitors-chart')
        // eslint-disable-next-line no-unused-vars
        var visitorsChart = new Chart($visitorsChart, {
            data: {
            labels: ['{{ $months[5] }}', '{{ $months[4] }}', '{{ $months[3] }}', '{{ $months[2] }}', '{{ $months[1] }}', '{{ $months[0] }}'],
            datasets: [{
                type: 'line',
                data: [{{ $depots[5] }}, {{ $depots[4] }}, {{ $depots[3] }}, {{ $depots[2] }}, {{ $depots[1] }}, {{ $depots[0] }}],
                backgroundColor: 'transparent',
                borderColor: '#56C128',
                pointBorderColor: '#56C128',
                pointBackgroundColor: '#56C128',
                fill: false
            },
            {
                type: 'line',
                data: [{{ $retraits[5] }}, {{ $retraits[4] }}, {{ $retraits[3] }}, {{ $retraits[2] }}, {{ $retraits[1] }}, {{ $retraits[0] }}],
                backgroundColor: 'tansparent',
                borderColor: '#ED1428',
                pointBorderColor: '#ED1428',
                pointBackgroundColor: '#ED1428',
                fill: false
            },
            {
                type: 'line',
                data: [{{ $transferts[5] }}, {{ $transferts[4] }}, {{ $transferts[3] }}, {{ $transferts[2] }}, {{ $transferts[1] }}, {{ $transferts[0] }}],
                backgroundColor: 'tansparent',
                borderColor: '#3353CB',
                pointBorderColor: '#3353CB',
                pointBackgroundColor: '#3353CB',
                fill: false
            }]
            },
            options: {
            maintainAspectRatio: false,
            tooltips: {
                mode: mode,
                intersect: intersect
            },
            hover: {
                mode: mode,
                intersect: intersect
            },
            legend: {
                display: false
            },
            scales: {
                yAxes: [{
                // display: false,
                gridLines: {
                    display: true,
                    lineWidth: '4px',
                    color: 'rgba(0, 0, 0, .2)',
                    zeroLineColor: 'transparent'
                },
                ticks: $.extend({
                    beginAtZero: true,
                    suggestedMax: 50
                }, ticksStyle)
                }],
                xAxes: [{
                display: true,
                gridLines: {
                    display: false
                },
                ticks: ticksStyle
                }]
            }
            }
        })
        })

        // lgtm [js/unused-local-variable]
    </script>
@endsection
