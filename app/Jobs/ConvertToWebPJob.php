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
use App\Helper\ImageHelper;

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
            
            $manager = ImageHelper::manager();

            $encode = fn ($image) => $convertToExtension === 'avif'
                ? $image->toAvif(100)
                : $image->toWebp(100);

            $webpImage = $encode($manager->read($fullPath));
            Storage::disk("public")->put($pathWithoutExt.".".$convertToExtension, (string) $webpImage);

            $fullwebpPath = Storage::disk($disk)->path($pathWithoutExt.".".$convertToExtension);
            $width = $manager->read($fullwebpPath)->width();

            if (!Storage::disk($disk)->exists($pathWithoutExt."-991.".$convertToExtension)) {
                $tabletWebpImage = $encode($manager->read($fullwebpPath)->scale(width: (int) ceil($width / 2)));
                Storage::disk("public")->put($pathWithoutExt."-991.".$convertToExtension, (string) $tabletWebpImage);
            }

            if (!Storage::disk($disk)->exists($pathWithoutExt."-767.".$convertToExtension)) {
                $mobileWebpImage = $encode($manager->read($fullwebpPath)->scale(width: (int) ceil($width / 3)));
                Storage::disk("public")->put($pathWithoutExt."-767.".$convertToExtension, (string) $mobileWebpImage);
            }

        } catch (Exception $e) {
            Log::error("WebP conversion failed: " . $e->getMessage());
        }
    }
}
