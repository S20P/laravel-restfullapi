<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <!-- <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
        <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
        <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
        <script src="//code.jquery.com/jquery-1.11.1.min.js"></script> -->

        <link rel="stylesheet" href="{{ asset('css/font-awesome.css') }}">
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <!-- Styles -->
        <style>
            /* html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            } */
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
              <style type="text/css">

              </style>
              <div class="container">
                <h2>My Table</h2>

                 <div class="row">
                   <div class="col-md-6">
                        <h3>test</h3>
                    </div> 
                    <div class="col-md-6">
                       <h3>test</h3>
                    </div> 
                 </div> 


                  	<div class="row">
              			<div class="col-md-8 col-md-offset-2">
              				<div class="panel panel-primary">
              					<div class="panel-heading">
              						<h3 class="panel-title">Developers</h3>
              					</div>



              					<!-- <table class="table table-hover" id="dev-table">
              						<thead>
              							<tr>
              								<th>#</th>
              								<th>First Name</th>
              								<th>Last Name</th>
              								<th>Username</th>
              							</tr>
              						</thead>
              						<tbody>
              							<tr>
              								<td>1</td>
              								<td>Kilgore</td>
              								<td>Trout</td>
              								<td>kilgore</td>
              							</tr>
              							<tr>
              								<td>2</td>
              								<td>Bob</td>
              								<td>Loblaw</td>
              								<td>boblahblah</td>
              							</tr>
              							<tr>
              								<td>3</td>
              								<td>Holden</td>
              								<td>Caulfield</td>
              								<td>penceyreject</td>
              							</tr>
              						</tbody>
              					</table> -->

                         <table class="table">
                           <thead class="thead-dark">
                             <tr>
                               <th>Firstname</th>
                               <th>Lastname</th>
                               <th>Email</th>
                             </tr>
                           </thead>
                           <tbody>
                             <tr>
                               <td>John</td>
                               <td>Doe</td>
                               <td>john@example.com</td>
                             </tr>
                             <tr>
                               <td>Mary</td>
                               <td>Moe</td>
                               <td>mary@example.com</td>
                             </tr>
                             <tr>
                               <td>July</td>
                               <td>Dooley</td>
                               <td>july@example.com</td>
                             </tr>
                           </tbody>
                         </table>



                    	</div>
              			</div>

              		</div>

              </div>
            </div>
        </div>



<script src="{{ asset('js/app.js') }}"></script>

    </body>
</html>
