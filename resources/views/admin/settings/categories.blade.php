@extends('layouts.app')

@section('title', 'Fee Categories')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <div class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-end">
            <div>
                <nav class="flex items-center gap-2 text-on-surface-variant mb-2">
                    <span class="text-label-sm font-label-sm">Settings</span>
                    <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    <span class="text-label-sm font-label-sm text-primary font-bold">Fee Categories</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">
                    Fee Categories
                </h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Configure categories used on fee line items. Categories in use cannot be deleted.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <section class="lg:col-span-4 bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5">
                <h2 class="text-headline-sm font-bold text-on-surface mb-4">Add Category</h2>

                @if ($errors->any())
                    <div style="background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 8px; margin-bottom: 12px;">
                        @foreach ($errors->all() as $error)
                            <p class="text-label-sm">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-on-surface mb-1">Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-primary text-on-primary rounded-lg text-label-sm font-semibold">
                        Add Category
                    </button>
                </form>
            </section>

            <section class="lg:col-span-8 bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-outline-variant bg-surface-container">
                                <th class="px-5 py-3 text-label-sm font-semibold text-on-surface-variant">Name</th>
                                <th class="px-5 py-3 text-label-sm font-semibold text-on-surface-variant text-center">Items</th>
                                <th class="px-5 py-3 text-label-sm font-semibold text-on-surface-variant text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                                <tr class="border-b border-outline-variant/60 hover:bg-surface-container-high/60 transition-colors">
                                    <td class="px-5 py-3 text-body-md text-on-surface">
                                        <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="name" value="{{ $category->name }}"
                                                class="rounded-lg border border-outline-variant px-3 py-1.5 bg-white focus:ring-2 focus:ring-primary" />
                                            <button type="submit" class="ml-2 px-3 py-1.5 bg-primary text-on-primary rounded-lg text-label-sm">Save</button>
                                        </form>
                                    </td>
                                    <td class="px-5 py-3 text-body-md text-on-surface text-center">
                                        {{ $category->fee_items_count }}
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        @if ($category->fee_items_count > 0)
                                            <span class="text-label-sm text-on-surface-variant">In use</span>
                                        @else
                                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                                style="display:inline;" onsubmit="return confirm('Delete this category?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="px-3 py-1.5 bg-error text-on-error rounded-lg text-label-sm">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            @if ($categories->isEmpty())
                                <tr>
                                    <td colspan="3" class="px-5 py-6 text-center text-on-surface-variant">
                                        No categories yet.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
@endsection
