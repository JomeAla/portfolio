<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait ImageUpload
{
    protected string $uploadBasePath = '/home/joalacom/public_html/public/uploads';
    protected string $defaultSubfolder = 'misc';

    public function handleImageUpload(Request $request, string $fieldName = 'image', ?string $subfolder = null): ?string
    {
        $currentValue = $request->input($fieldName);

        if (!$request->hasFile($fieldName . '_file')) {
            return $currentValue;
        }

        $file = $request->file($fieldName . '_file');
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = time() . '_' . uniqid() . '.' . $extension;
        $subfolder = $subfolder ?? $this->defaultSubfolder;
        $uploadPath = $this->uploadBasePath . '/' . $subfolder;

        try {
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            if (!is_writable($uploadPath)) {
                chmod($uploadPath, 0755);
            }

            $file->move($uploadPath, $filename);
            chmod($uploadPath . '/' . $filename, 0644);

            $path = '/uploads/' . $subfolder . '/' . $filename;
            Log::info('ImageUpload: uploaded ' . $path);
            return $path;
        } catch (\Exception $e) {
            Log::error('ImageUpload failed: ' . $e->getMessage(), [
                'field' => $fieldName,
                'subfolder' => $subfolder,
            ]);
            return $currentValue;
        }
    }

    public function handleMultipleImages(Request $request, string $fieldName, string $subfolder): array
    {
        $images = [];

        if ($request->hasFile($fieldName)) {
            $files = is_array($request->file($fieldName)) ? $request->file($fieldName) : [$request->file($fieldName)];

            foreach ($files as $idx => $file) {
                try {
                    $extension = $file->getClientOriginalExtension() ?: 'jpg';
                    $filename = time() . '_' . uniqid() . '_' . $idx . '.' . $extension;
                    $uploadPath = $this->uploadBasePath . '/' . $subfolder;

                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }

                    $file->move($uploadPath, $filename);
                    chmod($uploadPath . '/' . $filename, 0644);

                    $images[] = '/uploads/' . $subfolder . '/' . $filename;
                } catch (\Exception $e) {
                    Log::error('Multiple image upload error: ' . $e->getMessage(), ['index' => $idx]);
                }
            }
        }

        return $images;
    }

    public function deleteImage(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        $fullPath = public_path(ltrim($path, '/'));
        if (file_exists($fullPath)) {
            try {
                unlink($fullPath);
                return true;
            } catch (\Exception $e) {
                Log::error('Image delete failed: ' . $e->getMessage(), ['path' => $path]);
            }
        }

        return false;
    }

    protected function ensureUniqueSlug(string $modelClass, string $baseSlug, ?int $excludeId = null): string
    {
        $slug = $baseSlug;
        $counter = 1;

        $query = $modelClass::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
            $query = $modelClass::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }
}