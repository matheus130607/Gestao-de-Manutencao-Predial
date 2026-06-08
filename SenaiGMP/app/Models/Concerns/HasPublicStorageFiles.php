<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasPublicStorageFiles
{
    public function publicStoragePath(?string $path): ?string
    {
        $normalized = $this->normalizePublicStoragePath($path);

        if ($normalized === null) {
            return null;
        }

        if (Str::startsWith($normalized, ['http://', 'https://'])) {
            return $normalized;
        }

        return Storage::disk('public')->exists($normalized) ? $normalized : null;
    }

    public function publicStorageUrl(?string $path): ?string
    {
        $normalized = $this->normalizePublicStoragePath($path);

        if ($normalized === null) {
            return null;
        }

        if (Str::startsWith($normalized, ['http://', 'https://'])) {
            return $normalized;
        }

        return Storage::disk('public')->exists($normalized)
            ? Storage::disk('public')->url($normalized)
            : null;
    }

    private function normalizePublicStoragePath(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $normalized = str_replace('\\', '/', trim($path));
        $normalized = preg_replace('#^/+#', '', $normalized) ?? $normalized;

        foreach (['storage/', 'public/'] as $prefix) {
            if (Str::startsWith($normalized, $prefix)) {
                $normalized = Str::after($normalized, $prefix);
            }
        }

        return $normalized !== '' ? $normalized : null;
    }
}
