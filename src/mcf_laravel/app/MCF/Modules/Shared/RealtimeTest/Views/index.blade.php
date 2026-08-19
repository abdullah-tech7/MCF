@extends('Shared::Layout.app')


@section('content')

<style>
    .realtime-test {
        padding: 24px;
    }

    .realtime-test h1 {
        margin: 0 0 20px;
    }

    .realtime-test-summary {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
        padding: 14px 16px;
        border: 1px solid #ddd;
        border-radius: 8px;
    }

    .realtime-test-count {
        font-size: 18px;
    }

    .realtime-test-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 20px;
    }

    .realtime-test-actions form {
        margin: 0;
    }

    .realtime-test button,
    .realtime-test a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 6px 12px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background: #fff;
        color: #222;
        text-decoration: none;
        cursor: pointer;
        font-size: 13px;
        box-sizing: border-box;
    }

    .realtime-test button:hover,
    .realtime-test a:hover {
        background: #f5f5f5;
    }

    .realtime-test-table-wrapper {
        overflow-x: auto;
    }

    .realtime-test-table {
        width: 100%;
        border-collapse: collapse;
    }

    .realtime-test-table th,
    .realtime-test-table td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: start;
        vertical-align: middle;
    }

    .realtime-test-table th {
        background: #f5f5f5;
    }

    .realtime-test-table th:first-child,
    .realtime-test-table td:first-child {
        width: 40px;
        text-align: center;
    }

    .realtime-test-row-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .realtime-test-empty {
        padding: 24px;
        border: 1px solid #ddd;
        border-radius: 8px;
        text-align: center;
    }

    .realtime-test-selection {
        margin-bottom: 12px;
        font-size: 13px;
    }

    .realtime-test-title-link {
        font-weight: 600;
    }
</style>


