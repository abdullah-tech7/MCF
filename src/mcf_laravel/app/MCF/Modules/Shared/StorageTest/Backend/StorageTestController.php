<?php

namespace App\MCF\Modules\Shared\StorageTest\Backend;

use App\MCF\Base\MfcController;
use App\MCF\Storage\Data\McfFileData;
use App\MCF\Storage\Data\McfStorageMultiResult;
use App\MCF\Storage\Data\McfStorageResult;
use App\MCF\Storage\Data\StorageZipData;
use Illuminate\Http\Request;

final class StorageTestController extends MfcController
{
    public function __construct(
        private readonly StorageTestService $service,
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $result = $this->service->index();

        return view(
            'Shared::StorageTest.index',
            [
                'result' => $result,
            ],
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Find
    |--------------------------------------------------------------------------
    */

    public function find(
        string $reference,
    ) {
        $result = $this->service->find(
            $reference,
        );

        return $this->redirectWithResult(
            $result,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Upload
    |--------------------------------------------------------------------------
    */

    public function upload(
        Request $request,
    ) {
        $data = new McfFileData(
            file: $request->file('file'),

            folder: $request->input(
                'folder',
                'root',
            ),

            access: $request->input(
                'access',
                'protected',
            ),

            provider: $request->input(
                'provider',
            ),

            storageRoot: $request->input(
                'storage_root',
            ),
        );

        $result = $this->service->upload(
            $data,
        );

        return $this->redirectWithResult(
            $result,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Upload Many
    |--------------------------------------------------------------------------
    */

    public function uploadMany(
        Request $request,
    ) {
        $files = $request->file(
            'files',
            [],
        );

        $fileDataList = [];

        foreach ($files as $file) {
            $fileDataList[] = new McfFileData(
                file: $file,

                folder: $request->input(
                    'folder',
                    'root',
                ),

                access: $request->input(
                    'access',
                    'protected',
                ),

                provider: $request->input(
                    'provider',
                ),

                storageRoot: $request->input(
                    'storage_root',
                ),
            );
        }

        $result = $this->service->uploadMany(
            $fileDataList,
        );

        return $this->redirectWithMultiResult(
            $result,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | View
    |--------------------------------------------------------------------------
    */

    public function view(
        string $reference,
    ) {
        $result = $this->service->view(
            $reference,
        );

        if (! $result->isSuccess()) {
            return $this->redirectWithResult(
                $result,
            );
        }

        return redirect()->away(
            $result->data->source,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Download
    |--------------------------------------------------------------------------
    */

    public function download(
        string $reference,
    ) {
        $result = $this->service->download(
            $reference,
        );

        if (! $result->isSuccess()) {
            return $this->redirectWithResult(
                $result,
            );
        }

        return $result->data;
    }


    /*
    |--------------------------------------------------------------------------
    | Download Many
    |--------------------------------------------------------------------------
    */

    public function downloadMany(
        Request $request,
    ) {
        $references = $request->input(
            'references',
            [],
        );

        if (! is_array($references)) {
            $references = [];
        }

        $result = $this->service->downloadMany(
            $references,
        );

        if (! $result->isSuccess()) {
            return $this->redirectWithResult(
                $result,
            );
        }

        $data = $result->data;

        if (! $data instanceof StorageZipData) {
            return $this->redirectWithResult(
                McfStorageResult::failure(
                    \App\MCF\Storage\Data\StorageResultCode::DOWNLOAD_FAILED,
                    'The generated storage archive is invalid.',
                ),
            );
        }

        return response()
            ->download(
                $data->path,
                $data->name,
            )
            ->deleteFileAfterSend(true);
    }


    /*
    |--------------------------------------------------------------------------
    | Metadata
    |--------------------------------------------------------------------------
    */

    public function metadata(
        string $reference,
    ) {
        $result = $this->service->metadata(
            $reference,
        );

        return $this->redirectWithResult(
            $result,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        string $reference,
    ) {
        $result = $this->service->delete(
            $reference,
        );

        return $this->redirectWithResult(
            $result,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Many
    |--------------------------------------------------------------------------
    */

    public function deleteMany(
        Request $request,
    ) {
        $references = $request->input(
            'references',
            [],
        );

        if (! is_array($references)) {
            $references = [];
        }

        $result = $this->service->deleteMany(
            $references,
        );

        return $this->redirectWithMultiResult(
            $result,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Make Public
    |--------------------------------------------------------------------------
    */

    public function makePublic(
        string $reference,
    ) {
        $result = $this->service->makePublic(
            $reference,
        );

        return $this->redirectWithResult(
            $result,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Make Protected
    |--------------------------------------------------------------------------
    */

    public function makeProtected(
        string $reference,
    ) {
        $result = $this->service->makeProtected(
            $reference,
        );

        return $this->redirectWithResult(
            $result,
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Redirect With Result
    |--------------------------------------------------------------------------
    */

    private function redirectWithResult(
        McfStorageResult $result,
    ) {
        if ($result->isSuccess()) {
            return redirect()
                ->route(
                    'shared.storageTest.index',
                )
                ->with(
                    'success',
                    __($result->message),
                );
        }

        return redirect()
            ->route(
                'shared.storageTest.index',
            )
            ->with(
                'error',
                __($result->message),
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Redirect With Multi Result
    |--------------------------------------------------------------------------
    */

    private function redirectWithMultiResult(
        McfStorageMultiResult $result,
    ) {
        if ($result->isSuccess()) {
            return redirect()
                ->route(
                    'shared.storageTest.index',
                )
                ->with(
                    'success',
                    __($result->message),
                );
        }

        return redirect()
            ->route(
                'shared.storageTest.index',
            )
            ->with(
                'error',
                __($result->message),
            );
    }
}
