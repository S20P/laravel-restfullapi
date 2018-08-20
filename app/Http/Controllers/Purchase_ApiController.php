<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
Use App\User;  //User Model
use Validator;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Http\Resources\UserResource as UserResource;
use Hash;

class Purchase_ApiController extends Controller
{
  public $successStatus = "1";
  public $successMessage = "Success";
  public $successCode = "200";

  public $errorcode = "400";
  public $errorStatus ="0";
  public $errorMessage = "error";

  public $successCreatedCode="201";

  public $date_IST;
  

   public function __construct()
   {
          $this->middleware('jwt');
          set_time_limit(0);

          date_default_timezone_set('Asia/Kolkata');
          $ist = date("Y-m-d g:i:s");
          $this->date_IST = date ("Y-m-d H:i:s", strtotime($ist));
   }

    public function index(Request $request)
     {
      if($request->isMethod('get')){

             $product = DB::table('purchase')->orderBy('date', 'asc')->get();
               if($product){
                       if(count($product)<=0){
                          return response()->json([
                                                   "data"=>$product,
                                                   'status'=>$this->successStatus,
                                                   'code'=>$this->successCode,
                                                   'message'=>"Product Purchase is Empty"], 200);
                       }else{


                            $purchase_record = [];


                            for($i=0;$i<count($product);$i++){

                              $user_name = DB::table('user')->where('id', $product[$i]->user_id)->value('username');
                               $product_img = DB::table('product')->where('id', $product[$i]->product_id)->value('product_image');
                               $product_unit =  DB::table('product')->where('id', $product[$i]->product_id)->value('unit');
                               array_push($purchase_record,
                                           [
                                            "id"=>$product[$i]->id,
                                            "user_id"=>$product[$i]->user_id,
                                            "user_name"=>$user_name,
                                            "product_id"=>$product[$i]->product_id,
                                            "product_image"=>$product_img,
                                            "product_name"=>$product[$i]->product_name,
                                            "unit" =>$product_unit,
                                            "total_weight"=>$product[$i]->total_weight,
                                            "total_amount"=>$product[$i]->total_amount,
                                            "weight_labour"=>$product[$i]->weight_labour,
                                            "transport_labour"=>$product[$i]->transport_labour,
                                            "shop_number"=>$product[$i]->shop_number,
                                            "date"=>$product[$i]->date,
                                            "profit_range"=>$product[$i]->profit_range,
                                            "wastage"=>$product[$i]->wastage,
                                            "description"=>$product[$i]->description,
                                            "kg_price"=>$this->number_format($product[$i]->kg_price),
                                            "selling_price"=>number_format($product[$i]->selling_price),
                                            "created_at"=>$product[$i]->created_at,
                                            "updated_at"=>$product[$i]->updated_at,
                                           ]
                               );
                            }

                          return response()->json([
                                                   "data"=>$purchase_record,
                                                   'status'=>$this->successStatus,
                                                   'code'=>$this->successCode,
                                                   'message'=>$this->successMessage
                                                 ],$this->successCode);
                       }
                }
              else{
                  return response()->json([
                                          'status'=>$this->errorStatus,
                                          'code'=>$this->errorcode,
                                          'message'=>"Failed to Find Product Purchase"]);
              }
       }
       else{
           return response()->json("Method not Allow", 405);
       }
   }

     public function show(Request $request,$id)
     {
         if($request->isMethod('get')){

             $product = DB::table('purchase')->where('id', $id)->get();
                  if($product){
                       if(count($product)<=0){
                          return response()->json([
                                                   'status'=>$this->successStatus,
                                                   'code'=>$this->successCode,
                                                   'message'=>"Product Purchase Not Found for this id"
                                                  ], $this->successCode);
                       }
                       else{
                         $purchase_record = [];


                         for($i=0;$i<count($product);$i++){

                           $user_name = DB::table('user')->where('id', $product[$i]->user_id)->value('username');
                           $product_img = DB::table('product')->where('id', $product[$i]->product_id)->value('product_image');
                           $product_unit =  DB::table('product')->where('id', $product[$i]->product_id)->value('unit');

                           array_push($purchase_record,
                                        [
                                         "id"=>$product[$i]->id,
                                         "user_id"=>$product[$i]->user_id,
                                         "user_name"=>$user_name,
                                         "product_id"=>$product[$i]->product_id,
                                         "product_name"=>$product[$i]->product_name,
                                         "product_image"=>$product_img,
                                         "unit" =>$product_unit,
                                         "total_weight"=>$product[$i]->total_weight,
                                         "total_amount"=>$product[$i]->total_amount,
                                         "weight_labour"=>$product[$i]->weight_labour,
                                         "transport_labour"=>$product[$i]->transport_labour,
                                         "shop_number"=>$product[$i]->shop_number,
                                         "date"=>$product[$i]->date,
                                         "profit_range"=>$product[$i]->profit_range,
                                         "wastage"=>$product[$i]->wastage,
                                         "description"=>$product[$i]->description,
                                         "kg_price"=>$this->number_format($product[$i]->kg_price),
                                         "selling_price"=>number_format($product[$i]->selling_price),
                                         "created_at"=>$product[$i]->created_at,
                                         "updated_at"=>$product[$i]->updated_at,
                                        ]
                            );
                         }
                         return response()->json([
                                                  "data"=>$purchase_record,
                                                  'status'=>$this->successStatus,
                                                  'code'=>$this->successCode,
                                                  'message'=>$this->successMessage
                                                 ],$this->successCode);
                       }
                   }
                   else{
                     return response()->json([
                                             'status'=>$this->errorStatus,
                                             'code'=>$this->errorcode,
                                             'message'=>"Failed to Find Product Purchase"]);
                   }
             }
         else{
             return response()->json("Method not Allow", 405);
         }
     }

