<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Exception;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class SupplierContoller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenant_id = JWTAuth::parseToken()->getPayload()->get('tenant_id');
        $suppliers = Supplier::where('tenant_id', $tenant_id)->get();
        if(!$suppliers){
            return response()->json(['error'=> 'There is no suppliers'], 200);
        }
        else{
            return response()->json(['data' => $suppliers], 200);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $validated = $request->validate([
                'name'=> 'required|string',
                'phone' => 'required'
            ]);

            $tenant_id = JWTAuth::parseToken()->getPayload()->get('tenant_id');
            $validated['tenant_id'] = $tenant_id;
            $supplier = Supplier::create($validated);
            if($supplier){
                return response()->json(['data'=> $supplier], 201);
            }
            else{
                return response()->json(['error'=>'Error while add new supplier'],400);
            }
        }
        catch(Exception $e){
            return response()->json(['error'=> 'Error while create new supplier --> ' . $e->getMessage()],400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $supplier = Supplier::find($id);
        if($supplier){
            return response()->json($supplier, 200);
        }
        else{
            return response()->json(['error'=> 'There is no any supplier'], 400);
        }
    }


    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name'=> 'required|string',
            'phone'=> 'required'
        ]);
        $tenant_id = JWTAuth::parseToken()->getPayload()->get('tenant_id');
        $validated['tenant_id'] = $tenant_id;

        //// We get the supplier by id.
        $supplier = Supplier::find($id);
        $supplier->update($validated);

        return response()->json($supplier, 200);
    }

    
    public function destroy(string $id)
    {
        try{
            $supplier = Supplier::find($id);
            $supplier->delete();
            if($supplier){
                return response()->json(['message'=>'Supplier deleted successfully.'], 200);
            }
        }
        catch(Exception $e){
            return response()->json(['error'=>'Error while delete the supplier ==> ' . $e->getMessage()], 500);
        }
    }
}
