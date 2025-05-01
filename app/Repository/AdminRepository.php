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
            // Check if already accepted
            if (Property::where('id', $id)->where('status', 'accept')->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This property is already accepted',
                    'code' => 409,
                    'current_status' => 'accept' // Helpful for frontend
                ], 409);
            }

            $property = Property::findOrFail($id);
            $property->status = 'accept';
            $property->save();

            return $this->success('Property accepted successfully', 200, [
                'property' => $property,
                'new_status' => 'accept'
            ]);

        } catch (\Exception $e) {
            Log::error('Accept failed: ' . $e->getMessage());
            return $this->fail('Failed to accept property: ' . $e->getMessage(), 500);
        }
    }

    public function denied_property($user_id, $property_id)
    {
        DB::beginTransaction();
        try {
            $property = Property::where([
                'id' => $property_id,
                'user_id' => $user_id
            ])->firstOrFail();

            // Check if already denied (with exists())
            if (Denied_property::where([
                'user_id' => $user_id,
                'property_id' => $property_id
            ])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This property is already denied by this user',
                    'code' => 409,
                    'current_status' => 'denied'
                ], 409);
            }

            $property->update(['status' => 'denied']);

            $denied_property = Denied_property::create([
                'property_id' => $property->id,
                'user_id' => $property->user_id
            ]);

            DB::commit();

            return $this->success('Property denied successfully', 200, [
                'property' => $property,
                'denied_property' => $denied_property,
                'new_status' => 'denied'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Deny failed: ' . $e->getMessage());
            return $this->fail('Failed to deny property: ' . $e->getMessage(), 500);
        }
    }
}