     public function create(Request $request)
     {

        if($request->isMethod('post')){

            $validator = Validator::make($request->all(), [
                  'user_id' => 'required|numeric',
                  'product_id' => 'required|numeric',
                  'date'=>'required|date_format:Y-m-d',
               ]);

               if ($validator->fails()) {
                  return response()->json(['error'=>$validator->errors()], 401);
                  }
              else{
                     $user_id =  $request->input('user_id');
                     $product_id =  $request->input('product_id');
                     $total_weight =  $request->input('total_weight');
                     $total_amount =  $request->input('total_amount');
                     $weight_labour =  $request->input('weight_labour');
                     $transport_labour =  $request->input('transport_labour');
                     $shop_number =  $request->input('shop_number');
                     $last_amount =  $request->input('last_amount');
                     $wastage = $request->input('wastage');
                     $description = $request->input('description');
                     $selling_price = $request->input('selling_price');
                     $date =  $request->input('date');
                     $created_at = $this->date_IST;

               $is_allredy_id =  DB::table('purchase')
                                      ->where('product_id', $product_id)
                                      ->where('user_id',$user_id)
                                      ->where('date',$date)
                                      ->get();
                 if($is_allredy_id){
                   if(count($is_allredy_id)<=0){

                         $product_name = DB::table('product')->where('id', $product_id)->value('product_name');

                         if($product_name){

                             $user = DB::table('user')->where('id', $user_id)->get();
                                if($user){
                                  if(count($user)<=0){
                                    return response()->json([
                                                             'status'=>$this->successStatus,
                                                             'code'=>$this->successCode,
                                                             'message'=>"User Not Found for this user_id"], $this->successCode);
                                  }else{
                                    $product_is_id = DB::table('product_range')->where('product_id', $product_id)->get();
                                    if($product_is_id){
                                      if(count($product_is_id)<=0){
                                         return response()->json([
                                                                  'status'=>$this->successStatus,
                                                                  'code'=>$this->successCode,
                                                                  'message'=>"Product Range Not Found for this product_id"
                                                                 ], $this->successCode);
                                        }else{

                                            $kg_price = "";
                                            $profit_range ="";

                                            if($total_weight==null){
                                              $total_weight = "0";
                                            }else{
                                              $total_weight = $total_weight;
                                            }
                                            if($total_amount==null){
                                              $total_amount = "0";
                                            }else{
                                              $total_amount = $total_amount;
                                            }
                                            if($weight_labour==null){
                                              $weight_labour = "0";
                                            }else{
                                              $weight_labour = $weight_labour;
                                            }
                                            if($transport_labour==null){
                                              $transport_labour = "0";
                                            }else{
                                              $transport_labour = $transport_labour;
                                            }
                                            if($shop_number==null){
                                              $shop_number = "0";
                                            }else{
                                              $shop_number = $shop_number;
                                            }
                                            if($wastage==null){
                                              $wastage = "0";
                                            }else{
                                              $wastage = $wastage;
                                            }
                                            if($description==null){
                                              $description = "";
                                            }else{
                                              $description = $description;
                                            }
                                           


                                             $total = $total_amount + $weight_labour + $transport_labour;
                                              $kg_price = 0;

                                            if($total_amount>0){
                                              $kg_price = $total/$total_weight;
                                              $profit_range =  DB::table('product_range')
                                                                       ->where('product_id', $product_id)
                                                                       ->where('from_range','<=',$kg_price)
                                                                       ->where('to_range','>=',$kg_price)
                                                                       ->value('profit');
                                            }
                                                   //Find Kg Price

                                            if($profit_range==null){
                                              $profit_range = "0";
                                            }else{
                                              $profit_range = $profit_range;
                                            }
                                            if($last_amount==null){
                                              $last_amount = "0";
                                            }else{
                                              $last_amount = $last_amount;
                                            }
                                            
                                            
                                            
                                              if($selling_price==null){
                                                $new_price = ($kg_price / 100) * $profit_range;
                                                $selling_price =  $kg_price + $new_price; 
                                              }else{
                                                $selling_price = $selling_price;
                                              }


                                           $product_purchase_inserted_id = DB::table('purchase')->insertGetId([
                                                        'user_id' => $user_id,
                                                        'product_id'=>$product_id,
                                                        'product_name'=>$product_name,
                                                        'total_weight' => $total_weight,
                                                        'total_amount'=>$total_amount,
                                                        'weight_labour'=>$weight_labour,
                                                        'transport_labour'=>$transport_labour,
                                                        'shop_number'=>$shop_number,
                                                        'date'=>$date,
                                                        'profit_range'=>$profit_range,
                                                        'wastage'=>$wastage,
                                                        'description'=>$description,
                                                        'kg_price'=>$kg_price,
                                                        'selling_price'=>$selling_price,
                                                        'created_at'=>$created_at,
                                                        'updated_at'=>$created_at
                                                     ]);
                                                 if($product_purchase_inserted_id)
                                                 {

                                                    $product_purchase_data = DB::table('purchase')->where('id', $product_purchase_inserted_id)->get();

                                                    $purchase_record = [];


                                                    for($i=0;$i<count($product_purchase_data);$i++){

                                                      $user_name = DB::table('user')->where('id', $product_purchase_data[$i]->user_id)->value('username');
                                                      $product_img = DB::table('product')->where('id', $product_purchase_data[$i]->product_id)->value('product_image');
                                                      $product_unit =  DB::table('product')->where('id', $product_purchase_data[$i]->product_id)->value('unit');

                                                      array_push($purchase_record,
                                                                   [
                                                                    "id"=>$product_purchase_data[$i]->id,
                                                                    "user_id"=>$product_purchase_data[$i]->user_id,
                                                                    "user_name"=>$user_name,
                                                                    "product_id"=>$product_purchase_data[$i]->product_id,
                                                                    "product_name"=>$product_purchase_data[$i]->product_name,
                                                                    "product_image"=>$product_img,
                                                                    "unit" =>$product_unit,
                                                                    "total_weight"=>$product_purchase_data[$i]->total_weight,
                                                                    "total_amount"=>$product_purchase_data[$i]->total_amount,
                                                                    "weight_labour"=>$product_purchase_data[$i]->weight_labour,
                                                                    "transport_labour"=>$product_purchase_data[$i]->transport_labour,
                                                                    "shop_number"=>$product_purchase_data[$i]->shop_number,
                                                                    "date"=>$product_purchase_data[$i]->date,
                                                                    "profit_range"=>$product_purchase_data[$i]->profit_range,
                                                                    "wastage"=>$product_purchase_data[$i]->wastage,
                                                                    "description"=>$product_purchase_data[$i]->description,
                                                                    "kg_price"=>$this->number_format($product_purchase_data[$i]->kg_price),
                                                                    "selling_price"=>number_format($product_purchase_data[$i]->selling_price),
                                                                    "created_at"=>$product_purchase_data[$i]->created_at,
                                                                    "updated_at"=>$product_purchase_data[$i]->updated_at,
                                                                   ]
                                                       );
                                                    }
                                                    $balance_data = DB::table('balance')->where('user_id', $user_id)->where('date', $date)->get()->first();

                                                    if($balance_data){

                                                    $remaining_balance = $balance_data->remaining_balance;
                                                    $remaining_balance_new = $remaining_balance + $last_amount - $total;

                                                        $balance_result = DB::table('balance')
                                                                      ->where('user_id',$user_id)
                                                                      ->where('date',$date)
                                                                      ->update([
                                                                        'user_id' => $user_id,
                                                                        'remaining_balance' => $remaining_balance_new,
                                                                        'date'=>$date,
                                                                        'updated_at'=>$created_at
                                                                      ]);

                                                        if($balance_result){
                                                        $user_remaining_balance =  DB::table('balance')
                                                                                   ->where('user_id',$user_id)
                                                                                   ->where('date',$date)
                                                                                   ->get();
                                                        if($user_remaining_balance){
                                                           $user_remaining_balance = $user_remaining_balance[0]->remaining_balance;
                                                             if($user_remaining_balance<0){
                                                              $user_remaining_balance = "Insufficient";
                                                            }else{
                                                              $user_remaining_balance = $user_remaining_balance;
                                                            }
                                                            return response()->json([
                                                                                     'data'=>$purchase_record,
                                                                                     'status'=>$this->successStatus,
                                                                                     'code'=>$this->successCreatedCode,
                                                                                     'message'=>"Purchase order inserted successfully",
                                                                                     'Remaining balance is'=>$user_remaining_balance,
                                                                                   ], $this->successCode);
                                                        }else{
                                                        return response()->json([
                                                                                'status'=>$this->errorStatus,
                                                                                'code'=>$this->errorcode,
                                                                                'message'=>"Failed to find updated Balance "]);
                                                        }
                                                        }else{
                                                        return response()->json([
                                                                                'status'=>$this->errorStatus,
                                                                                'code'=>$this->errorcode,
                                                                                'message'=>"Failed to update Balance "]);
                                                        }


                                                        }else{
                                                        return response()->json(['data'=>$purchase_record,
                                                                                 'status'=>$this->successStatus,
                                                                                 'code'=>$this->successCreatedCode,
                                                                                 'message'=>"Purchase order inserted successfully",
                                                                                 'Balance'=>"Failed to Find Balance for this User",
                                                                               ], $this->successCode);


                                                        }


                                                 }
                                                 else{
                                                      return response()->json(['status'=>$this->errorStatus,
                                                                               'code'=>$this->errorcode,
                                                                               'message'=>"Product Purchase Not Inserted"], 400);
                                                 }
                                        }
                                        }else{
                                            return response()->json([
                                                                    'status'=>$this->errorStatus,
                                                                    'code'=>$this->errorcode,
                                                                    'message'=>"Failed to Find Product Purchase"]);

                                        }
                                  }
                                }else{
                                  return response()->json([
                                                          'status'=>$this->errorStatus,
                                                          'code'=>$this->errorcode,
                                                          'message'=>"Failed to Find User"]);
                                }



                         }else{
                           return response()->json([
                                                   'status'=>$this->errorStatus,
                                                   'code'=>$this->errorcode,
                                                   'message'=>"Product Not Found for this product_id"]);
                         }

                     }else{

                       return response()->json([
                                                'status'=>$this->successStatus,
                                                'code'=>$this->successCode,
                                                'message'=>"Already exists this purchase order"
                                               ], $this->successCode);
                     }
                }else{
                  return response()->json([
                                          'status'=>$this->errorStatus,
                                          'code'=>$this->errorcode,
                                          'message'=>"Failed to Find Product Purchase"]);

                }

                 }
             }
         else{
             return response()->json("Method not Allow", 405);
         }

     }


