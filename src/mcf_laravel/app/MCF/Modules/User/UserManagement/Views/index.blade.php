@extends('Shared::Layout.app')

@section('content')

<style>
    .user-management {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
        font-family: Arial, sans-serif;
    }

    .user-management h1 {
        margin-bottom: 25px;
    }

    .user-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        border: 1px solid #ddd;
    }

    .user-table th,
    .user-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #eee;
        text-align: left;
    }

    .user-table th {
        background: #f5f5f5;
        font-weight: 600;
    }

    .user-table tr:last-child td {
        border-bottom: none;
    }

    .status {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 13px;
        font-weight: 600;
    }

    .status-active {
        background: #e8f7ed;
        color: #198754;
    }

    .status-disabled {
        background: #fff4e5;
        color: #b76e00;
    }

    .status-deleted {
        background: #fdeaea;
        color: #dc3545;
    }

    .actions {
        display: flex;
        gap: 7px;
        flex-wrap: wrap;
    }

    .action-form {
        display: inline;
        margin: 0;
    }

    .btn {
        border: 0;
        border-radius: 5px;
        padding: 7px 12px;
        color: #fff;
        font-size: 13px;
        cursor: pointer;
    }

    .btn-disable {
        background: #f0ad4e;
    }

    .btn-enable {
        background: #198754;
    }

    .btn-delete {
        background: #dc3545;
    }

    .btn-restore {
        background: #0d6efd;
    }

    .btn-logout {
        background: #343a40;
    }

    .btn:hover {
        opacity: 0.85;
    }

    .empty {
        padding: 30px;
        text-align: center;
        color: #777;
    }

    .logout-section {
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #ddd;
    }
</style>

<div class="user-management">

    <h1>User Management</h1>

    <table class="user-table">

        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>

        @forelse ($users as $user)

            <tr>

                <td>
                    {{ $user->id }}
                </td>

                <td>
                    {{ $user->name }}
                </td>

                <td>
                    {{ $user->email }}
                </td>

                <td>
                {{ $user->role?->name ?? '-' }}

                </td>

                <td>

                    @if ($user->trashed())

                        <span class="status status-deleted">
                            Deleted
                        </span>

                    @elseif ($user->is_active)

                        <span class="status status-active">
                            Active
                        </span>

                    @else

                        <span class="status status-disabled">
                            Disabled
                        </span>

                    @endif

                </td>

                <td>

                    <div class="actions">

                        @if ($user->trashed())

                            <form
                                class="action-form"
                                method="POST"
                                action="{{ route(
                                    'user.userManagement.restore',
                                    $user->id
                                ) }}"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="btn btn-restore"
                                >
                                    Restore
                                </button>
                            </form>

                        @else

                            @if ($user->is_active)

                                <form
                                    class="action-form"
                                    method="POST"
                                    action="{{ route(
                                        'user.userManagement.disable',
                                        $user->id
                                    ) }}"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        class="btn btn-disable"
                                    >
                                        Disable
                                    </button>
                                </form>

                            @else

                                <form
                                    class="action-form"
                                    method="POST"
                                    action="{{ route(
                                        'user.userManagement.enable',
                                        $user->id
                                    ) }}"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        class="btn btn-enable"
                                    >
                                        Enable
                                    </button>
                                </form>

                            @endif

                            <form
                                class="action-form"
                                method="POST"
                                action="{{ route(
                                    'user.userManagement.delete',
                                    $user->id
                                ) }}"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="btn btn-delete"
                                >
                                    Delete
                                </button>
                            </form>

                        @endif

                    </div>

                </td>

            </tr>

        @empty

            <tr>
                <td
                    colspan="6"
                    class="empty"
                >
                    No users found.
                </td>
            </tr>

        @endforelse

        </tbody>

    </table>

    <div class="logout-section">

        <form
            method="POST"
            action="{{ route('user.auth.logout') }}"
        >
            @csrf

            <button
                type="submit"
                class="btn btn-logout"
            >
                Logout
            </button>
        </form>

    </div>

</div>

@endsection
