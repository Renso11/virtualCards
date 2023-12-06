<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BCB Mobile Admin</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="/plugins/fontawesome-free/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="/dist/css/adminlte.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="/plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="{{ asset('toastr/toastr.min.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" type="image/x-icon" href="/dist/img/bcb.png">
    @yield('css')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="/dist/img/bcb.png" alt="bcb" height="100"
                width="100">
        </div>

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar main-sidebar elevation-4 sidebar-light-warning elevation-4">
            <!-- Brand Logo -->
            <a href="/" class="brand-link">
                <img src="/dist/img/bcb.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
                    style="opacity: .8">
                <span class="brand-text font-weight-light">BcbVirtuelle</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="info">
                        <a href="#" class="d-block">{{ Auth::user()->name . ' ' . Auth::user()->lastname }}</a>
                    </div>
                </div>

                <!-- SidebarSearch Form -->
                <div class="form-inline">
                    <div class="input-group" data-widget="sidebar-search">
                        <input class="form-control form-control-sidebar" type="search" placeholder="Search"
                            aria-label="Search">
                        <div class="input-group-append">
                            <button class="btn btn-sidebar">
                                <i class="fas fa-search fa-fw"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                        <li class="nav-item">
                            <a href="/" class="nav-link @if (Route::currentRouteName() == 'welcome') active @endif">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>
                                    Tableau de bord
                                </p>
                            </a>
                        </li>

                        @if (hasPermission('roles') || hasPermission('frais') || hasPermission('commissions') || hasPermission('restrictions') || hasPermission('gammes') || hasPermission('carte.physiques'))
                            <li class="nav-header">Parametres</li>
                        @endif
                        @if (hasPermission('roles'))
                            <li class="nav-item">
                                <a href="/params/generales" class="nav-link @if (Route::currentRouteName() == 'generales') active @endif">
                                    <i class="nav-icon fas fa-cog"></i>
                                    <p>
                                        Générales
                                    </p>
                                </a>
                            </li>
                        @endif
                        @if (hasPermission('roles'))
                            <li class="nav-item">
                                <a href="/roles" class="nav-link @if (Route::currentRouteName() == 'roles') active @endif">
                                    <i class="nav-icon fas fa-registered"></i>
                                    <p>
                                        Roles
                                    </p>
                                </a>
                            </li>
                        @endif
                        @if (hasPermission('frais'))
                            <li class="nav-item">
                                <a href="/frais" class="nav-link @if (Route::currentRouteName() == 'frais') active @endif">
                                    <i class="nav-icon fas fa-cog"></i>
                                    <p>
                                        Frais et commissions
                                    </p>
                                </a>
                            </li>
                        @endif
                        @if (hasPermission('restrictions'))
                            <li class="nav-item">
                                <a href="/restrictions" class="nav-link @if (Route::currentRouteName() == 'restrictions') active @endif">
                                    <i class="nav-icon fas fa-exclamation-circle"></i>
                                    <p>
                                        Restrictions
                                    </p>
                                </a>
                            </li>
                        @endif
                        @if (hasPermission('users'))
                            <li class="nav-item">
                                <a href="/users" class="nav-link @if (Route::currentRouteName() == 'users') active @endif">
                                    <i class="nav-icon fas fa-users-cog"></i>
                                    <p>
                                        Utilisateurs
                                    </p>
                                </a>
                            </li>
                        @endif
                        @if (hasPermission('clients') || hasPermission('clients.attentes') || hasPermission('partenaires') || hasPermission('users') || hasPermission('rapport'))
                            <li class="nav-header">Exploitation</li>
                        @endif
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-briefcase"></i>
                                <p>
                                    Comptes
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="/commissions/elg" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            Compte commission ELG
                                        </p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/commissions/uba" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            Compte commission UBA
                                        </p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/commissions/partenaires" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            Compte partenaires
                                        </p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @if (hasPermission('clients') || hasPermission('clients.attentes'))
                            <li class="nav-item @if (in_array(Route::currentRouteName(), ['clients.attentes', 'clients', 'rechargements.client'])) menu-open @endif">
                                <a href="#" class="nav-link @if (in_array(Route::currentRouteName(), ['clients.attentes', 'clients', 'rechargements.client'])) active @endif">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>
                                        Clients
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    @if (hasPermission('clients'))
                                        <li class="nav-item">
                                            <a href="/clients" class="nav-link @if (Route::currentRouteName() == 'clients') active @endif">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>
                                                    Compte validé
                                                </p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (hasPermission('clients.attentes'))
                                        <li class="nav-item">
                                            <a href="/clients/attentes" class="nav-link @if (Route::currentRouteName() == 'clients.attentes') active @endif">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>
                                                    Compte en attente
                                                </p>
                                            </a>
                                        </li>
                                    @endif
                                    <li class="nav-item">
                                        <a href="/rechargements/clients" class="nav-link @if (Route::currentRouteName() == 'rechargements.client') active @endif">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Rechargement compte</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        @if (hasPermission('partenaires') || hasPermission('partenaire.recharge.attentes') || hasPermission('partenaire.vente.attentes'))
                            <li class="nav-item @if (in_array(Route::currentRouteName(), ['partenaires', 'partenaire.recharge.attentes', 'partenaire.vente.attentes'])) menu-open @endif">
                                <a href="#" class="nav-link @if (in_array(Route::currentRouteName(), ['partenaires', 'partenaire.recharge.attentes', 'partenaire.vente.attentes'])) active @endif">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>
                                        Partenaires
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="/partenaires" class="nav-link @if (Route::currentRouteName() == 'partenaires') active @endif">
                                            <i class="nav-icon far fa-circle"></i>
                                            <p>
                                                Liste
                                            </p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/partenaire/recharges/attentes" class="nav-link @if (Route::currentRouteName() == 'partenaire.recharge.attentes') active @endif">
                                            <i class="nav-icon far fa-circle"></i>
                                            <p>
                                                Validation rechargemts
                                            </p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/partenaire/ventes/attentes" class="nav-link @if (Route::currentRouteName() == 'partenaire.vente.attentes') active @endif">
                                            <i class="nav-icon far fa-circle"></i>
                                            <p>
                                                Validation ventes
                                            </p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        @if (hasPermission('partenaires') || hasPermission('partenaire.api') || hasPermission('partenaire.api.fees'))
                            <li class="nav-item @if (in_array(Route::currentRouteName(), ['partenaires.api', 'partenaires.api.fees'])) menu-open @endif">
                                <a href="#" class="nav-link @if (in_array(Route::currentRouteName(), ['partenaires.api', 'partenaire.api.fees'])) active @endif">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>
                                        Partenaires via API
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="/partenaires/api" class="nav-link @if (Route::currentRouteName() == 'partenaires.api') active @endif">
                                            <i class="nav-icon far fa-circle"></i>
                                            <p>
                                                Liste
                                            </p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/partenaires/api/fees" class="nav-link @if (Route::currentRouteName() == 'partenaire.api.fees') active @endif">
                                            <i class="nav-icon far fa-circle"></i>
                                            <p>
                                                Configuration des frais
                                            </p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/partenaires/api/recharge/attentes" class="nav-link @if (Route::currentRouteName() == 'partenaire.api.fees') active @endif">
                                            <i class="nav-icon far fa-circle"></i>
                                            <p>
                                                Appro en attente
                                            </p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/partenaires/api/transactions" class="nav-link @if (Route::currentRouteName() == 'partenaire.api.fees') active @endif">
                                            <i class="nav-icon far fa-circle"></i>
                                            <p>
                                                Transactions
                                            </p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        <!--li class="nav-item">
                            <a href="/carte/perso" class="nav-link">
                                <i class="far fa-credit-card nav-icon"></i>
                                <p>Cmde carte perso</p>
                            </a>
                        </li>
                        <li-- class="nav-item">
                            <a href="/carte/perso" class="nav-link">
                                <i class="far fa-credit-card nav-icon"></i>
                                <p>Compte UBA</p>
                            </a>
                        </li-->


                        <!--@if (hasPermission('vente.physiques.attentes') || hasPermission('vente.physiques.finalises') || hasPermission('vente.physiques.rejetes'))
                            <li class="nav-item @if (in_array(Route::currentRouteName(), ['vente.physiques.attentes', 'vente.physiques.finalises', 'vente.physiques.rejetes'])) menu-open @endif">
                                <a href="#" class="nav-link @if (in_array(Route::currentRouteName(), ['vente.physiques.attentes', 'vente.physiques.finalises', 'vente.physiques.rejetes'])) active @endif">
                                    <i class="nav-icon fas fa-shopping-cart"></i>
                                    <p>
                                        Ventes physiques
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    @if (hasPermission('vente.physiques.attentes'))
                                        <li class="nav-item">
                                            <a href="/ventes/physiques/attentes" class="nav-link @if (Route::currentRouteName() == 'vente.physiques.attentes') active @endif">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>En attente</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (hasPermission('vente.physiques.finalises'))
                                        <li class="nav-item">
                                            <a href="/ventes/physiques/finalises" class="nav-link @if (Route::currentRouteName() == 'vente.physiques.finalises') active @endif">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>Finalisés</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (hasPermission('vente.physiques.rejetes'))
                                        <li class="nav-item">
                                            <a href="/ventes/physiques/rejetes" class="nav-link @if (Route::currentRouteName() == 'vente.physiques.rejetes') active @endif">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>Rejetés</p>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        @if (hasPermission('vente.virtuelles.attentes') || hasPermission('vente.virtuelles.finalises') || hasPermission('vente.virtuelles.rejetes'))
                            <li class="nav-item @if (in_array(Route::currentRouteName(), ['vente.virtuelles.attentes', 'vente.virtuelles.finalises', 'vente.virtuelles.rejetes'])) menu-open @endif">
                                <a href="#" class="nav-link @if (in_array(Route::currentRouteName(), ['vente.virtuelles.attentes', 'vente.virtuelles.finalises', 'vente.virtuelles.rejetes'])) active @endif">
                                    <i class="nav-icon fas fa-shopping-cart"></i>
                                    <p>
                                        Ventes virtuelles
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    @if (hasPermission('vente.virtuelles.attentes'))
                                        <li class="nav-item">
                                            <a href="/ventes/virtuelles/attentes" class="nav-link @if (Route::currentRouteName() == 'vente.virtuelles.attentes') active @endif">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>En attente</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (hasPermission('vente.virtuelles.finalises'))
                                        <li class="nav-item">
                                            <a href="/ventes/virtuelles/finalises" class="nav-link @if (Route::currentRouteName() == 'vente.virtuelles.finalises') active @endif">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>Finalisés</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (hasPermission('vente.virtuelles.rejetes'))
                                        <li class="nav-item">
                                            <a href="/ventes/virtuelles/rejetes" class="nav-link @if (Route::currentRouteName() == 'vente.virtuelles.rejetes') active @endif">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>Rejetés</p>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        @if (hasPermission('rechargements.attente') || hasPermission('rechargement.finalises') || hasPermission('rechargement.rejetes'))
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon fas fa-money-bill"></i>
                                    <p>
                                        Rechargements
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    @if (hasPermission('rechargements.attente'))
                                        <li class="nav-item">
                                            <a href="/rechargements/attentes" class="nav-link">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>En attente</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (hasPermission('rechargement.finalises'))
                                        <li class="nav-item">
                                            <a href="/rechargements/finalises" class="nav-link">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>Finalisés</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if (hasPermission('rechargement.rejetes'))
                                        <li class="nav-item">
                                            <a href="/rechargements/rejetes" class="nav-link">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>Rejetés</p>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                        <li class="nav-header">Etats</li>-->
                        @if (hasPermission('rapport'))
                            <li class="nav-item  @if (in_array(Route::currentRouteName(), ['rapport.depots', 'rapport.retraits', 'rapport.transferts'])) menu-open @endif">
                                <a href="#" class="nav-link  @if (in_array(Route::currentRouteName(), ['rapport.depots', 'rapport.retraits', 'rapport.transferts'])) active @endif">
                                    <i class="nav-icon fas fa-file"></i>
                                    <p>
                                        Rapports
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="/rapport/depots" class="nav-link @if (Route::currentRouteName() == 'rapport.depots') active @endif">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Operations de depots</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/rapport/retraits" class="nav-link @if (Route::currentRouteName() == 'rapport.retraits') active @endif">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Operations de retraits</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/rapport/transferts" class="nav-link @if (Route::currentRouteName() == 'rapport.transferts') active @endif">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Operations de transferts</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        <li class="nav-header">Compte</li>
                        <li class="nav-item">
                            <a href="/profile" class="nav-link @if (Route::currentRouteName() == 'profile') active @endif">
                                <i class="nav-icon fas fa-user"></i>
                                <p>
                                    Profile
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link"
                                onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>
                                    Déconnexion
                                </p>
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">@yield('page')</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="/">Accueil</a></li>
                                <li class="breadcrumb-item active">@yield('page')</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            @yield('content')
        </div>
        <!-- /.content-wrapper -->
        <footer class="main-footer">
            <strong>Copyright &copy; {{ date('Y') }} <a href="#">ELG Technologie</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0
            </div>
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="/plugins/jquery/jquery.min.js"></script>
    
    <!-- Bootstrap 4 -->
    <script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- ChartJS -->
    <script src="/plugins/chart.js/Chart.min.js"></script>
    <!-- Sparkline -->
    <script src="/plugins/sparklines/sparkline.js"></script>
    <!-- JQVMap -->
    <script src="/plugins/jqvmap/jquery.vmap.min.js"></script>
    <script src="/plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
    <!-- jQuery Knob Chart -->
    <script src="/plugins/jquery-knob/jquery.knob.min.js"></script>
    <!-- daterangepicker -->
    <script src="/plugins/moment/moment.min.js"></script>
    <script src="/plugins/daterangepicker/daterangepicker.js"></script>
    <script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
    <!-- AdminLTE App -->
    <script src="/dist/js/adminlte.js"></script>
    <script src="{{ asset('toastr/toastr.min.js') }}"></script>
    <script>
        $(function() {
            @if (session()->has('success'))
                toastr.success("{{ session()->get('success') }}")
            @endif
            @if (session()->has('warning'))
                toastr.warning("{{ session()->get('warning') }}")
            @endif
            @if (session()->has('error'))
                toastr.error("{{ session()->get('error') }}")
            @endif
        }); 
    </script>
    <script src="/excel/dist/jquery.table2excel.js"></script>
    @yield('js')
</body>

</html>
