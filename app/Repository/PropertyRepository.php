<?php

namespace App\Repository;

use App\ImageTrait;
use App\Models\Premium;
use App\Models\Property;
use App\Models\Saved_properties;
use App\ResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PropertyRepository implements Interface\PropertyRepositoryInterface
{
    use ResponseTrait;
    use ImageTrait;

    public function index()
    {
        try {
            $goldenFeatured = Property::with('images')
                ->where('is_featured', true)
                ->whereHas('user.premium', function ($q) {
                    $q->where('plan', 'golden');
                })
                ->latest()
                ->get();

            $proFeatured = Property::with('images')
                ->where('is_featured', true)
                ->whereHas('user.premium', function ($q) {
                    $q->where('plan', 'pro');
                })
                ->latest()
                ->get();

            $standardFeatured = Property::with('images')
                ->where('is_featured', true)
                ->whereHas('user.premium', function ($q) {
                    $q->where('plan', 'standard');
                })
                ->latest()
                ->get();

            $goldenNormal = Property::with('images')
                ->where('is_featured', false)
                ->whereHas('user.premium', function ($q) {
                    $q->where('plan', 'golden');
                })
                ->latest()
                ->get();

            $proNormal = Property::with('images')
                ->where('is_featured', false)
                ->whereHas('user.premium', function ($q) {
                    $q->where('plan', 'pro');
                })
                ->latest()
                ->get();

            $standardNormal = Property::with('images')
                ->where('is_featured', false)
                ->whereHas('user.premium', function ($q) {
                    $q->where('plan', 'standard');
                })
                ->latest()
                ->get();

            $others = Property::with('images')
                ->whereDoesntHave('user.premium')
                ->latest()
                ->get();

            // دمج الكل بترتيب مخصص
            $all = $goldenFeatured
                ->concat($proFeatured)
                ->concat($standardFeatured)
                ->concat($goldenNormal)
                ->concat($proNormal)
                ->concat($standardNormal)
                ->concat($others);

            if ($all->isEmpty()) {
                return $this->fail('There is no property found', 404);
            }

            return $this->success('Data fetched successfully', 200, $all->values());
        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 500);
        }
    }
    public function show($request)
    {
        try {
            $prop_by_id = Property::where(['id'=>$request->id,'user_id'=>auth()->user()->id])->with('images')->first();
            if (!$prop_by_id){
                return $this->fail('There is no property found',404);
            }
            return $this->success('Data fetched successfully',200,$prop_by_id);

        }catch (\Exception $e){
            return $this->fail($e,500);
        }
    }


    public function store($request)
    {
        DB::beginTransaction();
        try {
            $prop = new Property();
            $prop->title = $request->title;
            $prop->description = $request->description;
            $prop->price = $request->price;
            $prop->area = $request->area;
            $prop->type = $request->type;
            $prop->purpose = $request->purpose;
            $prop->phone = $request->phone;
            $prop->balconies = $request->balconies;
            $prop->bedrooms = $request->bedrooms;
            $prop->bathrooms = $request->bathrooms;
            $prop->livingRooms = $request->livingRooms;
            $prop->location_lat = $request->location_lat;
            $prop->location_lon = $request->location_lon;
            $prop->user_id = auth()->user()->id;
            $prop->address = $request->address;

            $isFeatured = $request->boolean('is_featured', false);

            if ($isFeatured) {
                $premium = Premium::where('user_id', auth()->id())
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now())
                    ->first();

                if (!$premium) {
                    return $this->fail('There is no active premium', 403);
                }

                if ($premium->plan !== 'golden') {
                    if ($premium->used_featured >= $premium->max_featured) {
                        return $this->fail('You have used all your featured listings, but you can still post normal listings.', 403);
                    }
                }

                $premium->increment('used_featured');

                $prop->is_featured = true;
            } else {
                $prop->is_featured = false;
            }

            $prop->save();

            $images = [];

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $response = Http::withOptions([
                        'verify' => false // ← تعطيل SSL هنا
                    ])->attach(
                        'image',
                        file_get_contents($file),
                        $file->getClientOriginalName()
                    )->post("https://api.imgbb.com/1/upload", [
                        'key' => env('IMGBB_API_KEY'),
                    ]);

                    if ($response->successful()) {
                        $url = $response['data']['url'];

                        $images[] = $prop->images()->create([
                            'filename' => $file->getClientOriginalName(),
                            'imageable_id' => $prop->id,
                            'imageable_type' => Property::class,
                            'url' => $url,
                        ]);
                    } else {
                        Log::error('Failed to upload image to imgBB', [
                            'response' => $response->body(),
                        ]);
                    }
                }
            }

            DB::commit();
            return $this->success('Data has been stored successfully', 201, [
                'property' => $prop,
                'images' => $images,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Insert failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->fail('Insert failed: ' . $e->getMessage(), 500);
        }
    }

    public function update($request, Property $property)
    {
        if ($property->user_id != auth()->id()) {
            return $this->fail('Unauthorized to update this property.', 403);
        }
        try {
        $property->update([
            'title'=>$request->title,
            'description'=>$request->description,
            'price'=>$request->price,
            'area'=>$request->area,
            'type'=>$request->type,
            'purpose'=>$request->purpose,
            'phone'=>$request->phone,
            'balconies'=>$request->balconies,
            'bedrooms'=>$request->bedrooms,
            'bathrooms'=>$request->bathrooms,
            'livingRooms'=>$request->livingRooms,
            'location_lat'=>$request->location_lat,
            'location_lon'=>$request->location_lon,
            'address'=>$request->address,
            'status'=>'pending'
        ]);

            $uploadedImages = [];

            if ($request->hasFile('images')) {
                foreach ($property->images as $image) {
                    $this->deleteImage($image->url);
                    $image->delete();
                }

                foreach ($request->file('images') as $file) {
                    $response = Http::timeout(90)
                        ->withOptions(['verify' => false])
                        ->attach(
                            'image',
                            file_get_contents($file),
                            $file->getClientOriginalName()
                        )
                        ->post('https://api.imgbb.com/1/upload?key=' . env('IMGBB_API_KEY'));

                    if ($response->successful()) {
                        $url = $response['data']['url'];

                        $uploadedImages[] = $property->images()->create([
                            'filename' => $file->getClientOriginalName(),
                            'url' => $url,
                            'imageable_id' => $property->id,
                            'imageable_type' => Property::class
                        ]);
                    } else {
                        Log::error('Failed to upload image to imgBB', [
                            'response' => $response->body(),
                        ]);
                    }
                }
            }

            DB::commit();

            $property->load('images');

            return $this->success('Property updated successfully', 200, [
                'property' => $property,
                'images' => !empty($uploadedImages) ? $uploadedImages : $property->images,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update failed: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString()); // Log more details
            return $this->fail('Update failed: ' . $e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $property = Property::find($id);
            if ($property){
                if ($property->user_id != auth()->user()->id){
                    DB::rollBack();
                    return $this->fail('Unauthorized to delete this property.', 403);
                }
                foreach ($property->images as $image) {
                    if (!empty($image->filename)){
                        $this->deleteImage($image->filename);
                    }
                    $image->delete();
                }
                $property->delete();
                DB::commit();
                return response()->json(['message'=>'Property deleted successfully','code'=>200]);
            }else {
                DB::commit();
                return $this->success('Property not found or already deleted.', 200, null);
            }
        }catch (\Exception $e){
            DB::rollBack();
            Log::error('Delete failed: ' . $e->getMessage());
            return $this->fail('Delete failed: ' . $e->getMessage(), 500);
        }
    }

    public function saved_property($request, Property $property)
    {
        try {
            //$prop = Property::where('id',$id)->select('id');

            $alreadySaved = Saved_properties::where([
                'user_id'=>auth()->user()->id,
                'property_id'=>$property->id,
            ])->exists();

            if ($alreadySaved){
                return $this->fail('This property alreay saved',409);
            }

            $saved_prop = Saved_properties::create([
                'property_id'=>$property->id,
                'user_id'=>auth()->user()->id,
            ]);
            return $this->success('The property saved successfully',200,['property_saved'=>$saved_prop,'property'=>$property]);
        }catch (\Exception $e){
            DB::rollBack();
            Log::error('Saved failed: ' . $e->getMessage());
            return $this->fail('Saved failed: ' . $e->getMessage(), 500);
        }
    }

    public function show_saved_property()
    {
        try {                             // Eager load property and its images
            $show = Saved_properties::with('property.images')->where('user_id',auth()->user()->id)->get();
            if ($show->isEmpty()){
                return $this->fail('There is no properties saved',404);
            }
            return $this->success('The saved properties fetched',200,['properties'=>$show]);

        }catch (\Exception $e){
            DB::rollBack();
            Log::error('Saved failed: ' . $e->getMessage());
            return $this->fail('Saved failed: ' . $e->getMessage(), 500);
        }
    }

    public function remove_saved_property($id)
    {
        try {
            $user = auth()->user();
            if ($user){
                $sa_prop = Saved_properties::where(['user_id'=>Auth::user()->id,'property_id'=>$id])->first();
                if (!$sa_prop){
                    return $this->fail('The saved property not found or already deleted',404);
                }
                $sa_prop->delete();
                return $this->success('The saved property deleted successfully',200,$sa_prop);
            }
            return $this->fail("You don't have permission to delete this property",403);
        }catch (\Exception $e){
            Log::error('Removed failed: ' . $e->getMessage());
            return $this->fail('Removed failed: ' . $e->getMessage(), 500);
        }

    }

    public function premium($request)
    {
        try {

            $prem = new Premium();
            $prem->name = auth()->user()->name;
            $prem->office_name = $request->office_name;
            $prem->office_location = $request->office_location;
            $prem->phone = $request->phone;
            $prem->about = $request->about;
            $prem->plan = $request->plan;
            $prem->duration = $request->duration;
            $prem->user_id = auth()->user()->id;

            switch ($request->plan) {
                case 'standard':
                    $prem->max_featured = 10;
                    break;
                case 'pro':
                    $prem->max_featured = 50;
                    break;
                case 'golden':
                    $prem->max_featured = null; // null تعني "غير محدود"
                    break;
            }

            $user_exist = Premium::where('name', auth()->user()->name)->exists();
            if ($user_exist){
                return $this->fail('This user already send premium request',409);
            }
            $prem->save();

            return $this->success('Premium request has been saved',201,$prem);

        }catch (\Exception $e){
            Log::error('Store premium failed: ' . $e->getMessage());
            return $this->fail('Store premium failed: ' . $e->getMessage(), 500);
        }
    }

    public function agentDetail($id)
    {
        try {                                                                       // Eager loading
            $user = Premium::where(['user_id'=>$id,'status'=>'accepted'])->with('user.properties.images')->first();
            if (empty($user)){
                return $this->fail('There is no user has premium',404);
            }
            return $this->success('Premium user has been fetched',200,$user);
        }catch (\Exception $e){
            Log::error('Get premium user failed: ' . $e->getMessage());
            return $this->fail('Get premium user failed: ' . $e->getMessage(), 500);
        }
    }
}
