<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Crypt;
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
   return parent::toArray($request);
      // if($this->isadmin=="no"){
      //   return [
      //       'id'=>$this->id,
      //       'username'=>$this->username,
      //       'password'=>$this->password_orignal,
      //       'isadmin'=>$this->isadmin,
      //       'created_at'=>$this->created_at,
      //       'updated_at'=>$this->updated_at,
      //   ];
      // }else{
      //   return "";
      // }

    }

    public function with($request){
      return [
          'status'=>"1",
          'code'=>"200",
          'message'=>"Success"
      ];
}

}
