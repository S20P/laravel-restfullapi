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
use Image;
use File;
class ProductApiController extends Controller
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
          $this->middleware('jwt' ,['except' => ['range_test']]);
          set_time_limit(0);
          date_default_timezone_set('Asia/Kolkata');
          $ist = date("Y-m-d g:i:s");
          $this->date_IST = date ("Y-m-d H:i:s", strtotime($ist));
   }

    public function index(Request $request)
     {
      if($request->isMethod('get')){
         //Get All product
             $product = DB::table('product')->get();
             $product_range_data = [];
               if($product){
                       if(count($product)<=0){
                          return response()->json([
                                                   "data"=>$product,
                                                   'status'=>$this->successStatus,
                                                   'code'=>$this->successCode,
                                                   'message'=>"Product is Empty"], 200);
                       }else{

                         for($i=0;$i<count($product);$i++){
                           $product_range = DB::table('product_range')->where('product_id', $product[$i]->id)->get();
                           array_push($product_range_data,
                                  ["id"=>$product[$i]->id,
                                   "product_name"=>$product[$i]->product_name,
                                   "product_image"=>$product[$i]->product_image,
                                   "unit"=>$product[$i]->unit,
                                   "description"=>$product[$i]->description,
                                   "range"=>$product_range,
                                   "last_price"=>$this->number_format($product[$i]->last_price),
                                   "created_at"=>$product[$i]->created_at,
                                   "updated_at"=>$product[$i]->updated_at
                                  ]
                            );
                         }
                          return response()->json([
                                                   "data"=>$product_range_data,
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
                                          'message'=>"Failed to Find Product"]);
              }
       }
       else{
           return response()->json("Method not Allow", 405);
       }
   }

     public function show(Request $request,$id)
     {
         if($request->isMethod('get')){
            //Get Specific product
            $product_range_res = [];
             $product = DB::table('product')->where('id', $id)->get();

                  if($product){
                       if(count($product)<=0){
                          return response()->json([
                                                   'status'=>$this->successStatus,
                                                   'code'=>$this->successCode,
                                                   'message'=>"Product Not Found for this id"
                                                  ], $this->successCode);
                       }
                       else{

                         $product_range = DB::table('product_range')->where('product_id', $id)->get();
                         $i=0;
                         array_push($product_range_res,
                                ["id"=>$product[$i]->id,
                                 "product_name"=>$product[$i]->product_name,
                                 "product_image"=>$product[$i]->product_image,
                                 "unit"=>$product[$i]->unit,
                                 "description"=>$product[$i]->description,
                                 "range"=>$product_range,
                                 "last_price"=>$this->number_format($product[$i]->last_price),
                                 "created_at"=>$product[$i]->created_at,
                                 "updated_at"=>$product[$i]->updated_at
                               ]);


                         return response()->json([
                                                  "data"=>$product_range_res,
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
                                             'message'=>"Failed to Find Product"]);
                   }
             }
         else{
             return response()->json("Method not Allow", 405);
         }
     }

     public function create(Request $request)
     {
       //Create New Product
        if($request->isMethod('post')){

            $validator = Validator::make($request->all(), [
                  'product_name' => 'required|string|max:255|unique:product',
                  'unit'=>'required',
               ]);

               if ($validator->fails()) {
                  return response()->json(['error'=>$validator->errors()], 401);
                  }
              else{
                     $product_name =  $request->input('product_name');
                     $description =  $request->input('description');
                     $unit =  $request->input('unit');
                     $created_at =  $this->date_IST;

                     if ($request->hasFile('product_image'))
                      {
                       $validator = Validator::make($request->all(), [
                             'product_image'=>'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                          ]);

                          if ($validator->fails()) {
                             return response()->json(['error'=>$validator->errors()], 401);
                           }
                           else{
                             //file
                             $image = $request->file('product_image');
                             $destinationPath = 'images/product';
                             $extension = $image->getClientOriginalExtension();
                             $fileName = $product_name.".".$extension;
                             File::isDirectory($destinationPath) or File::makeDirectory($destinationPath, 0777, true, true);
                             $path =  $image->move($destinationPath,$fileName);
                             $product_image = asset($destinationPath.'/'.$fileName);

                             $product = DB::table('product')->insertGetId([
                                          'product_name' => $product_name,
                                          'description'=>trim(preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ',$description )),
                                          'product_image' => $product_image,
                                          'unit'=>$unit,
                                          'created_at'=>$created_at,
                                          'updated_at'=>$created_at
                                       ]);
                           }
                     }
                     else
                     {
                       $product = DB::table('product')->insertGetId([
                                    'product_name' => $product_name,
                                    'description'=>trim(preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ',$description )),
                                    'product_image' => "/",
                                    'unit'=>$unit,
                                    'created_at'=>$created_at,
                                    'updated_at'=>$created_at
                                 ]);
                     }


                     if($product)
                     {
                       $profit_list = ["200","175","150","100","75","50","40","35"];
                       $from_range = range(0.1, 200, 5);
                       $to_range = range(5,200,5);
                       for($i=0;$i<count($from_range);$i++){

                                if($i==0){
                                    $from_range_val = $i;
                                }
                                else{

                                  $from_range_val = $from_range[$i];
                                }


                             $to_range_val = $to_range[$i];

                             if(isset($profit_list[$i])){
                              $profit = $profit_list[$i];
                             }else{
                               $profit = "35";
                             }

                             $product_range_inserted_id = DB::table('product_range')->insert([
                                            'product_id' => $product,
                                            'from_range'=>$from_range_val,
                                            'to_range' => $to_range_val,
                                            'profit'=>$profit,
                                            'created_at'=>$created_at,
                                            'updated_at'=>$created_at
                                         ]);

                        }


                           $product_range_data = [];
                           $product_data = DB::table('product')->where('id', $product)->get();

                           for($i=0;$i<1;$i++){
                             $product_range = DB::table('product_range')->where('product_id', $product_data[$i]->id)->get();
                             array_push($product_range_data,
                                    ["id"=>$product_data[$i]->id,
                                     "product_name"=>$product_data[$i]->product_name,
                                     "product_image"=>$product_data[$i]->product_image,
                                     "unit"=>$product_data[$i]->unit,
                                     "description"=>$product_data[$i]->description,
                                     "range"=>$product_range,
                                     "created_at"=>$product_data[$i]->created_at,
                                     "updated_at"=>$product_data[$i]->updated_at
                                    ]
                              );
                           }

                           return response()->json(['data'=>$product_range_data,
                                                    'status'=>$this->successStatus,
                                                    'code'=>$this->successCreatedCode,
                                                    'message'=>"Product Inserted Successfully"], $this->successCode);
                     }
                     else{
                          return response()->json(['status'=>$this->errorStatus,
                                                   'code'=>$this->errorcode,
                                                   'message'=>"Product Not Inserted"], 400);
                     }
                 }
             }
         else{
             return response()->json("Method not Allow", 405);
            }
     }


     public function update(Request $request, $id)
     {
        //Update Product
        if($request->isMethod('post')){
             $validator = Validator::make($request->all(), [
                   'product_name' => 'required|string',
                ]);

              if ($validator->fails()) {
                 return response()->json(['error'=>$validator->errors()], 401);
                }

             $product = DB::table('product')->where('id', $id)->get();

              if($product)
              {
                if(count($product)<=0){
                   return response()->json(['status'=>$this->successStatus,
                                            'code'=>$this->successCode,
                                            'message'=>"Product Not Found for this id"], $this->successCode);
                }
                else{
                         $product_range_res = [];

                         $product_name =  $request->input('product_name');
                         $description =  $request->input('description');
                         $unit =  $request->input('unit');
                         $updated_at =  $this->date_IST;

                         $product_already = DB::table('product')
                                         ->where('product_name', $product_name)
                                         ->whereNotIn('id', [$id])
                                         ->get();

                           if($product_already){
                             if(count($product_already)>=1){
                               return response()->json(['error'=>"The product name has already been taken."]);
                             }else{


                               if($product_name==null){
                                 $product_name = $product[0]->product_name;
                               }else{
                                 $product_name = $product_name;
                               }
                               if($description==null){
                                 $description = $product[0]->description;
                               }else{
                                 $description = $description;
                               }
                               if($unit==null){
                                 $unit = $product[0]->unit;
                               }else{
                                 $unit = $unit;
                               }


                               if ($request->hasFile('product_image'))
                                {
                                 $validator = Validator::make($request->all(), [
                                       'product_image'=>'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                                    ]);

                                    if ($validator->fails()) {
                                       return response()->json(['error'=>$validator->errors()], 401);
                                     }
                                     else{
                                       //file
                                       $image = $request->file('product_image');
                                       $destinationPath = 'images/product';
                                       $extension = $image->getClientOriginalExtension();
                                       $fileName = $product_name.".".$extension;

                                       File::isDirectory($destinationPath) or File::makeDirectory($destinationPath, 0777, true, true);
                                       $path =  $image->move($destinationPath,$fileName);
                                       $product_image = asset($destinationPath.'/'.$fileName);

                                      $product =  DB::table('product')
                                             ->where('id',$id)
                                             ->update([
                                                       'product_name' => $product_name,
                                                       'description'=>trim(preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ',$description )),
                                                       'product_image' => $product_image,
                                                       'unit'=>$unit,
                                                       'updated_at'=>$updated_at
                                                      ]);
                                     }
                               }
                               else
                               {
                                 $product =  DB::table('product')
                                        ->where('id',$id)
                                        ->update([
                                                  'product_name' => $product_name,
                                                  'description'=>trim(preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ',$description )),
                                                  'unit'=>$unit,
                                                  'updated_at'=>$updated_at
                                                 ]);
                               }

                               if($product) {
                                    $product_data = DB::table('product')->where('id', $id)->get();
                                    $product_range = DB::table('product_range')->where('product_id', $id)->get();
                                    $i=0;
                                    array_push($product_range_res,
                                           ["id"=>$product_data[$i]->id,
                                            "product_name"=>$product_data[$i]->product_name,
                                            "product_image"=>$product_data[$i]->product_image,
                                            "unit"=>$product_data[$i]->unit,
                                            "description"=>$product_data[$i]->description,
                                            "range"=>$product_range,
                                            "last_price"=>$this->number_format($product_data[$i]->last_price),
                                            "created_at"=>$product_data[$i]->created_at,
                                            "updated_at"=>$product_data[$i]->updated_at
                                          ]);


                                     return response()->json(['data'=>$product_range_res,
                                                              'status'=>$this->successStatus,
                                                              'code'=>$this->successCreatedCode,
                                                              'message'=>"Product Updated Successfully"], 201);
                               }
                               else{

                                    return response()->json(['status'=>$this->errorStatus,
                                                             'code'=>$this->errorcode,
                                                             'message'=>"Product Not Updated"], 400);
                               }

                             }
                           }
                           else{
                               return response()->json([
                                                       'status'=>$this->errorStatus,
                                                       'code'=>$this->errorcode,
                                                       'message'=>"Failed to Update Product"]);
                                }

               }
             }
            else{
              return response()->json([
                                      'status'=>$this->errorStatus,
                                      'code'=>$this->errorcode,
                                      'message'=>"Failed to Update Product"]);
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
         $product = DB::table('product')->where('id', $id)->get();
        if($product)
          {
               if(count($product)<=0){
                  return response()->json(['status'=>$this->successStatus,
                                           'code'=>$this->successCode,
                                           'message'=>"Product Not Found for this id"], $this->successCode);
               }
               else{

                  $product =  DB::table('product')->where('id',$id)->delete();
                   if($product){
                     $product_range =  DB::table('product_range')->where('product_id', $id)->get();
                     if($product_range)
                       {
                            if(count($product_range)<=0){
                              return response()->json(['data'=>null,
                                                      'status'=>$this->successStatus,
                                                      'code'=>"204",
                                                      'message'=>"Product Deleted Successfully"], $this->successCode);  }
                            else{
                               $product_range_remove =  DB::table('product_range')->where('product_id',$id)->delete();
                                if($product_range_remove){
                                  return response()->json(['data'=>null,
                                                          'status'=>$this->successStatus,
                                                          'code'=>"204",
                                                          'message'=>"Product and Product Range Deleted Successfully"], $this->successCode);
                                }
                              else{
                                return response()->json(['data'=>null,
                                                        'status'=>$this->successStatus,
                                                        'code'=>"204",
                                                        'message'=>"Product Deleted Successfully"], $this->successCode);
                                }
                              }
                       }
                       else{
                         return response()->json(['data'=>null,
                                                 'status'=>$this->successStatus,
                                                 'code'=>"204",
                                                 'message'=>"Product Deleted Successfully"], $this->successCode);
                          }
                   }
                 else{
                       return response()->json(['status'=>$this->errorStatus,
                                                'code'=>$this->errorcode,
                                                'message'=>"Product Not Deleted"], $this->successCode);
                   }
                 }
          }
          else{
            return response()->json([
                                    'status'=>$this->errorStatus,
                                    'code'=>$this->errorcode,
                                    'message'=>"Failed to Delete Product"]);
             }
     }
     else{
         return response()->json("Method not Allow", 405);
     }
     }



     // public function open_form(){
     //      return view('fileupload');
     //   }

     public function product_image_upload(Request $request){

       if($request->isMethod('post')){

            $validator = Validator::make($request->all(), [
                  'file'=>'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
               ]);

             if ($validator->fails()) {
                return response()->json(['error'=>$validator->errors()], 401);
               }

             $image = $request->file('file');
            //   return response()->json($image->getClientOriginalName());
             $destinationPath = 'images/product';
             $fileName = rand(1, 999) .$image->getClientOriginalName();

             File::isDirectory($destinationPath) or File::makeDirectory($destinationPath, 0777, true, true);
             $path =  $image->move($destinationPath,$fileName);

             $image_path = asset($destinationPath.'/'.$fileName);
             return response()->json($image_path);



          }
          else{
              return response()->json("Method not Allow", 405);
          }
     }






   public function getLatestPriceOnProduct(Request $request)
   {
     if($request->isMethod('get')){

               $product_price_data = [];
               $product = DB::table('product')->get();
               if($product){
                    if(count($product)<=0){
                       return response()->json([
                                                'status'=>$this->successStatus,
                                                'code'=>$this->successCode,
                                                'message'=>"Price Not Found for this Product"
                                               ], $this->successCode);
                    }
                    else{

                      for($i=0;$i<count($product);$i++){
                        array_push($product_price_data,
                               ["id"=>$product[$i]->id,
                                "product_name"=>$product[$i]->product_name,
                                "product_image"=>$product[$i]->product_image,
                                "unit"=>$product[$i]->unit,
                                "last_price"=>$this->number_format($product[$i]->last_price),
                                "date"=>$product[$i]->date,
                                "description"=>$product[$i]->description,
                                "created_at"=>$product[$i]->created_at,
                                "updated_at"=>$product[$i]->updated_at
                              ]);
                      }





                      return response()->json([
                                               "data"=>$product_price_data,
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
                                          'message'=>"Failed to Find Last Price for Product"]);
                }




          }
      else{
          return response()->json("Method not Allow", 405);
      }

   }


//Add default ranges :  default. 0-200 (gap of 5) e.g. 0-5, 5.1-10, 10.1-15

public function range_test()
{


  $from_range = range(0.1, 200, 5);
  $to_range = range(5,200,5);
 $profit_list = ["200","175","150","100","75","50","40","35"];
  echo "<table>";

   echo "<tr><td>from-range</td><td>to-range</td><td>profit</td></tr>";

 //  print_r($range);
  for($i=0;$i<count($from_range);$i++){
    echo "<tr>";
           if($i==0){
               $from_range_val = $i;
           }
           else{

             $from_range_val = $from_range[$i];
           }
           echo "<td>";
         echo $from_range_val;
        echo "</td>";

        echo "<td>";
         //print_r($range);
        echo $to_range[$i];
        echo "</td>";

        echo "<td>";
         //print_r($range);
         if(isset($profit_list[$i])){
          $profit = $profit_list[$i];
         }else{
           $profit = "35";
         }
        echo $profit;
        echo "</td>";


     echo "</tr>";
  }


  echo "</table>";
}


public function add_Product_Record()
{
  $product_list = [ "GUNDA",
                    "SEEDLESS LEMON",
                    "METHO",
                    "DESI VALOR PAPDI",
                    "DESI MARCHA",
                    "DESI DUDHI",
                    "FLOWER CABBAGE",
                    "PIKADOR MARCHA",
                    "SURTI MARCHA",
                    "Cholli",
                    "AMERICAN MAKAI",
                    "KHEERA KAKADI",
                    "DESI KAKDI",
                    "WHITE KAKADI",
                    "SARAGVO",
                    "RINGAN (Butha)",
                    "Galka",
                    "PURPLE MOGRI",
                    "VATANA",
                    "Gavar",
                    "Bhinda",
                    "Dudhi",
                    "ROUND DUDHI",
                    "BLACK BUTHA",
                    "LIMBU",
                    "KACHI KERI",
                    "KACHA PAPAYA",
                    "CHOLA",
                    "GREEN BHATTA",
                    "RINGAN - Ravaiya Violet",
                    "GREEN RAVAIYA",
                    "Surti Papdi",
                    "Tinsa",
                    "Tindola",
                    "Tomato",
                    "NAYLON CHOLI",
                    "Turiya- Ridge Gourd",
                    "Valor Papdi",
                    "FANAS",
                    "KACHA TOMOTO",
                    "Tuver",
                    "RED PIKADOR MARCHA",
                    "RED SURTI MARCHA",
                    "Kolu",
                    "HIMSON TAMETA",
                    "FAFDA MARCHA",
                    "JODHPURI KACHA KELA",
                    "DESHI FLAWER",
                    "CHINESE KAKADI",
                    "SITARA MARCHA",
                    "PARVAL",
                    "RINGANI",
                    "SING",
                    "KACHA KELA",
                    "Fansi",
                    "Karela",
                    "LAMBA RINGAN",
                    "Cabbage",
                    "Capsicum Green",
                    "AMLA",
                    "KARAMDA",
                    "HAFUS",
                    "BADAMI KERI",
                    "GRAPES FRUIT",
                    "BLACK GRAPES BOX",
                    "KESAR KERI",
                    "ACHAR KERI",
                    "BOR-APPLE",
                    "Watermelon Black boy",
                    "GOLDEN APPLE",
                    "Chickoo",
                    "washington apple",
                    "naspati",
                    "Custard Apple",
                    "KIWI BOX",
                    "INDIAN ORANG",
                    "KASMIRI BUTTER",
                    "GUAVA",
                    "GREEN GRAPES",
                    "SITAFAL",
                    "Grapes Red California",
                    "GRAPES",
                    "HIMACHAL APPLE",
                    "ROYAL APPLE",
                    "IMPORTER SMALL ORANGE",
                    "KHATUMBDA",
                    "Kiwi",
                    "GREEN APPLE",
                    "ELAICHI BANANA",
                    "SITAFAL BOX",
                    "STRAWBERRY BOX",
                    "RED POMEGRANATE",
                    "MOSAMBI",
                    "Papaya",
                    "BIG SITAFAL",
                    "RED SITAFAL",
                    "Pineapple",
                    "Plum Imported",
                    "Pomegranate",
                    "APPLE THELI BAG",
                    "PINEAPPLE",
                    "MINI APPLE",
                    "TETI",
                    "BOR",
                    "BIG KIWI",
                    "MALTA ORANGE",
                    "ANJEER BOX",
                    "BLACK SUGAR CAN",
                    "LITCHI",
                    "BLACK GRAPES",
                    "PEAR BOX",
                    "SHIMLA APPLE",
                    "Guawa",
                    "HANUMAN FAL",
                    "AFGHAN APPLE",
                    "APRICOT",
                    "KIMIA",
                    "GOLDEN SITFAL",
                    "SMALL KIWI BOX",
                    "FUJI APPLE",
                    "LILI GRAPE",
                    "ALPHONSO MANGO BOX",
                    "ALPONO MANGO",
                    "IRAN APPLE",
                    "APPLE BOR",
                    "Dragon Fruits",
                    "AMRA FAL",
                    "GREEN COCONUT",
                    "STAR FRUTI",
                    "RAAS BERRY",
                    "WHITE JAMMU",
                    "KOLU DELICIOUS",
                    "GOLDEN SITAFAL BOX",
                    "SUN",
                    "SHIMLA STAR APPLE",
                    "STRAW BERRY",
                    "BOBY JAM",
                    "Orange Imp",
                    "MARWA KERI",
                    "Bell apple",
                    "Small Orange",
                    "GORAS AMLI",
                    "blue berry",
                    "Avacado",
                    "CALIFOINAI GRAPES",
                    "KIWI BERRY",
                    "JAMFAL",
                    "BANANA",
                    "PEAR",
                    "Fiji Apple",
                    "KASHMIRI APPLE",
                    "AMLI",
                    "SHAKKAR TETI",
                    "PATRA",
                    "TANDALJO",
                    "KOTHMIR",
                    "LILU LASAN",
                    "MOGRO",
                    "GREEN MOGRI",
                    "Methi",
                    "FUDINO",
                    "SUVANI BHAJI",
                    "LILI DUNGRI",
                    "CHANA NI BHAJI",
                    "MULA",
                    "PALAK",
                    "RED MULA",
                    "KOKAM",
                    "ALOVERA LEAF",
                    "LIMDO",
                    "KANDA MOGRI",
                    "VARACHA LILI LASAN",
                    "DESI KOTHMIR",
                    "LILI CHA",
                    "WAFER BATAKA",
                    "WHITE ONION",
                    "LASAN",
                    "AADU",
                    "RED HALDAR",
                    "Onion",
                    "Potato",
                    "SARGUM",
                    "Ratalu",
                    "ROUND RATALU",
                    "Suran",
                    "BABY POTATO",
                    "FOLELU LASAN",
                    "NEW BATAKA",
                    "NEW POTATO",
                    "JODHPURI GAJAR",
                    "SHAKARIYA",
                    "ADVI",
                    "WHITE HALDAR",
                    "Beet Root",
                    "GAJAR",
                    "MAKAI DANA",
                    "LETTUS",
                    "RED CABBAGE",
                    "YELLOW ZUCCHINI",
                    "SALARI",
                    "LEMON GRASS",
                    "KHIRU",
                    "Mashroom",
                    "AVACODA",
                    "LILA CHANA",
                    "MAG",
                    "SINGODA",
                    "MAG BOWL",
                    "POMEGRANAT BOWL",
                    "DESI CHANA",
                    "MATH",
                    "CELERY",
                    "BASIL LEAVES",
                    "KED BIJ",
                    "zuchini",
                    "CHERRY TOMATO",
                    "FROZEN VATANA",
                    "PARSLEY",
                    "PANEER",
                    "PAK CHOI",
                    "RED ROSE",
                    "Baby Corn Peeled",
                    "Brocolli",
                    "Capsicum Red",
                    "Capsicum Yellow",
                    ];



  $description = "";
  $unit = "kg";
  $created_at =  $this->date_IST;

  $list_length = count($product_list);
  
   
 $profit_list = ["200","175","150","100","75","50","40","35"];

  for($l=0;$l<count($product_list);$l++){

    $product = DB::table('product')->insertGetId([
               'product_name' => $product_list[$l],
               'description'=>trim(preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ',$description )),
               'product_image' => "/",
               'unit'=>$unit,
               'created_at'=>$created_at,
               'updated_at'=>$created_at
            ]);
            if($product)
            {
              $from_range = range(0.1, 200, 5);
              $to_range = range(5,200,5);
              for($i=0;$i<count($from_range);$i++){

                       if($i==0){
                           $from_range_val = $i;
                       }
                       else{
                         $from_range_val = $from_range[$i];
                       }


                    $to_range_val = $to_range[$i];
                    if(isset($profit_list[$i])){
                     $profit = $profit_list[$i];
                    }else{
                      $profit = "35";
                    }
                    $product_range_inserted_id = DB::table('product_range')->insert([
                                   'product_id' => $product,
                                   'from_range'=>$from_range_val,
                                   'to_range' => $to_range_val,
                                   'profit'=>$profit,
                                   'created_at'=>$created_at,
                                   'updated_at'=>$created_at
                                ]);

                                

               }
             
             }

           }
}




 public function getproduct()
 {
         $product = DB::table('product')->get();

         // $product = DB::table('product')
         //    ->join('product_range', 'product.id', '=', 'product_range.product_id')
         //    ->groupBy('product.id')
         //    ->get();
         //
         //    $product = DB::table('product')
         //                ->join('product_range', 'product.id', '=', 'product_range.product_id')
         //                ->get();

           $len = count($product);
            return response()->json([
                                     "data"=>$len,
                                     'status'=>$this->successStatus,
                                     'code'=>$this->successCode,
                                     'message'=>$this->successMessage
                                   ],$this->successCode);

 }



public function Removeallproduct()
{
  DB::table('product')->delete();
  DB::table('product_range')->delete();
}




public function product_list(Request $request)
{
  if($request->isMethod('get')){
    //Get All product
        $product = DB::table('product')->get();
      
          if($product){
                  if(count($product)<=0){
                     return response()->json([
                                              "data"=>$product,
                                              'status'=>$this->successStatus,
                                              'code'=>$this->successCode,
                                              'message'=>"Product is Empty"], 200);
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
                                     'message'=>"Failed to Find Product"]);
         }
  }
  else{
      return response()->json("Method not Allow", 405);
  }
}


public function number_format($number)
{
   //return number_format($number);
   return number_format($number, 2, '.', ',');
   
}

}
