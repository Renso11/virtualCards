@extends('base')
@section('css')
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endsection
@section('page')
    Liste des roles
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <button type="button" class="btn waves-effect waves-light btn-primary" data-toggle="modal" data-target="#add-role">Ajouter des roles</button>
                <br>
                <br>
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">Liste des roles</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped example1">
                            <thead>
                                <tr>
                                    <th>Role</th>
                                    <th>Permmisions</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $item)
                                    <tr>
                                        <td>{{ $item->libelle }}</td>
                                        <td>
                                            @foreach ($item->rolePermissions as $value)
                                                <span class="badge" style="background: rgb(192, 188, 188)">{{ $value->permission ? $value->permission->libelle : 'oko' }}</span>
                                            @endforeach
                                        </td>
                                        @if (hasPermission('roles.edit') || hasPermission('roles.delete'))
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        @if (hasPermission('roles.edit'))
                                                            <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#edit-role-{{ $item->id }}"><i class="fa fa-edit"></i>&nbsp;&nbsp;Modifier les informations</a>
                                                        @endif
                                                        @if (hasPermission('roles.delete'))
                                                            <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#del-role-{{ $item->id }}"><i class="fa fa-trash"></i>&nbsp;&nbsp;Supprimer le role</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>

                                    <div class="modal fade" id="edit-role-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="exampleModalLabel1">Modification du role {{ $item->libelle }}</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <form action="/roles/edit/{{ $item->id }}" method="POST" id="form-edit-{{ $item->id }}"> 
                                                    @csrf
                                                    <input type="hidden" name="permissions" id="edit-liste-permissions-{{ $item->id }}">
                                                    <div class="modal-body">
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label for="">Libellé</label>
                                                                        <input type="text" class="form-control" id="libelle-{{ $item->id }}" value="{{ $item->libelle }}" name="libelle" placeholder="Libellé du role">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="div-permissions">
                                                                <div class="form-group">
                                                                    <label for="">Permissions</label>
                                                                </div>
                                                                <div class="row">
                                                                    @foreach ($permissions as $value)
                                                                        <div class="col-md-6 div-check-permissions">
                                                                            <label for=""><input type="checkbox" @if(in_array($value->id,$item->rolePermissions->pluck('permission_id')->all())) checked @endif class="check-permissions-{{ $item->id }}" value="{{ $value->id }}"> {{ $value->libelle }} </label>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Annuler</button>
                                                        <button type="button" class="btn btn-primary edit-role" data-id="{{ $item->id }}">Modifier</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="del-role-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="exampleModalLabel1">Suppression du role {{ $item->libelle }} </h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <form action="/roles/delete/{{ $item->id }}" method="POST"> 
                                                    @csrf
                                                    <div class="modal-body">
                                                        <p>Cela implique que tous les porteurs de ce role ne pourront plus utilisé l'application. <br> Etes vous sur de supprimer ce role ?</p>
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

    <div class="modal fade" id="add-role" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel1">Definition des roles</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form action="/roles/add" id="form-add" method="POST"> 
                    @csrf
                    <input type="hidden" name="permissions" id="add-liste-permissions">
                    <div class="modal-body">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="">Libellé</label>
                                        <input type="text" class="form-control" id="libelle" name="libelle" placeholder="Libellé du role">
                                    </div>
                                </div>
                            </div>
                            <div class="div-permissions">
                                <div class="form-group">
                                    <label for="">Permissions</label>
                                    <br>
                                    <label for=""><input type="checkbox" id="check-all" value="all"> Tout selectionner </label>
                                    <br>
                                </div>
                                <div class="row">
                                    @foreach ($permissions as $value)
                                        <div class="col-md-6 div-check-permissions">
                                            <label for=""><input type="checkbox" class="check-permissions" value="{{ $value->id }}"> {{ $value->libelle }} </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-primary" id="add-btn-role">Enregistrer</button>
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
        $('#add-btn-role').on('click',function(e){
            e.preventDefault()
            var search =  $('.check-permissions:checkbox:checked').map(function(){
                return $(this).val();
            }).get(); 
            if($('#libelle').val() == ''){
                toastr.warning("Renseigner le libelle")
            }else if(search.length == 0){
                toastr.warning("Choisisser au moins une permissions")
            }else{
                $('#add-liste-permissions').val(JSON.stringify(search))
                $('#form-add').submit()
            }
        })
            
        $('.edit-role').on('click',function(e){
            e.preventDefault()
            var id = $(this).attr('data-id')
            var search =  $('.check-permissions-'+id+':checkbox:checked').map(function(){
                return $(this).val();
            }).get();
            if($('#libelle-'+id).val() == ''){
                toastr.warning("Renseigner le libelle")
            }else if(search.length == 0){
                toastr.warning("Choisisser au moins une permissions")
            }else{
                $('#edit-liste-permissions-'+id).val(JSON.stringify(search))
                $('#form-edit-'+id).submit()
            }
        })
            
        $('#check-all').on('change',function(e){
            e.preventDefault()
            if($(this).is(":checked") == true){
                $(".check-permissions").prop("checked", true );
            }else{
                $(".check-permissions").prop("checked", false );
            }
        })

    </script>
@endsection