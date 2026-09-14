{{-- resources/views/admin/fees/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Create Fee')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">

        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <nav class="flex items-center gap-1.5 text-on-surface-variant font-label-sm text-label-sm mb-2">
                    <a href="{{ route('admin.fees.index') }}" class="hover:text-primary transition-colors">Fees</a>
                    <span class="material-symbols-outlined text-[14px] text-outline">chevron_right</span>
                    <span class="text-on-surface font-semibold">Add New</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Add New Fee</h1>
                <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl">
                    Build a fee header for a student, then attach one or more line items.
                </p>
            </div>
            <a href="{{ route('admin.fees.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-surface-container-lowest border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors font-label-md text-label-md shadow-sm self-start">
                <span class="material-symbols-outlined text-[18px]">close</span>
                <span>Cancel</span>
            </a>
        </div>

        @include('admin.partials.flash')

        <form method="POST" action="{{ route('admin.fees.store') }}" id="fee-form" class="flex flex-col gap-6">
            @csrf

            <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-8 shadow-sm">
                <div class="flex items-center gap-3.5 pb-6 mb-6 border-b border-outline-variant">
                    <div class="w-11 h-11 rounded-xl bg-primary-fixed flex items-center justify-center text-primary shadow-sm">
                        <span class="material-symbols-outlined text-[24px]">receipt_long</span>
                    </div>
                    <div>
                        <h2 class="font-headline-md text-headline-md text-on-surface">Fee Details</h2>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">Who owes it, and for which term</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1.5 md:col-span-2">
                        <label class="block font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider" for="student_id">
                            Student <span class="text-error">*</span>
                        </label>
                        <select id="student_id" name="student_id" required
                            class="w-full px-3.5 py-2.5 rounded-lg bg-surface-container-low text-on-surface font-body-md text-body-md appearance-none focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer @error('student_id') ring-2 ring-error @enderror">
                            <option value="">— Select student —</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->student_id }}" @selected(old('student_id') == $student->student_id)>
                                    {{ $student->full_name }} ({{ $student->schoolClass->class_name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider" for="academic_year_id">
                            Academic Year <span class="text-error">*</span>
                        </label>
                        <select id="academic_year_id" name="academic_year_id" required
                            class="w-full px-3.5 py-2.5 rounded-lg bg-surface-container-low text-on-surface font-body-md text-body-md appearance-none focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer @error('academic_year_id') ring-2 ring-error @enderror">
                            <option value="">— Select year —</option>
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->year_id }}" @selected(old('academic_year_id') == $year->year_id)>
                                    {{ $year->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider" for="term_id">
                            Term <span class="text-error">*</span>
                        </label>
                        <select id="term_id" name="term_id" required
                            class="w-full px-3.5 py-2.5 rounded-lg bg-surface-container-low text-on-surface font-body-md text-body-md appearance-none focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer @error('term_id') ring-2 ring-error @enderror">
                            <option value="">— Select term —</option>
                            @foreach ($terms as $term)
                                <option value="{{ $term->term_id }}" @selected(old('term_id') == $term->term_id)>
                                    {{ $term->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider" for="due_date">
                            Due Date <span class="text-error">*</span>
                        </label>
                        <input type="date" id="due_date" name="due_date" value="{{ old('due_date') }}" required
                            class="w-full px-3.5 py-2.5 rounded-lg bg-surface-container-low text-on-surface font-body-md text-body-md focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all @error('due_date') ring-2 ring-error @enderror">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider" for="description">
                            Description
                        </label>
                        <input type="text" id="description" name="description" value="{{ old('description') }}"
                            placeholder="e.g. Term 1 tuition and boarding"
                            class="w-full px-3.5 py-2.5 rounded-lg bg-surface-container-low text-on-surface font-body-md text-body-md focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                    </div>
                </div>
            </section>

            <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-8 shadow-sm">
                <div class="flex items-center justify-between gap-3 pb-6 mb-6 border-b border-outline-variant">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-xl bg-secondary-fixed flex items-center justify-center text-secondary shadow-sm">
                            <span class="material-symbols-outlined text-[24px]">list_alt</span>
                        </div>
                        <div>
                            <h2 class="font-headline-md text-headline-md text-on-surface">Fee Line Items</h2>
                            <p class="font-body-sm text-body-sm text-on-surface-variant">Each item adds to the total amount due</p>
                        </div>
                    </div>
                    <button type="button" id="add-item-btn"
                        class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary rounded-lg text-label-sm font-semibold hover:bg-primary-container transition-colors shadow-sm shrink-0">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Add Item
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-outline-variant">
                                <th class="pb-2 font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider">Item Name</th>
                                <th class="pb-2 font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider">Category</th>
                                <th class="pb-2 font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider text-right">Amount</th>
                                <th class="pb-2 w-16"></th>
                            </tr>
                        </thead>
                        <tbody id="items-body">
                            <!-- Rows rendered by JS -->
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-outline-variant">
                                <td colspan="2" class="pt-4 text-right font-title-md text-title-md font-semibold text-on-surface">Total</td>
                                <td class="pt-4 text-right font-title-md text-title-md font-bold text-on-surface font-mono" id="running-total">ZMW 0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($errors->has('fee_items'))
                    <p class="mt-3 text-error font-body-sm text-body-sm">{{ $errors->first('fee_items') }}</p>
                @endif
            </section>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.fees.index') }}"
                    class="px-5 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors font-label-md text-label-md">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-primary text-on-primary font-label-md text-label-md font-bold hover:bg-primary-container transition-all flex items-center gap-2 shadow-md">
                    <span class="material-symbols-outlined text-[20px]">add_circle</span>
                    <span>Create Fee</span>
                </button>
            </div>
        </form>
    </main>

    @push('scripts')
        <script>
            (function () {
                const categories = @json($categories->pluck('name'));
                const tbody = document.getElementById('items-body');
                const addBtn = document.getElementById('add-item-btn');
                const totalEl = document.getElementById('running-total');
                let rowIndex = 0;

                const fieldClass = 'w-full px-3 py-2 rounded-lg bg-surface-container-low text-on-surface font-body-sm text-body-sm focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all';

                function renderOptions(selected = '') {
                    return categories.map(c =>
                        `<option value="${c}" ${c === selected ? 'selected' : ''}>${c}</option>`
                    ).join('');
                }

                function recalc() {
                    let sum = 0;
                    tbody.querySelectorAll('tr').forEach(row => {
                        const input = row.querySelector('.item-amount');
                        const val = parseFloat(input.value);
                        if (!isNaN(val) && val > 0) sum += val;
                    });
                    totalEl.textContent = 'ZMW ' + sum.toFixed(2);
                }

                function addRow(data = null) {
                    const idx = rowIndex++;
                    const tr = document.createElement('tr');
                    tr.dataset.row = idx;
                    tr.className = 'border-b border-outline-variant/60 last:border-b-0';
                    tr.innerHTML = `
                        <td class="py-2 pr-2">
                            <input type="text" name="fee_items[${idx}][item_name]" value="${data?.item_name ?? ''}"
                                required placeholder="e.g. Tuition" class="${fieldClass} item-name" />
                        </td>
                        <td class="py-2 px-2">
                            <select name="fee_items[${idx}][category]" required
                                class="${fieldClass} appearance-none cursor-pointer item-category">
                                ${renderOptions(data?.category)}
                            </select>
                        </td>
                        <td class="py-2 pl-2">
                            <input type="number" step="0.01" min="0" name="fee_items[${idx}][amount]" value="${data?.amount ?? ''}"
                                required placeholder="0.00" class="${fieldClass} item-amount text-right font-mono" />
                        </td>
                        <td class="py-2 pl-2 text-center">
                            <button type="button" class="remove-row p-2 text-on-surface-variant hover:text-error hover:bg-error-container/20 rounded-lg transition-all" title="Remove item">
                                <span class="material-symbols-outlined text-xl">delete</span>
                            </button>
                        </td>
                    `;

                    tr.querySelector('.item-amount').addEventListener('input', recalc);
                    tr.querySelector('.remove-row').addEventListener('click', () => {
                        tr.remove();
                        recalc();
                    });

                    tbody.appendChild(tr);
                    recalc();
                }

                addBtn.addEventListener('click', () => addRow());

                // Seed with one empty row.
                addRow();
            })();
        </script>
    @endpush
@endsection
