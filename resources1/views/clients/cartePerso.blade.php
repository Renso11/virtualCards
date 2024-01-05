@extends('base')
@section('css')
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endsection
@section('page')
    Cartes personnalisées
@endsection
@section('content')
<section class="content">  
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Activation de cartes personnalisées</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-control custom-radio">
                                    <input class="custom-control-input" type="radio" id="customRadio1" name="choose" value="max" checked>
                                    <label for="customRadio1" class="custom-control-label">Activer pour une liste de personne</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-radio">
                                    <input class="custom-control-input" type="radio" id="customRadio2" name="choose" value="un">
                                    <label for="customRadio2" class="custom-control-label">Activer pour une personne</label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div id="max">
                            <form action="/add/carte/perso/multi" method="post" id="form-carte-perso-multi" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="customRadio1">Fichier Excel de la liste</label>
                                        <input class="form-control" type="file" accept=".xls,.xlsx,.csv" name="file" placeholder="Cliquer pour choisir" />
                                    </div>
                                </div>
                                <br>
                                <div class="text-right">
                                    <button type="submit" class="btn btn-style btn-primary">Initier</button>
                                </div>
                            </form>
                        </div>
                        <div id="un" style="display: none">
                            <form action="/add/carte/perso/unique" method="post" id="form-carte-perso-unique" enctype="multipart/form-data">
                                <div class="row">
                                    @csrf
                                    <div class="col-md-6 form-group">
                                        <label for="customRadio1">Nom</label>
                                        <input class="form-control" type="text" name="nom" id="form-unique-nom" placeholder="Nom" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="customRadio1">Prénoms</label>
                                        <input class="form-control" type="text" name="prenom" id="form-unique-prenom" placeholder="Prénoms" required>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="customRadio1">Adresse mail</label>
                                        <input class="form-control" type="email" name="email" id="form-unique-email" placeholder="Adresse mail" required="">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="customRadio1">Telephone</label>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <select class="form-control select2bs4" name="code" id="form-unique-codepays" required  data-placeholder="Selectionner le pays">
                                                    <option value=""></option>
                                                    @foreach ($countries as $key => $item)
                                                        <option value="{{ $item['code'] }}">+{{ $item['code'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-9">
                                                <input class="form-control" type="number" name="tel" id="form-unique-telephone" placeholder="Telephone" required="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="customRadio1">Naissance</label>
                                        <input class="form-control" required type="date" id="form-unique-naissance" name="naissance" placeholder="Cliquer pour choisir" />
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="customRadio1">Pays</label>
                                        <select id="form-unique-pays" required name="pays" class="form-control select2bs4" style="width:100%">
                                            <option selected="selected" value="">Sélectionnez un pays...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="customRadio1">Departement</label>
                                        <select id="form-unique-departement" disabled required name="dep" class="form-control select2bs4" style="width:100%">
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="customRadio1">Ville</label>
                                        <input type="text" class="form-control" name="ville" id="form-unique-ville" placeholder="Ville" required="">
                                    </div>
                                    <div class="col-md-12 form-group">
                                        <label for="customRadio1">Adresse complete</label>
                                        <input type="text" class="form-control" name="adresse" id="form-unique-adresse" placeholder="Adresse complete" required="">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="customRadio1">Profession</label>
                                        <input type="text" class="form-control" name="prof" id="form-unique-profession" placeholder="Profession" required="">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="customRadio1">Revenu</label>
                                        <select class="form-control select2bs4" name="revenu" required id="form-unique-revenu" style="width:100%">
                                            <option selected="selected" value="">Sélectionnez le revenu..</option>
                                            <option value="< 150 000"> < 150 000 </option>
                                            <option value="< 500 000"> < 500 000 </option>
                                            <option value="> 500 000"> > 500 000 </option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="customRadio1">Type de piece</label>
                                        <select required class="form-control select2bs4"  name="piece_type" id="form-unique-typepiece" style="width:100%">
                                            <option selected="selected" value="">Sélectionnez le type de piece</option>
                                            <option value="1">Passeport</option>
                                            <option value="2">CNI</option>
                                            <option value="3">Permis de conduire</option>
                                            <option value="4">Autres</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="customRadio1">Numero de piece</label>
                                        <input required class="form-control" type="text"  id="form-unique-numeropiece" name="numpiece" placeholder="ID de la pièce">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="customRadio1">Image de la piece</label>
                                        <input  class="form-control" type="file" accept=".pdf,image/png, image/jpeg" name="piece" placeholder="Cliquer pour choisir" />
                                    </div>
                                </div>
                                <div class="text-right">
                                    <button type="button" id="btn-carte-perso-unique" class="btn btn-style btn-primary">Initier</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-0">
                    <h3 class="card-title">Commande de cartes personnalisées</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped" id="example1">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Nom et prénoms</th>
                                    <th>CustomerID</th>
                                    <th>4 dernier</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cartes as $item)
                                    <tr>
                                        <td>{{ $item->created_at }}</td>
                                        <td>{{ $item->kycClient->lastname.' '.$item->kycClient->name }}</td>
                                        <td>{{ $item->code }}</td>
                                        <td>{{ $item->last }}</td>
                                        @if (hasPermission('client.edit') || hasPermission('client.details') || hasPermission('client.delete')|| hasPermission('client.activation')|| hasPermission('client.desactivation')|| hasPermission('client.reset.password'))
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#show-client-{{ $item->id }}"><i class="fa fa-eye"></i> Voir le KYC</a>
                                                    </div>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                    <div class="modal fade" id="show-client-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="exampleModalLabel1">KYC de {{ $item->kycClient->lastname.' '.$item->kycClient->name }}</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <form action="/client/edit/{{ $item->id }}" method="POST"> 
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-8">
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
                                                                <label for="">Pays</label>
                                                                <p class="text-capitalize">{{ $item->kycClient->country }}</p>
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
                                                                <label for="">Revenu</label>
                                                                <p>{{ $item->kycClient->revenu }}</p>
                                                            </div>
                                                            <div class="col-md-8">
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
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Annuler</button>
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

    
    <div class="modal fade" id="modal-resume-carte-perso-unique" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel1">Verifier les informations pour activer la carte</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="customRadio1">Nom</label>
                            <br>
                            <span id="show-unique-nom"></span>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="customRadio1">Prénoms</label>
                            <br>
                            <span id="show-unique-prenom"></span>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="customRadio1">Adresse mail</label>
                            <br>
                            <span id="show-unique-email"></span>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="customRadio1">Telephone</label>
                            <br>
                            <span id="show-unique-telephone"></span>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="customRadio1">Naissance</label>
                            <br>
                            <span id="show-unique-naissance"></span>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="customRadio1">Departement</label>
                            <br>
                            <span id="show-unique-departement"></span>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="customRadio1">Ville</label>
                            <br>
                            <span id="show-unique-ville"></span>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="customRadio1">Adresse complete</label>
                            <br>
                            <span id="show-unique-adresse"></span>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="customRadio1">Profession</label>
                            <br>
                            <span id="show-unique-profession"></span>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="customRadio1">Revenu</label>
                            <br>
                            <span id="show-unique-revenu"></span>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="customRadio1">Type de piece</label>
                            <br>
                            <span id="show-unique-typepiece"></span>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="customRadio1">Numero de piece</label>
                            <br>
                            <span id="show-unique-numeropiece"></span>
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Annuler</button>
                    <button type="button" id="submit-carte-perso-unique" class="btn btn-primary">Activer</button>
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
        $("#example1").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "order": [0,'desc'],
            "buttons": ["copy", "csv", "excel", "pdf"]
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
        
        $('input[type=radio][name=choose]').change(function() {
            if (this.value == 'max') {
                $('#max').show('slow');
                $('#un').hide();
            }
            else if (this.value == 'un') {
                $('#max').hide();
                $('#un').show('slow');
                //$('#all-cartes').val('');
            }
        });
        
        $('#btn-carte-perso-unique').on('click',function(e){
            var $this = $(this);
            $this.attr('disabled',true);
            $this.html('<i class="fa fa-spinner fa-spin"></i> Initiation en cours');
            if($('#form-unique-nom').val() == ''){
                $this.attr('disabled',false)
                $this.html('Initier');
                toastr.warning('Renseignez le champs nom');
            }else if($('#form-unique-prenom').val() == ''){
                $this.attr('disabled',false)
                $this.html('Initier')
                toastr.warning('Renseignez le champs prenom');
            }else if($('#form-unique-email').val() == ''){
                $this.attr('disabled',false)
                $this.html('Initier')
                toastr.warning('Renseignez le champs email');
            }else if($('#form-unique-codepays').val() == ''){
                $this.attr('disabled',false)
                $this.html('Initier')
                toastr.warning('Renseignez le champs codepays');
            }else if($('#form-unique-telephone').val() == ''){
                $this.attr('disabled',false)
                $this.html('Initier')
                toastr.warning('Renseignez le champs telephone');
            }else if($('#form-unique-naissance').val() == ''){
                $this.attr('disabled',false)
                $this.html('Initier')
                toastr.warning('Renseignez le champs naissance');
            }else if($('#form-unique-departement').val() == ''){
                $this.attr('disabled',false)
                $this.html('Initier')
                toastr.warning('Renseignez le champs departement');
            }else if($('#form-unique-ville').val() == ''){
                $this.attr('disabled',false)
                $this.html('Initier')
                toastr.warning('Renseignez le champs ville');
            }else if($('#form-unique-adresse').val() == ''){
                $this.attr('disabled',false)
                $this.html('Initier')
                toastr.warning('Renseignez le champs adresse');
            }else if($('#form-unique-profession').val() == ''){
                $this.attr('disabled',false)
                $this.html('Initier')
                toastr.warning('Renseignez le champs profession');
            }else if($('#form-unique-revenu').val() == ''){
                $this.attr('disabled',false)
                $this.html('Initier')
                toastr.warning('Renseignez le champs revenu');
            }else if($('#form-unique-typepiece').val() == ''){
                $this.attr('disabled',false)
                $this.html('Initier')
                toastr.warning('Renseignez le champs type de piece');
            }else if($('#form-unique-numeropiece').val() == ''){
                $this.attr('disabled',false)
                $this.html('Initier')
                toastr.warning('Renseignez le champs numero de piece');
            }else {
                $('#show-unique-nom').html($('#form-unique-nom').val())
                $('#show-unique-prenom').html($('#form-unique-prenom').val())
                $('#show-unique-email').html($('#form-unique-email').val())
                $('#show-unique-telephone').html($('#form-unique-codepays').val()+' '+$('#form-unique-telephone').val())
                $('#show-unique-naissance').html($('#form-unique-naissance').val())
                $('#show-unique-departement').html($('#form-unique-departement  option:selected').text())
                $('#show-unique-ville').html($('#form-unique-ville').val())
                $('#show-unique-adresse').html($('#form-unique-adresse').val())
                $('#show-unique-profession').html($('#form-unique-profession').val())
                $('#show-unique-revenu').html($('#form-unique-revenu').val())
                $('#show-unique-typepiece').html($('#form-unique-typepiece  option:selected').text())
                $('#show-unique-numeropiece').html($('#form-unique-numeropiece').val())
                $('#modal-resume-carte-perso-unique').modal('show');
                $this.attr('disabled',false)
                $this.html('Initier')
            }
        })

        $('#submit-carte-perso-unique').on('click',function(e){
            e.preventDefault()
            var $this = $(this);
            $this.attr('disabled',true);
            $this.html('<i class="fa fa-spinner fa-spin"></i> Activation en cours');
            $("#form-carte-perso-unique").submit();
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

        $('#form-unique-pays').on('change',function name(params) {
            $('#form-unique-departement').prop('disabled',false);
            var states = JSON.parse($('#form-unique-pays :selected').attr('data-state'));

            var tr = ''

            states.forEach(element => {
                 tr = tr + `<option selected="selected" value="${element.state_code}">${element.name}</option>`
            });

            $('#form-unique-departement').html(tr)
            $('#form-unique-departement').prepend(`<option selected="selected" value="">Sélectionnez un departement...</option>`)
            
            $('#form-unique-departement').select2({
                theme: 'bootstrap4'
            })
        })    
    </script>
@endsection