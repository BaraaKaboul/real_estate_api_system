<?php

namespace App\Repository;

use App\Models\Property;
use App\ResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VisitorRepository implements Interface\VisitorRepositoryInterface
{
    use ResponseTrait;

    public function index()
    {
        try {
            $prop = Property::where('status','accept')->orderBy('created_at','DESC')->paginate(4);
            if ($prop->isEmpty()){
                return $this->fail('There is no properties to show',404);
            }
            return $this->success('Property fetched successfully',200,['properties'=>$prop]);

        }catch (\Exception $e){
            DB::rollBack();
            Log::error('visitor failed: ' . $e->getMessage());
            return $this->fail('visitor failed: ' . $e->getMessage(), 500);
        }
    }

    public function show($request){
        try {
            $prop_by_id = Property::where('id',$request->id)->with('images')->first();
            if (!$prop_by_id){
                return $this->fail('There is no property found',404);
            }
            return $this->success('Data fetched successfully',200,$prop_by_id);

        }catch (\Exception $e){
            return $this->fail($e,500);
        }
    }
}
