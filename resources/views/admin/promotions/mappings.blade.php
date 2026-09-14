@extends('layouts.app')

@section('title', 'Promotion Mappings')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <div class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">
        @include('admin.partials.flash')


        <a href="{{ route('admin.promotions.index') }}"
           class="inline-flex items-center gap-1.5 mb-4 font-label-md text-label-md text-primary hover:underline">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Promotion
        </a>

        <div class="mb-6">
            <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Promotion Mappings</h1>
            <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl">
                Set where each class sends its students at year-end, so you do not choose again every year.
                These are defaults — you can still override any student on the day.
            </p>
        </div>



        <form method="POST" action="{{ route('admin.promotions.mappings.save') }}" class="max-w-4xl">
            @csrf
            @method('PUT')

            <div class="rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full font-body-md text-body-md">
                        <thead class="bg-surface-container-low font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">
                            <tr>
                                <th class="text-left font-medium px-4 py-3">From</th>
                                <th class="text-left font-medium px-4 py-3">Promotes to</th>
                                <th class="text-left font-medium px-4 py-3">Or graduates</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/40">
                            @forelse ($classes as $class)
                                @php $mapping = $mappings->get($class->class_id); @endphp
                                <tr class="align-middle" x-data="{ graduates: @js((bool) ($mapping->graduates ?? false)) }">
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-on-surface">{{ $class->class_name }}</p>
                                        <p class="font-body-sm text-body-sm text-on-surface-variant">{{ $class->gradeLevel?->name ?? 'No grade level' }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <select name="mappings[{{ $class->class_id }}][to_class_id]"
                                            :disabled="graduates"
                                            class="w-full rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40 disabled:opacity-40">
                                            <option value="">Not set</option>
                                            @foreach ($classes as $option)
                                                @continue($option->class_id === $class->class_id)
                                                <option value="{{ $option->class_id }}" @selected($mapping?->to_class_id === $option->class_id)>
                                                    {{ $option->class_name }}@if ($option->gradeLevel) — {{ $option->gradeLevel->name }}@endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-3">
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="hidden" name="mappings[{{ $class->class_id }}][graduates]" value="0">
                                            <input type="checkbox" name="mappings[{{ $class->class_id }}][graduates]" value="1"
                                                x-model="graduates"
                                                class="rounded border-outline-variant text-secondary focus:ring-secondary/40">
                                            <span class="font-body-md text-body-md text-on-surface">Final grade level</span>
                                        </label>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-4 py-10 text-center font-body-md text-body-md text-on-surface-variant">No classes exist yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($classes->isNotEmpty())
                <div class="mt-6">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-space-md py-2 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm shadow-sm transition-all">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        <span>Save mappings</span>
                    </button>
                </div>
            @endif
        </form>
    </div>
@endsection
