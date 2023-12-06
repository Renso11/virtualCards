<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE = edge">
    <meta name="viewport" content="width = device-width, initial-scale = 1.0">
    <title> Rapport des retraits du <b>{{ $debut }}</b> au <b>{{ $fin }}</b> </title>

    <style>
        @import url("https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css");
        #watermark {
            position: fixed;

            /** 
                Set a position in the page for your image
                This should center it vertically
            **/
            bottom:   30%;
            left:     36%;

            /** Change image dimensions**/
            width:    8cm;
            height:   8cm;

            /** Your watermark should be behind every content**/
            z-index:  -1000;
            opacity: 0.4;
        }
    </style>
</head>

<body>
    <div id="watermark">
        <img src="{{ asset('/img/bcb.png') }}" height="100%" width="100%" />
    </div>
    <main>
        <div style="padding:2%">
            <div class="row pull-left">    
                <div class="col-md-4">
                    <p class="text-center">Edité le {{ date('d-M-Y à h:i') }}</p>
                </div>
            </div>
            <br>
            <br>
            <div class="row pull-right">    
                <div class="col-md-12">
                    <p class="text-center">Tél : +229 60608820 - 61319161 &nbsp;&nbsp;&nbsp; Site web : virtualcards.bestcash.me &nbsp;&nbsp;&nbsp;  Email : info@bestcash.me</p>
                </div>
            </div>
            <br>
            <div class="row pull-left">
                <div class="col-md-12">
                    <img src="{{ asset('/img/bcb.png') }}" style="width: 20%" alt="">
                </div>
            </div>
            <br>
            <br>
            <br>
            <br>
            <div class="row">
                <div class="col-md-12 text-center">
                    <h3>Rapport des retraits sur periode </h3>
                </div>
            </div>
            <br>
            <div class="row pull-left">
                <div class="col-md-12">
                    <p class="text-center">Du <b>{{ $debut }}</b> au <b>{{ $fin }}</b></p>
                </div>
            </div>
            <br>
            <br>  
            
            <div class="row">
                <div class="col-md-12">
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
                            @foreach($retraits as $item)
                                <tr>
                                    <td>{{ $item->created_at->format('d-m-Y à h:i:s') }}</td>
                                    <td>{{ $item->partenaire->libelle }}</td>
                                    <td>{{ $item->userClient->name.' '.$item->userClient->lastname }}</td>
                                    <td>{{ $item->libelle }}</td>
                                    <td>{{ $item->montant }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KinkN" crossorigin="anonymous">
    </script>
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>

</body>

</html>