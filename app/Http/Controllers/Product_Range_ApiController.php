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

class Product_Range_ApiController extends Controller
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

             $product = DB::table('product_range')->get();
               if($product){
                       if(count($product)<=0){
                          return response()->json([
                                                   "data"=>$product,
                                                   'status'=>$this->successStatus,
                                                   'code'=>$this->successCode,
                                                   'message'=>"Product Range is Empty"], 200);
                       }else{
                          return response()->json([
                                                   "data"=>$product,
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
                                          'message'=>"Failed to Find Product Range"]);
              }
       }
       else{
           return response()->json("Method not Allow", 405);
       }
   }

     public function show(Request $request,$id)
     {
         if($request->isMethod('get')){

             $product = DB::table('product_range')->where('id', $id)->get();
                  if($product){
                       if(count($product)<=0){
                          return response()->json([
                                                   'status'=>$this->successStatus,
                                                   'code'=>$this->successCode,
                                                   'message'=>"Product Range Not Found for this id"
                                                  ], $this->successCode);
                       }
                       else{
                         return response()->json([
                                                  "data"=>$product,
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
                                             'message'=>"Failed to Find Product Range"]);
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
                  'product_id' => 'required|numeric',
                  'from_range' => 'required|numeric',
                  'to_range'=>'required|numeric',
                  'profit'=>'required|numeric'
               ]);

               if ($validator->fails()) {
                  return response()->json(['error'=>$validator->errors()], 401);
                  }
              else{
                     $product_id =  $request->input('product_id');
                     $from_range =  $request->input('from_range');
                     $to_range =  $request->input('to_range');
                     $profit =  $request->input('profit');
                     $created_at = $this->date_IST;


                    $product_is_id = DB::table('product')->where('id', $product_id)->get();
                    if($product_is_id){
                      if(count($product_is_id)<=0){
                         return response()->json([
                                                  'status'=>$this->successStatus,
                                                  'code'=>$this->successCode,
                                                  'message'=>"Product Not Found for this product_id"
                                                 ], $this->successCode);
                        }else{

                          $range_already = DB::table('product_range')
                                                      ->where('product_id', $product_id)
                                                      ->where('from_range', $from_range)
                                                      ->where('to_range', $to_range)
                                                      ->get();
                          if($range_already){
                            if(count($range_already)<=0){

                              $range_in_from = DB::table('product_range')
                                                            ->where('product_id', $product_id)
                                                            ->where('from_range','<=',$from_range)
                                                            ->where('to_range','>=',$from_range)
                                                            ->get();
                              if($range_in_from)
                              {
                                if(count($range_in_from)>=1)
                                    {
                                      return response()->json([
                                                               'status'=>$this->successStatus,
                                                               'code'=>$this->successCode,
                                                               'message'=>"Already exists from-Range for this Product"
                                                              ], $this->successCode);
                                    }

                              }
                              $range_in_to = DB::table('product_range')
                                              ->where('product_id', $product_id)
                                              ->where('from_range','<=',$to_range)
                                              ->where('to_range','>=',$to_range)
                                              ->get();
                              if($range_in_to)
                              {
                                if(count($range_in_to)>=1)
                                    {
                                      return response()->json([
                                                               'status'=>$this->successStatus,
                                                               'code'=>$this->successCode,
                                                               'message'=>"Already exists to-Range for this Product"
                                                              ], $this->successCode);
                                    }
                              }

                                      $product_inserted_id = DB::table('product_range')->insertGetId([
                                                     'product_id' => $product_id,
                                                     'from_range'=>$from_range,
                                                     'to_range' => $to_range,
                                                     'profit'=>$profit,
                                                     'created_at'=>$created_at,
                                                     'updated_at'=>$created_at
                                                  ]);


                                    if($product_inserted_id)
                                    {
                                        $product_range_data = DB::table('product_range')->where('id', $product_inserted_id)->get();
                                          return response()->json(['data'=>$product_range_data,
                                                                   'status'=>$this->successStatus,
                                                                   'code'=>$this->successCreatedCode,
                                                                   'message'=>"Product Range Inserted Successfully"], $this->successCode);
                                    }
                                    else{
                                         return response()->json(['status'=>$this->errorStatus,
                                                                  'code'=>$this->errorcode,
                                                                  'message'=>"Product Range Not Inserted"], 400);
                                    }

                            }else{
                              return response()->json([
                                                       'status'=>$this->successStatus,
                                                       'code'=>$this->successCode,
                                                       'message'=>"Already exists Range for this Product"
                                                      ], $this->successCode);
                            }
                          }else{
                            return response()->json([
                                                    'status'=>$this->errorStatus,
                                                    'code'=>$this->errorcode,
                                                    'message'=>"Failed to add range"]);
                          }

                        }
                    }else{
                        return response()->json([
                                                'status'=>$this->errorStatus,
                                                'code'=>$this->errorcode,
                                                'message'=>"Failed to Find Product "]);

                    }
                 }
             }
         else{
             return response()->json("Method not Allow", 405);
         }

     }


     public function update(Request $request, $id)
     {

        if($request->isMethod('post'))
        {
             $validator = Validator::make($request->all(), [
                     'product_id' => 'required|numeric',
                ]);

              if ($validator->fails())
                {
                 return response()->json(['error'=>$validator->errors()], 401);
                }

             $product = DB::table('product_range')->where('id', $id)->get();

              if($product)
              {
                if(count($product)<=0){
                   return response()->json(['status'=>$this->successStatus,
                                            'code'=>$this->successCode,
                                            'message'=>"Product Range Not Found for this id"], $this->successCode);
                }
                else{

                        $product_id =  $request->input('product_id');
                        $from_range =  $request->input('from_range');
                        $to_range =  $request->input('to_range');
                        $profit =  $request->input('profit');
                        $updated_at = $this->date_IST;

                        $product_is_id = DB::table('product')->where('id', $product_id)->get();
                        if($product_is_id){
                          if(count($product_is_id)<=0)
                           {
                              return response()->json([
                                                      'status'=>$this->successStatus,
                                                      'code'=>$this->successCode,
                                                      'message'=>"Product  Not Found for this product_id"
                                                     ], $this->successCode);
                           }
                        else
                           {
                              $range_already = DB::table('product_range')
                                                  ->where('product_id', $product_id)
                                                  ->where('from_range', $from_range)
                                                  ->where('to_range', $to_range)
                                                  ->whereNotIn('id', [$id])
                                                  ->get();
                              if($range_already)
                              {
                                if(count($range_already)>=1)
                                {
                                  return response()->json([
                                                           'status'=>$this->successStatus,
                                                           'code'=>$this->successCode,
                                                           'message'=>"Already exists Range for this Product",
                                                          ], $this->successCode);
                                }
                              else
                                {
                                  $range_already_group = DB::table('product_range')
                                                                    ->where('id', $id)
                                                                    ->get();
                                    if($range_already_group)
                                    {
                                      if($from_range==null)
                                      {
                                        $from_range = $range_already_group[0]->from_range;
                                      }else
                                      {
                                        $from_range = $from_range;
                                        $range_in_from = DB::table('product_range')
                                                                      ->where('product_id', $product_id)
                                                                      ->where('from_range','<=',$from_range)
                                                                      ->where('to_range','>=',$from_range)
                                                                      ->whereNotIn('id', [$id])
                                                                      ->get();
                                        if($range_in_from)
                                        {
                                          if(count($range_in_from)>=1)
                                              {
                                                return response()->json([
                                                                         'status'=>$this->successStatus,
                                                                         'code'=>$this->successCode,
                                                                         'message'=>"Already exists from-Range for this Product"
                                                                        ], $this->successCode);
                                              }

                                        }

                                      }
                                      if($to_range==null)
                                      {
                                       $to_range = $range_already_group[0]->to_range;
                                     }else
                                      {
                                          $to_range = $to_range;
                                          $range_in_to = DB::table('product_range')
                                                          ->where('product_id', $product_id)
                                                          ->where('from_range','<=',$to_range)
                                                          ->where('to_range','>=',$to_range)
                                                          ->whereNotIn('id', [$id])
                                                          ->get();
                                          if($range_in_to)
                                          {
                                            if(count($range_in_to)>=1)
                                                {
                                                  return response()->json([
                                                                           'status'=>$this->successStatus,
                                                                           'code'=>$this->successCode,
                                                                           'message'=>"Already exists to-Range for this Product"
                                                                          ], $this->successCode);
                                                }
                                          }
                                      }
                                      if($profit==null)
                                      {
                                      $profit = $range_already_group[0]->profit;
                                      }else
                                      {
                                      $profit = $profit;
                                      }


                                             $product =  DB::table('product_range')
                                                   ->where('id',$id)
                                                   ->update([
                                                             'product_id' => $product_id,
                                                             'from_range'=>$from_range,
                                                             'to_range' => $to_range,
                                                             'profit'=>$profit,
                                                             'updated_at'=>$updated_at
                                                            ]);

                                             if($product=='1')
                                              {
                                                   $product_range_data = DB::table('product_range')->where('id', $id)->get();
                                                   return response()->json(['data'=>$product_range_data,
                                                                            'status'=>$this->successStatus,
                                                                            'code'=>$this->successCreatedCode,
                                                                            'message'=>"Product Range Updated Successfully"], 201);
                                             }
                                             else
                                             {

                                                  return response()->json(['status'=>$this->errorStatus,
                                                                           'code'=>$this->errorcode,
                                                                           'message'=>"Product Range Not Updated"], 400);
                                             }

                                        }
                                        else
                                        {
                                        return response()->json([
                                                              'status'=>$this->errorStatus,
                                                              'code'=>$this->errorcode,
                                                              'message'=>"Failed to find range"]);
                                        }

                               }
                             }else
                             {
                              return response()->json([
                                                      'status'=>$this->errorStatus,
                                                      'code'=>$this->errorcode,
                                                      'message'=>"Failed to Find Product Range"]);

                             }
                            }
                    }else
                    {
                        return response()->json([
                                                'status'=>$this->errorStatus,
                                                'code'=>$this->errorcode,
                                                'message'=>"Failed to Find Product Range"]);

                    }
                }
             }
            else
            {
              return response()->json([
                                      'status'=>$this->errorStatus,
                                      'code'=>$this->errorcode,
                                      'message'=>"Failed to Update Product Range"]);
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
        $product =  DB::table('product_range')->where('id', $id)->get();
        if($product)
          {
               if(count($product)<=0){
                  return response()->json(['status'=>$this->successStatus,
                                           'code'=>$this->successCode,
                                           'message'=>"Product Range Not Found for this id"], $this->successCode);
               }
               else{
                  $product =  DB::table('product_range')->where('id',$id)->delete();
                   if($product){
                     return response()->json(['data'=>null,
                                             'status'=>$this->successStatus,
                                             'code'=>"204",
                                             'message'=>"Product Range Deleted Successfully"], $this->successCode);
                   }
                 else{
                       return response()->json(['status'=>$this->errorStatus,
                                                'code'=>$this->errorcode,
                                                'message'=>"Product Range Not Deleted"], $this->successCode);
                   }
                 }
          }
          else{
            return response()->json([
                                    'status'=>$this->errorStatus,
                                    'code'=>$this->errorcode,
                                    'message'=>"Failed to Delete Product Range"]);
             }
     }
     else{
         return response()->json("Method not Allow", 405);
     }
     }
}
