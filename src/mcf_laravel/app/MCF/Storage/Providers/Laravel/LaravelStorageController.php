<?php

namespace App\MCF\Storage\Providers\Laravel;

use App\MCF\Storage\Data\StorageReference;
use App\MCF\Storage\Registry\StorageRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class LaravelStorageController
{
    /*
    |--------------------------------------------------------------------------
    | Public
    |--------------------------------------------------------------------------
    */

    public function public(
        string $reference,
    ): StreamedResponse {
        return $this->serveFile(
            $reference,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Temporary
    |--------------------------------------------------------------------------
    */

    public function temporary(
        Request $request,
        string $reference,
    ): StreamedResponse {
        if (!$request->hasValidSignature()) {
            abort(403);
        }

        return $this->serveFile(
            $reference,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Serve
    |--------------------------------------------------------------------------
    */

    private function serveFile(
        string $reference,
    ): StreamedResponse {
        $registry = new StorageRegistry();

        $storageReference = StorageReference::fromString(
            $reference,
        );

        $record = $registry->find(
            $storageReference,
        );

        if ($record === null) {
            abort(404);
        }

        if ($record->provider !== 'laravel') {
            abort(404);
        }

        $disk = Storage::disk(
            $record->storageRoot,
        );

        $path = $this->buildPath(
            $record->folder,
            (string) $record->reference(),
        );

        if (!$disk->exists($path)) {
            abort(404);
        }

        $stream = $disk->readStream(
            $path,
        );

        if ($stream === false || $stream === null) {
            abort(404);
        }

        $mimeType = $disk->mimeType(
            $path,
        );

        $size = $disk->size(
            $path,
        );

        $fileName = $this->escapeFileName(
            $record->originalName,
        );

        return response()->stream(
            function () use ($stream): void {
                fpassthru($stream);

                fclose($stream);
            },
            200,
            [
                'Content-Type' => $mimeType ?: 'application/octet-stream',

                'Content-Length' => $size,

                'Content-Disposition' =>
                    'inline; filename="' . $fileName . '"',

                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Internal
    |--------------------------------------------------------------------------
    */

    private function buildPath(
        string $folder,
        string $reference,
    ): string {
        $folder = trim($folder, '/');
        $reference = trim($reference, '/');

        if (
            $folder === ''
            || strtolower($folder) === 'root'
        ) {
            return $reference;
        }

        return $folder . '/' . $reference;
    }

    private function escapeFileName(
        string $fileName,
    ): string {
        return addcslashes(
            $fileName,
            "\"\\",
        );
    }
}
