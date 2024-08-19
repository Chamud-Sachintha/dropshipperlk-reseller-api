<?php

namespace App\Http\Controllers;

use App\Exports\SimpleExcelExport;
use App\Helpers\AppHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExcelExportController extends Controller
{

    private $AppHelper;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
    }

    public function DownloadExcel(Request $request)
    {
        // You can add validation or authorization logic here if needed
        set_time_limit(300); // 300 seconds = 5 minutes

        $sellerId = (is_null($request->sellerId) || empty($request->sellerId)) ? "" : $request->sellerId;

        if ($sellerId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Seller ID");
        }
        
        $selectedReportType = $request->input('selectedReportType');
        $token = $request->input('token');
        $typerepo = '';


        if ($selectedReportType == '1') {

            $typerepo = "Order_Report";
        } else {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Type");
        }

        $export = new SimpleExcelExport($selectedReportType, $sellerId);

        $filename = $typerepo . '_' . Carbon::now()->format('Y-m-d_H-i-s');

        return Excel::download($export, $filename . '.xlsx');
    }
}
