<?php

namespace CMS\SiteManager\Support;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait ValidatesImageDimensions
{
    protected function validateImageWithinLimits(Request $request, string $field, array $config, string $label): void
    {
        $file = $request->file($field);

        if (!$file) {
            return;
        }

        $this->assertImageWithinLimits($file->getRealPath(), $file->getSize(), $config, $field, $label);
    }

    protected function validateImageArrayWithinLimits(Request $request, string $field, array $config, string $label): void
    {
        foreach ($request->file($field, []) as $index => $file) {
            if (!$file) {
                continue;
            }

            $this->assertImageWithinLimits($file->getRealPath(), $file->getSize(), $config, "{$field}.{$index}", $label);
        }
    }

    protected function assertImageWithinLimits(string $path, int $sizeBytes, array $config, string $field, string $label): void
    {
        $imageSize = @getimagesize($path);

        if (!$imageSize) {
            return;
        }

        [$width, $height] = $imageSize;
        $maxWidth = $config['width'] ?? null;
        $maxHeight = $config['height'] ?? null;
        $maxSizeKb = $config['max_size'] ?? null;

        if ($maxWidth && $width > $maxWidth) {
            throw ValidationException::withMessages([
                $field => "{$label} width must be less than or equal to {$maxWidth}px.",
            ]);
        }

        if ($maxHeight && $height > $maxHeight) {
            throw ValidationException::withMessages([
                $field => "{$label} height must be less than or equal to {$maxHeight}px.",
            ]);
        }

        if ($maxSizeKb && $sizeBytes > ($maxSizeKb * 1024)) {
            throw ValidationException::withMessages([
                $field => "{$label} size must be less than or equal to {$maxSizeKb} KB.",
            ]);
        }
    }
}
