@extends('base')
@section('css')
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endsection
@section('page')
    Rapport des transferts
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Remplissez le filtre</h5>
                    </div>
                    <form id="form-search">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 col-lg-6">
                                    <div class="form-group">
                                        <label for="">Clients</label>
                                        <select class="form-control select2bs4" multiple id="client" name="clients[]" style="width:100%">
                                            @foreach ($users as $item)
                                                <option value="{{$item->id}}">{{$item->name.' '.$item->lastname}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-6">
                                    <div class="form-group">
                                        <label for="">Periode</label>
                                        <input type="text" class="form-control daterange" id="periode" name="periode" placeholder="">
                                    </div>
                                </div>
                                <div class="offset-md-4 col-md-4">
                                    <button id="btn-search" style="width: 100%" class="btn btn-info"><i class="fa fa-search"></i> Rechercher</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-center" style="margin-top: 3%" id="resultat">
                                    
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="/plugins/select2/js/select2.full.min.js"></script>
    <script>
        
    //Initialize Select2 Elements
        $('.select2').select2()

        //Initialize Select2 Elements
        $('.select2bs4').select2({
        theme: 'bootstrap4'
        })

        $('.daterange').daterangepicker();


        
        $('#btn-search').on('click',function(e) {
            e.preventDefault()
            var $this = $(this)
            $this.prop('disabled',true)
            $('#resultat').hide()
            $this.html('<i class="fa fa-spinner fa-spin"></i> Recherche...')
            var formData = new FormData($('#form-search')[0]);
            var client  = $('#client').val();
            var periode = $('#periode').val();

            if(periode == ""){
                alert("Veuillez renseigner la fin de la periode de recherche")
            }else{   
                $.ajax({
                    url: "/search/transferts",
                    data: formData,
                    processData: false,
                    contentType: false,
                    method: "post",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(data){
                        $('#resultat').html(data)
                        $('#resultat').show()
                        $this.html(`<i class="fa fa-search"></i> Rechercher</button>`)
                        $this.prop('disabled',false)
                        
                        
                        $(".exportToExcel").click(function (e) {
                            var table = $('#table');
                            if (table && table.length) {
                                $(table).table2excel({
                                    exclude: ".noExl",
                                    name: "Rapport des transferts du " + periode,
                                    filename: "rapport des transferts du " + periode + ".xls",
                                    fileext: ".xls",
                                    exclude_img: true,
                                    exclude_links: true,
                                    exclude_inputs: true,
                                    preserveColors: false
                                });
                            }
                        });
                    }
                }); 
            }
        })
    </script>
@endsection