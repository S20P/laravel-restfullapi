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
use Crypt;
class UserApiController extends Controller
{
  public $successStatus = "1";
  public $successMessage = "Success";
  public $successCode = "200";

  public $errorcode = "400";
  public $errorStatus ="0";
  public $errorMessage = "error";

  public $successCreatedCode="201";
  private $date_IST;

   public function __construct()
   {
       $this->middleware('jwt', ['except' => ['create','user_check','update_user_without_token']]);
       set_time_limit(0);

       date_default_timezone_set('Asia/Kolkata');
       $ist = date("Y-m-d g:i:s");
       $this->date_IST = date ("Y-m-d H:i:s", strtotime($ist));

   }

    public function index(Request $request)
     {
        if($request->isMethod('get'))
        {
             $user = User::all();
             $user_data = [];
                if($user)
                {
                        if(count($user)<=0){
                           return response()->json([
                                                    "data"=>$user,
                                                    'status'=>$this->successStatus,
                                                    'code'=>$this->successCode,
                                                    'message'=>"User is Empty"], $this->successCode);
                        }else{
                    

                         return response()->json([
                                                  "data"=>$user,
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
                                           'message'=>"Failed to Find User"]);
               }
           }
      else{
           return response()->json("Method not Allow", 405);
        }
   }

    public function show(Request $request,$id)
     {
       if($request->isMethod('get')){
         //Get Specific User

          $user = DB::table('user')->where('id', $id)->get();
              if($user)
              {
                 if(count($user)<=0){
                    return response()->json([
                                             'status'=>$this->successStatus,
                                             'code'=>$this->successCode,
                                             'message'=>"User Not Found for this id"], $this->successCode);
                 }
                 else{
                  $user = User::findOrFail($id);
                  return new UserResource($user);
                 }
               }
               else{
                 return response()->json([
                                         'status'=>$this->errorStatus,
                                         'code'=>$this->errorcode,
                                         'message'=>"Failed to Find User"]);
               }
           }
       else{
           return response()->json("Method not Allow", 405);
       }
     }

     public function create(Request $request)
     {
       //Create New User

      if($request->isMethod('post')){

      $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:user'
         ]);

         if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
           }
        else{
            $password =  $request->input('password');
            $phone = $request->input('phone');

               if($password!==null&&$phone==null){
                 $validator = Validator::make($request->all(), [
                          'password'=>'min:6'
                        ]);
                  if ($validator->fails()) {
                         return response()->json(['error'=>$validator->errors()], 401);
                       }
                  else{
                    $user = new User();
                    $user->username =  $request->input('username');
                    $user->password =  Hash::make($password);
                    $user->password_orignal =  $password;
                    $user->phone =  "";
                    $user->isadmin = "no";
                    $user->created_at = $this->date_IST;
                    $user->updated_at = $this->date_IST;
                    $user->save();
                    if($user->save()) {

                          return response()->json(['data'=>$user,
                                                   'status'=>$this->successStatus,
                                                   'code'=>$this->successCreatedCode,
                                                   'message'=>"User Inserted Successfully"], $this->successCode);
                    }
                    else{

                         return response()->json(['status'=>$this->errorStatus,
                                                  'code'=>$this->errorcode,
                                                  'message'=>"User Not Inserted"], 400);
                    }

                       }
               }
               elseif($password!==null&&$phone!==null){
                $validator = Validator::make($request->all(), [
                         'password'=>'min:6',
                         'phone'=>'min:10|numeric'
                       ]);
                 if ($validator->fails()) {
                        return response()->json(['error'=>$validator->errors()], 401);
                      }
                 else{
                   $user = new User();
                   $user->username =  $request->input('username');
                   $user->password =  Hash::make($password);
                   $user->password_orignal =  $password;
                   $user->phone = $phone;
                   $user->isadmin = "no";
                   $user->created_at = $this->date_IST;
                   $user->updated_at = $this->date_IST;
                   $user->save();
                   if($user->save()) {

                         return response()->json(['data'=>$user,
                                                  'status'=>$this->successStatus,
                                                  'code'=>$this->successCreatedCode,
                                                  'message'=>"User Inserted Successfully"], $this->successCode);
                   }
                   else{

                        return response()->json(['status'=>$this->errorStatus,
                                                 'code'=>$this->errorcode,
                                                 'message'=>"User Not Inserted"], 400);
                   }

                      }
              }
              elseif($password==null&&$phone!==null){
                $validator = Validator::make($request->all(), [
                         'phone'=>'min:10|numeric'
                       ]);
                 if ($validator->fails()) {
                        return response()->json(['error'=>$validator->errors()], 401);
                      }
                 else{
                   $user = new User();
                   $user->username =  $request->input('username');
                   $user->password =  "";
                   $user->password_orignal =  "";
                   $user->phone = $phone;
                   $user->isadmin = "no";
                   $user->created_at =$this->date_IST;
                   $user->updated_at = $this->date_IST;
                   $user->save();
                   if($user->save()) {

                         return response()->json(['data'=>$user,
                                                  'status'=>$this->successStatus,
                                                  'code'=>$this->successCreatedCode,
                                                  'message'=>"User Inserted Successfully"], $this->successCode);
                   }
                   else{

                        return response()->json(['status'=>$this->errorStatus,
                                                 'code'=>$this->errorcode,
                                                 'message'=>"User Not Inserted"], 400);
                   }

                      }
              }else{

                       $user = new User();
                       $user->username =  $request->input('username');
                       $user->password =  "";
                       $user->password_orignal =  "";
                       $user->phone = "";
                       $user->isadmin = "no";
                       $user->created_at = $this->date_IST;
                       $user->updated_at = $this->date_IST;
                       $user->save();
                       if($user->save()) {

                             return response()->json(['data'=>$user,
                                                      'status'=>$this->successStatus,
                                                      'code'=>$this->successCreatedCode,
                                                      'message'=>"User Inserted Successfully"], $this->successCode);
                       }
                       else{

                            return response()->json(['status'=>$this->errorStatus,
                                                     'code'=>$this->errorcode,
                                                     'message'=>"User Not Inserted"], 400);
                       }
                      }
           }
         }
         else{
             return response()->json("Method not Allow", 405);
         }

     }


     public function update(Request $request, $id)
     {
       //Update User
       if($request->isMethod('post'))
       {
         $validator = Validator::make($request->all(), [
               'username' => 'required|string',
               'password' => 'min:6',
            ]);

            if ($validator->fails()) {
               return response()->json(['error'=>$validator->errors()], 401);
              }
        else{
             $phone = $request->input('phone');
             
              
             $user = DB::table('user')->where('id', $id)->get();

                if($user)
                    {
                      if(count($user)<=0)
                          {
                                  return response()->json(['status'=>$this->successStatus,
                                                           'code'=>$this->successCode,
                                                           'error'=>"User Not Found for this id"], $this->successCode);
                           }
                      else{

                        if($phone!==null){
                          $validator = Validator::make($request->all(), [
                                   'phone'=>'min:10|numeric'
                                 ]);
                           if ($validator->fails()) {
                                  return response()->json(['error'=>$validator->errors()], 401);
                                }
                           else{
                            $phone_no = $phone;  
                           }
                          }else{
           
                            $phone_no = $user['0']->phone;
                          
                          }

                          $password =  $request->input('password');

                          if($password!==null){
                            $validator = Validator::make($request->all(), [
                                     'password'=>'min:6'
                                   ]);
                             if ($validator->fails()) {
                                    return response()->json(['error'=>$validator->errors()], 401);
                                  }
                             else{
                              $password = Hash::make($password);  
                              $password_orignal = $password;
                             }
                            }else{
                              $password = $user['0']->password;
                              $password_orignal =  $user['0']->password_orignal;
                            }



                           $username =  $request->input('username');
                           $isadmin =  "no";
                           
                           $updated_at = $this->date_IST;
                           $user_already = DB::table('user')
                                           ->where('username', $username)
                                           ->whereNotIn('id', [$id])
                                           ->get();

                             if($user_already){
                               if(count($user_already)>=1){
                                 return response()->json(['status'=>$this->errorStatus,
                                                           'code'=>$this->errorcode,
                                                           'error'=>"The username has already been taken."],404);
                               }
                               else{

                                 $user_result =  DB::table('user')
                                       ->where('id',$id)
                                       ->update([
                                                 'username' => $username,
                                                 'isadmin'=>$isadmin,
                                                 'password' => $password,
                                                 'password_orignal' => $password_orignal,
                                                 'phone'=>$phone_no,
                                                 'updated_at'=>$updated_at
                                                ]);

                                 if($user_result) {
                                   if($user_result==1){
                                     $user_data = User::findOrFail($id);
                                     //$user_data =  DB::table('user')->where('id',$id)->get();
                                       return response()->json([
                                                                'data'=>$user_data,
                                                                'status'=>$this->successStatus,
                                                                'code'=>$this->successCreatedCode,
                                                                'message'=>"User Updated Successfully"
                                                              ], 201);
                                  }
                                 }
                                 else{

                                      return response()->json(['status'=>$this->errorStatus,
                                                               'code'=>$this->errorcode,
                                                               'message'=>"User Not Updated"], 400);
                                 }
                               }
                             }  else{
                                 return response()->json([
                                                         'status'=>$this->errorStatus,
                                                         'code'=>$this->errorcode,
                                                         'message'=>"Failed to Update User"]);
                               }

                         }
                    }
           else{
             return response()->json([
                                     'status'=>$this->errorStatus,
                                     'code'=>$this->errorcode,
                                     'message'=>"Failed to Update User"]);
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

          $user = DB::table('user')->where('id', $id)->get();
         if($user)
           {
                if(count($user)<=0){
                   return response()->json(['status'=>$this->successStatus,
                                            'code'=>$this->successCode,
                                            'message'=>"User Not Found for this id"], $this->successCode);
                }
                else{
                    $user_result =  DB::table('user')->where('id',$id)->delete();
                    if($user_result){
                      return response()->json(['data'=>null,
                                              'status'=>$this->successStatus,
                                              'code'=>"204",
                                              'message'=>"User Deleted Successfully"], $this->successCode);
                    }
                  else{
                        return response()->json(['status'=>$this->errorStatus,
                                                 'code'=>$this->errorcode,
                                                 'message'=>"User Not Deleted"], $this->successCode);
                    }
                  }
           }
           else{
             return response()->json([
                                     'status'=>$this->errorStatus,
                                     'code'=>$this->errorcode,
                                     'message'=>"Failed to Delete User"]);
              }
     }
     else{
         return response()->json("Method not Allow", 405);
     }
     }


    public function user_check(Request $request){
      if($request->isMethod('post'))
      {           

        $username =  $request->input('username');
        $phone = $request->input('phone');

        if($phone!==null)
        {
              $validator = Validator::make($request->all(), [
                      'phone'=>'min:10|numeric'
                    ]);
              if ($validator->fails()) 
              {
                      return response()->json(['error'=>$validator->errors()], 401);
              }
              else
              {
                    $user_already = DB::table('user')
                    ->where('phone', $phone)
                    ->get();

                    if($user_already){
                      if(count($user_already)>=1){
                        return response()->json([
                                                "data"=>$user_already,
                                                'status'=>$this->successStatus,
                                                'code'=>$this->successCode,
                                                'message'=>$this->successMessage
                                              ],$this->successCode);
                      }
                      else{
                        return response()->json(['status'=>$this->successStatus,
                                                'code'=>$this->successCode,
                                                'message'=>"User Not Found"], $this->successCode);

                      }
                    }  else{
                        return response()->json([
                                                'status'=>$this->errorStatus,
                                                'code'=>$this->errorcode,
                                                'message'=>"Failed to check User"]);
                      } 
              }
          }
          elseif($username!==null)
          {
              $validator = Validator::make($request->all(), [
                'username' => 'required|string',
              ]);

              if ($validator->fails()) 
                {
                  return response()->json(['error'=>$validator->errors()], 401);
                }
                else
                {
                  $user_already = DB::table('user')
                  ->where('username', $username)
                  ->get();

                      if($user_already){
                        if(count($user_already)>=1){
                          return response()->json([
                                                  "data"=>$user_already,
                                                  'status'=>$this->successStatus,
                                                  'code'=>$this->successCode,
                                                  'message'=>$this->successMessage
                                                ],$this->successCode);
                        }
                        else{
                          return response()->json(['status'=>$this->successStatus,
                                                  'code'=>$this->successCode,
                                                  'message'=>"User Not Found"], $this->successCode);

                        }
                      }  else{
                          return response()->json([
                                                  'status'=>$this->errorStatus,
                                                  'code'=>$this->errorcode,
                                                  'message'=>"Failed to check User"]);
                        }

                }
          }
       
                         
                }
            else{
              return response()->json("Method not Allow", 405);
            }
    }




    public function update_user_without_token(Request $request, $id)
    {
      //Update User
      if($request->isMethod('post'))
      {
        $validator = Validator::make($request->all(), [
              'username' => 'required|string',
              'password' => 'required|min:6',
           ]);

           if ($validator->fails()) {
              return response()->json(['error'=>$validator->errors()], 401);
             }
             $phone = $request->input('phone');


            $user = DB::table('user')->where('id', $id)->get();
               if($user)
                   {
                     if(count($user)<=0)
                         {
                                 return response()->json(['status'=>$this->successStatus,
                                                          'code'=>$this->successCode,
                                                          'error'=>"User Not Found for this id"], $this->successCode);
                          }
                     else{

                      
                          if($phone!==null){
                            $validator = Validator::make($request->all(), [
                                    'phone'=>'min:10|numeric'
                                  ]);
                            if ($validator->fails()) {
                                    return response()->json(['error'=>$validator->errors()], 401);
                                  }
                            else{
                              $phone_no = $phone;  
                            }
                            }else{
                              $phone_no = $user['0']->phone;
                            }

                          $username =  $request->input('username');
                          $isadmin =  "no";
                          $password =  Hash::make($request->input('password'));
                          $updated_at = $this->date_IST;

                          $user_already = DB::table('user')
                                          ->where('username', $username)
                                          ->whereNotIn('id', [$id])
                                          ->get();

                            if($user_already){
                              if(count($user_already)>=1){
                                return response()->json(['status'=>$this->errorStatus,
                                                          'code'=>$this->errorcode,
                                                          'error'=>"The username has already been taken."],404);
                              }
                              else{
                                $user_result =  DB::table('user')
                                      ->where('id',$id)
                                      ->update([
                                                'username' => $username,
                                                'isadmin'=>$isadmin,
                                                'phone'=>$phone_no,
                                                'password' =>$password,
                                                'password_orignal' => $request->input('password'),
                                                'updated_at'=>$updated_at
                                               ]);

                                if($user_result) {
                                  if($user_result==1){
                                    $user_data = User::findOrFail($id);
                                    //$user_data =  DB::table('user')->where('id',$id)->get();
                                      return response()->json([
                                                               'data'=>$user_data,
                                                               'status'=>$this->successStatus,
                                                               'code'=>$this->successCreatedCode,
                                                               'message'=>"User Updated Successfully"
                                                             ], 201);
                                 }
                                }
                                else{

                                     return response()->json(['status'=>$this->errorStatus,
                                                              'code'=>$this->errorcode,
                                                              'message'=>"User Not Updated"], 400);
                                }
                              }
                            }  else{
                                return response()->json([
                                                        'status'=>$this->errorStatus,
                                                        'code'=>$this->errorcode,
                                                        'message'=>"Failed to Update User"]);
                              }

                        }
                   }
          else{
            return response()->json([
                                    'status'=>$this->errorStatus,
                                    'code'=>$this->errorcode,
                                    'message'=>"Failed to Update User"]);
          }
        }
    else{
        return response()->json("Method not Allow", 405);
    }
   }

   function deleteAlldata(Request $request)
   {
     if($request->isMethod('delete'))
     {
                           
      // purchase
      //expense

              //  $balance_deleted_id =  DB::table('expense')->delete();

              $balance_deleted_id = DB::table('product')->update(['last_price' => null]);

                 if($balance_deleted_id){
                   return response()->json(['data'=>null,
                                           'status'=>$this->successStatus,
                                           'code'=>"204",
                                           'message'=>"Table Record Deleted Successfully"], $this->successCode);
                 }
               else{
                     return response()->json(['status'=>$this->errorStatus,
                                              'code'=>$this->errorcode,
                                              'message'=>"Table Record Not Deleted"], $this->successCode);
                 }
                          
   }
   else{
       return response()->json("Method not Allow", 405);
   }
   }




}
