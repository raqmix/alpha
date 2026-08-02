<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManagerStatic;

class ConvertToWebPJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $imagePath;
    protected $pathWithoutExt;
    protected $disk;

    /**
     * Create a new job instance.
     */
    public function __construct($imagePath, $pathWithoutExt, $disk)
    {
        $this->imagePath = $imagePath;
        $this->pathWithoutExt = $pathWithoutExt;
        $this->disk = $disk;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $imagePath = $this->imagePath;
        $pathWithoutExt = $this->pathWithoutExt;
        $disk = $this->disk;

        try 
        {
            $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
            if($extension == "gif"){
                $convertToExtension = "avif";
            }
            else{
                $convertToExtension = "webp";   
            }

            if($disk)
            {
                $fullPath = Storage::disk($disk)->path($imagePath);
            }
            else
            {
                $fullPath = public_path($imagePath);
            }
            
            $webpImage = ImageManagerStatic::make($fullPath)->encode($convertToExtension, 100);
            Storage::disk("public")->put($pathWithoutExt.".".$convertToExtension, $webpImage);

            $fullwebpPath = Storage::disk($disk)->path($pathWithoutExt.".".$convertToExtension);
            $image = Image::make($fullwebpPath);
            $width = $image->width();

            if (!Storage::disk($disk)->exists($pathWithoutExt."-991.".$convertToExtension)) {
                $tabletWebpImage = Image::make($fullwebpPath)->widen(ceil($width / 2))->stream();
                Storage::disk("public")->put($pathWithoutExt."-991.".$convertToExtension, $tabletWebpImage);
            }

            if (!Storage::disk($disk)->exists($pathWithoutExt."-767.".$convertToExtension)) {
                $mobileWebpImage = Image::make($fullwebpPath)->widen(ceil($width / 3))->stream();
                Storage::disk("public")->put($pathWithoutExt."-767.".$convertToExtension, $mobileWebpImage);
            }

        } catch (Exception $e) {
            Log::error("WebP conversion failed: " . $e->getMessage());
        }
    }
}