     public function update(Request $request, $id)
     {

         if($request->isMethod('post')){

             $validator = Validator::make($request->all(), [
                   'user_id' => 'required|numeric',
                   'product_id' => 'required|numeric',
                   'date'=>'required|date_format:Y-m-d'
                ]);

                if ($validator->fails()) {
                   return response()->json(['error'=>$validator->errors()], 401);
                   }
               else{


                      $product_is_id = DB::table('purchase')->where('id', $id)->get();

                      if($product_is_id){
                        if(count($product_is_id)<=0){
                           return response()->json(['status'=>$this->successStatus,
                                                    'code'=>$this->successCode,
                                                    'message'=>"Product Purchase Not Found for this id"
                                                   ], $this->successCode);
                          }else{

                            $user_id =  $request->input('user_id');
                            $isAdmin =  $request->input('isAdmin');
                            $product_id =  $request->input('product_id');
                            $total_weight =  $request->input('total_weight');
                            $total_amount =  $request->input('total_amount');
                            $weight_labour =  $request->input('weight_labour');
                            $transport_labour =  $request->input('transport_labour');
                            $shop_number =  $request->input('shop_number');
                            $last_amount =  $request->input('last_amount');
                            $date =  $request->input('date');
                            $created_at = $this->date_IST;
                            $wastage = $request->input('wastage');
                            $description = $request->input('description');
                            $selling_price = $request->input('selling_price');

                            $is_allredy_order =  DB::table('purchase')
                                                   ->where('product_id', $product_id)
                                                   ->where('user_id',$user_id)
                                                   ->where('date',$date)
                                                   ->whereNotIn('id', [$id])
                                                   ->get();

                          if($is_allredy_order){
                            if(count($is_allredy_order)>=1){
                               return response()->json([
                                                        'status'=>$this->successStatus,
                                                        'code'=>$this->successCode,
                                                        'message'=>"Already exists this purchase order"
                                                       ], $this->successCode);
                              }else{
                                $product_name = DB::table('product')->where('id', $product_id)->value('product_name');
                                if($product_name){

                                      $user = DB::table('user')->where('id', $user_id)->get();
                                       if($user){
                                         if(count($user)<=0){
                                           return response()->json([
                                                                    'status'=>$this->successStatus,
                                                                    'code'=>$this->successCode,
                                                                    'message'=>"User Not Found for this user_id"], $this->successCode);
                                         }else{

                                           $product_is_id = DB::table('product_range')->where('product_id', $product_id)->get();
                                           if($product_is_id){
                                             if(count($product_is_id)<=0){
                                                return response()->json([
                                                                         'status'=>$this->successStatus,
                                                                         'code'=>$this->successCode,
                                                                         'message'=>"Product Range  Not Found for this product_id"
                                                                        ], $this->successCode);
                                               }else{

                                          // $user_flage = $user[0]->isadmin;

                                           if($isAdmin=='true'){

                                             $total_weight =  $request->input('total_weight');

                                             $product_purchase_this = DB::table('purchase')
                                                                      ->where('id',$id)
                                                                      ->get();

                                                          $total_weight_this =$product_purchase_this[0]->total_weight;
                                                          $total_amount_this =$product_purchase_this[0]->total_amount;
                                                          $weight_labour_this =$product_purchase_this[0]->weight_labour;
                                                          $transport_labour_this =$product_purchase_this[0]->transport_labour;
                                                          $shop_number_this =$product_purchase_this[0]->shop_number;
                                                          $profit_range_this =$product_purchase_this[0]->profit_range;
                                                          $kg_price_this =$product_purchase_this[0]->kg_price;
                                                          $wastage_this = $product_purchase_this[0]->wastage;
                                                          $description_this  = $product_purchase_this[0]->description;


                                             if($total_weight==null){
                                               $total_weight_update = $total_weight_this;
                                             }else{
                                               $total_weight_update = $total_weight;
                                             }
                                             if($total_amount==null){
                                               $total_amount_update = $total_amount_this;
                                             }else{
                                               $total_amount_update = $total_amount;
                                             }
                                             if($weight_labour==null){
                                               $weight_labour_update = $weight_labour_this;
                                             }else{
                                               $weight_labour_update = $weight_labour;
                                             }
                                             if($transport_labour==null){
                                               $transport_labour_update = $transport_labour_this;
                                             }else{
                                               $transport_labour_update = $transport_labour;
                                             }
                                             if($shop_number==null){
                                               $shop_number_update = $shop_number_this;
                                             }else{
                                               $shop_number_update = $shop_number;
                                             }
                                             if($wastage==null){
                                               $wastage_update = $wastage_this;
                                             }else{
                                               $wastage_update = $wastage;
                                             }

                                             if($description==null){
                                               $description_update = $description_this;
                                             }else{
                                               $description_update = $description;
                                             }


                                              $total = $total_amount_update + $weight_labour_update + $transport_labour_update;

                                              $product_price = DB::table('product')
                                                   ->where('id',$product_id)
                                                   ->value('last_price');

                                               if($product_price){
                                                 $kg_price = $product_price;
                                               }else{
                                                 $kg_price = $kg_price_this;
                                               }

                                             if($total_weight_update>0){

                                               $Wastage_kg =  $total_weight_update * $wastage_update / 100;
                                               $Wastage_kg_est =  $total_weight_update - $Wastage_kg;
                                               $PerKgPrice = $total/$Wastage_kg_est;
                                               
                                             }
                                             else{
                                               $PerKgPrice = $kg_price;
                                             }
                                                    //Find Kg Price


                                              $profit_range = DB::table('product_range')
                                                                       ->where('product_id', $product_id)
                                                                       ->where('from_range','<=',$PerKgPrice)
                                                                       ->where('to_range','>=',$PerKgPrice)
                                                                       ->value('profit');    //Find Profit Range


                                              if($selling_price==null){
                                              $new_price = ($PerKgPrice / 100) * $profit_range;
                                              $selling_price =  $PerKgPrice + $new_price; 
                                              }else{
                                              $selling_price = $selling_price;
                                              }



                                             $product_result = DB::table('purchase')
                                                  ->where('id',$id)
                                                  ->update([
                                                           'user_id'=>$user_id,
                                                           'product_id'=>$product_id,
                                                           'product_name'=>$product_name,
                                                           'total_weight' => $total_weight_update,
                                                           'total_amount'=>$total_amount_update,
                                                           'weight_labour'=>$weight_labour_update,
                                                           'transport_labour'=>$transport_labour_update,
                                                           'shop_number'=>$shop_number_update,
                                                           'date'=>$date,
                                                           'profit_range'=>$profit_range,
                                                           'wastage'=>$wastage_update,
                                                           'description'=>$description_update,
                                                           'kg_price'=>$PerKgPrice,
                                                           'selling_price'=>$selling_price,
                                                           'created_at'=>$created_at,
                                                           'updated_at'=>$created_at
                                                        ]);

                                                        $product_last_price = DB::table('product')
                                                             ->where('id',$product_id)
                                                             ->update([
                                                                      'last_price'=>$PerKgPrice,
                                                                      'date'=>$date,
                                                                     ]);

                                             // $product_result = DB::table('purchase')
                                             //      ->where('id',$id)
                                             //      ->update([
                                             //               'user_id'=>$user_id,
                                             //               'total_weight' => $total_weight
                                             //              ]);

                                             if($product_result=='1')
                                             {

                                                  $purchase_data = DB::table('purchase')->where('id', $id)->get();

                                                  $purchase_record = [];

                                                  for($i=0;$i<count($purchase_data);$i++){

                                                  $user_name = DB::table('user')->where('id', $purchase_data[$i]->user_id)->value('username');
                                                  $product_img = DB::table('product')->where('id', $purchase_data[$i]->product_id)->value('product_image');
                                                  $product_unit =  DB::table('product')->where('id', $purchase_data[$i]->product_id)->value('unit');

                                                  array_push($purchase_record,
                                                               [
                                                                "id"=>$purchase_data[$i]->id,
                                                                "user_id"=>$purchase_data[$i]->user_id,
                                                                "user_name"=>$user_name,
                                                                "product_id"=>$purchase_data[$i]->product_id,
                                                                "product_name"=>$purchase_data[$i]->product_name,
                                                                "product_image"=>$product_img,
                                                                "unit" =>$product_unit,
                                                                "total_weight"=>$purchase_data[$i]->total_weight,
                                                                "total_amount"=>$purchase_data[$i]->total_amount,
                                                                "weight_labour"=>$purchase_data[$i]->weight_labour,
                                                                "transport_labour"=>$purchase_data[$i]->transport_labour,
                                                                "shop_number"=>$purchase_data[$i]->shop_number,
                                                                "date"=>$purchase_data[$i]->date,
                                                                "profit_range"=>$purchase_data[$i]->profit_range,
                                                                "wastage"=>$purchase_data[$i]->wastage,
                                                                "description"=>$purchase_data[$i]->description,
                                                                "kg_price"=>$this->number_format($purchase_data[$i]->kg_price),
                                                                'selling_price'=>number_format($purchase_data[$i]->selling_price),
                                                                "created_at"=>$purchase_data[$i]->created_at,
                                                                "updated_at"=>$purchase_data[$i]->updated_at,
                                                               ]
                                                   );
                                                  }



                                                  $balance_data = DB::table('balance')->where('user_id', $user_id)->where('date', $date)->get()->first();
                                                  if($balance_data){

                                                    $remaining_balance = $balance_data->remaining_balance;
                                                    $remaining_balance_new = $remaining_balance + $last_amount - $total;
         
                                                    $balance_result = DB::table('balance')
                                                                  ->where('user_id',$user_id)
                                                                  ->where('date',$date)
                                                                  ->update([
                                                                    'user_id' => $user_id,
                                                                    'remaining_balance' => $remaining_balance_new,
                                                                    'date'=>$date,
                                                                    'updated_at'=>$created_at
                                                                  ]);
         
                                                    if($balance_result){
                                                    $user_remaining_balance =  DB::table('balance')
                                                                               ->where('user_id',$user_id)
                                                                               ->where('date',$date)
                                                                               ->get();
         
                                                    if($user_remaining_balance){
                                                       $user_remaining_balance = $user_remaining_balance[0]->remaining_balance;
                                                       
                                                       //   if($user_remaining_balance<0){
                                                       //    $user_remaining_balance = "Insufficient";
                                                       //  }else{
                                                       //    $user_remaining_balance = $user_remaining_balance;
                                                       //  }
                                                        return response()->json([
                                                                                 'data'=>$purchase_record,
                                                                                 'status'=>$this->successStatus,
                                                                                 'code'=>$this->successCreatedCode,
                                                                                 'message'=>"Product Purchase Updated Successfully",
                                                                                 'balance_remain'=>$user_remaining_balance,
                                                                               ], $this->successCode);
                                                    }else{
                                                    return response()->json([
                                                                            'status'=>$this->errorStatus,
                                                                            'code'=>$this->errorcode,
                                                                            'message'=>"Failed to find updated Balance "]);
                                                    }
                                                    }else{
                                                    return response()->json([
                                                                            'status'=>$this->errorStatus,
                                                                            'code'=>$this->errorcode,
                                                                            'message'=>"Failed to update Balance "]);
                                                    }
         
         
                                                    }else{
         
         
                                                     $remaining_balance_new = $last_amount - $total;
         
                                                     $balance_inserted_id = DB::table('balance')->insertGetId([
                                                       'user_id' => $user_id,
                                                       'balance'=>"0",
                                                       'remaining_balance' =>$remaining_balance_new,
                                                       'date'=>$date,
                                                       'created_at'=>$created_at,
                                                       'updated_at'=>$created_at
                                                    ]);
         
         
         
                                                    return response()->json(['data'=>$purchase_record,
                                                                             'status'=>$this->successStatus,
                                                                             'code'=>$this->successCreatedCode,
                                                                             'message'=>"Product Purchase Updated Successfully",
                                                                             'balance_remain'=>$remaining_balance_new
                                                                           ], $this->successCode);
         
         
                                                    }

                                                  }
                                                  else{
                                                       return response()->json(['status'=>$this->errorStatus,
                                                                                'code'=>$this->errorcode,
                                                                                'message'=>"Product Purchase Not Updated"], 400);
                                                  }
                                           }else{
                                             $product_purchase_this = DB::table('purchase')
                                                                      ->where('id',$id)
                                                                      ->get();

                                                          $total_weight_this =$product_purchase_this[0]->total_weight;
                                                          $total_amount_this =$product_purchase_this[0]->total_amount;
                                                          $weight_labour_this =$product_purchase_this[0]->weight_labour;
                                                          $transport_labour_this =$product_purchase_this[0]->transport_labour;
                                                          $shop_number_this =$product_purchase_this[0]->shop_number;
                                                          $profit_range_this =$product_purchase_this[0]->profit_range;
                                                          $kg_price_this =$product_purchase_this[0]->kg_price;
                                                          $wastage_this = $product_purchase_this[0]->wastage;
                                                          $description_this  = $product_purchase_this[0]->description;


                                             if($total_weight==null){
                                               $total_weight_update = $total_weight_this;
                                             }else{
                                               $total_weight_update = $total_weight;
                                             }
                                             if($total_amount==null){
                                               $total_amount_update = $total_amount_this;
                                             }else{
                                               $total_amount_update = $total_amount;
                                             }
                                             if($weight_labour==null){
                                               $weight_labour_update = $weight_labour_this;
                                             }else{
                                               $weight_labour_update = $weight_labour;
                                             }
                                             if($transport_labour==null){
                                               $transport_labour_update = $transport_labour_this;
                                             }else{
                                               $transport_labour_update = $transport_labour;
                                             }
                                             if($shop_number==null){
                                               $shop_number_update = $shop_number_this;
                                             }else{
                                               $shop_number_update = $shop_number;
                                             }
                                             if($wastage==null){
                                               $wastage_update = $wastage_this;
                                             }else{
                                               $wastage_update = $wastage;
                                             }

                                             if($description==null){
                                               $description_update = $description_this;
                                             }else{
                                               $description_update = $description;
                                             }


                                              $total = $total_amount_update + $weight_labour_update + $transport_labour_update;

                                              $product_price = DB::table('product')
                                                   ->where('id',$product_id)
                                                   ->value('last_price');

                                               if($product_price){
                                                 $kg_price = $product_price;
                                               }else{
                                                 $kg_price = $kg_price_this;
                                               }


                                             if($total_weight_update>0){
                                               $Wastage_kg =  $total_weight_update * $wastage_update / 100;
                                               $Wastage_kg_est =  $total_weight_update - $Wastage_kg;
                                               $PerKgPrice = $total/$Wastage_kg_est;
                                             }
                                             else{
                                               $PerKgPrice = $kg_price;
                                             }
                                                    //Find Kg Price


                                              $profit_range = DB::table('product_range')
                                                                       ->where('product_id', $product_id)
                                                                       ->where('from_range','<=',$PerKgPrice)
                                                                       ->where('to_range','>=',$PerKgPrice)
                                                                       ->value('profit');    //Find Profit Range


                                              if($selling_price==null){
                                              $new_price = ($PerKgPrice / 100) * $profit_range;
                                              $selling_price =  $PerKgPrice + $new_price; 
                                              }else{
                                              $selling_price = $selling_price;
                                              }


                                             $product_result = DB::table('purchase')
                                                  ->where('id',$id)
                                                  ->update([
                                                           'user_id'=>$user_id,
                                                           'product_id'=>$product_id,
                                                           'product_name'=>$product_name,
                                                           'total_weight' => $total_weight_update,
                                                           'total_amount'=>$total_amount_update,
                                                           'weight_labour'=>$weight_labour_update,
                                                           'transport_labour'=>$transport_labour_update,
                                                           'shop_number'=>$shop_number_update,
                                                           'date'=>$date,
                                                           'profit_range'=>$profit_range,
                                                           'wastage'=>$wastage_update,
                                                           'description'=>$description_update,
                                                           'kg_price'=>$PerKgPrice,
                                                           'selling_price'=>$selling_price,
                                                           'created_at'=>$created_at,
                                                           'updated_at'=>$created_at
                                                        ]);

                                                        $product_last_price = DB::table('product')
                                                             ->where('id',$product_id)
                                                             ->update([
                                                                      'last_price'=>$PerKgPrice,
                                                                      'date'=>$date,
                                                                     ]);



                                                  if($product_result=='1')
                                                  {

                                           $purchase_data = DB::table('purchase')->where('id', $id)->get();

                                           $purchase_record = [];

                                           for($i=0;$i<count($purchase_data);$i++){

                                           $user_name = DB::table('user')->where('id', $purchase_data[$i]->user_id)->value('username');
                                           $product_img = DB::table('product')->where('id', $purchase_data[$i]->product_id)->value('product_image');
                                           $product_unit =  DB::table('product')->where('id', $purchase_data[$i]->product_id)->value('unit');

                                           array_push($purchase_record,
                                                        [
                                                         "id"=>$purchase_data[$i]->id,
                                                         "user_id"=>$purchase_data[$i]->user_id,
                                                         "user_name"=>$user_name,
                                                         "product_id"=>$purchase_data[$i]->product_id,
                                                         "product_name"=>$purchase_data[$i]->product_name,
                                                         "product_image"=>$product_img,
                                                         "unit" =>$product_unit,
                                                         "total_weight"=>$purchase_data[$i]->total_weight,
                                                         "total_amount"=>$purchase_data[$i]->total_amount,
                                                         "weight_labour"=>$purchase_data[$i]->weight_labour,
                                                         "transport_labour"=>$purchase_data[$i]->transport_labour,
                                                         "shop_number"=>$purchase_data[$i]->shop_number,
                                                         "date"=>$purchase_data[$i]->date,
                                                         "profit_range"=>$purchase_data[$i]->profit_range,
                                                         "wastage"=>$purchase_data[$i]->wastage,
                                                         "description"=>$purchase_data[$i]->description,
                                                         "kg_price"=>$this->number_format($purchase_data[$i]->kg_price),
                                                         'selling_price'=>number_format($purchase_data[$i]->selling_price),
                                                         "created_at"=>$purchase_data[$i]->created_at,
                                                         "updated_at"=>$purchase_data[$i]->updated_at,
                                                        ]
                                            );
                                           }


                                           $balance_data = DB::table('balance')->where('user_id', $user_id)->where('date', $date)->get()->first();

                                           if($balance_data){

                                           $remaining_balance = $balance_data->remaining_balance;
                                           $remaining_balance_new = $remaining_balance + $last_amount - $total;

                                           $balance_result = DB::table('balance')
                                                         ->where('user_id',$user_id)
                                                         ->where('date',$date)
                                                         ->update([
                                                           'user_id' => $user_id,
                                                           'remaining_balance' => $remaining_balance_new,
                                                           'date'=>$date,
                                                           'updated_at'=>$created_at
                                                         ]);

                                           if($balance_result){
                                           $user_remaining_balance =  DB::table('balance')
                                                                      ->where('user_id',$user_id)
                                                                      ->where('date',$date)
                                                                      ->get();

                                           if($user_remaining_balance){
                                              $user_remaining_balance = $user_remaining_balance[0]->remaining_balance;
                                              //   if($user_remaining_balance<0){
                                              //    $user_remaining_balance = "Insufficient";
                                              //  }else{
                                              //    $user_remaining_balance = $user_remaining_balance;
                                              //  }
                                               return response()->json([
                                                                        'data'=>$purchase_record,
                                                                        'status'=>$this->successStatus,
                                                                        'code'=>$this->successCreatedCode,
                                                                        'message'=>"Product Purchase Updated Successfully",
                                                                        'balance_remain'=>$user_remaining_balance,
                                                                      ], $this->successCode);
                                           }else{
                                           return response()->json([
                                                                   'status'=>$this->errorStatus,
                                                                   'code'=>$this->errorcode,
                                                                   'message'=>"Failed to find updated Balance "]);
                                           }
                                           }else{
                                           return response()->json([
                                                                   'status'=>$this->errorStatus,
                                                                   'code'=>$this->errorcode,
                                                                   'message'=>"Failed to update Balance "]);
                                           }


                                           }else{


                                            $remaining_balance_new = $last_amount - $total;

                                            $balance_inserted_id = DB::table('balance')->insertGetId([
                                              'user_id' => $user_id,
                                              'balance'=>"0",
                                              'remaining_balance' =>$remaining_balance_new,
                                              'date'=>$date,
                                              'created_at'=>$created_at,
                                              'updated_at'=>$created_at
                                           ]);



                                           return response()->json(['data'=>$purchase_record,
                                                                    'status'=>$this->successStatus,
                                                                    'code'=>$this->successCreatedCode,
                                                                    'message'=>"Product Purchase Updated Successfully",
                                                                    'balance_remain'=>$remaining_balance_new
                                                                  ], $this->successCode);


                                           }
                                                  }
                                                  else{
                                                       return response()->json(['status'=>$this->errorStatus,
                                                                                'code'=>$this->errorcode,
                                                                                'message'=>"Product Purchase Not Updated"], 400);
                                                  }
                                              }
                                            }
                                           }else{
                                              return response()->json([
                                                                      'status'=>$this->errorStatus,
                                                                      'code'=>$this->errorcode,
                                                                      'message'=>"Failed to Find Product Range"]);

                                           }
                                         }
                                       }else{
                                         return response()->json([
                                                                 'status'=>$this->errorStatus,
                                                                 'code'=>$this->errorcode,
                                                                 'message'=>"Failed to Find User"]);
                                       }


                                  }
                                  else{
                                    return response()->json([
                                                            'status'=>$this->errorStatus,
                                                            'code'=>$this->errorcode,
                                                            'message'=>"Failed to Find Product name"]);
                                  }


                              }
                          }else{
                              return response()->json([
                                                      'status'=>$this->errorStatus,
                                                      'code'=>$this->errorcode,
                                                      'message'=>"Failed to Find Purchase"]);

                          }

                          }
                          }else{
                              return response()->json([
                                                      'status'=>$this->errorStatus,
                                                      'code'=>$this->errorcode,
                                                      'message'=>"Failed to Find Product Purchase"]);

                          }
                  }
              }
          else{
              return response()->json("Method not Allow", 405);
          }
    }


