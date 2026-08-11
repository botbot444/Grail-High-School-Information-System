@extends('layouts.app')

@section('title', 'Edit Fee')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <div class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">
        <div style="margin-bottom: 25px; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h2 style="color: #177aa4;">Edit Fee</h2>
                <p style="color: gray;">Adjust the header or line items below.</p>
            </div>
            <a href="{{ route('admin.fees.index') }}" class="btn"
                style="background: #e2e8f0; color: #177aa4; padding: 12px 20px; text-decoration:none;">
                <i class="fa-solid fa-arrow-left"></i> Back to Fees
            </a>
        </div>

        @if ($errors->any())
            <div style="background: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                <strong>Errors:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="table-section" style="max-width: 960px;">
            <form method="POST" action="{{ route('admin.fees.update', $fee->fee_id) }}" id="fee-form">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-on-surface mb-1">Student *</label>
                        <select name="student_id" required
                            class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary">
                            <option value="">— Select student —</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->student_id }}"
                                    {{ old('student_id', $fee->student_id) == $student->student_id ? 'selected' : '' }}>
                                    {{ $student->full_name }} ({{ $student->schoolClass->class_name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-on-surface mb-1">Due Date *</label>
                        <input type="date" name="due_date" value="{{ old('due_date', $fee->due_date) }}" required
                            class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-on-surface mb-1">Academic Year *</label>
                        <select name="academic_year_id" required
                            class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary">
                            <option value="">— Select year —</option>
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->year_id }}" {{ old('academic_year_id', $fee->academic_year_id) == $year->year_id ? 'selected' : '' }}>
                                    {{ $year->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-on-surface mb-1">Term *</label>
                        <select name="term_id" required
                            class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary">
                            <option value="">— Select term —</option>
                            @foreach ($terms as $term)
                                <option value="{{ $term->term_id }}" {{ old('term_id', $fee->term_id) == $term->term_id ? 'selected' : '' }}>
                                    {{ $term->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-on-surface mb-1">Description</label>
                        <input type="text" name="description" value="{{ old('description', $fee->description) }}"
                            class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                    </div>
                </div>

                <section class="mt-6 bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-headline-sm font-bold text-on-surface">Fee Line Items</h3>
                        <button type="button" id="add-item-btn"
                            class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary rounded-lg text-label-sm font-semibold">
                            <span class="material-symbols-outlined text-[16px]">add</span>
                            Add Item
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-outline-variant">
                                    <th class="pb-2 text-label-sm font-semibold text-on-surface-variant">Item Name</th>
                                    <th class="pb-2 text-label-sm font-semibold text-on-surface-variant">Category</th>
                                    <th class="pb-2 text-label-sm font-semibold text-on-surface-variant text-right">Amount</th>
                                    <th class="pb-2 text-label-sm font-semibold text-on-surface-variant text-center w-16"></th>
                                </tr>
                            </thead>
                            <tbody id="items-body">
                                <!-- Rows rendered by JS -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="pt-4 text-right text-body-md font-semibold text-on-surface">Total</td>
                                    <td class="pt-4 text-right text-body-md font-bold text-on-surface font-mono" id="running-total">ZMW 0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if ($errors->has('fee_items'))
                        <p class="mt-2 text-error text-label-sm">{{ $errors->first('fee_items') }}</p>
                    @endif
                </section>

                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="submit" class="btn save-btn"
                        style="background: #177aa4; color: white; padding: 12px 20px;">
                        <i class="fa-solid fa-check"></i> Update Fee
                    </button>
                    <a href="{{ route('admin.fees.index') }}" class="btn"
                        style="background: #e2e8f0; color: #1e293b; padding: 12px 20px; text-decoration: none;">
                        <i class="fa-solid fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

        @php
            $existingFeeItems = $fee->feeItems->map(fn ($i) => [
                'item_name' => $i->item_name,
                'category'  => $i->category,
                'amount'    => $i->amount,
            ])->values()->all();
        @endphp

    <script>
        (function () {
            const categories = @json($categories->pluck('name'));
            const existing = @json($existingFeeItems);
            const tbody = document.getElementById('items-body');
            const addBtn = document.getElementById('add-item-btn');
            const totalEl = document.getElementById('running-total');
            let rowIndex = 0;

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
                tr.innerHTML = `
                    <td class="py-2 pr-2">
                        <input type="text" name="fee_items[${idx}][item_name]" value="${data?.item_name ?? ''}"
                            required class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary item-name" />
                    </td>
                    <td class="py-2 px-2">
                        <select name="fee_items[${idx}][category]" required
                            class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary item-category">
                            ${renderOptions(data?.category)}
                        </select>
                    </td>
                    <td class="py-2 pl-2">
                        <input type="number" step="0.01" min="0" name="fee_items[${idx}][amount]" value="${data?.amount ?? ''}"
                            required class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary item-amount text-right" />
                    </td>
                    <td class="py-2 pl-2 text-center">
                        <button type="button" class="remove-row p-2 text-on-surface-variant hover:text-error hover:bg-error-container/20 rounded transition-all" title="Remove">
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

            // Seed existing rows.
            existing.forEach(row => addRow(row));
            if (existing.length === 0) addRow();
        })();
    </script>
</div>
@endsection
