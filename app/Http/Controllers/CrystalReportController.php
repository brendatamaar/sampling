<?php

namespace App\Http\Controllers;

use App\Models\CrystalReport1;
use App\Models\CrystalReport2;
use App\Models\CrystalReport3;
use App\Models\CrystalReport4;
use App\Models\CrystalReport5;
use App\Models\CrystalReport6;
use App\Models\CrystalReport7;
use Illuminate\Http\Request;
use App\Models\RegionImportMappings;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class CrystalReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        return view('crystal_reports.index', [
            'crystal_reports' => CrystalReport1::orderBy('id', 'DESC')->paginate(10)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CrystalReport1  $crystalReport1
     * @return \Illuminate\Http\Response
     */
    public function show(CrystalReport1 $crystalReport1)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CrystalReport1  $crystalReport1
     * @return \Illuminate\Http\Response
     */
    public function edit(CrystalReport1 $crystalReport1)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CrystalReport1  $crystalReport1
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CrystalReport1 $crystalReport1)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CrystalReport1  $crystalReport1
     * @return \Illuminate\Http\Response
     */
    public function destroy(CrystalReport1 $crystalReport1)
    {
        //
    }

    /**
     * Remove all data.
     */
    public function deleteAll(): RedirectResponse
    {
        $user = auth()->user();
        $regionId = $user->region_id;

        // Fetch the import class for CrystalReport from the database
        $mapping = RegionImportMappings::where('region_id', $regionId)->first();

        if (!$mapping || !$mapping->data_no) {
            return redirect('crystal_reports')->with('error', 'Invalid region ID or no import class defined for CrystalReport!');
        }

        $modelClass = 'App\\Models\\CrystalReport' . $mapping->data_no;

        if (!class_exists($modelClass)) {
            return redirect('crystal_reports')->with('error', 'The specified import class does not exist.');
        }

        $modelClass::truncate();
        return redirect()->route('crystal_reports.index')
            ->with('error', 'Semua data berhasil dihapus.');
    }

    public function importExcel(Request $request)
    {
        $indexSheet = $request->input('sheet');
        $user = auth()->user();
        $regionId = $user->region_id;

        // Fetch the import class for CrystalReport from the database
        $mapping = RegionImportMappings::where('region_id', $regionId)->first();

        if (!$mapping || !$mapping->data_no) {
            return redirect('crystal_reports')->with('error', 'Invalid region ID or no import class defined for CrystalReport!');
        }

        $importClass = 'App\\Imports\\CrystalReport' . $mapping->data_no . 'Import';

        try {
            Excel::import(new $importClass($indexSheet), $request->file('file'));
        } catch (\Exception $e) {
            return redirect('crystal_reports')->with('error', 'Error! Pastikan sheet dan template excel sudah sesuai. ');
        }

        return redirect('crystal_reports')->with('status', 'Import excel di sheet ' . $indexSheet . ' berhasil');
    }

    public function downloadImportTemplate()
    {
        $path = base_path('/template/crystal_report.xls');
        ;

        return response()->download($path, 'crystal_report.xls', [
            'Content-Type' => 'text/xls',
        ]);
    }

    public function cetakBarcode()
    {
        $user = auth()->user();
        $regionId = $user->region_id;

        // Fetch the import class for CrystalReport from the database
        $mapping = RegionImportMappings::where('region_id', $regionId)->first();

        if (!$mapping || !$mapping->data_no) {
            return redirect('crystal_reports')->with('error', 'Invalid region ID or no import class defined for CrystalReport!');
        }

        $modelClass = 'App\\Models\\CrystalReport' . $mapping->data_no;

        if (!class_exists($modelClass)) {
            return redirect('crystal_reports')->with('error', 'The specified import class does not exist.');
        }

        $dataproduk = $modelClass::all()->groupBy('location')->map(function ($items) {
            return $items->unique('item_no')->unique('item_name');
                })->values();
        $pdf = PDF::loadView('crystal_reports.barcode', compact('dataproduk'));
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption(['dpi' => 150, 'defaultFont' => 'serif']);
        return $pdf->stream('crystal_report.pdf');
    }

    public function cetakQR()
    {
        $user = auth()->user();
        $regionId = $user->region_id;

        // Fetch the import class for CrystalReport from the database
        $mapping = RegionImportMappings::where('region_id', $regionId)->first();

        if (!$mapping || !$mapping->data_no) {
            return redirect('crystal_reports')->with('error', 'Invalid region ID or no import class defined for CrystalReport!');
        }

        $modelClass = 'App\\Models\\CrystalReport' . $mapping->data_no;

        if (!class_exists($modelClass)) {
            return redirect('crystal_reports')->with('error', 'The specified import class does not exist.');
        }

        $dataproduk = $modelClass::all()->groupBy('location')->map(function ($items) {
            return $items->unique('item_no')->unique('item_name');
                })->values();
        $pdf = PDF::loadView('crystal_reports.qr', compact('dataproduk'));
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption(['dpi' => 150, 'defaultFont' => 'serif']);
        return $pdf->stream('crystal_report.pdf');
    }   
}
