<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\X12Inbound;
use DataTables;
use Illuminate\Support\Facades\DB;

class InboundStatusController extends Controller
{
    public function index()
    {
        return view('inboundstatus');
    }

    public function getInbound(Request $request)
    {
        if ($request->ajax()) {

            $data = X12Inbound::where('edi_code','=','997')
            ->orderBy('created_at','desc')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    return '';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
}
