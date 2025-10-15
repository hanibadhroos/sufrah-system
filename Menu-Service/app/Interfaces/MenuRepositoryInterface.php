<?php
namespace App\Interfaces;

use Illuminate\Http\Request;

interface MenuRepositoryInterface{

    public function index();
    public function show($id);
    public function create(Request $request);
    public function update(Request $request, $id);
    public function delete($id);
}