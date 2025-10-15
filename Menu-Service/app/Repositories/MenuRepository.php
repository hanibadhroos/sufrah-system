<?php
namespace App\Repositories;

use App\Interfaces\MenuRepositoryInterface;
use App\Models\Menu;
use Exception;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
class MenuRepository implements MenuRepositoryInterface{

    public function index(){
        try{
            $menus = Menu::all();
            if($menus){
                return response()->json(['data' => $menus], 200);
            }
            return response()->json(['error'=> 'There are not any menus.'], 400);
        }
        catch(Exception $e){
            return response()->json(['error'=> 'Error while get menus --> ' . $e->getMessage()], 400);
        }
    }

    public function show($id){
        try{
            $menu = Menu::find($id);
            if($menu){
                return  response()->json(['data'=> $menu], 200);
            }
            return response()->json(['error'=> 'There is not a menu with id = ' . $id], 400);
        }
        catch(Exception $e){
            return response()->json(['error'=> 'Error while show menu info ---> ' . $e->getMessage()], 400);
        }
    }

    public function create(Request $request){
        try{
            $validated = $request->validate([
                'name'=> 'required|string',
                'description' => 'nullable|text'
            ]);
            $validated['id'] = Str::uuid();
            $validated['branch_id'] = JWTAuth::parseToken()->getPayload()->get('ten');
        }
        catch(Exception $e){
            return response()->json(['error' => 'Error while add new menu ' . $e->getMessage()], 400);
        }
    }
    
    public function update(Request $request, $id){}
    public function delete($id){}
}