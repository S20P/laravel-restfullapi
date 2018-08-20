<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
Use App\User;  //User Model
use Validator;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Http\Resources\UserResource as UserResource;
use Hash;
use PDF;
use Image;
use File;
use Carbon;
class Expense_ApiController extends Controller
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
        if($request->isMethod('post')){
  
            $date =  $request->input('date');

            $query = DB::table('expense')
                          ->select('expense.*')
                          ->orderBy('date', 'asc');

            $user_auth  = Auth::guard('api')->user();

            $user_id = $user_auth->id;

            if($date!==null){
                $validator = Validator::make($request->all(), [
                'date'=>'required|date_format:Y-m-d'
                ]);

                if($validator->fails()) {
                    return response()->json(['error'=>$validator->errors()], 401);
                    } 
                else{
                  
                   if($user_id){
                        $expense =  $query
                        ->where('user_id','=',$user_id)
                        ->where('date','=',$date)
                        ->get();
                        }
                        else{
                            $expense =  $query->where('date','=',$date)
                            ->get();
                        }
               } 
            }
            elseif($date==null){ 

              // $expense = DB::table('expense')->get();

              if($user_id){
               $expense =  $query
               ->where('user_id','=',$user_id)
               ->get();
               }
               else{
                $expense = $query->get();
               }

            }
                 if($expense){
                     
                         if(count($expense)<=0){
                            return response()->json([
                                                     "data"=>$expense,
                                                     'status'=>$this->successStatus,
                                                     'code'=>$this->successCode,
                                                     'message'=>"expense is Empty"], 200);
                         }else{
 
                            return response()->json([
                                                     "data"=>$expense,
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
                                            'message'=>"Failed to Find expense"]);
                }
         }
         else{
             return response()->json("Method not Allow", 405);
         }
     }
  
       public function show(Request $request,$id)
       {
           if($request->isMethod('get')){

            $query = DB::table('expense')
            ->select('expense.*')
            ->orderBy('date', 'asc');
  
               $expense =  $query->where('id', $id)->get();
                    if($expense){
                         if(count($expense)<=0){
                            return response()->json([
                                                     'status'=>$this->successStatus,
                                                     'code'=>$this->successCode,
                                                     'message'=>"expense Not Found for this id"
                                                    ], $this->successCode);
                         }
                         else{
                          
                           return response()->json([
                                                    "data"=>$expense,
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
                                               'message'=>"Failed to Find expense"]);
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
                    'petrol' => 'required',
                    'police' => 'required',
                    'transport_rent' => 'required',
                    'date'=>'required|date_format:Y-m-d',
                 ]);
  
                 if ($validator->fails()) {
                    return response()->json(['error'=>$validator->errors()], 401);
                    }
                else{
                       $user_id =  $request->input('user_id');
                       $petrol =  $request->input('petrol');
                       $police =  $request->input('police');
                       $transport_rent =  $request->input('transport_rent');
                       $date =  $request->input('date');
                       $created_at = $this->date_IST;
  
                 $is_allredy_id =  DB::table('expense')
                                        ->where('user_id', $user_id)
                                        ->where('date',$date)
                                        ->get();

                   if($is_allredy_id){
                      if(count($is_allredy_id)<=0){

                        $user = DB::table('user')->where('id', $user_id)->get();

                        if($user){
                                  if(count($user)<=0)
                                  {
                                    return response()->json([
                                                             'status'=>$this->successStatus,
                                                             'code'=>$this->successCode,
                                                             'message'=>"User Not Found for this user_id"], $this->successCode);
                                  }
                                  else
                                  {

                                    $user_name = $user[0]->username;

                                    $expense_inserted_id = DB::table('expense')->insertGetId([
                                                'user_id' => $user_id,
                                                'user_name' => $user_name,
                                                'petrol'=>$petrol,
                                                'police' => $police,
                                                'transport_rent'=>$transport_rent,
                                                'date'=>$date,
                                                'created_at'=>$created_at,
                                                'updated_at'=>$created_at
                                            ]);

                                    if($expense_inserted_id)
                                    {

                                       // $expense_data = DB::table('expense')->where('id', $expense_inserted_id)->get();
                                       
                                        $total = $petrol + $police + $transport_rent;

                                        $balance_data = DB::table('balance')->where('user_id', $user_id)->where('date', $date)->get()->first();

                                        if($balance_data){

                                        $remaining_balance = $balance_data->remaining_balance;
                                        $remaining_balance_new = $remaining_balance - $total;

                                        $balance_result = DB::table('balance')
                                                      ->where('user_id',$user_id)
                                                      ->where('date',$date)
                                                      ->update([
                                                        'user_id' => $user_id,
                                                        'remaining_balance' => $remaining_balance_new,
                                                        'date'=>$date,
                                                        'updated_at'=>$created_at
                                                      ]);

                                       
                                        }else{
                                        $remaining_balance_new = 0 - $total;

                                        $balance_inserted_id = DB::table('balance')->insertGetId([
                                          'user_id' => $user_id,
                                          'balance'=>"0",
                                          'remaining_balance' =>$remaining_balance_new,
                                          'date'=>$date,
                                          'created_at'=>$created_at,
                                          'updated_at'=>$created_at
                                       ]);
                                       
                                       }

                                       
                                       $query = DB::table('expense')
                                       ->leftJoin('balance', function($join)
                                                 {
                                                     $join->on('expense.user_id', '=', 'balance.user_id');
                                                     $join->on( 'expense.date', '=', 'balance.date');
                                                 })
                                                 ->select(
                                                    'expense.id as expense_id',
                                                    'expense.user_id',
                                                    'expense.user_name',
                                                    'expense.petrol',
                                                    'expense.police', 
                                                    'expense.transport_rent',
                                                    'balance.id as balance_id',
                                                    'balance.balance',
                                                    'balance.remaining_balance',
                                                    'expense.date'
                                                );
                             

                                        //   $expense_data_record = $query->where('expense.id', $expense_inserted_id)->get(); 
                                          $expense_data_record = $query->where('expense.user_id', $user_id)->where('expense.date', $date)->get(); 

                                   
                                        return response()->json(['data'=>$expense_data_record,
                                                                'status'=>$this->successStatus,
                                                                'code'=>$this->successCreatedCode,
                                                                'message'=>"expense Inserted Successfully"], $this->successCode);

                                    }
                                    else{
                                        return response()->json(['status'=>$this->errorStatus,
                                                                'code'=>$this->errorcode,
                                                                'message'=>"expense Not Inserted"], 400);
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
                                                            'status'=>$this->successStatus,
                                                            'code'=>$this->successCode,
                                                            'message'=>"expense is already assigned"
                                                            ], $this->successCode);
                                }
                    }else{
                      return response()->json([
                                              'status'=>$this->errorStatus,
                                              'code'=>$this->errorcode,
                                              'message'=>"Failed to add expense"]);
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
                     'date'=>'required|date_format:Y-m-d'
                  ]);
  
                  if ($validator->fails()) {
                     return response()->json(['error'=>$validator->errors()], 401);
                     }
                 else{
  
  
                        $expense_is_id = DB::table('expense')->where('id', $id)->get();
  
                        if($expense_is_id){
                          if(count($expense_is_id)<=0){
                             return response()->json(['status'=>$this->successStatus,
                                                      'code'=>$this->successCode,
                                                      'message'=>"expense Not Found for this id"
                                                     ], $this->successCode);
                            }else{
  
                                $user_id =  $request->input('user_id');
                                $petrol =  $request->input('petrol');
                                $police =  $request->input('police');
                                $transport_rent =  $request->input('transport_rent');
                                $date =  $request->input('date');
                                $updated_at = $this->date_IST;
  
                              $is_allredy_order =  DB::table('expense')
                                                     ->where('user_id',$user_id)
                                                     ->where('date',$date)
                                                     ->whereNotIn('id', [$id])
                                                     ->get();
  
                            if($is_allredy_order){
                              if(count($is_allredy_order)>=1){
                                 return response()->json([
                                                          'status'=>$this->successStatus,
                                                          'code'=>$this->successCode,
                                                          'message'=>"Already exists this expense"
                                                         ], $this->successCode);
                                }else{
                                
                                        $user = DB::table('user')->where('id', $user_id)->get();
                                         if($user){
                                           if(count($user)<=0){
                                             return response()->json([
                                                                      'status'=>$this->successStatus,
                                                                      'code'=>$this->successCode,
                                                                      'message'=>"User Not Found for this user_id"], $this->successCode);
                                           }else{
  
                                             
                                            $user_name = $user[0]->username;
  
                                               $expense_this = DB::table('expense')
                                                                        ->where('id',$id)
                                                                        ->get();
  
                                                            $petrol_this =$expense_this[0]->petrol;
                                                            $police_this =$expense_this[0]->police;
                                                            $transport_rent_this =$expense_this[0]->transport_rent;
                                                            $user_name_this =$expense_this[0]->user_name;
  
                                               $last_balance =  $petrol_this + $police_this + $transport_rent_this;

  
                                               if($petrol==null){
                                                 $petrol_update = $petrol_this;
                                                
                                               }else{
                                                 $petrol_update = $petrol;
                                                
                                               }
                                               if($police==null){
                                                
                                                 $police_update = $police_this;
                                               }else{
                                                 $police_update = $police;
                                                 
                                               }
                                               if($transport_rent==null){
                                                 $transport_rent_update = $transport_rent_this;
                                               }else{
                                                 $transport_rent_update = $transport_rent;
                                               }
                                               if($user_name==null){
                                                 $user_name_update = $user_name_this;
                                               }else{
                                                 $user_name_update = $user_name;
                                               }
  
  
                                               $expense_result = DB::table('expense')
                                                    ->where('id',$id)
                                                    ->update([
                                                            'user_id' => $user_id,
                                                            'user_name' => $user_name_update,
                                                            'petrol'=>$petrol_update,
                                                            'police' => $police_update,
                                                            'transport_rent'=>$transport_rent_update,
                                                            'date'=>$date,
                                                            'updated_at'=>$updated_at
                                                          ]);
  
                                                        if($expense_result) {
                                                        
                                                            $total = $petrol_update + $police_update + $transport_rent_update;

                                                            $balance_data = DB::table('balance')->where('user_id', $user_id)->where('date', $date)->get()->first();
                    
                                                            if($balance_data){
                    
                                                            $remaining_balance = $balance_data->remaining_balance;
                                                            $remaining_balance_new = $remaining_balance + $last_balance - $total;
                   
                                                            $balance_result = DB::table('balance')
                                                                          ->where('user_id',$user_id)
                                                                          ->where('date',$date)
                                                                          ->update([
                                                                            'user_id' => $user_id,
                                                                            'remaining_balance' => $remaining_balance_new,
                                                                            'date'=>$date,
                                                                            'updated_at'=>$updated_at
                                                                          ]);
                    
                                                           
                                                            }else{
                                                            $remaining_balance_new = 0 - $total;
                    
                                                            $balance_inserted_id = DB::table('balance')->insertGetId([
                                                              'user_id' => $user_id,
                                                              'balance'=>"0",
                                                              'remaining_balance' =>$remaining_balance_new,
                                                              'date'=>$date,
                                                              'created_at'=>$updated_at,
                                                              'updated_at'=>$updated_at
                                                           ]);
                                                           
                                                           }
                    
                                                           
                                                           $query = DB::table('expense')
                                                           ->leftJoin('balance', function($join)
                                                                     {
                                                                         $join->on('expense.user_id', '=', 'balance.user_id');
                                                                         $join->on( 'expense.date', '=', 'balance.date');
                                                                     })
                                                                     ->select(
                                                                        'expense.id as expense_id',
                                                                        'expense.user_id',
                                                                        'expense.user_name',
                                                                        'expense.petrol',
                                                                        'expense.police', 
                                                                        'expense.transport_rent',
                                                                        'balance.id as balance_id',
                                                                        'balance.balance',
                                                                        'balance.remaining_balance',
                                                                        'expense.date'
                                                                    );
                    
                                                           
                                                              $expense_data_record = $query->where('expense.user_id', $user_id)->where('expense.date', $date)->get(); 
                    

                                                             return response()->json(['data'=>$expense_data_record,
                                                                                      'status'=>$this->successStatus,
                                                                                      'code'=>$this->successCreatedCode,
                                                                                      'message'=>"expense Updated Successfully"], 201);
                                                       }
                                                       else{
                        
                                                            return response()->json(['status'=>$this->errorStatus,
                                                                                     'code'=>$this->errorcode,
                                                                                     'message'=>"expense Not Updated"], 400);
                                                       }


                                            
                                           }
                                         }else{
                                           return response()->json([
                                                                   'status'=>$this->errorStatus,
                                                                   'code'=>$this->errorcode,
                                                                   'message'=>"Failed to Find User"]);
                                         }
                                
  
  
                                }
                            }else{
                                return response()->json([
                                                        'status'=>$this->errorStatus,
                                                        'code'=>$this->errorcode,
                                                        'message'=>"Failed to Find expense"]);
  
                            }
  
                            }
                            }else{
                                return response()->json([
                                                        'status'=>$this->errorStatus,
                                                        'code'=>$this->errorcode,
                                                        'message'=>"Failed to Find expense"]);
  
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
           $expense = DB::table('expense')->where('id', $id)->get();
          if($expense)
            {
                 if(count($expense)<=0){
                    return response()->json(['status'=>$this->successStatus,
                                             'code'=>$this->successCode,
                                             'message'=>"expense  Not Found for this id"], $this->successCode);
                 }
                 else{
                    $expense_deleted =  DB::table('expense')->where('id',$id)->delete();
                     if($expense_deleted){
                       return response()->json(['data'=>null,
                                               'status'=>$this->successStatus,
                                               'code'=>"204",
                                               'message'=>"expense Deleted Successfully"], $this->successCode);
                     }
                   else{
                         return response()->json(['status'=>$this->errorStatus,
                                                  'code'=>$this->errorcode,
                                                  'message'=>"expense  Not Deleted"], $this->successCode);
                     }
                   }
            }
            else{
              return response()->json([
                                      'status'=>$this->errorStatus,
                                      'code'=>$this->errorcode,
                                      'message'=>"Failed to Delete expense "]);
               }
       }
       else{
           return response()->json("Method not Allow", 405);
       }
       }
  
  //  expense Report---------------------------------------------------------

  public function expenseReport(Request $request){
    if($request->isMethod('post')){

         $from_date =  $request->input('from-date');
         $to_date =  $request->input('to-date');
         $user_id =  $request->input('user_id');
         $date =  $request->input('date');

         $query = DB::table('expense')
                     ->select('expense.*')
                     ->orderBy('date', 'asc');


            if($user_id!==null&&$to_date==null&&$from_date==null&&$date==null){
                        $expense_result =  $query->where('user_id', $user_id)->get();
                        $expenseReport = true; 
                }
            elseif($user_id!==null&&$date!==null&&$to_date==null&&$from_date==null){
                        $validator = Validator::make($request->all(), [
                        'date'=>'required|date_format:Y-m-d'
                        ]);

                    if($validator->fails()) {
                        return response()->json(['error'=>$validator->errors()], 401);
                        } 
                    else{
                        $expense_result =  $query
                                            ->where('user_id', $user_id)
                                            ->where('date','=',$date)
                                            ->get();
                    } 
                    $expenseReport = true; 
            } 
            elseif($date!==null&&$user_id==null&&$to_date==null&&$from_date==null){
                    $validator = Validator::make($request->all(), [
                    'date'=>'required|date_format:Y-m-d'
                    ]);

                    if($validator->fails()) {
                        return response()->json(['error'=>$validator->errors()], 401);
                        } 
                    else{
                        $expense_result =  $query
                                            ->where('date','=',$date)
                                            ->get();
                    } 
                }    
            elseif($user_id!==null&&$to_date!==null&&$from_date==null&&$date==null){
                            $validator = Validator::make($request->all(), [
                            'to-date'=>'required|date_format:Y-m-d'
                            ]);

                        if($validator->fails()) {
                            return response()->json(['error'=>$validator->errors()], 401);
                            } 
                        else{
                            $expense_result =  $query
                                                ->where('user_id', $user_id)
                                                ->where('date','<=',$to_date)
                                                ->get();
                        } 
                 $expenseReport = true;

                }
                elseif($user_id!==null&&$to_date==null&&$from_date!==null&&$date==null){
                            $validator = Validator::make($request->all(), [
                                'from-date'=>'required|date_format:Y-m-d',
                            ]);

                            if($validator->fails()) {
                                return response()->json(['error'=>$validator->errors()], 401);
                            } 
                            else{
                                $expense_result =  $query
                                ->where('user_id', $user_id)
                                ->where('date','>=',$from_date)
                                ->get();
                            } 
                $expenseReport = true;         
                }
                elseif($user_id==null&&$to_date!==null&&$from_date==null&&$date==null){
                            $validator = Validator::make($request->all(), [
                                'to-date'=>'required|date_format:Y-m-d'
                            ]);

                            if($validator->fails()) {
                                return response()->json(['error'=>$validator->errors()], 401);
                            } 
                            else{
                                $expense_result =  $query
                                ->where('date','<=',$to_date)
                                ->get();
                            }  
                }
                elseif($user_id==null&&$to_date==null&&$from_date!==null&&$date==null){
                                
                            $validator = Validator::make($request->all(), [
                                'from-date'=>'required|date_format:Y-m-d',
                            ]);

                            if($validator->fails()) {
                                return response()->json(['error'=>$validator->errors()], 401);
                            } 
                            else{
                            $expense_result =  $query
                            ->where('date','>=',$from_date)
                            ->get();
                            }   
                    
                }
            elseif($user_id!==null&&$to_date!==null&&$from_date!==null&&$date==null){

                        $validator = Validator::make($request->all(), [
                            'from-date'=>'required|date_format:Y-m-d',
                            'to-date'=>'required|date_format:Y-m-d'
                        ]);

                        if($validator->fails()) {
                            return response()->json(['error'=>$validator->errors()], 401);
                        } 
                        else{
                        $expense_result =  $query
                        ->where('user_id', $user_id)
                        ->whereBetween('date', [$from_date, $to_date])
                        ->get();
                        } 
          $expenseReport = true;     
                        
                }
                elseif($user_id==null&&$to_date!==null&&$from_date!==null&&$date==null){

                        $validator = Validator::make($request->all(), [
                            'from-date'=>'required|date_format:Y-m-d',
                            'to-date'=>'required|date_format:Y-m-d'
                        ]);

                        if($validator->fails()) {
                            return response()->json(['error'=>$validator->errors()], 401);
                        } 
                        else{
                        $expense_result =  $query
                        ->whereBetween('date', [$from_date, $to_date])
                        ->get();
                        } 
                    
                }
                elseif($user_id==null&&$to_date==null&&$from_date==null&&$date==null){
                
                $expense_result =  $query->get();
                    
                }


                if($expense_result){
                  if(count($expense_result)<=0){
                     return response()->json([
                                              'status'=>$this->successStatus,
                                              'code'=>$this->successCode,
                                              'message'=>"expense is empty"
                                             ], $this->successCode);
                    }else{
                        
                        // return $purchase_record;
                            view()->share('data',$expense_result);
                           
                            // return view('purchase_pdfview');
                           
                            if($user_id!==null){
                              view()->share('expenseReport',$expenseReport);
                            }
                            if($from_date!==null){
                             // $from_date = Carbon::parse($from_date)->format('d/m/Y');
                              view()->share('from_date',$from_date);
                            }
                            if($to_date!==null){
                             // $to_date = Carbon::parse($from_date)->format('d/m/Y');
                              view()->share('to_date',$to_date);
                            }
                            if($date!==null){
                                // $to_date = Carbon::parse($from_date)->format('d/m/Y');
                                 view()->share('current_date',$date);
                            }

                                     $pdf = PDF::loadView('ExpenseReport_pdfview');
                                    // return $pdf->download('purchase.pdf');

                                     $destinationPath = "pdf";
                                     File::isDirectory($destinationPath) or File::makeDirectory($destinationPath, 0777, true, true);

                                      //make filename
                                         $ldate = date('d-m-Y');
                                         $t=time();
                                         $filename = "ExpenseReport-".$ldate."-".$t.".pdf";

                                      $pdf->save('pdf/'.$filename);
                                     if( $pdf->save('pdf/'.$filename)){
                                       $file_storage_path = asset("pdf/".$filename);
                                       return response()->json([
                                                                "data"=>$expense_result,
                                                                "file"=>$file_storage_path,
                                                                'status'=>$this->successStatus,
                                                                'code'=>$this->successCode,
                                                                'message'=>$this->successMessage
                                                              ],$this->successCode);
                                     }
                                     else{
                                       return response()->json([
                                                               'status'=>$this->errorStatus,
                                                               'code'=>$this->errorcode,
                                                               'message'=>"Failed to generate pdf file"]);
                                     }

                    }
                  }
                else{
                    return response()->json([
                                            'status'=>$this->errorStatus,
                                            'code'=>$this->errorcode,
                                            'message'=>"Failed to Find expense"]);

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
