<?php

namespace App\Repository;

use App\ImageTrait;
use App\Models\Property;
use App\Models\Saved_properties;
use App\ResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class PropertyRepository implements Interface\PropertyRepositoryInterface
{
    use ResponseTrait;
    use ImageTrait;

    public function index()
    {
        try {
            $data = Property::where('user_id',auth()->user()->id)->orderBy('created_at','DESC')->with('images')->paginate(5);
            if (!$data){
                return $this->fail('There is no property found',404);
            }
            return $this->success('Data fetched successfully',200,$data);

        }catch (\Exception $e){
            return $this->fail($e,500);
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

            $prop->save();

            $images = [];
//            if ($request->hasFile('images')) {
//                foreach ($request->file('images') as $file) {
//                    $path = $this->storeImage($file, auth()->user()->name);
//
//                    $images[] = $prop->images()->create([
//                        'filename' => time() . '/' . $file->getClientOriginalName(),
//                        'imageable_id' => $prop->id,
//                        'imageable_type'=> 'App\Models\Property'
//                    ]);
//                }
//            }
            if ($request->has('images') && is_array($request->input('images'))) {
                $imageUrls = $request->input('images');
                foreach ($imageUrls as $imageUrl) {
                    if (filter_var($imageUrl, FILTER_VALIDATE_URL)) { // تحقق إضافي أن كل عنصر هو URL صالح
                        // افترض أن لديك علاقة 'images' في موديل Property
                        // وأن موديل Image لديه عمود 'url' أو 'link' أو 'path' لتخزين الرابط الكامل
                        // أو إذا كنت لا تزال تستخدم 'filename' لتخزين الرابط الكامل، لا بأس بذلك مؤقتًا
                        // لكن من الأفضل أن يكون اسم العمود معبرًا مثل 'url'.

                        // مثال إذا كان عمودك اسمه 'url' في جدول الصور:
                        $imageModel = $prop->images()->create([
                            'filename' => $imageUrl, // <--- حفظ رابط Cloudinary الكامل
                             'imageable_id' => $prop->id, // هذا سيتم تعيينه تلقائيًا بواسطة العلاقة
                             'imageable_type'=> 'App\Models\Property' // هذا سيتم تعيينه تلقائيًا بواسطة العلاقة
                        ]);
                        $savedImageModels[] = $imageModel;

                        // مثال إذا كنت ستستخدم عمود 'filename' مؤقتًا لحفظ الرابط الكامل:
                        // $imageModel = $prop->images()->create(['filename' => $imageUrl]);
                        // $savedImageModels[] = $imageModel;

                    } else {
                        Log::warning('Invalid URL found in images array:', ['filename' => $imageUrl]);
                    }
                }
            }
            DB::commit();
            //return $this->success('Data has been stored successfully',201,['property'=>$prop, 'images'=>$images]);
            return $this->success(
                'Data has been stored successfully',
                201,
                ['property' => $prop->load('images')] // افترض أن لديك علاقة اسمها 'images' في موديل Property
            // أو أرجع $savedImageModels إذا كنت تفضل
            );

        }catch (\Exception $e){
            DB::rollBack();
            return $this->fail($e,500);
        }
    }

    public function update($request, Property $property)
    {
        if ($property->user_id != auth()->id()) {
            return $this->fail('Unauthorized to update this property.', 403);
        }

        $validator = Validator::make($request->all(), [
            'title'        => 'required|string|max:255',
            'description'  => 'required|string',
            'price'        => 'required|numeric|min:0',
            'area'         => 'required|numeric|min:0',
            'type'         => ['required', Rule::in(['house', 'commercial'])], // Make sure Rule is imported
            'purpose'      => ['required', Rule::in(['sale', 'rent'])],
            'phone'        => 'required|string|max:20',
            'balconies'    => 'nullable|integer|min:0',
            'bedrooms'     => 'nullable|integer|min:0',
            'bathrooms'    => 'nullable|integer|min:0',
            'livingRooms'  => 'nullable|integer|min:0',
            'location_lat' => 'required|numeric|between:-90,90',
            'location_lon' => 'required|numeric|between:-180,180',
            'address'      => 'required|string|max:500',
            'images'       => 'nullable|array', // Validate 'images' key is an array if present
            'images.*'     => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048' // Validate each image
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return $this->fail($validator->errors(), 400); // Return validation errors
        }

        // Get ONLY validated data
        $validatedData = $validator->validated();


        DB::beginTransaction();
        try {
            $property->update($validatedData); // This performs an UPDATE query
            //$property->status = 'pending';

            $newlyUploadedImages = [];
            if ($request->hasFile('images')) {
                // Delete existing images
                foreach ($property->images as $oldImage) {
                    $this->deleteImage($oldImage->filename);
                    $oldImage->delete();
                }

                // Store new images
                foreach ($request->file('images') as $file) {
                    $path = $this->storeImage($file, auth()->user()->name);

                    $newlyUploadedImages[] = $property->images()->create([
                        'filename' => $path,
                        'imageable_id' => $property->id,
                        'imageable_type'=> 'App\Models\Property'
                    ]);
                }
            }

            DB::commit();

            // Load the relationship to ensure it's fresh for the response.
            $property->load('images');

            return $this->success('Property updated successfully', 200, [
                'property' => $property,
                'images' => !empty($newlyUploadedImages) ? $newlyUploadedImages : $property->images
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
    }
}
