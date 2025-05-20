<?php

namespace App;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\Expr\New_;
use Cloudinary\Api\Upload\UploadApi;

trait ImageTrait
{
//    public function storeImage($file,$userName){
//        $filename = time() . '_' . $file->getClientOriginalName();
//        $path = $file->storeAs("attachments/properties/".$userName,$filename, 'public');
//        return $path;
//    }
      public function storeImage($file, $options = []){
          (new UploadApi())->upload($file);
      }

    public function deleteImage($deleteUrl)
    {
        try {
            Http::withOptions(['verify' => false])
                ->timeout(30)
                ->get($deleteUrl);
        } catch (\Exception $e) {
            Log::warning('Could not delete image from imgBB: ' . $e->getMessage());
        }
    }
}
