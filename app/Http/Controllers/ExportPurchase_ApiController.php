<?php

namespace App\Http\Controllers;

use DB;
use File;
use Illuminate\Http\Request;
use PDF;
use Validator;
use Carbon;
use DateTime;
class ExportPurchase_ApiController extends Controller
{
    public $successStatus = "1";
    public $successMessage = "Success";
    public $successCode = "200";

    public $errorcode = "400";
    public $errorStatus = "0";
    public $errorMessage = "error";

    public $successCreatedCode = "201";
    
    public $date_IST;
    public function __construct()
    {
        $this->middleware('jwt');
        set_time_limit(0);

        date_default_timezone_set('Asia/Kolkata');
        $ist = date("Y-m-d g:i:s");
        $this->date_IST = date ("Y-m-d H:i:s", strtotime($ist));
      
    }

    public function Export_Purchase_file(Request $request)
    {

        if ($request->isMethod('post')) {

            $date = $request->input('date');
            $user_id = $request->input('user_id');

            $from_date = $request->input('from-date');
            $to_date = $request->input('to-date');

            if ($from_date !== null && $to_date !== null) {
                $validator = Validator::make($request->all(), [
                    'from-date' => 'required|date_format:Y-m-d',
                    'to-date' => 'required|date_format:Y-m-d',
                ]);
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 401);
                }
            } elseif ($from_date !== null && $to_date == null) {
                $validator = Validator::make($request->all(), [
                    'from-date' => 'required|date_format:Y-m-d',
                ]);
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 401);
                }
            } elseif ($from_date == null && $to_date !== null) {
                $validator = Validator::make($request->all(), [
                    'to-date' => 'required|date_format:Y-m-d',
                ]);
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 401);
                }
            }

            $table = DB::table('purchase');

            $query = DB::table('purchase')

                ->leftJoin('user', function ($join) {
                    $join->on('purchase.user_id', '=', 'user.id');
                }
                )
                ->leftJoin('product', function ($join) {
                    $join->on('purchase.product_id', '=', 'product.id');
                }
                )
                ->leftJoin('balance', function ($join) {
                    $join->on('purchase.user_id', '=', 'balance.user_id');
                    $join->on('purchase.date', '=', 'balance.date');
                })
                ->orderBy('purchase.date', 'asc')
                ->select('product.*',
                         'purchase.*',
                         'balance.balance',
                         'balance.remaining_balance',
                         'balance.id as balance_id',
                         'user.username as user_name');

            $total_sum = DB::raw("SUM(total_amount + weight_labour + transport_labour)");

            // dayReport ----------------------------------------------------------------
            if ($date == null && $user_id == null) {
                $purchase_result = $query->get();
                $purchase_cost_total = $table->value($total_sum);
                $dayReport = true;
            } elseif ($date !== null && $user_id == null) {
                $validator = Validator::make($request->all(), [
                    'date' => 'date_format:Y-m-d',
                ]);
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 401);
                } else {
                    $purchase_result = $query->where('purchase.date', $date)->get();
                    $purchase_cost_total = $table->where('date', '=', $date)->value($total_sum);

                    $dayReport = true;
                }

            }

//  *dayReport------------------------------------------------------------------

// userReport ------------------------------------------------------------------
            elseif ($date == null && $user_id !== null) {

                if ($from_date == null && $to_date == null) {
                    $purchase_result = $query->where('purchase.user_id', $user_id)->get();

                    $purchase_cost_total = $table
                        ->where('user_id', '=', $user_id)
                        ->value($total_sum);
                } elseif ($from_date !== null && $to_date !== null) {

                    $purchase_result = $query
                        ->where('purchase.user_id', $user_id)
                        ->whereBetween('purchase.date', [$from_date, $to_date])
                        ->get();

                    $purchase_cost_total = $table
                        ->where('user_id', '=', $user_id)
                        ->whereBetween('date', [$from_date, $to_date])
                        ->value($total_sum);
                } elseif ($from_date !== null && $to_date == null) {

                    $purchase_result = $query
                        ->where('purchase.user_id', $user_id)
                        ->where('purchase.date', '>=', $from_date)
                        ->get();

                    $purchase_cost_total = $table
                        ->where('user_id', '=', $user_id)
                        ->where('date', '>=', $from_date)
                        ->value($total_sum);
                } elseif ($from_date == null && $to_date !== null) {

                    $purchase_result = $query
                        ->where('purchase.user_id', $user_id)
                        ->where('purchase.date', '<=', $to_date)
                        ->get();

                    $purchase_cost_total = $table
                        ->where('user_id', '=', $user_id)
                        ->where('date', '<=', $to_date)
                        ->value($total_sum);
                }

                $userReport = true;
            }