     public function destroy(Request $request,$id)
     {
       if($request->isMethod('delete'))
       {

         $product = DB::table('purchase')->where('id', $id)->get();
        if($product)
          {
               if(count($product)<=0){
                  return response()->json(['status'=>$this->successStatus,
                                           'code'=>$this->successCode,
                                           'message'=>"Product Purchase Not Found for this id"], $this->successCode);
               }
               else{

                $purchase_cost_total =  DB::table('purchase')
                ->where('id', $id)
                ->value(DB::raw("SUM(total_amount + weight_labour + transport_labour)"));
                
                $current_purchase = DB::table('purchase')->select('date', 'user_id')->where('id', $id)->get();
                $data = json_decode($current_purchase);
                   $i = 0;
                   $date = $data[$i]->date;
                   $user_id = $data[$i]->user_id;
                    
                  $balance_data = DB::table('balance')
                                      ->where('user_id', $user_id)
                                      ->where('date', $date)
                                      ->get()
                                      ->first();

                  if($balance_data){

                    $updated_at = $this->date_IST;
                    $remaining_balance = $balance_data->remaining_balance;
                    $remaining_balance_new = $remaining_balance + $purchase_cost_total;
                   // $remaining_balance_new = (number_format($remaining_balance) + abs($remaining_balance) + $purchase_cost_total);

                  $balance_result = DB::table('balance')
                                ->where('user_id',$user_id)
                                ->where('date',$date)
                                ->update([
                                  'remaining_balance' => $remaining_balance_new,
                                  'updated_at'=>$updated_at
                                ]);
                     
                  if($balance_result)  {           
                  
                    $product =  DB::table('purchase')->where('id',$id)->delete();

                   if($product){
                     return response()->json(['data'=>null,
                                             'status'=>$this->successStatus,
                                             'code'=>"204",
                                             'message'=>"Product Purchase Deleted Successfully"], $this->successCode);
                   }
                 else{
                       return response()->json(['status'=>$this->errorStatus,
                                                'code'=>$this->errorcode,
                                                'message'=>"Product Purchase Not Deleted"], $this->successCode);
                   }

                  }
                  }else{
                    $product =  DB::table('purchase')->where('id',$id)->delete();

                    if($product){
                      return response()->json(['data'=>null,
                                              'status'=>$this->successStatus,
                                              'code'=>"204",
                                              'message'=>"Product Purchase Deleted Successfully"], $this->successCode);
                    }
                  else{
                        return response()->json(['status'=>$this->errorStatus,
                                                 'code'=>$this->errorcode,
                                                 'message'=>"Product Purchase Not Deleted"], $this->successCode);
                    }
                  }
                 
                }
          }
          else{
            return response()->json([
                                    'status'=>$this->errorStatus,
                                    'code'=>$this->errorcode,
                                    'message'=>"Failed to Delete Product Purchase"]);
             }
     }
     else{
         return response()->json("Method not Allow", 405);
     }
     }



     public function number_format($number)
     {
       return number_format($number, 2, '.', ',');
     //  return number_format($number);
     }



}
