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


class Balance_ApiController extends Controller
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

             $balance_all = DB::table('balance')->orderBy('date', 'asc')->get();
               if($balance_all){
                       if(count($balance_all)<=0){
                          return response()->json([
                                                   "data"=>$balance_all,
                                                   'status'=>$this->successStatus,
                                                   'code'=>$this->successCode,
                                                   'message'=>"Balance is Empty"], 200);
                       }else{


                         $balance_record = [];

                     for($i=0;$i<count($balance_all);$i++){

                       $user_name = DB::table('user')->where('id', $balance_all[$i]->user_id)->value('username');
                       array_push($balance_record,
                                    [
                                     "id"=>$balance_all[$i]->id,
                                     "user_id"=>$balance_all[$i]->user_id,
                                     "user_name"=>$user_name,
                                     "date"=>$balance_all[$i]->date,
                                     "balance"=>$balance_all[$i]->balance,
                                     "remaining_balance"=>$balance_all[$i]->remaining_balance,
                                     "created_at"=>$balance_all[$i]->created_at,
                                     "updated_at"=>$balance_all[$i]->updated_at,
                                    ]
                        );
                     }

                          return response()->json([
                                                   "data"=>$balance_record,
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
                                          'message'=>"Failed to Find Balance"]);
              }
       }
       else{
           return response()->json("Method not Allow", 405);
       }
   }

     public function show(Request $request,$id)
     {
         if($request->isMethod('get')){

             $balance_data = DB::table('balance')->orderBy('date', 'asc')->where('id', $id)->get();
                  if($balance_data){
                       if(count($balance_data)<=0){
                          return response()->json([
                                                   'status'=>$this->successStatus,
                                                   'code'=>$this->successCode,
                                                   'message'=>"Balance Not Found for this id"
                                                  ], $this->successCode);
                       }
                       else{
                         return response()->json([
                                                  "data"=>$balance_data,
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
                                             'message'=>"Failed to Find Balance"]);
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
                  'balance' => 'required|numeric',
                  'remaining_balance'=>'required|numeric',
                  'date'=>'required|date_format:Y-m-d'
               ]);

               if ($validator->fails()) {
                  return response()->json(['error'=>$validator->errors()], 401);
                  }
              else{
                     $user_id =  $request->input('user_id');
                     $balance =  $request->input('balance');
                     $remaining_balance =  $request->input('remaining_balance');
                     $date =  $request->input('date');
                     $created_at = $this->date_IST;

                  $balance_already = DB::table('balance')
                                              ->where('user_id', $user_id)
                                              ->where('date', $date)
                                              ->get();
                    if($balance_already){
                      if(count($balance_already)<=0){
                        $balance_inserted_id = DB::table('balance')->insertGetId([
                                     'user_id' => $user_id,
                                     'balance'=>$balance,
                                     'remaining_balance' => $remaining_balance,
                                     'date'=>$date,
                                     'created_at'=>$created_at,
                                     'updated_at'=>$created_at
                                  ]);
                        if($balance_inserted_id)
                        {
                              $balance_data = DB::table('balance')->where('id', $balance_inserted_id)->get();

                              $balance_record = [];

                          for($i=0;$i<count($balance_data);$i++){

                            $user_name = DB::table('user')->where('id', $balance_data[$i]->user_id)->value('username');
                            array_push($balance_record,
                                         [
                                          "id"=>$balance_data[$i]->id,
                                          "user_id"=>$balance_data[$i]->user_id,
                                          "user_name"=>$user_name,
                                          "date"=>$balance_data[$i]->date,
                                          "balance"=>$balance_data[$i]->balance,
                                          "remaining_balance"=>$balance_data[$i]->remaining_balance,
                                          "created_at"=>$balance_data[$i]->created_at,
                                          "updated_at"=>$balance_data[$i]->updated_at,
                                         ]
                             );
                          }



                              return response()->json(['data'=>$balance_record,
                                                       'status'=>$this->successStatus,
                                                       'code'=>$this->successCreatedCode,
                                                       'message'=>"Balance Inserted Successfully"], $this->successCode);
                        }
                        else{
                             return response()->json(['status'=>$this->errorStatus,
                                                      'code'=>$this->errorcode,
                                                      'message'=>"Balance Not Inserted"], 400);
                        }
                      }
                      else{
                        return response()->json([
                                                 'status'=>$this->successStatus,
                                                 'code'=>$this->successCode,
                                                 'message'=>"Balance is already assigned to this user"
                                                ], $this->successCode);
                      }
                    }else{
                      return response()->json([
                                              'status'=>$this->errorStatus,
                                              'code'=>$this->errorcode,
                                              'message'=>"Failed to add Balance"]);
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
                     'balance' => 'required|numeric',
                     'remaining_balance'=>'required|numeric',
                     'date'=>'required|date_format:Y-m-d'
                ]);

              if ($validator->fails()) {
                 return response()->json(['error'=>$validator->errors()], 401);
                }

             $balance_id = DB::table('balance')->where('id', $id)->get();

              if($balance_id)
              {
                if(count($balance_id)<=0){
                   return response()->json(['status'=>$this->successStatus,
                                            'code'=>$this->successCode,
                                            'message'=>"Balance Not Found for this id"], $this->successCode);
                }
                else{

                        $user_id =  $request->input('user_id');
                        $balance =  $request->input('balance');
                        $remaining_balance =  $request->input('remaining_balance');
                        $date =  $request->input('date');
                        $updated_at = $this->date_IST;

                        $balance_already = DB::table('balance')
                                                    ->where('user_id', $user_id)
                                                    ->where('date', $date)
                                                    ->whereNotIn('id', [$id])
                                                    ->get();
                          if($balance_already){
                            if(count($balance_already)>=1){
                              return response()->json([
                                                       'status'=>$this->successStatus,
                                                       'code'=>$this->successCode,
                                                       'message'=>"Balance is already assigned to this user",
                                                      ], $this->successCode);
                            }
                            else{

                                    $balance_updated_result = DB::table('balance')
                                          ->where('id',$id)
                                          ->where('user_id',$user_id)
                                          ->update([
                                                    'balance'=>$balance,
                                                    'remaining_balance' => $remaining_balance,
                                                    'date'=>$date,
                                                    'updated_at'=>$updated_at
                                                   ]);
                                    if($balance_updated_result=='1') {

                                          $balance_data = DB::table('balance')->where('id', $id)->get();


                                          $balance_record = [];

                                      for($i=0;$i<count($balance_data);$i++){

                                        $user_name = DB::table('user')->where('id', $balance_data[$i]->user_id)->value('username');
                                        array_push($balance_record,
                                                     [
                                                      "id"=>$balance_data[$i]->id,
                                                      "user_id"=>$balance_data[$i]->user_id,
                                                      "user_name"=>$user_name,
                                                      "date"=>$balance_data[$i]->date,
                                                      "balance"=>$balance_data[$i]->balance,
                                                      "remaining_balance"=>$balance_data[$i]->remaining_balance,
                                                      "created_at"=>$balance_data[$i]->created_at,
                                                      "updated_at"=>$balance_data[$i]->updated_at,
                                                     ]
                                         );
                                      }

                                          return response()->json(['data'=>$balance_record,
                                                                   'status'=>$this->successStatus,
                                                                   'code'=>$this->successCreatedCode,
                                                                   'message'=>"Balance Updated Successfully"], 201);
                                    }
                                    else{

                                         return response()->json(['status'=>$this->errorStatus,
                                                                  'code'=>$this->errorcode,
                                                                  'message'=>"Balance Not Updated"], 400);
                                    }
                           }
                          }
                          else{
                            return response()->json([
                                                    'status'=>$this->errorStatus,
                                                    'code'=>$this->errorcode,
                                                    'message'=>"Failed to Update Balance"]);
                          }

               }
             }
            else{
              return response()->json([
                                      'status'=>$this->errorStatus,
                                      'code'=>$this->errorcode,
                                      'message'=>"Failed to Update Balance"]);
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
         $balance_id = DB::table('balance')->where('id', $id)->get();
        if($balance_id)
          {
               if(count($balance_id)<=0){
                  return response()->json(['status'=>$this->successStatus,
                                           'code'=>$this->successCode,
                                           'message'=>"Balance Not Found for this id"], $this->successCode);
               }
               else{
                  $balance_deleted_id =  DB::table('balance')->where('id',$id)->delete();
                   if($balance_deleted_id){
                     return response()->json(['data'=>null,
                                             'status'=>$this->successStatus,
                                             'code'=>"204",
                                             'message'=>"Balance Deleted Successfully"], $this->successCode);
                   }
                 else{
                       return response()->json(['status'=>$this->errorStatus,
                                                'code'=>$this->errorcode,
                                                'message'=>"Balance Not Deleted"], $this->successCode);
                   }
                 }
          }
          else{
            return response()->json([
                                    'status'=>$this->errorStatus,
                                    'code'=>$this->errorcode,
                                    'message'=>"Failed to Delete Balance"]);
             }
     }
     else{
         return response()->json("Method not Allow", 405);
     }
     }



    


     public function list_balance_on_date(Request $request)
     {
       if($request->isMethod('post')){

            $date =  $request->input('date');
            $user_id =  $request->input('user_id');

            $query = DB::table('balance')
            ->leftJoin('user', function($join)
            {
                $join->on('balance.user_id', '=', 'user.id');
            })
            ->select(
               'balance.*',
               'user.username as user_name'
           )
           ->orderBy('balance.date', 'asc');

            if($date==null&&$user_id==null){
                 $balance_result =  $query->get();
            }

            elseif($date==null&&$user_id!==null){
                $balance_result =  $query->where('balance.user_id', $user_id)->get();
            }

            elseif($date!==null&&$user_id==null){
              $validator = Validator::make($request->all(), [
                       'date'=>'date_format:Y-m-d'
                     ]);
               if ($validator->fails()) {
                      return response()->json(['error'=>$validator->errors()], 401);
                    }
               else{
                 $balance_result =  $query->where('balance.date', $date)->get();
                    }

            }

            elseif($date!==null&&$user_id!==null){
              $validator = Validator::make($request->all(), [
                       'date'=>'date_format:Y-m-d'
                     ]);
                 if ($validator->fails()) {
                      return response()->json(['error'=>$validator->errors()], 401);
                    }
                else{
                $balance_result =  $query->where('balance.date', $date)->where('balance.user_id', $user_id)->get();
                    }
            }

            else{
              return response()->json([
                                      'status'=>$this->errorStatus,
                                      'code'=>$this->errorcode,
                                      'message'=>"error"]);
            }

                   if($balance_result){
                     if(count($balance_result)<=0){
                        return response()->json([
                                                 'status'=>$this->successStatus,
                                                 'code'=>$this->successCode,
                                                 'message'=>"Balance is empty"
                                                ], $this->successCode);
                       }else{


                        return response()->json([
                          "data"=>$balance_result,
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


 
   
}
