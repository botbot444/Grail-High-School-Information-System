<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Grail - Register</title>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css'])
    </head>

    <body class="bg-surface">

        <div class="min-h-screen flex flex-col items-center py-10 px-4">
            <div class="w-full max-w-3xl">

                <div class="text-center mb-8">
                    <h1 class="text-3xl font-extrabold" style="color:#177aa4;">GRAIL</h1>
                    <p class="text-on-surface-variant mt-1">Register yourself and your child — an admin will review it before anything becomes active.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        <p class="font-semibold">Please review the following:</p>
                        <ul class="mt-2 list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    <section class="bg-white rounded-2xl shadow-sm p-6">
                        <div class="flex items-center gap-2 mb-5">
                            <span class="material-symbols-outlined text-[#177aa4]">person</span>
                            <h2 class="text-lg font-bold text-on-surface">Your Details</h2>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-on-surface mb-1">First Name *</label>
                                <input type="text" name="parent_first_name" value="{{ old('parent_first_name') }}" required
                                    class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-on-surface mb-1">Last Name *</label>
                                <input type="text" name="parent_last_name" value="{{ old('parent_last_name') }}" required
                                    class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-semibold text-on-surface mb-1">Email *</label>
                                <input type="email" name="parent_email" value="{{ old('parent_email') }}" required
                                    class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-on-surface mb-1">Password *</label>
                                <input type="password" name="parent_password" required autocomplete="new-password"
                                    class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-on-surface mb-1">Confirm Password *</label>
                                <input type="password" name="parent_password_confirmation" required autocomplete="new-password"
                                    class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-on-surface mb-1">Phone</label>
                                <input type="tel" name="parent_phone" value="{{ old('parent_phone') }}"
                                    class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-on-surface mb-1">National ID</label>
                                <input type="text" name="parent_national_id" value="{{ old('parent_national_id') }}"
                                    class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-semibold text-on-surface mb-1">Address</label>
                                <input type="text" name="parent_address" value="{{ old('parent_address') }}"
                                    class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-on-surface mb-1">Occupation</label>
                                <input type="text" name="parent_occupation" value="{{ old('parent_occupation') }}"
                                    class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                            </div>
                        </div>
                    </section>

                    <section class="bg-white rounded-2xl shadow-sm p-6">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="material-symbols-outlined text-[#177aa4]">family_restroom</span>
                            <h2 class="text-lg font-bold text-on-surface">Your Child's Details</h2>
                        </div>
                        <p class="text-xs text-on-surface-variant mb-5">
                            This is a new admission request — an admin will place your child in a class once approved.
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-on-surface mb-1">First Name *</label>
                                <input type="text" name="child_first_name" value="{{ old('child_first_name') }}" required
                                    class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-on-surface mb-1">Last Name *</label>
                                <input type="text" name="child_last_name" value="{{ old('child_last_name') }}" required
                                    class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-on-surface mb-1">Date of Birth *</label>
                                <input type="date" name="child_date_of_birth" value="{{ old('child_date_of_birth') }}" required
                                    class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-on-surface mb-1">Gender *</label>
                                <select name="child_gender" required
                                    class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary">
                                    <option value="">Select Gender</option>
                                    <option value="Male" {{ old('child_gender') === 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('child_gender') === 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-semibold text-on-surface mb-1">Child's Email *</label>
                                <input type="email" name="child_email" value="{{ old('child_email') }}" required
                                    class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                                <p class="mt-1 text-xs text-on-surface-variant">
                                    Used for your child's own login once approved. A one-time password will be sent to you.
                                </p>
                            </div>
                        </div>
                    </section>

                    <div class="flex items-center justify-between">
                        <a href="{{ route('login') }}" class="text-sm text-on-surface-variant hover:text-primary underline">
                            Already have an account? Sign in
                        </a>
                        <button type="submit"
                            class="px-6 py-3 rounded-xl text-white font-semibold shadow-sm hover:opacity-90 transition-opacity"
                            style="background:#177aa4;">
                            Submit for Review
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </body>

</html>
