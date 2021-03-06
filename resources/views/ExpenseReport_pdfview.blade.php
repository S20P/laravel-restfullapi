<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }
            h1,h2,h4,p{
                padding:8px;
                margin: 0;
            }
            h3{
                margin:6px;
                
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
            }
           
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height" style="padding:0px 15px;">
            <div class="content">
              <style type="text/css">
                    table {
                      border-collapse: separate;
                      border-spacing: 0;
                      font-size: 12px;
                      margin: auto;
                      float: none;
                      width:100%;
                    }
                    th,
                    td {
                      padding: 5px 5px;
                    }
                    thead {
                      background: #395870;
                      color: #fff;
                    }
                    th {
                      font-weight: bold;
                    }
                    tbody tr:nth-child(even) {
                      background: #f0f0f2;
                    }
                    td {
                      border-bottom: 1px solid #cecfd5;
                      border-right: 1px solid #cecfd5;
                      color: #000;
                      background: #f1f1f1;
                      font-weight: 600;
                    }
                    td:first-child {
                      border-left: 1px solid #cecfd5;
                    }

                    .text-right{
                      text-align: right !important;
                      padding:0px;
                    }
                    .text-left{
                        text-align:left !important;
                        padding:0px;
                    }
                   .new-row{
                       width:100%;
                       padding:15px;
                   }
                   .h3{
                    font-size :20px;
                   }
                   .pd-r{
                    float: right !important;
                    text-align: right !important;
                   }
              </style>

              
              
              <div class="container">
   <!-- Expense Report -->
                 @if(isset($data))
                   
                    <h1>Expense Report</h1>
                    
                    <h4>
                    @if(isset($current_date))
                        <b>Date :</b>{{ Carbon\Carbon::parse($current_date)->format('d/m/Y') }} 
                    @endif 
                     @if(isset($from_date))
                    <b>From Date :</b> {{ Carbon\Carbon::parse($from_date)->format('d/m/Y') }}  
                     @endif
                     <b>    </b>
                     @if(isset($to_date))
                    <b>To Date :</b> {{ Carbon\Carbon::parse($to_date)->format('d/m/Y') }}
                     @endif
                   </h4>
                    

                    @if(isset($expenseReport))
                                <h3 class="text-left"> User Name :  {{$data[0]->user_name}}  </h3> 
                    @endif
   
                       <table>
                            <thead>
                              <tr>
                              @if(!isset($expenseReport))
                                <th scope="col">UserName</th>
                              @endif
                               
                                <th scope="col">Petrol</th>
                                <th scope="col">Police</th>
                                <th scope="col">Transport Rent</th>
                                <th scope="col">Date</th>
                              </tr>
                            </thead>
                            <tbody>

                              <?php
                                for($i=0;$i<count($data);$i++){
                              ?>
                                <tr>
                                @if(!isset($expenseReport))
                                  <td> {{$data[$i]->user_name}} </td>
                                @endif
                                  <td>{{$data[$i]->petrol}}</td>
                                  <td>{{$data[$i]->police}}</td>
                                  <td>{{$data[$i]->transport_rent}}</td>
                                  <td>
                                     {{ Carbon\Carbon::parse($data[$i]->date)->format('d-m-Y') }}
                                  </td>

                                </tr>
                            <?php } ?>
                            </tbody>
                   </table>
              </div>
              @endif
                <!-- end Expense Report --> 
            </div>
        </div>
    </body>
</html>
