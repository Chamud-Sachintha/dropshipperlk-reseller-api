<?php

namespace App\Http\Controllers;

use App\Exports\SimpleExcelExport;
use App\Helpers\AppHelper;
use App\Models\Reseller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExcelExportController extends Controller
{

    private $AppHelper;
    private $Reseller;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Reseller = new Reseller();
    }

    public function DownloadExcel(Request $request)
    {
        // You can add validation or authorization logic here if needed
        set_time_limit(300); // 300 seconds = 5 minutes

        $seller_info = $this->Reseller->find_by_token($request->token);
        
        $selectedReportType = $request->input('selectedReportType');
        $token = $request->input('token');
        $typerepo = '';


        if ($selectedReportType == '1') {
            $typerepo = "Order_Report";
        } else if ($selectedReportType == '2') {
            $typerepo = "Commision_Report";
        } else {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Type");
        }

        $export = new SimpleExcelExport($selectedReportType, $seller_info->id);

        $filename = $typerepo . '_' . Carbon::now()->format('Y-m-d_H-i-s');

        return Excel::download($export, $filename . '.xlsx');
    }
}
