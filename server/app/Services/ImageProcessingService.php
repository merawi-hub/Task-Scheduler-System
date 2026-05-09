<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageProcessingService
{
    private ImageManager $imageManager;

    public function __construct()
    {
        // Initialize Image Manager with GD driver
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Process a batch of images
     *
     * @param array $imagePaths Array of image file paths
     * @param array $operations Operations to perform (resize, compress, thumbnail)
     * @return array Results with processed image paths
     */
    public function processBatch(array $imagePaths, array $operations = []): array
    {
        $results = [];
        
        foreach ($imagePaths as $imagePath) {
            try {
                $result = $this->processImage($imagePath, $operations);
                $results[] = [
                    'original' => $imagePath,
                    'processed' => $result,
                    'status' => 'success'
                ];
            } catch (\Exception $e) {
                Log::error("Image processing failed for {$imagePath}: " . $e->getMessage());
                $results[] = [
                    'original' => $imagePath,
                    'processed' => null,
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Process a single image
     *
     * @param string $imagePath Path to the image file
     * @param array $operations Operations to perform
     * @return array Paths to processed images
     */
    public function processImage(string $imagePath, array $operations = []): array
    {
        $processedPaths = [];

        // Get the image from storage
        if (!Storage::disk('public')->exists($imagePath)) {
            throw new \Exception("Image not found: {$imagePath}");
        }

        $fullPath = Storage::disk('public')->path($imagePath);
        $image = $this->imageManager->read($fullPath);

        // Get filename without extension
        $pathInfo = pathinfo($imagePath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'];

        // Perform operations
        if (in_array('resize', $operations) || empty($operations)) {
            $resizedPath = $this->resizeImage($image, $directory, $filename, $extension);
            $processedPaths['resized'] = $resizedPath;
        }

        if (in_array('compress', $operations) || empty($operations)) {
            $compressedPath = $this->compressImage($image, $directory, $filename, $extension);
            $processedPaths['compressed'] = $compressedPath;
        }

        if (in_array('thumbnail', $operations) || empty($operations)) {
            $thumbnailPath = $this->createThumbnail($image, $directory, $filename, $extension);
            $processedPaths['thumbnail'] = $thumbnailPath;
        }

        return $processedPaths;
    }

    /**
     * Resize image to standard dimensions
     */
    private function resizeImage($image, string $directory, string $filename, string $extension): string
    {
        $resizedImage = clone $image;
        $resizedImage->scale(width: 1920, height: 1080);
        
        $outputPath = "{$directory}/processed/{$filename}_resized.{$extension}";
        $fullOutputPath = Storage::disk('public')->path($outputPath);
        
        // Ensure directory exists
        $outputDir = dirname($fullOutputPath);
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $resizedImage->save($fullOutputPath);
        
        return $outputPath;
    }

    /**
     * Compress image to reduce file size
     */
    private function compressImage($image, string $directory, string $filename, string $extension): string
    {
        $compressedImage = clone $image;
        
        $outputPath = "{$directory}/processed/{$filename}_compressed.{$extension}";
        $fullOutputPath = Storage::disk('public')->path($outputPath);
        
        // Ensure directory exists
        $outputDir = dirname($fullOutputPath);
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Save with compression (quality 75 for JPEG)
        if (strtolower($extension) === 'jpg' || strtolower($extension) === 'jpeg') {
            $compressedImage->toJpeg(quality: 75)->save($fullOutputPath);
        } else {
            $compressedImage->save($fullOutputPath);
        }
        
        return $outputPath;
    }

    /**
     * Create thumbnail
     */
    private function createThumbnail($image, string $directory, string $filename, string $extension): string
    {
        $thumbnailImage = clone $image;
        $thumbnailImage->scale(width: 300, height: 300);
        
        $outputPath = "{$directory}/processed/{$filename}_thumbnail.{$extension}";
        $fullOutputPath = Storage::disk('public')->path($outputPath);
        
        // Ensure directory exists
        $outputDir = dirname($fullOutputPath);
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $thumbnailImage->save($fullOutputPath);
        
        return $outputPath;
    }

    /**
     * Get image dimensions
     */
    public function getImageDimensions(string $imagePath): array
    {
        if (!Storage::disk('public')->exists($imagePath)) {
            throw new \Exception("Image not found: {$imagePath}");
        }

        $fullPath = Storage::disk('public')->path($imagePath);
        $image = $this->imageManager->read($fullPath);

        return [
            'width' => $image->width(),
            'height' => $image->height()
        ];
    }

    /**
     * Validate image file
     */
    public function validateImage(string $imagePath): bool
    {
        try {
            if (!Storage::disk('public')->exists($imagePath)) {
                return false;
            }

            $fullPath = Storage::disk('public')->path($imagePath);
            $this->imageManager->read($fullPath);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
