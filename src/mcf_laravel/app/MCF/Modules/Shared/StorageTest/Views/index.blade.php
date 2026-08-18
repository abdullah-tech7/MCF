@extends('Shared::Layout.app')


@section('content')

<style>
    .storage-test {
        padding: 24px;
    }

    .storage-test h1 {
        margin: 0 0 24px;
    }

    .storage-test-upload {
        padding: 16px;
        margin-bottom: 24px;
        border: 1px solid #ddd;
        border-radius: 8px;
    }

    .storage-test-upload form {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
    }

    .storage-test-bulk-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 16px;
        align-items: center;
    }

    .storage-test-bulk-actions button {
        padding: 7px 12px;
        border: 1px solid #ccc;
        border-radius: 4px;
        background: #fff;
        color: #222;
        cursor: pointer;
        font-size: 13px;
    }

    .storage-test-table {
        width: 100%;
        border-collapse: collapse;
    }

    .storage-test-table th,
    .storage-test-table td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
        vertical-align: middle;
    }

    .storage-test-table th {
        background: #f5f5f5;
    }

    .storage-test-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .storage-test-actions form {
        display: inline;
    }

    .storage-test-actions a,
    .storage-test-actions button {
        padding: 6px 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        background: #fff;
        color: #222;
        text-decoration: none;
        cursor: pointer;
        font-size: 13px;
    }

    .storage-test-select {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    .storage-test-empty {
        padding: 24px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 8px;
    }

    .storage-test-error {
        padding: 16px;
        margin-bottom: 24px;
        border: 1px solid #dc3545;
        border-radius: 8px;
        color: #842029;
        background: #f8d7da;
    }

    .storage-test-success {
        padding: 16px;
        margin-bottom: 24px;
        border: 1px solid #198754;
        border-radius: 8px;
        color: #0f5132;
        background: #d1e7dd;
    }
</style>


<div class="storage-test">

    <h1>
        {{ __('Storage Test') }}
    </h1>




    {{-- Single Upload --}}

    <div class="storage-test-upload">

        <form
            action="{{ route('shared.storageTest.upload') }}"
            method="POST"
            enctype="multipart/form-data"
        >

            @csrf

            <input
                type="file"
                name="file"
                required
            >

            <input
                type="text"
                name="folder"
                value="root"
                placeholder="{{ __('Folder') }}"
            >

            <select name="access">

                <option value="protected">
                    {{ __('Protected') }}
                </option>

                <option value="public">
                    {{ __('Public') }}
                </option>

            </select>

            <button type="submit">
                {{ __('Upload') }}
            </button>

        </form>

    </div>


    {{-- Multi Upload --}}

    <div class="storage-test-upload">

        <form
            action="{{ route('shared.storageTest.uploadMany') }}"
            method="POST"
            enctype="multipart/form-data"
        >

            @csrf

            <input
                type="file"
                name="files[]"
                multiple
                required
            >

            <input
                type="text"
                name="folder"
                value="root"
                placeholder="{{ __('Folder') }}"
            >

            <select name="access">

                <option value="protected">
                    {{ __('Protected') }}
                </option>

                <option value="public">
                    {{ __('Public') }}
                </option>

            </select>

            <button type="submit">
                {{ __('Upload Multiple') }}
            </button>

        </form>

    </div>


    {{-- Storage Result --}}

    @if (
        isset($result)
        && $result->isFailure()
    )

        <div class="storage-test-error">

            <strong>
                {{ __('Storage Error') }}
            </strong>

            <div>
                {{ __($result->message) }}
            </div>

        </div>

    @elseif (
        isset($result)
        && is_array($result->data)
        && count($result->data) > 0
    )

        {{-- Bulk Actions --}}

        <div class="storage-test-bulk-actions">

            <button
                type="button"
                id="storage-test-download"
            >
                {{ __('Download Selected') }}
            </button>

            <button
                type="button"
                id="storage-test-delete"
            >
                {{ __('Delete Selected') }}
            </button>

        </div>


        {{-- Multi Download Form --}}

        <form
            id="storage-test-download-many-form"
            action="{{ route('shared.storageTest.downloadMany') }}"
            method="POST"
        >

            @csrf

        </form>


        {{-- Multi Delete Form --}}

        <form
            id="storage-test-delete-many-form"
            action="{{ route('shared.storageTest.deleteMany') }}"
            method="POST"
        >

            @csrf
            @method('DELETE')

        </form>


        {{-- Storage Records --}}

        <table class="storage-test-table">

            <thead>

                <tr>

                    <th>

                        <input
                            type="checkbox"
                            id="storage-test-select-all"
                            class="storage-test-select"
                        >

                    </th>

                    <th>{{ __('Reference') }}</th>
                    <th>{{ __('Original Name') }}</th>
                    <th>{{ __('Extension') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('MIME Type') }}</th>
                    <th>{{ __('Size') }}</th>
                    <th>{{ __('Folder') }}</th>
                    <th>{{ __('Provider') }}</th>
                    <th>{{ __('Storage Root') }}</th>
                    <th>{{ __('Access') }}</th>
                    <th>{{ __('Actions') }}</th>

                </tr>

            </thead>


            <tbody>

                @foreach ($result->data as $record)

                    <tr>

                        <td>

                            <input
                                type="checkbox"
                                class="storage-test-select storage-test-file-checkbox"
                                value="{{ $record->reference() }}"
                            >

                        </td>


                        <td>
                            {{ $record->reference() }}
                        </td>


                        <td>
                            {{ $record->originalName }}
                        </td>


                        <td>
                            {{ $record->extension }}
                        </td>


                        <td>
                            {{ $record->type }}
                        </td>


                        <td>
                            {{ $record->mimeType }}
                        </td>


                        <td>
                            {{ $record->size }}
                        </td>


                        <td>
                            {{ $record->folder }}
                        </td>


                        <td>
                            {{ $record->provider }}
                        </td>


                        <td>
                            {{ $record->storageRoot }}
                        </td>


                        <td>
                            {{ $record->access }}
                        </td>


                        <td>

                            <div class="storage-test-actions">

                                {{-- View --}}

                                <a
                                    href="{{ route(
                                        'shared.storageTest.view',
                                        [
                                            'reference' => $record->reference(),
                                        ]
                                    ) }}"
                                    target="_blank"
                                >
                                    {{ __('View') }}
                                </a>


                                {{-- Download --}}

                                <a
                                    href="{{ route(
                                        'shared.storageTest.download',
                                        [
                                            'reference' => $record->reference(),
                                        ]
                                    ) }}"
                                >
                                    {{ __('Download') }}
                                </a>


                                {{-- Metadata --}}

                                <a
                                    href="{{ route(
                                        'shared.storageTest.metadata',
                                        [
                                            'reference' => $record->reference(),
                                        ]
                                    ) }}"
                                >
                                    {{ __('Metadata') }}
                                </a>


                                {{-- Find --}}

                                <a
                                    href="{{ route(
                                        'shared.storageTest.find',
                                        [
                                            'reference' => $record->reference(),
                                        ]
                                    ) }}"
                                >
                                    {{ __('Find') }}
                                </a>


                                {{-- Access --}}

                                @if ($record->isPublic())

                                    <form
                                        action="{{ route(
                                            'shared.storageTest.makeProtected',
                                            [
                                                'reference' => $record->reference(),
                                            ]
                                        ) }}"
                                        method="POST"
                                    >

                                        @csrf
                                        @method('PATCH')

                                        <button type="submit">
                                            {{ __('Make Protected') }}
                                        </button>

                                    </form>

                                @else

                                    <form
                                        action="{{ route(
                                            'shared.storageTest.makePublic',
                                            [
                                                'reference' => $record->reference(),
                                            ]
                                        ) }}"
                                        method="POST"
                                    >

                                        @csrf
                                        @method('PATCH')

                                        <button type="submit">
                                            {{ __('Make Public') }}
                                        </button>

                                    </form>

                                @endif


                                {{-- Delete --}}

                                <form
                                    action="{{ route(
                                        'shared.storageTest.delete',
                                        [
                                            'reference' => $record->reference(),
                                        ]
                                    ) }}"
                                    method="POST"
                                >

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit">
                                        {{ __('Delete') }}
                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    @else

        <div class="storage-test-empty">
            {{ __('No files found.') }}
        </div>

    @endif

