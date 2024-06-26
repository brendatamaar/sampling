<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateStoreRequest;
use App\Models\RegionImportMappings;
use App\Models\Regions;
use App\Models\Stores;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class StoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-store|edit-store|delete-store', ['only' => ['index', 'show']]);
        $this->middleware('permission:create-store', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-store', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-store', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        return view('stores.index', [
            'stores' => Stores::orderBy('site_id')->paginate(10)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        return view('stores.create', [
            'regions' => Regions::pluck('reg_name', 'reg_id')->all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        Stores::create($input);

        return redirect()->route('stores.index')
            ->withSuccess('New store is added successfully.');
    }

    /**
     * Display the specified resource.
     *
     */
    public function show(Stores $store): View
    {
        return view('stores.show', [
            'store' => $store
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     */
    public function edit(Stores $store): View
    {
        return view('stores.edit', [
            'store' => $store,
            'regions' => Regions::pluck('reg_name', 'reg_id')->all(),
            'storeRegions' => $store->regions->pluck('reg_id')->all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     */
    public function update(UpdateStoreRequest $request, Stores $store)
    {
        $input = $request->all();

        $store->update($input);

        return redirect()->back()
            ->withSuccess('Store is updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     */
    public function destroy(Stores $store): RedirectResponse
    {
        $store->delete();
        return redirect()->route('stores.index')
            ->withSuccess('Store is deleted successfully.');
    }

    public function indexMapping()
    {
        return view('stores.index_mapping', [
            'mappings' => RegionImportMappings::orderBy('region_id')->paginate(10)
        ]);
    }

    public function updateMapping(Request $request)
    {
        $request->validate([
            'data_no' => 'required|integer',
            'region_id' => 'required|integer',
        ]);

        $regionMapping = RegionImportMappings::find($request->input('region_id'));
        if ($regionMapping) {
            $regionMapping->data_no = $request->input('data_no');
            $regionMapping->save();
            return redirect()->route('stores.mapping')->with('status', 'Data updated successfully!');
        }

        return redirect()->route('stores.mapping')->with('error', 'Failed to update data!');
    }

    public function fetchSitesByRegion($region_id)
    {
        $sites = Stores::where('region_id', $region_id)->get();
        return response()->json($sites);
    }
}
