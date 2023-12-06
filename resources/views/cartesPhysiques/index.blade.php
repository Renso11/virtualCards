@extends('base')
@section('css')
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endsection
@section('page')
    Cartes physiques disponibles
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="row">
            @foreach ($gammes as $item)
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box">
                        <div class="inner">
                            <h3>{{ count($item->cartesEnStock) }}</h3>

                            <p>{{ $item->libelle }}</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-credit-card"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="row">
            <div class="col-12">
                @if (hasPermission('carte.physiques.add'))
                    <button type="button" class="btn waves-effect waves-light btn-primary" data-toggle="modal" data-target="#add-gamme">Mise en stock</button>
                @endif
                <br>
                <br>
                <div class="card">
                    <div class="card-header border-0">
                    <h3 class="card-title">Liste des cartes</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped example1">
                            <thead>
                                <tr>
                                    <th>Gamme</th>
                                    <th>Serie</th>
                                    <th>CustomerID</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cartePhysiques as $item)
                                    <tr>
                                        <td>{{ $item->gamme->libelle }} </td>
                                        <td>{{ $item->serie }}</td>
                                        <td>{{ $item->code }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add-gamme" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
        <div class="modal-dialog  modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel1">Mise en stock de cartes</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form action="#" id="form-add-card-magasin" method="POST"> 
                    @csrf
                    <div class="modal-body">
                        <div class="card-body">
                            <input type="hidden" name="cartes" id="all-cartes">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="custom-control custom-radio">
                                      <input class="custom-control-input" type="radio" id="customRadio2" name="choose" value="max" checked>
                                      <label for="customRadio2" class="custom-control-label">Ajouter une liste de carte</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="custom-control custom-radio">
                                      <input class="custom-control-input" type="radio" id="customRadio1" name="choose" value="un">
                                      <label for="customRadio1" class="custom-control-label">Ajouter une seule carte</label>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="row"  id="div-form-stokage-masse">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <select class="form-control select2bs4" id="gamme-carte-magasin" style="width:100%">
                                            <option value="">Selectionner une gamme</option>
                                            @foreach ($gammes as $item)
                                                <option value="{{$item->id}}">{{$item->libelle}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="id-carte-debut" placeholder="ID de la premiere carte">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="num-debut" placeholder="N° debut série">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="num-fin" placeholder="N° fin série">
                                    </div>
                                </div>
                                <div class="col-md-6 offset-md-3">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-primary" id="add-carte-magasin" style="width:100%">Ajouter</button>
                                    </div>
                                </div>
                            </div> 
                            <div class="row" style="display: none" id="div-form-stokage-un">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <select class="form-control select2bs4" id="gamme-carte-magasin-un" style="width:100%">
                                            <option value="">Selectionner la gamme</option>
                                            @foreach ($gammes as $item)
                                                <option value="{{$item->id}}">{{$item->libelle}}</option>
                                            @endforeach 
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="serie-carte" placeholder="N° serie de la carte">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="id-carte" placeholder="Customer ID">
                                    </div>
                                </div>
                                <div class="col-md-6 offset-md-3">
                                    <button type="button" class="btn btn-primary" style="width: 100%" id="add-carte-magasin-un">Ajouter</button>
                                </div>
                            </div>                 
                            <br>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table id="tableau-list" class="table table-bordered table-hover">
                                            <thead> 
                                                <tr>
                                                    <th>Gamme de la carte</th>
                                                    <th>N° serie</th>
                                                    <th>Customer ID</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="ajout-card-liste">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" id="add-list-carte-magasin">Stocker</button>
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
    <script>
        var arr = [];

        $('#tableau-list').DataTable();
        
        $('#add-carte-magasin-un').on('click',function(e){
            var $this = $(this)
            $this.attr('disabled',true)
            $this.html('<i class="fa fa-spinner fa-spin"></i> Ajout en cours')

            var gamme = $( "#gamme-carte-magasin-un option:selected" ).text();
            var gammeid = $( "#gamme-carte-magasin-un option:selected" ).val();
            var serie = $('#serie-carte').val()
            var carte = $('#id-carte').val()
            var id = parseInt(carte)
            var long = id.toString().length
            var reste = 10 - long

            if(reste > 0){                            
                for (let a = 1; a <= reste; a++) {
                    id  = '0'+id.toString()
                } 
            }

            if(gammeid == "" || carte == "" || serie == ""){
                toastr.warning("Veuillez renseigner tous les champs")
                $this.attr('disabled',false)
                $this.html('Ajouter')
            }else{
                arr.push({
                    id : id,
                    gamme : gammeid,
                    serie : serie
                })

                $('#tableau-list').DataTable().destroy()
                $('#ajout-card-liste').prepend(`
                <tr>
                    <td>${gamme}</td>
                    <td>${serie}</td>
                    <td class="id">${id}</td>
                    <td>
                        <button type="button" class="btn btn-danger del-carte">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`)

                $("#gamme-carte-magasin-un").val("").change();
                $('#liste').show()
                $('#id-carte').val('')
                $('#serie-carte').val('')
                $('#tableau-list').DataTable();
                
                $("#tableau-list").unbind().on("click", ".del-carte", function(){
                    var idCarte = $(this).parent().parent().find('.id').html()
                    var x = arr.findIndex( search => search.id === idCarte);
                    arr.splice(x, 1);
                    $(this).parent().parent().hide()
                    $('#tableau-list').DataTable().destroy()
                    $('#tableau-list').DataTable();
                })  
                $this.attr('disabled',false)
                $this.html('Ajouter')
            }              
        })

        $('#add-carte-magasin').on('click',function(e){
            var $this = $(this)
            $this.attr('disabled',true)
            $this.html('<i class="fa fa-spinner fa-spin"></i> Ajout en cours')

            var gamme = $( "#gamme-carte-magasin option:selected" ).text();
            var gammeid = $( "#gamme-carte-magasin option:selected" ).val();
            var debut = $('#id-carte-debut').val()
            var ndebut = $('#num-debut').val()
            var nfin = $('#num-fin').val()

            if(gammeid == "" || debut == "" || ndebut == "" || nfin == ""){
                toastr.warning("Veuillez renseigner tous les champs")
                $this.html('Ajouter')
                $this.attr('disabled',false)
            }else{

                var nbCarte = parseInt(nfin)-parseInt(ndebut)

                if(nbCarte < 0){
                    toastr.warning("Vérifier l'ordre des numeros de serie de carte")
                    $this.html('Ajouter')
                    $this.attr('disabled',false)
                }else{
                    $('#tableau-list').DataTable().destroy()
                    for (let i = 0; i <= nbCarte; i++) {
                        var id = parseInt(parseInt(debut) + i)
                        var serie = parseInt(parseInt(ndebut) + i)
                        var long = id.toString().length
                        var reste = 10 - long
                        if(reste > 0){                            
                            for (let a = 1; a <= reste; a++) {
                                id  = '0'+id.toString()
                            } 
                        }  
                        arr.push({
                            id : id,
                            gamme : gammeid,
                            serie : serie
                        })

                        $('#no-carte').hide()
                        $('#ajout-card-liste').prepend(`
                        <tr>
                            <td>${gamme}</td>
                            <td>${serie}</td>
                            <td class="id">${id}</td>
                            <td>
                                <button type="button" class="btn btn-danger del-carte">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>`)
                    }
                    $('#tableau-list').DataTable()
                    
                    $("#gamme-carte-magasin").val("").change();
                    $('#id-carte-debut').val('')
                    $('#num-debut').val('')
                    $('#num-fin').val('')


                    $("#tableau-list").unbind().on("click", ".del-carte", function(){
                        var idCarte = $(this).parent().parent().find('.id').html()
                        var x = arr.findIndex( search => search.id === idCarte);
                        arr.splice(x, 1);
                        $(this).parent().parent().hide()
                        $('#tableau-list').DataTable().destroy()
                        $('#tableau-list').DataTable();
                    })  

                }
            }      
            $this.attr('disabled',false)
            $this.html('Ajouter')        
        })

        $('input[type=radio][name=choose]').change(function() {
            if (this.value == 'max') {
                $('#div-form-stokage-masse').show('slow');
                $('#div-form-stokage-un').hide();
                $("#gamme-carte-magasin-un").val("").change();
                $('#id-carte').val('')
                $('#serie-carte').val('')
                $('#all-cartes').val('');
            }
            else if (this.value == 'un') {
                $('#div-form-stokage-masse').hide();
                $('#div-form-stokage-un').show('slow');
                $('#all-cartes').val('');
                $("#gamme-carte-magasin").val("").change();
                $('#id-carte-debut').val('')
                $('#num-debut').val('')
                $('#num-fin').val('')
            }
        });

        $('#add-list-carte-magasin').on('click',function(e){
            e.preventDefault();
            var $this = $(this)
            $this.attr('disabled',true)
            $this.html('<i class="fa fa-spinner fa-spin"></i> Stockage en cours')
            if(arr.length == 0){    
                toastr.warning("Veuillez ajouter au moins une carte à la liste de carte")
                $this.attr('disabled',false)
                $this.html('Stocker')
            }else{
                $('#all-cartes').val(JSON.stringify(arr))
                var formData = new FormData($('#form-add-card-magasin')[0]);
                $.ajax({
                    url: "/cartes/physiques/add",
                    data: formData,
                    processData: false,
                    contentType: false,
                    method: "post",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(data){
                        if(data == "some_exist"){
                            toastr.warning("Stockage effectué. Certaines cartes dont les n° de série existe déjà dans la base n'ont pas été enregistré.")
                            $('#id-carte').val('')
                            $('#serie-carte').val('')
                            $('#all-cartes').val('');
                            $("#gamme-carte-magasin").val("").change();
                            $('#id-carte-debut').val('')
                            $('#num-debut').val('')
                            $('#num-fin').val('')
                            $("#gamme-carte-magasin-un").val("").change();
                            $('#tableau-list').DataTable().destroy()
                            $('#ajout-card-liste').html(``)
                            $('#tableau-list').DataTable();
                            $this.attr('disabled',false)
                            $this.html('Stocker')
                        }else if(data == 'error'){
                            toastr.error("Une erreur s'est produite")
                            $this.attr('disabled',false)
                            $this.html('Stocker')
                        }else{
                            toastr.success("Stockage effectué avec success.")
                            $this.parent().parent().find(".id-carte-client").val('')
                            $('#id-carte').val('')
                            $('#serie-carte').val('')
                            $('#all-cartes').val('');
                            $("#gamme-carte-magasin").val("").change();
                            $('#id-carte-debut').val('')
                            $('#num-debut').val('')
                            $('#num-fin').val('')
                            $("#gamme-carte-magasin-un").val("").change();
                            $('#tableau-list').DataTable().destroy()
                            $('#ajout-card-liste').html(``)
                            $('#tableau-list').DataTable();
                            $this.attr('disabled',false)
                            $this.html('Stocker')
                            arr = []
                        }
                        
                    }
                });
            }
        })
    </script>
@endsection