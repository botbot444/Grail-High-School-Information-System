@extends('layouts.app')

@section('title', 'User Accounts')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <div class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">

        <div class="mb-6">
            <nav class="flex items-center gap-2 text-on-surface-variant mb-2">
                <span class="text-label-sm font-label-sm">Dashboard</span>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span class="text-label-sm font-label-sm text-primary font-bold">User Accounts</span>
            </nav>
            <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">User Accounts</h1>
            <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl">
                Sign-in access for every account. Deactivating someone takes effect immediately — if they are
                signed in, their session ends on their next click.
            </p>
        </div>

        @include('admin.partials.flash')

        {{-- Counts --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            @foreach ([
                ['Total accounts', $counts['total'], 'group'],
                ['Active', $counts['active'], 'check_circle'],
                ['Deactivated', $counts['inactive'], 'block'],
            ] as [$label, $value, $icon])
                <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">{{ $label }}</span>
                        <span class="material-symbols-outlined text-on-surface-variant text-[18px]">{{ $icon }}</span>
                    </div>
                    <p class="font-headline-md text-headline-md font-extrabold text-on-surface">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        @if ($activeAdmins <= 1)
            <div class="mb-6 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3">
                <p class="font-body-md text-body-md text-amber-900 flex items-start gap-2">
                    <span class="material-symbols-outlined text-[20px]">warning</span>
                    <span>
                        <strong>Only one active administrator.</strong>
                        That account cannot be deactivated or demoted until another admin exists — otherwise
                        nobody could get back into the admin portal.
                    </span>
                </p>
            </div>
        @endif

        {{-- Filters --}}
        <form method="GET" class="mb-4 bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email"
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Role</label>
                    <select name="role_id" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                        <option value="">All roles</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected(request('role_id') == $role->id)>{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Status</label>
                    <select name="status" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                        <option value="">Any</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Deactivated</option>
                    </select>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-on-primary hover:bg-primary/90 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">filter_alt</span> Apply
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-3 py-2 text-sm font-medium text-on-surface-variant hover:text-primary">Clear</a>
            </div>
        </form>

        {{-- Accounts --}}
        <div class="rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full font-body-md text-body-md">
                    <thead class="bg-surface-container-low font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">
                        <tr>
                            <th class="text-left font-medium px-4 py-3">Account</th>
                            <th class="text-left font-medium px-4 py-3">Role</th>
                            <th class="text-center font-medium px-4 py-3">Status</th>
                            <th class="text-right font-medium px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/40">
                        @forelse ($users as $user)
                            @php $isSelf = $user->id === auth()->id(); @endphp
                            <tr class="hover:bg-surface-container-low/60 transition-colors align-middle">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-on-surface">
                                        {{ $user->name }}
                                        @if ($isSelf)
                                            <span class="ml-1 text-xs font-normal text-on-surface-variant">(you)</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-on-surface-variant">{{ $user->email }}</p>
                                </td>

                                <td class="px-4 py-3">
                                    @if ($isSelf)
                                        <span class="text-sm text-on-surface-variant">{{ ucfirst($user->role_name ?? '—') }}</span>
                                    @else
                                        <form method="POST" action="{{ route('admin.users.role', $user->id) }}" class="flex items-center gap-1.5">
                                            @csrf @method('PUT')
                                            <select name="role_id" onchange="this.form.submit()"
                                                class="rounded-lg border border-outline-variant px-2 py-1.5 text-sm focus:ring-2 focus:ring-primary">
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->id }}" @selected($user->role_id === $role->id)>{{ ucfirst($role->name) }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <span @class([
                                        'inline-flex px-2.5 py-1 rounded-lg font-label-sm text-label-sm font-semibold',
                                        'bg-green-50 text-green-700' => $user->is_active,
                                        'bg-red-50 text-red-700' => ! $user->is_active,
                                    ])>{{ $user->is_active ? 'Active' : 'Deactivated' }}</span>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        @unless ($isSelf)
                                            <form method="POST" action="{{ route('admin.users.reset-password', $user->id) }}"
                                                  onsubmit="return confirm('Issue a temporary password for {{ $user->name }}? Their current password stops working immediately.');">
                                                @csrf @method('PUT')
                                                <button type="submit" title="Issue a temporary password"
                                                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container hover:text-primary text-sm font-medium transition-colors">
                                                    <span class="material-symbols-outlined text-[18px]">key</span>
                                                    <span class="hidden lg:inline">Reset</span>
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.users.status', $user->id) }}"
                                                  onsubmit="return confirm('{{ $user->is_active ? 'Deactivate' : 'Reactivate' }} {{ $user->name }}?');">
                                                @csrf @method('PUT')
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold transition-colors {{ $user->is_active ? 'border border-error/40 text-error hover:bg-error-container' : 'bg-primary text-on-primary hover:bg-primary/90' }}">
                                                    <span class="material-symbols-outlined text-[18px]">{{ $user->is_active ? 'block' : 'check_circle' }}</span>
                                                    <span class="hidden lg:inline">{{ $user->is_active ? 'Deactivate' : 'Activate' }}</span>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-on-surface-variant">Ask another admin</span>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-12 text-center font-body-md text-body-md text-on-surface-variant">
                                No accounts match these filters.
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="px-4 py-3 border-t border-outline-variant">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