<div class="realtime-test">

    <h1>
        {{ __('Realtime Test') }}
    </h1>


    {{-- Unread Count --}}

    <div class="realtime-test-summary">

        <span>
            {{ __('Unread') }}:
        </span>

        <strong
            id="realtime-test-count"
            class="realtime-test-count"
        >
            {{ $state['count'] }}
        </strong>

    </div>


    {{-- Global Actions --}}

    <div class="realtime-test-actions">

        {{-- Add Random Notification --}}

        <form
            action="{{ route(
                'shared.realtimeTest.addNotification'
            ) }}"
            method="POST"
        >
            @csrf

            <button type="submit">
                {{ __('Add Random Notification') }}
            </button>
        </form>


        {{-- Mark All As Read --}}

        <form
            action="{{ route(
                'shared.realtimeTest.markAllAsRead'
            ) }}"
            method="POST"
        >
            @csrf
            @method('PATCH')

            <button type="submit">
                {{ __('Mark All As Read') }}
            </button>
        </form>


        {{-- Mark All As Unread --}}

        <form
            action="{{ route(
                'shared.realtimeTest.markAllAsUnread'
            ) }}"
            method="POST"
        >
            @csrf
            @method('PATCH')

            <button type="submit">
                {{ __('Mark All As Unread') }}
            </button>
        </form>


        {{-- Delete All --}}

        <form
            action="{{ route(
                'shared.realtimeTest.deleteAll'
            ) }}"
            method="POST"
        >
            @csrf
            @method('DELETE')

            <button type="submit">
                {{ __('Delete All') }}
            </button>
        </form>

    </div>


    {{-- Selected Actions --}}

    <div class="realtime-test-actions">

        <button
            type="button"
            id="mark-selected-read"
        >
            {{ __('Mark Selected As Read') }}
        </button>

        <button
            type="button"
            id="delete-selected"
        >
            {{ __('Delete Selected') }}
        </button>

    </div>


    <div
        id="realtime-test-selection"
        class="realtime-test-selection"
    >
        {{ __('Selected') }}: 0
    </div>


    {{-- Unread Notifications Only --}}

    <div
        id="realtime-test-list"
        class="realtime-test-table-wrapper"
    >

        @if (
            count($state['notifications']) > 0
        )

            <table class="realtime-test-table">

                <thead>

                    <tr>

                        <th>
                            <input
                                type="checkbox"
                                id="select-all"
                            >
                        </th>

                        <th>
                            {{ __('Title') }}
                        </th>

                        <th>
                            {{ __('Message') }}
                        </th>

                        <th>
                            {{ __('Date') }}
                        </th>

                        <th>
                            {{ __('Actions') }}
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @foreach (
                        $state['notifications']
                        as $notification
                    )

                        <tr>

                            <td>
                                <input
                                    type="checkbox"
                                    class="notification-select"
                                    value="{{ $notification['id'] }}"
                                >
                            </td>

                            <td>

                                @if (
                                    ! empty(
                                        $notification['url']
                                    )
                                )

                                    <a
                                        href="{{ $notification['url'] }}"
                                        class="realtime-test-title-link"
                                    >
                                        {{ $notification['title'] }}
                                    </a>

                                @else

                                    {{ $notification['title'] }}

                                @endif

                            </td>

                            <td>
                                {{ $notification['message'] }}
                            </td>

                            <td>
                                {{ $notification['created_at'] }}
                            </td>

                            <td>

                                <div
                                    class="realtime-test-row-actions"
                                >

                                    @if (
                                        ! empty(
                                            $notification['url']
                                        )
                                    )

                                        <a
                                            href="{{ $notification['url'] }}"
                                        >
                                            {{ __('Open') }}
                                        </a>

                                    @endif


                                    <form
                                        action="{{ route(
                                            'shared.realtimeTest.markAsRead',
                                            [
                                                'notification' =>
                                                    $notification['id'],
                                            ]
                                        ) }}"
                                        method="POST"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit">
                                            {{ __('Mark As Read') }}
                                        </button>
                                    </form>


                                    <form
                                        action="{{ route(
                                            'shared.realtimeTest.delete',
                                            [
                                                'notification' =>
                                                    $notification['id'],
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

            <div class="realtime-test-empty">
                {{ __('No unread notifications.') }}
            </div>

        @endif

    </div>

</div>


<script>
    MCF.realtime('notifications', {

        onUpdate: function (state) {

            /*
            |--------------------------------------------------------------------------
            | Update Count
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                'realtime-test-count'
            ).textContent = state.count;


            /*
            |--------------------------------------------------------------------------
            | Update List
            |--------------------------------------------------------------------------
            */

            const container =
                document.getElementById(
                    'realtime-test-list'
                );


            if (
                state.notifications.length === 0
            ) {
                container.innerHTML = `
                    <div class="realtime-test-empty">
                        @json(__('No unread notifications.'))
                    </div>
                `;

                updateSelectionCount();

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Table
            |--------------------------------------------------------------------------
            */

            const table =
                document.createElement('table');

            table.className =
                'realtime-test-table';


            const thead =
                document.createElement('thead');

            thead.innerHTML = `
                <tr>
                    <th>
                        <input
                            type="checkbox"
                            id="select-all"
                        >
                    </th>

                    <th>
                        @json(__('Title'))
                    </th>

                    <th>
                        @json(__('Message'))
                    </th>

                    <th>
                        @json(__('Date'))
                    </th>

                    <th>
                        @json(__('Actions'))
                    </th>
                </tr>
            `;


            const tbody =
                document.createElement('tbody');


            /*
            |--------------------------------------------------------------------------
            | Rows
            |--------------------------------------------------------------------------
            */

            state.notifications.forEach(
                function (notification) {

                    const row =
                        document.createElement('tr');


                    /*
                    |--------------------------------------------------------------------------
                    | Checkbox
                    |--------------------------------------------------------------------------
                    */

                    const selectCell =
                        document.createElement('td');

                    const checkbox =
                        document.createElement('input');

                    checkbox.type =
                        'checkbox';

                    checkbox.className =
                        'notification-select';

                    checkbox.value =
                        notification.id;

                    selectCell.appendChild(
                        checkbox
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Title
                    |--------------------------------------------------------------------------
                    */

                    const title =
                        document.createElement('td');

                    if (notification.url) {

                        const titleLink =
                            document.createElement('a');

                        titleLink.href =
                            notification.url;

                        titleLink.className =
                            'realtime-test-title-link';

                        titleLink.textContent =
                            notification.title || '';

                        title.appendChild(
                            titleLink
                        );

                    } else {

                        title.textContent =
                            notification.title || '';
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Message
                    |--------------------------------------------------------------------------
                    */

                    const message =
                        document.createElement('td');

                    message.textContent =
                        notification.message || '';


                    /*
                    |--------------------------------------------------------------------------
                    | Date
                    |--------------------------------------------------------------------------
                    */

                    const date =
                        document.createElement('td');

                    date.textContent =
                        notification.created_at || '';


                    /*
                    |--------------------------------------------------------------------------
                    | Actions
                    |--------------------------------------------------------------------------
                    */

                    const actions =
                        document.createElement('td');

                    const actionsContainer =
                        document.createElement('div');

                    actionsContainer.className =
                        'realtime-test-row-actions';


                    /*
                    |--------------------------------------------------------------------------
                    | Open
                    |--------------------------------------------------------------------------
                    */

                    if (notification.url) {

                        const open =
                            document.createElement('a');

                        open.href =
                            notification.url;

                        open.textContent =
                            @json(__('Open'));

                        actionsContainer.appendChild(
                            open
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Mark As Read
                    |--------------------------------------------------------------------------
                    */

                    const readForm =
                        createNotificationForm(
                            '{{ csrf_token() }}',
                            'PATCH',
                            '{{ url('/realtimeTest/notifications') }}'
                                + '/'
                                + encodeURIComponent(
                                    notification.id
                                )
                                + '/read',
                            @json(__('Mark As Read')),
                        );

                    actionsContainer.appendChild(
                        readForm
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Delete
                    |--------------------------------------------------------------------------
                    */

                    const deleteForm =
                        createNotificationForm(
                            '{{ csrf_token() }}',
                            'DELETE',
                            '{{ url('/realtimeTest/notifications') }}'
                                + '/'
                                + encodeURIComponent(
                                    notification.id
                                ),
                            @json(__('Delete')),
                        );

                    actionsContainer.appendChild(
                        deleteForm
                    );


                    actions.appendChild(
                        actionsContainer
                    );


                    row.appendChild(
                        selectCell
                    );

                    row.appendChild(
                        title
                    );

                    row.appendChild(
                        message
                    );

                    row.appendChild(
                        date
                    );

                    row.appendChild(
                        actions
                    );

                    tbody.appendChild(
                        row
                    );
                }
            );


            table.appendChild(
                thead
            );

            table.appendChild(
                tbody
            );


            container.innerHTML = '';

            container.appendChild(
                table
            );


            bindSelection();
        },

        onError: function (error) {

            console.error(
                'MCF Realtime Error:',
                error
            );
        },

    });


    /*
    |--------------------------------------------------------------------------
    | Form Helper
    |--------------------------------------------------------------------------
    */

    function createNotificationForm(
        csrfToken,
        method,
        action,
        label,
    ) {
        const form =
            document.createElement('form');

        form.method =
            'POST';

        form.action =
            action;


        const token =
            document.createElement('input');

        token.type =
            'hidden';

        token.name =
            '_token';

        token.value =
            csrfToken;

        form.appendChild(
            token
        );


        const methodInput =
            document.createElement('input');

        methodInput.type =
            'hidden';

        methodInput.name =
            '_method';

        methodInput.value =
            method;

        form.appendChild(
            methodInput
        );


        const button =
            document.createElement('button');

        button.type =
            'submit';

        button.textContent =
            label;

        form.appendChild(
            button
        );


        return form;
    }


    /*
    |--------------------------------------------------------------------------
    | Selection
    |--------------------------------------------------------------------------
    */

    function getSelectedIds()
    {
        return Array
            .from(
                document.querySelectorAll(
                    '.notification-select:checked'
                )
            )
            .map(
                function (checkbox) {
                    return checkbox.value;
                }
            );
    }


    function updateSelectionCount()
    {
        const count =
            getSelectedIds().length;

        document.getElementById(
            'realtime-test-selection'
        ).textContent =
            @json(__('Selected')) +
            ': ' +
            count;
    }


    function bindSelection()
    {
        const selectAll =
            document.getElementById(
                'select-all'
            );

        if (selectAll) {

            selectAll.addEventListener(
                'change',
                function () {

                    document
                        .querySelectorAll(
                            '.notification-select'
                        )
                        .forEach(
                            function (checkbox) {

                                checkbox.checked =
                                    selectAll.checked;
                            }
                        );

                    updateSelectionCount();
                }
            );
        }


        document
            .querySelectorAll(
                '.notification-select'
            )
            .forEach(
                function (checkbox) {

                    checkbox.addEventListener(
                        'change',
                        updateSelectionCount
                    );
                }
            );


        updateSelectionCount();
    }


    /*
    |--------------------------------------------------------------------------
    | Mark Selected As Read
    |--------------------------------------------------------------------------
    */

    document.getElementById(
        'mark-selected-read'
    ).addEventListener(
        'click',
        async function () {

            const ids =
                getSelectedIds();

            if (ids.length === 0) {
                return;
            }


            for (const id of ids) {

                await fetch(
                    '{{ url('/realtimeTest/notifications') }}'
                        + '/'
                        + encodeURIComponent(id)
                        + '/read',
                    {
                        method: 'POST',

                        headers: {
                            'X-CSRF-TOKEN':
                                '{{ csrf_token() }}',

                            'X-Requested-With':
                                'XMLHttpRequest',

                            'Content-Type':
                                'application/x-www-form-urlencoded',
                        },

                        body:
                            '_method=PATCH',
                    }
                );
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Delete Selected
    |--------------------------------------------------------------------------
    */

    document.getElementById(
        'delete-selected'
    ).addEventListener(
        'click',
        async function () {

            const ids =
                getSelectedIds();

            if (ids.length === 0) {
                return;
            }


            for (const id of ids) {

                await fetch(
                    '{{ url('/realtimeTest/notifications') }}'
                        + '/'
                        + encodeURIComponent(id),
                    {
                        method: 'POST',

                        headers: {
                            'X-CSRF-TOKEN':
                                '{{ csrf_token() }}',

                            'X-Requested-With':
                                'XMLHttpRequest',

                            'Content-Type':
                                'application/x-www-form-urlencoded',
                        },

                        body:
                            '_method=DELETE',
                    }
                );
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Initial Selection
    |--------------------------------------------------------------------------
    */

    bindSelection();
</script>

@endsection
