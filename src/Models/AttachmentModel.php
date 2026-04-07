<?php

namespace Systha\Core\Models;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Exceptions\DecoderException;
use Intervention\Image\Exceptions\EncoderException;
use Intervention\Image\ImageManager;

class AttachmentModel extends Model
{
    protected $table = 'attachments';
    protected $guarded = [];
    protected $appends = ['url'];


    public function usages() :HasMany
    {
        return $this->hasMany(AttachmentUsageModel::class);
    }

    public function getUrlAttribute(): ?string
    {
        $path = $this->path
            ?? $this->file_name
            ?? null;

        if (!is_string($path) || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('media');

        return $disk->url($path);
    }

    public static function storeUpload(UploadedFile $file, string $directory = 'attachments'): self
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('media');
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $normalizedName = Str::of($originalName)
            ->lower()
            ->replaceMatches('/[\s_]+/', '-')
            ->slug('-');
        $originalExtension = Str::lower($file->getClientOriginalExtension());
        $storedExtension = static::shouldKeepOriginalExtension($originalExtension) ? $originalExtension : 'webp';
        $filename = sprintf(
            '%s-%s.%s',
            $normalizedName->value() !== '' ? $normalizedName->value() : 'attachment',
            Str::lower((string) Str::uuid()),
            $storedExtension
        );
        $dimensions = null;

        if (str_starts_with((string) $file->getMimeType(), 'image/')) {
            $dimensions = @getimagesize($file->getRealPath());
        }

        $path = $directory . '/' . $filename;

        if (! static::storeImagePayload($disk, $file, $path, $originalExtension)) {
            $path = $disk->putFileAs($directory, $file, sprintf(
                '%s-%s.%s',
                $normalizedName->value() !== '' ? $normalizedName->value() : 'attachment',
                Str::lower((string) Str::uuid()),
                $originalExtension
            ));
            $filename = basename($path);
        }

        return static::create([
            'disk' => 'media',
            'file_name' => $filename,
            'path' => $path,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => $file->getSize(),
            'width' => is_array($dimensions) ? ($dimensions[0] ?? null) : null,
            'height' => is_array($dimensions) ? ($dimensions[1] ?? null) : null,
        ]);
    }

    protected static function shouldKeepOriginalExtension(string $extension): bool
    {
        return in_array($extension, ['webp', 'avif'], true);
    }

    protected static function storeImagePayload(FilesystemAdapter $disk, UploadedFile $file, string $path, string $originalExtension): bool
    {
        if (static::shouldKeepOriginalExtension($originalExtension)) {
            return (bool) $disk->putFileAs(dirname($path), $file, basename($path));
        }

        if (! str_starts_with((string) $file->getMimeType(), 'image/')) {
            return false;
        }

        try {
            $image = ImageManager::gd()->read($file->getRealPath());
            $encoded = (string) $image->toWebp(90);
        } catch (DecoderException|EncoderException|\Throwable) {
            return false;
        }

        return (bool) $disk->put($path, $encoded);
    }
}
