<?php

namespace App\Repository;

use App\Models\Denied_property;
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

    public function banUser($id)
    {
        try {
            $user = User::where('role','user')->findOrFail($id);
            $user->update([
                'status'=>'ban',
            ]);
            return $this->success('User status updated successfully',200,$user);
        }catch (\Exception $e){
            Log::error('Updated failed: ' . $e->getMessage());
            return $this->fail('Updated failed: ' . $e->getMessage(), 500);
        }
    }

    public function unBanUser($id)
    {
        try {
            $user = User::where('role','user')->findOrFail($id);
            $user->update([
                'status'=>'active',
            ]);
            return $this->success('User status updated successfully',200,$user);
        }catch (\Exception $e){
            Log::error('Updated failed: ' . $e->getMessage());
            return $this->fail('Updated failed: ' . $e->getMessage(), 500);
        }
    }

    public function accept_pending_property($id){
        try {
            $checkProp = Property::where(['id'=>$id, 'status'=>'accept']);
            if ($checkProp){
                return $this->fail('This property already accepted',409);
            }

            $prop = Property::findOrFail($id)->update([
               'status'=>'accept'
            ]);
            return $this->success('Property status updated successfully',200,$prop);
        }catch (\Exception $e){
            Log::error('Updated failed: ' . $e->getMessage());
            return $this->fail('Updated failed: ' . $e->getMessage(), 500);
        }
    }

    public function denied_property($user_id, $property_id)
    {
        DB::beginTransaction();
        try {
            $prop = Property::where(['id'=>$property_id, 'user_id'=>$user_id])->first();
            if (!$prop){
                return $this->fail('The property not found', 404);
            }

            $alreadyExist = Denied_property::where(['user_id'=>$user_id, 'property_id'=>$property_id]);
            if ($alreadyExist){
                return $this->fail('This property is already denied by this user', 409);
            }

            $prop->update([
                'status'=>'denied'
            ]);
            $denied_property = Denied_property::create([
                'property_id'=>$prop->id,
                'user_id'=>$prop->user_id
            ]);
            DB::commit();
            return $this->success('Property updated and added to the denied table',200,['property'=>$prop,'denied_property'=>$denied_property]);

        }catch (\Exception $e){
            DB::rollBack();
            Log::error('Updated failed: ' . $e->getMessage());
            return $this->fail('Updated failed: ' . $e->getMessage(), 500);
        }
    }
}
