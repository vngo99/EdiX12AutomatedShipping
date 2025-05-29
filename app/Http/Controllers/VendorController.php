<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\OrderItem;
use DataTables;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    public function index()
    {
        return view('vendor');
    }

    public function getVendor(Request $request)
    {
        if ($request->ajax()) {
            
            $data = Vendor::latest()->get();
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
