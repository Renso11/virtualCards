@extends('base')
@section('css')
@endsection
@section('page')
    Mon profile
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Informations</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <strong><i class="fas fa-user mr-1"></i> Nom et prénoms</strong>

                        <p class="text-muted">
                            {{ $user->name . ' ' . $user->lastname }}
                        </p>

                        <hr>

                        <strong><i class="fas fa-at mr-1"></i> Username</strong>

                        <p class="text-muted">{{ $user->username }}</p>

                        <hr>

                        <strong><i class="fas fa-lock mr-1"></i> Derniere connexion</strong>

                        <p class="text-muted">
                            <span class="tag tag-danger">{{ $user->lastconnexion }}</span>
                        </p>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#info">Informations</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#pass">Mot de passe</a>
                            </li>
                        </ul>
                    </div><!-- /.card-header -->
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="active tab-pane" id="info">
                                <div class="card-body table-responsive">
                                    <form action="/profile/informations/edit" method="POST" class="form-horizontal form-material">
                                        @csrf
                                        <div class="form-group">
                                            <label class="col-md-12">Nom</label>
                                            <div class="col-md-12">
                                                <input type="text" placeholder="Nom" name="name" value="{{ $user->name }}" class="form-control form-control-line">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-12">Prenom</label>
                                            <div class="col-md-12">
                                                <input type="text" placeholder="Prenom" name="lastname" value="{{ $user->lastname }}" class="form-control form-control-line">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="example-email" class="col-md-12">Username</label>
                                            <div class="col-md-12">
                                                <input type="text" placeholder="Username" name="username" value="{{ $user->username }}" class="form-control form-control-line"  id="example-email">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <button class="btn btn-success">Modifier</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- /.tab-pane -->
                            <div class="tab-pane" id="pass">
                                <div class="card-body table-responsive">
                                    <form action="/profile/password/change" id="form-change-password" method="POST" class="form-horizontal form-material">
                                        @csrf
                                        <div class="form-group">
                                            <label class="col-md-12">Nouveau mot de passe</label>
                                            <div class="col-md-12">
                                                <input type="password" id="password" name="password" placeholder="Nouveau mot de passe" class="form-control form-control-line">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="example-email" class="col-md-12">Confirmer le mot de passe</label>
                                            <div class="col-md-12">
                                                <input type="password" id="conf-password" placeholder="Confirmer le mot de passe" class="form-control form-control-line" name="example-email" id="example-email">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <button type="button" class="btn btn-success" id="change-password">Modifier</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div><!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
        </div>
    </div>
</section>
@endsection
@section('js')
    <script>
        $('#change-password').on('click',function(e) {
            e.preventDefault()
            if($('#password').val() !== $('#conf-password').val()){
                toastr.warning("Les deux mots de passe ne correspondent pas")
            }else{
                $('#form-change-password').submit()
            }
        })
    </script>
@endsection
