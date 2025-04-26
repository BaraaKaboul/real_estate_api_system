<?php

namespace App\Repository;

use App\Models\Property;
use App\Models\User;
use App\ResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminRepository implements Interface\AdminRepositoryInterface
{
    use ResponseTrait;
    public function pending_properties()
    {
        try {
            $prop = Property::where('status','=','pending')->orderBy('created_at','DESC')->paginate(4);
            if ($prop->isEmpty()){
                return $this->fail('There is no pending properties',404);
            }
            return $this->success('The properties fetched successfully',200,$prop);
        }catch (\Exception $e){
            DB::rollBack();
            Log::error('Fetched failed: ' . $e->getMessage());
            return $this->fail('Fetched failed: ' . $e->getMessage(), 500);
        }
    }

    public function get_users()
    {
        try {
            $users = User::where('role','user')->get();
            if ($users->isEmpty()){
                return $this->fail('There is no users to show',404);
            }
            return $this->success('All users fetched',200,$users);

        }catch (\Exception $e){
            DB::rollBack();
            Log::error('Fetched failed: ' . $e->getMessage());
            return $this->fail('Fetched failed: ' . $e->getMessage(), 500);
        }
    }
}
