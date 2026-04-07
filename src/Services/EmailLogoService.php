<?php

namespace Systha\Core\Services;

use Illuminate\Support\Facades\Storage;
use Systha\Core\Models\AttachmentModel;
use Systha\Core\Models\Company;
use Systha\Core\Models\Vendor;

class EmailLogoService
{
    public function companyLogoDataUri(?Company $company, string $fallback = 'images/noimage.png'): string
    {
        $fallbackPath = public_path($fallback);
        $fallbackUri = $this->fileToDataUri($fallbackPath) ?? $fallback;

        if (! $company instanceof Company) {
            return $fallbackUri;
        }

        $attachment = $company->primaryLogo->attachment ?? null;

        return $this->attachmentToDataUri($attachment)
            ?? $this->urlToDataUri($attachment?->url)
            ?? $fallbackUri;
    }

    public function vendorLogoDataUri(?Vendor $vendor, string $fallback = 'images/noimage.png'): string
    {
        $fallbackPath = public_path($fallback);
        $fallbackUri = $this->fileToDataUri($fallbackPath) ?? $fallback;

        if (! $vendor instanceof Vendor) {
            return $fallbackUri;
        }

        $profilePic = $vendor->profile_pic;

        // Try media disk path first if available
        if ($profilePic && Storage::disk('media')->exists($profilePic)) {
            $contents = Storage::disk('media')->get($profilePic);
            $mime = $this->mimeFromExtension(pathinfo($profilePic, PATHINFO_EXTENSION))
                ?? $this->mimeFromBuffer($contents)
                ?? 'image/png';

            return $this->toDataUri($mime, $contents);
        }

        // Next, try vendor logo URL accessor (may be route URL)
        $logoUrl = $vendor->logo ?? null;

        return $this->urlToDataUri($logoUrl)
            ?? $fallbackUri;
    }

    private function attachmentToDataUri(?AttachmentModel $attachment): ?string
    {
        if (! $attachment) {
            return null;
        }

        $disk = $attachment->disk ?: 'media';
        $path = $attachment->path ?: $attachment->file_name;

        if (! $path || ! Storage::disk($disk)->exists($path)) {
            return null;
        }

        $contents = Storage::disk($disk)->get($path);

        $mime = $this->mimeFromExtension(pathinfo($path, PATHINFO_EXTENSION))
            ?? $this->mimeFromBuffer($contents)
            ?? 'image/png';

        return $this->toDataUri($mime, $contents);
    }

    private function urlToDataUri(?string $url): ?string
    {
        if (! $url || ! str_starts_with($url, 'http')) {
            return null;
        }

        $contents = @file_get_contents($url);
        if ($contents === false) {
            return null;
        }

        $mime = $this->mimeFromExtension(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION))
            ?? $this->mimeFromBuffer($contents)
            ?? 'image/png';

        return $this->toDataUri($mime, $contents);
    }

    private function fileToDataUri(string $path): ?string
    {
        if (! is_file($path)) {
            return null;
        }

        $contents = @file_get_contents($path);
        if ($contents === false) {
            return null;
        }

        $mime = $this->mimeFromExtension(pathinfo($path, PATHINFO_EXTENSION))
            ?? $this->mimeFromBuffer($contents)
            ?? 'image/png';

        return $this->toDataUri($mime, $contents);
    }

    private function mimeFromExtension(?string $extension): ?string
    {
        if (! $extension) {
            return null;
        }

        $ext = strtolower($extension);
        return match ($ext) {
            'jpg' => 'image/jpeg',
            'jpeg', 'png', 'gif', 'webp', 'svg' => 'image/' . $ext,
            default => null,
        };
    }

    private function mimeFromBuffer(string $contents): ?string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if (! $finfo) {
            return null;
        }
        $mime = finfo_buffer($finfo, $contents) ?: null;
        finfo_close($finfo);

        return $mime;
    }

    private function toDataUri(string $mime, string $contents): string
    {
        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }
}
