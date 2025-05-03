<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyValidationRequest;
use App\Models\Property;
use App\Repository\Interface\PropertyRepositoryInterface;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    protected $property;

    public function __construct(PropertyRepositoryInterface $property){
        $this->property = $property;
    }

    public function index(){
        return $this->property->index();
    }

    public function store(PropertyValidationRequest $request){
        return $this->property->store($request);
    }

    public function show(Request $request){
        return $this->property->show($request);
    }

    public function update(Request $request, Property $property){
        return $this->property->update($request, $property);
    }

    public function destroy($id){
        return $this->property->delete($id);
    }

    public function saved_property(Request $request, Property $property){
        return $this->property->saved_property($request, $property);
    }

    public function show_saved_property(){
        return $this->property->show_saved_property();
    }

    public function remove_saved_property($id){
        return $this->property->remove_saved_property($id);
    }
}
