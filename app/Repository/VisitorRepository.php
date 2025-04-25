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
            $prop = Property::orderBy('created_at','DESC')->paginate(4);
            return $this->success('Property fetched successfully',200,['properties'=>$prop]);

        }catch (\Exception $e){
            DB::rollBack();
            Log::error('visitor failed: ' . $e->getMessage());
            return $this->fail('visitor failed: ' . $e->getMessage(), 500);
        }
    }
}