// *userReport ------------------------------------------------------------------

            // currentReport ------------------------------------------------------------------

            elseif ($date !== null && $user_id !== null) {
                $validator = Validator::make($request->all(), [
                    'date' => 'date_format:Y-m-d',
                ]);
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 401);
                } else {
                    $purchase_result = $query->where('purchase.date', $date)->where('purchase.user_id', $user_id)->get();
                    $purchase_cost_total = $table
                        ->where('user_id', $user_id)
                        ->where('date', '=', $date)
                        ->value($total_sum);

                    $currentReport = true;

                }

            }
            // *currentReport ------------------------------------------------------------------
            else {
                return response()->json([
                    'status' => $this->errorStatus,
                    'code' => $this->errorcode,
                    'message' => "error"]);
            }

            if ($purchase_result) {
                if (count($purchase_result) <= 0) {
                    return response()->json([
                        'status' => $this->successStatus,
                        'code' => $this->successCode,
                        'message' => "Product Purchase is empty",
                    ], $this->successCode);
                } else {

                    $purchase_record = json_decode($purchase_result);
                   

                    view()->share('data', $purchase_record);
                    if ($purchase_cost_total) {
                        view()->share('totalCost', $purchase_cost_total);
                    }

                    if (isset($userReport)) {
                        if ($userReport == true) {
                            view()->share('userReport', "true");
                            if ($from_date !== null) {
                                // $from_date = Carbon::parse($from_date)->format('d/m/Y');
                                view()->share('from_date', $from_date);
                            }
                            if ($to_date !== null) {
                                // $to_date = Carbon::parse($from_date)->format('d/m/Y');
                                view()->share('to_date', $to_date);
                            }
                        }
                    }
                    if (isset($dayReport)) {
                        if ($dayReport == true) {
                            view()->share('dayReport', "true");
                        }
                    }

                    if (isset($currentReport)) {
                        if ($currentReport == true) {
                            view()->share('currentReport', "true");
                        }
                    }

                    if ($date !== null) {
                        //  $date = Carbon::parse($date)->format('d/m/Y');
                        view()->share('current_date', $date);
                    }

                    // return view('purchase_pdfview');

                    $pdf = PDF::loadView('purchase_pdfview');
                    // return $pdf->download('purchase.pdf'); //............*direct download file
                    $destinationPath = "pdf";

                    //check folder is or not
                    File::isDirectory($destinationPath) or File::makeDirectory($destinationPath, 0777, true, true);

                    //make filename
                    $ldate = date('d-m-Y');
                    $t = time();
                    $filename = "p" . $purchase_record[0]->product_id . "-" . $ldate . "-" . $t . ".pdf";

                    //save file
                    $pdf->save('pdf/' . $filename);
                    if ($pdf->save('pdf/' . $filename)) {
                        $file_storage_path = asset("pdf/" . $filename);
                        return response()->json(["data" => $purchase_record,
                            "totalCost" => $purchase_cost_total,
                            "file" => $file_storage_path,
                            'status' => $this->successStatus,
                            'code' => $this->successCode,
                            'message' => $this->successMessage,
                        ], $this->successCode);
                    } else {
                        return response()->json([
                            'status' => $this->errorStatus,
                            'code' => $this->errorcode,
                            'message' => "Failed to generate pdf file"]);
                    }

                }
            } else {
                return response()->json([
                    'status' => $this->errorStatus,
                    'code' => $this->errorcode,
                    'message' => "Failed to Find Product Purchase"]);

            }

        } else {
            return response()->json("Method not Allow", 405);
        }

    }

    public function list_Purchase_on_date(Request $request)
    {
        if ($request->isMethod('post')) {

            $date = $request->input('date');
            $user_id = $request->input('user_id');

            $table = DB::table('purchase');

            $query = DB::table('purchase')
                ->leftJoin('expense', function ($join) {
                    $join->on('purchase.user_id', '=', 'expense.user_id');
                    $join->on('purchase.date', '=', 'expense.date');
                }
                )
                ->leftJoin('user', function ($join) {
                    $join->on('purchase.user_id', '=', 'user.id');
                }
                )
                ->leftJoin('product', function ($join) {
                    $join->on('purchase.product_id', '=', 'product.id');
                }
                )
                ->leftJoin('balance', function ($join) {
                    $join->on('purchase.user_id', '=', 'balance.user_id');
                    $join->on('purchase.date', '=', 'balance.date');
                })
                ->orderBy('purchase.date', 'asc')
                ->select('product.*', 'purchase.*', 'balance.balance', 'balance.remaining_balance', 'balance.id as balance_id', 'expense.petrol', 'expense.id as expense_id', 'expense.police', 'expense.transport_rent', 'user.username as user_name');

            $total_sum = DB::raw("SUM(total_amount + weight_labour + transport_labour)");

            if ($date == null && $user_id !== null) {
                $purchase_result = $query->where('purchase.user_id', $user_id)->get();
                $purchase_cost_total = $table->where('user_id', '=', $user_id)->value($total_sum);
            } else if ($date !== null && $user_id == null) {
                $validator = Validator::make($request->all(), [
                    'date' => 'date_format:Y-m-d',
                ]);
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 401);
                } else {
                    $purchase_result = $query->where('purchase.date', $date)->get();
                    $purchase_cost_total = $table->where('date', '=', $date)->value($total_sum);
                }

            } else if ($date !== null && $user_id !== null) {
                $validator = Validator::make($request->all(), [
                    'date' => 'date_format:Y-m-d',
                ]);
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 401);
                } else {
                    $purchase_result = $query->where('purchase.user_id', $user_id)->where('purchase.date', $date)->get();
                    $purchase_cost_total = $table->where('date', '=', $date)
                        ->where('user_id', '=', $user_id)
                        ->value($total_sum);

                }
            } else if ($date == null && $user_id == null) {
                $purchase_result = $query->get();
                $purchase_cost_total = $table->value($total_sum);
            } else {
                return response()->json([
                    'status' => $this->errorStatus,
                    'code' => $this->errorcode,
                    'message' => "error"]);
            }

            if ($purchase_result) {
                if (count($purchase_result) <= 0) {
                    return response()->json([
                        'status' => $this->successStatus,
                        'code' => $this->successCode,
                        'message' => "Product Purchase is empty",
                    ], $this->successCode);
                } else {
                    return response()->json([
                        "data" => $purchase_result,
                        "totalCost" => $purchase_cost_total,
                        'status' => $this->successStatus,
                        'code' => $this->successCode,
                        'message' => $this->successMessage,
                    ], $this->successCode);

                }
            } else {
                return response()->json([
                    'status' => $this->errorStatus,
                    'code' => $this->errorcode,
                    'message' => "Failed to Find Product Purchase"]);

            }
        } else {
            return response()->json("Method not Allow", 405);
        }

    }

    //  PurchaseProductReport---------------------------------------------------------

    public function PurchaseProductReport(Request $request)
    {
        if ($request->isMethod('post')) {

            $from_date = $request->input('from-date');
            $to_date = $request->input('to-date');
            $product_id = $request->input('product_id');

            $query = DB::table('purchase')
            ->leftJoin('user', function ($join) {
                $join->on('purchase.user_id', '=', 'user.id');
            })
            ->leftJoin('product', function ($join) {
                $join->on('purchase.product_id', '=', 'product.id');
            })
            ->orderBy('purchase.date', 'asc')
            ->select( 'purchase.*',               
                      'user.username as user_name',
                      'product.product_image',
                      'product.unit'
                    );

            if ($product_id !== null && $to_date == null && $from_date == null)
             {
                $purchase_result =  $query->where('purchase.product_id', $product_id)->get();

            } elseif ($product_id !== null && $to_date !== null && $from_date == null) {
                $validator = Validator::make($request->all(), [
                    'to-date' => 'required|date_format:Y-m-d',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 401);
                } else {
                    $purchase_result =  $query
                        ->where('purchase.product_id', $product_id)
                        ->where('purchase.date', '<=', $to_date)
                        ->get();
                }
            } elseif ($product_id !== null && $to_date == null && $from_date !== null) {
                $validator = Validator::make($request->all(), [
                    'from-date' => 'required|date_format:Y-m-d',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 401);
                } else {
                    $purchase_result =  $query
                        ->where('purchase.product_id', $product_id)
                        ->where('purchase.date', '>=', $from_date)
                        ->get();
                }

            } elseif ($product_id == null && $to_date !== null && $from_date == null) {
                $validator = Validator::make($request->all(), [
                    'to-date' => 'required|date_format:Y-m-d',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 401);
                } else {

                    $purchase_result =  $query
                        ->where('purchase.date', '<=', $to_date)
                        ->get();
                }

            } elseif ($product_id == null && $to_date == null && $from_date !== null) {

                $validator = Validator::make($request->all(), [
                    'from-date' => 'required|date_format:Y-m-d',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 401);
                } else {
                    $purchase_result =  $query
                        ->where('purchase.date', '>=', $from_date)
                        ->get();
                }

            } elseif ($product_id !== null && $to_date !== null && $from_date !== null) {

                $validator = Validator::make($request->all(), [
                    'from-date' => 'required|date_format:Y-m-d',
                    'to-date' => 'required|date_format:Y-m-d',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 401);
                } else {
                    $purchase_result =  $query
                        ->where('purchase.product_id', $product_id)
                        ->whereBetween('purchase.date', [$from_date, $to_date])
                        ->get();
                }

            } elseif ($product_id == null && $to_date !== null && $from_date !== null) {

                $validator = Validator::make($request->all(), [
                    'from-date' => 'required|date_format:Y-m-d',
                    'to-date' => 'required|date_format:Y-m-d',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 401);
                } else {
                    $purchase_result =  $query
                        ->whereBetween('purchase.date', [$from_date, $to_date])
                        ->get();
                }

            } elseif ($product_id == null && $to_date == null && $from_date == null) {

                $purchase_result =  $query->get();

            }

            if ($purchase_result) {
                if (count($purchase_result) <= 0) {
                    return response()->json([
                        'status' => $this->successStatus,
                        'code' => $this->successCode,
                        'message' => "Product Purchase order is empty",
                    ], $this->successCode);
                } else {
                   
                    // return $purchase_record;
                    view()->share('data', $purchase_result);

                    // return view('purchase_pdfview');

                    if ($product_id !== null) {
                        view()->share('productReport', "true");
                    }
                    if ($from_date !== null) {
                        // $from_date = Carbon::parse($from_date)->format('d/m/Y');
                        view()->share('from_date', $from_date);
                    }
                    if ($to_date !== null) {
                        // $to_date = Carbon::parse($from_date)->format('d/m/Y');
                        view()->share('to_date', $to_date);
                    }

                    $pdf = PDF::loadView('ProductReport_pdfview');
                    // return $pdf->download('purchase.pdf');

                    $destinationPath = "pdf";
                    File::isDirectory($destinationPath) or File::makeDirectory($destinationPath, 0777, true, true);

                    //make filename
                    $ldate = date('d-m-Y');
                    $t = time();
                    $filename = "p" . $purchase_result[0]->product_id . "-" . $ldate . "-" . $t . ".pdf";

                    $pdf->save('pdf/' . $filename);
                    if ($pdf->save('pdf/' . $filename)) {
                        $file_storage_path = asset("pdf/" . $filename);
                        return response()->json([
                            "data" => $purchase_result,
                            "file" => $file_storage_path,
                            'status' => $this->successStatus,
                            'code' => $this->successCode,
                            'message' => $this->successMessage,
                        ], $this->successCode);
                    } else {
                        return response()->json([
                            'status' => $this->errorStatus,
                            'code' => $this->errorcode,
                            'message' => "Failed to generate pdf file"]);
                    }

                }
            } else {
                return response()->json([
                    'status' => $this->errorStatus,
                    'code' => $this->errorcode,
                    'message' => "Failed to Find Product Purchase order"]);

            }

        } else {
            return response()->json("Method not Allow", 405);
        }
    }

    public function PurchaseCostonDate(Request $request)
    {
        if ($request->isMethod('post')) {

            $validator = Validator::make($request->all(), [
                'date' => 'required|date_format:Y-m-d',

            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 401);
            } else {

                $date = $request->input('date');
                $purchase_result = DB::table('purchase')
                    ->where('date', '=', $date)
                    ->value(DB::raw("SUM(total_amount + weight_labour + transport_labour)"));

                if ($purchase_result) {

                    return response()->json([
                        "data" => $purchase_result,
                        'status' => $this->successStatus,
                        'code' => $this->successCode,
                        'message' => $this->successMessage,
                    ], $this->successCode);

                } else {
                    return response()->json([
                        'status' => $this->errorStatus,
                        'code' => $this->errorcode,
                        'message' => "Product Purchase order not found for this date"]);

                }
            }

        } else {
            return response()->json("Method not Allow", 405);
        }

    }

    public function PurchaseCostonMonthYear(Request $request)
    {
        if ($request->isMethod('post')) {

            $validator = Validator::make($request->all(), [
                'date' => 'required|date_format:Y-m',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 401);
            } else {

                $date = $request->input('date');

                $time = strtotime($date);
                $month = date("m", $time);
                $year = date("Y", $time);

                $purchase_result = DB::table("purchase")
                    ->whereMonth('date', $month)
                    ->whereYear('date', $year)
                    ->value(DB::raw("SUM(total_amount + weight_labour + transport_labour)"));

                if ($purchase_result) {

                    return response()->json([
                        "data" => $purchase_result,
                        'status' => $this->successStatus,
                        'code' => $this->successCode,
                        'message' => $this->successMessage,
                    ], $this->successCode);

                } else {
                    return response()->json([
                        'status' => $this->errorStatus,
                        'code' => $this->errorcode,
                        'message' => "Product Purchase order not found for this month"]);

                }
            }

        } else {
            return response()->json("Method not Allow", 405);
        }

    }

    public function PurchaseRetailReport(Request $request)
    {
        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'date' => 'required|date_format:Y-m-d',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 401);
            } else {

                $query = DB::table('purchase')
                ->leftJoin('user', function ($join) {
                    $join->on('purchase.user_id', '=', 'user.id');
                })
                ->leftJoin('product', function ($join) {
                    $join->on('purchase.product_id', '=', 'product.id');
                })
                ->orderBy('purchase.date', 'asc')
                ->select( 'purchase.*',               
                          'user.username as user_name',
                          'product.product_image',
                          'product.unit'
                        );


                $date = $request->input('date');

                $product = $query
                    ->where('purchase.date', '=', $date)
                    ->get();

                if ($product) {
                    if (count($product) <= 0) {
                        return response()->json([
                            'status' => $this->successStatus,
                            'code' => $this->successCode,
                            'message' => "Product Purchase Not Found for this date",
                        ], $this->successCode);
                    } else {

                        $purchase_cost_total = DB::table('purchase')
                            ->where('date', '=', $date)
                            ->value(DB::raw("SUM(total_amount + weight_labour + transport_labour)"));

                                       // return $purchase_record;
                        view()->share('data', $product);
                        if ($purchase_cost_total) {
                            view()->share('totalCost', $purchase_cost_total);
                        }
                        if ($date !== null) {
                            //  $date = Carbon::parse($date)->format('d/m/Y');
                            view()->share('current_date', $date);
                        }
                        // return view('purchase_pdfview');

                        $pdf = PDF::loadView('PurchaseRetailReport_pdfview');
                        // return $pdf->download('purchase.pdf');

                        $destinationPath = "pdf";
                        File::isDirectory($destinationPath) or File::makeDirectory($destinationPath, 0777, true, true);

                        //make filename
                        $ldate = date('d-m-Y');
                        $t = time();
                        $filename = "p" . $product[0]->product_id . "-" . $ldate . "-" . $t . ".pdf";

                        $pdf->save('pdf/' . $filename);
                        if ($pdf->save('pdf/' . $filename)) {
                            $file_storage_path = asset("pdf/" . $filename);
                            return response()->json([
                                "data" => $product,
                                "totalCost" => $purchase_cost_total,
                                "file" => $file_storage_path,
                                'status' => $this->successStatus,
                                'code' => $this->successCode,
                                'message' => $this->successMessage,
                            ], $this->successCode);
                        } else {
                            return response()->json([
                                'status' => $this->errorStatus,
                                'code' => $this->errorcode,
                                'message' => "Failed to generate pdf file"]);
                        }

                    }
                } else {
                    return response()->json([
                        'status' => $this->errorStatus,
                        'code' => $this->errorcode,
                        'message' => "Failed to Find Product Purchase"]);
                }
            }
        } else {
            return response()->json("Method not Allow", 405);
        }
    }

    public function ReviewSellingPrice(Request $request)
    {
        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'date' => 'required|date_format:Y-m-d',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 401);
            } else {

                $date = $request->input('date');
                $product = DB::table('purchase')
                    ->where('date', '=', $date)
                    ->orderBy('date', 'asc')
                    ->get();

                if ($product) {
                    if (count($product) <= 0) {
                        return response()->json([
                            'status' => $this->successStatus,
                            'code' => $this->successCode,
                            'message' => "Product Purchase Not Found for this date",
                        ], $this->successCode);
                    } else {

                        $review_record = [];

                        for ($i = 0; $i < count($product); $i++) {

                            $user_name = DB::table('user')->where('id', $product[$i]->user_id)->value('username');
                            $product_img = DB::table('product')->where('id', $product[$i]->product_id)->value('product_image');
                            $product_unit = DB::table('product')->where('id', $product[$i]->product_id)->value('unit');

                            $current_date = DB::table('purchase')
                                ->where('product_id', $product[$i]->product_id)
                                ->where('date', '=', $date)
                                ->value('selling_price');

                            if ($current_date) {
                                $current_sell_price = $current_date;
                            } else {
                                $current_sell_price = 0;
                            }

                            $oldest_date = DB::table('purchase')
                                ->where('product_id', $product[$i]->product_id)
                                ->where('date', '<', $date)
                                ->latest()
                                ->value('selling_price');

                            if ($oldest_date) {
                                $oldest_sell_price = $oldest_date;
                            } else {
                                $oldest_sell_price = 0;
                            }

                            $differ_between_old_and_new_sellprice = $current_sell_price - $oldest_sell_price;

                            if ($differ_between_old_and_new_sellprice > 0) {
                                $flag = "up";
                            } elseif ($differ_between_old_and_new_sellprice == 0) {
                                $flag = "equal";
                            } else {
                                $flag = "down";
                            }

                            array_push($review_record,
                                [
                                    "id" => $product[$i]->id,
                                    "user_id" => $product[$i]->user_id,
                                    "user_name" => $user_name,
                                    "product_id" => $product[$i]->product_id,
                                    "product_name" => $product[$i]->product_name,
                                    "product_image" => $product_img,
                                    "unit" => $product_unit,
                                    "last_selling_price" => number_format($oldest_sell_price),
                                    "current_selling_price" => number_format($current_sell_price),
                                    "change" => $this->number_format($differ_between_old_and_new_sellprice),
                                    "flag" => $flag,
                                    "kg_price" => $this->number_format($product[$i]->kg_price),
                                    "date" => $product[$i]->date,
                                ]
                            );
                        }

                        return response()->json(["data" => $review_record,
                            'status' => $this->successStatus,
                            'code' => $this->successCode,
                            'message' => $this->successMessage,
                        ], $this->successCode);

                    }
                } else {
                    return response()->json([
                        'status' => $this->errorStatus,
                        'code' => $this->errorcode,
                        'message' => "Failed to Find latest selling price"]);
                }
            }
        } else {
            return response()->json("Method not Allow", 405);
        }
    }

    public function number_format($number)
    {
        return number_format($number, 2, '.', ',');
        //  return number_format($number);

    }

    public function test_api(Request $request)
    {
        
    
      $user_id = $request->input('user_id');
      $from_date = $request->input('from-date');
      $to_date = $request->input('to-date');


      $query = DB::table('purchase')

      ->leftJoin('user', function ($join) {
          $join->on('purchase.user_id', '=', 'user.id');
      }
      )
      ->leftJoin('product', function ($join) {
          $join->on('purchase.product_id', '=', 'product.id');
      }
      )
      ->leftJoin('balance', function ($join) {
          $join->on('purchase.user_id', '=', 'balance.user_id');
          $join->on('purchase.date', '=', 'balance.date');
      })
      ->orderBy('purchase.date', 'asc')
      ->select('product.*', 'purchase.*', 'balance.balance', 'balance.remaining_balance', 'balance.id as balance_id', 'user.username as user_name');


      $purchase_result = $query
      ->where('purchase.user_id', $user_id)
      ->whereBetween('purchase.date', [$from_date, $to_date])
      ->get();



      $purchase_record = json_decode($purchase_result);


      view()->share('data', $purchase_record);


      if (isset($userReport)) {
          if ($userReport == true) {
              view()->share('userReport', "true");
              if ($from_date !== null) {
                  // $from_date = Carbon::parse($from_date)->format('d/m/Y');
                  view()->share('from_date', $from_date);
              }
              if ($to_date !== null) {
                  // $to_date = Carbon::parse($from_date)->format('d/m/Y');
                  view()->share('to_date', $to_date);
              }
          }
      }
        

      $pdf = PDF::loadView('purchase_pdfview');
    
      $destinationPath = "pdf";
     
      File::isDirectory($destinationPath) or File::makeDirectory($destinationPath, 0777, true, true);
      //make filename
      $ldate = date('d-m-Y');
      $t = time();
      $filename = "p" . $purchase_record[0]->product_id . "-" . $ldate . "-" . $t . ".pdf";

      //save file
      $pdf->save('pdf/' . $filename);
      if ($pdf->save('pdf/' . $filename)) {
          $file_storage_path = asset("pdf/" . $filename);
          return response()->json(["data" => $purchase_record,
              "file" => $file_storage_path,
              'status' => $this->successStatus,
              'code' => $this->successCode,
              'message' => $this->successMessage,
          ], $this->successCode);
      } else {
          return response()->json([
              'status' => $this->errorStatus,
              'code' => $this->errorcode,
              'message' => "Failed to generate pdf file"]);
      }

    }


    //Convert UTC timeZone to IST -----------------------------------------------------

  public function timezone_opration()
  {

   // date_default_timezone_set('Asia/Kolkata');

    $utc = date("Y-m-d H:i:s");
    $ist = date("Y-m-d g:i:s");
    $date = date ("Y-m-d H:i:s", strtotime($ist));

  return response()->json(["UTC" => $utc,
                           "IST" => $date,
                           'status' => $this->successStatus,
                           'code' => $this->successCode,
                           'message' => $this->successMessage,
                       ], $this->successCode);

  }




}
