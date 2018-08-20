<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;
Use App\User;  //User Model
use Validator;
use DB;
use JWTAuth;


class AuthController extends Controller
{

  public $successStatus = "1";
  public $successCode = "200";

      /**
       * Create a new AuthController instance.
       *
       * @return void
       */
      public function __construct()
      {
        $this->middleware('jwt', ['except' => ['login']]);
      }

      /**
       * Get a JWT via given credentials.
       *
       * @return \Illuminate\Http\JsonResponse
       */
      public function login(Request $request)
      {

        $validator = Validator::make($request->all(), [
              'username' => 'required|string',
              'password' => 'required|string|min:6',
           ]);

           if ($validator->fails()) {
              return response()->json(['error'=>$validator->errors()], 401);
             }
           $credentials = request(['username', 'password']);
      $credentials = $request->only('username', 'password');
         if(! $token =  JWTAuth::attempt($credentials)){
                    return response()->json(['error'=>'Unauthorised'], 401);
                }

                if ( JWTAuth::attempt($credentials)) {
                    $user = auth()->user();
                  //  $user['token'] =  $this->respondWithToken($token);
                    $token = $this->respondWithToken($token);
                    $user['token'] = $token->original;
                    return response()->json(['success' => $user->toArray(),
                                              'status'=>$this->successStatus,
                                              'code'=>$this->successCode,
                                            ], $this->successCode);
                  }
                //
                // return response()->json([
                //     'data' => $user->toArray(),"Token"=>$token
                // ]);
                //  }


       //    $credentials = request(['email', 'password']);
       //
       //    if (! $token = auth()->attempt($credentials)) {
       //        return response()->json(['error' => 'Unauthorized'], 401);
       //    }
       //    if (auth()->attempt($credentials)) {
       //      $user = auth()->user();
       //
       // return response()->json([
       //     'data' => $user->toArray(),"Token"=>$token
       // ]);
       //  }

        //  return $this->respondWithToken($token);
      }

      /**
       * Get the authenticated User.
       *
       * @return \Illuminate\Http\JsonResponse
       */
      public function me()
      {
          $user = auth()->user();
          if($user){
            return response()->json(['data' =>$user]);
          }
          else{
           return response()->json(['data' =>$user,"message"=>"Please Login"]);
          }

      }

      /**
       * Log the user out (Invalidate the token).
       *
       * @return \Illuminate\Http\JsonResponse
       */
      public function logout()
      {
          auth()->logout();

          return response()->json(['message' => 'Successfully logged out'],200);
      }

      /**
       * Refresh a token.
       *
       * @return \Illuminate\Http\JsonResponse
       */
      public function refresh()
      {
          return $this->respondWithToken(auth()->refresh());
      }

      /**
       * Get the token array structure.
       *
       * @param  string $token
       *
       * @return \Illuminate\Http\JsonResponse
       */
      protected function respondWithToken($token)
      {
          return response()->json([
              'access_token' => $token,
          ]);
    //Backup --------Remove Token type above ----------------------------------------------------------
          // return response()->json([
          //     'access_token' => $token,
          //     'token_type' => 'bearer',
          //     'expires_in' => auth()->factory()->getTTL() * 60
          // ]);
      }
      public function payload()
      {
          return $payload = auth()->payload();
      }
}
