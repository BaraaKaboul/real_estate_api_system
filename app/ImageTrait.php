<?php

namespace App;

use Illuminate\Support\Facades\Storage;

trait ImageTrait
{
    public function storeImage($file,$userName){
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs("attachments/properties/".$userName,$filename, 'public');
        return $path;
    }

    public function deleteImage($path)
    {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
