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
                       padding:8px;
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
                 @if(isset($data))
                   
                    
                    @if(isset($dayReport))

                      
                    @if(isset($current_date))
                    <h1>Purchase Day Report </h1>
                    <h4><b>Date :</b> {{ Carbon\Carbon::parse($current_date)->format('d/m/Y') }}</h4>  
                    @else
                    <h1>Purchase Report </h1>
                    @endif 

                    @endif 
                                
    <!-- Current Report -->
                    @if(isset($currentReport))

                    <h1>CurrentDay Report </h1>

                     @if(isset($current_date))
                    <h4><b>Date :</b>{{ Carbon\Carbon::parse($current_date)->format('d/m/Y') }}</h4>  
                     @endif 

                            <div class="new-row text-left p-bt">
                                <span class="text-left h3 "> User Name : {{$data[0]->user_name}} </span>
                               
                                <span class="text-right pd-r h3"> Balance: {{$data[0]->balance}}  </span> 
                             
                                
                            </div> 

                    @endif 
  <!-- end Current Report --> 

   <!-- User Report -->                  

                  @if(isset($userReport))

                  <h1>Users Report </h1>

                   <h4>
                     @if(isset($from_date))
                     <b>From Date :</b> {{ Carbon\Carbon::parse($from_date)->format('d/m/Y') }}  
                     @endif
                     <b>    </b>
                     @if(isset($to_date))
                    <b>To Date :</b> {{ Carbon\Carbon::parse($to_date)->format('d/m/Y') }}
                     @endif
                   </h4>
                             
                    <h3 class="text-left"> User Name : {{$data[0]->user_name}} </h3>
                             
                    @endif 
                             
  <!-- end User Report -->    
                       <table>
                            <thead>
                              <tr>
                              @if(isset($dayReport))
                                <th scope="col" colspan="0">User</th>
                              @endif 
                                <th scope="col">Product</th>
                                <th scope="col">Total Weight</th>
                                <th scope="col">Total Amount</th>
                                <th scope="col">Weight Labour</th>
                                <th scope="col">Transport Labour</th>
                                <th scope="col">Shop Number</th>
                                <th scope="col">Date</th>
                                <th scope="col">Time</th>
                                <th scope="col">Profit Range(%)</th>
                                <th scope="col">Kg Price</th>
                                <th scope="col">Selling Price</th>
                                <th scope="col">Total Cost</th>
                              </tr>
                            </thead>
                            <tbody>

                              <?php
                                for($i=0;$i<count($data);$i++){
                              ?>
                                <tr>
                                @if(isset($dayReport))
                                  <td style="width:15%">{{$data[$i]->user_name}}</td>
                                @endif 
                                  <td style="width:15%">{{$data[$i]->product_name}}</td>
                                  <td>{{$data[$i]->total_weight}}</td>
                                  <td>{{$data[$i]->total_amount}}</td>
                                  <td>{{$data[$i]->weight_labour}}</td>
                                  <td>{{$data[$i]->transport_labour}}</td>
                                  <td>{{$data[$i]->shop_number}}</td>
                                  <td style="width:70px">
                                  {{ Carbon\Carbon::parse($data[$i]->date)->format('d-m-Y') }}
                                  </td>
                                  <td>
                         <?php
                                  $created_at = $data[$i]->created_at;
                                  $timestamp = strtotime($created_at);
                                  $time = date("H:i:s",$timestamp);
                          ?>
                                  {{$time}}
                                  </td>
                                  <td>{{$data[$i]->profit_range}}</td>
                                  <td>
                                  <?php 
                                 $kg_price = number_format($data[$i]->kg_price, 2, '.', ',');
                                  ?>
                                  {{$kg_price}}
                                  </td>
                                  <td>
                                  <?php 
                                 $selling_price = number_format($data[$i]->selling_price, 2, '.', ',');
                                  ?>
                                  {{$selling_price}}
                                  </td>
                                  <td>{{$data[$i]->total_amount + $data[$i]->weight_labour + $data[$i]->transport_labour}}</td>
                                </tr>
                            <?php } ?>
                            </tbody>
                   </table>
                   @if(isset($totalCost))
                                <h3 class="text-right"> Total : {{$totalCost}}  </h3> 
                  @endif 
              </div>
              @endif
            </div>
        </div>
    </body>
</html>