</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {

        const selectAll = document.getElementById(
            'storage-test-select-all'
        );

        const checkboxes = document.querySelectorAll(
            '.storage-test-file-checkbox'
        );

        const downloadButton = document.getElementById(
            'storage-test-download'
        );

        const deleteButton = document.getElementById(
            'storage-test-delete'
        );

        const downloadManyForm = document.getElementById(
            'storage-test-download-many-form'
        );

        const deleteManyForm = document.getElementById(
            'storage-test-delete-many-form'
        );


        /*
        |--------------------------------------------------------------------------
        | Nothing To Select
        |--------------------------------------------------------------------------
        */

        if (!selectAll) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Select All
        |--------------------------------------------------------------------------
        */

        selectAll.addEventListener(
            'change',
            function () {

                checkboxes.forEach(
                    function (checkbox) {

                        checkbox.checked =
                            selectAll.checked;

                    }
                );

                selectAll.indeterminate = false;

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Individual Selection
        |--------------------------------------------------------------------------
        */

        checkboxes.forEach(
            function (checkbox) {

                checkbox.addEventListener(
                    'change',
                    function () {

                        const checkedCount =
                            document.querySelectorAll(
                                '.storage-test-file-checkbox:checked'
                            ).length;

                        selectAll.checked =
                            checkedCount === checkboxes.length;

                        selectAll.indeterminate =
                            checkedCount > 0
                            && checkedCount < checkboxes.length;

                    }
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Get Selected References
        |--------------------------------------------------------------------------
        */

        function getSelectedReferences() {

            return Array.from(
                document.querySelectorAll(
                    '.storage-test-file-checkbox:checked'
                )
            ).map(
                function (checkbox) {
                    return checkbox.value;
                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Add References To Form
        |--------------------------------------------------------------------------
        */

        function addReferencesToForm(
            form,
            references,
        ) {

            form.querySelectorAll(
                'input[name="references[]"]'
            ).forEach(
                function (input) {
                    input.remove();
                }
            );


            references.forEach(
                function (reference) {

                    const input =
                        document.createElement('input');

                    input.type = 'hidden';
                    input.name = 'references[]';
                    input.value = reference;

                    form.appendChild(input);

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Download Selected
        |--------------------------------------------------------------------------
        |
        | 0
        |   Do nothing.
        |
        | 1
        |   Use normal single download.
        |
        | 2+
        |   Use downloadMany().
        |
        */

        if (downloadButton) {

            downloadButton.addEventListener(
                'click',
                function () {

                    const references =
                        getSelectedReferences();


                    if (references.length === 0) {
                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Single
                    |--------------------------------------------------------------------------
                    */

                    if (references.length === 1) {

                        const reference =
                            references[0];

                        window.location.href =
                            "{{ url('/storageTest') }}"
                            + "/"
                            + encodeURIComponent(reference)
                            + "/download";

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Many
                    |--------------------------------------------------------------------------
                    */

                    addReferencesToForm(
                        downloadManyForm,
                        references,
                    );

                    downloadManyForm.submit();

                }
            );

        }


      /*
|--------------------------------------------------------------------------
| Delete Selected
|--------------------------------------------------------------------------
|
| 0
|   Do nothing.
|
| 1
|   Use normal single delete.
|
| 2+
|   Use deleteMany().
|
*/

if (deleteButton) {

    deleteButton.addEventListener(
        'click',
        function () {

            const references =
                getSelectedReferences();


            if (references.length === 0) {
                return;
            }


            if (! confirm(
                "{{ __('Are you sure you want to delete the selected files?') }}"
            )) {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Single
            |--------------------------------------------------------------------------
            */

            if (references.length === 1) {

                const reference =
                    references[0];

                const form =
                    document.createElement('form');

                form.method = 'POST';

                form.action =
                    "{{ url('/storageTest') }}"
                    + "/"
                    + encodeURIComponent(reference);

                const csrf =
                    document.createElement('input');

                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value =
                    "{{ csrf_token() }}";

                form.appendChild(csrf);


                const method =
                    document.createElement('input');

                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';

                form.appendChild(method);


                document.body.appendChild(form);

                form.submit();

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Many
            |--------------------------------------------------------------------------
            */

            addReferencesToForm(
                deleteManyForm,
                references,
            );

            deleteManyForm.submit();

        }
    );

}
    });
</script>

@endsection
