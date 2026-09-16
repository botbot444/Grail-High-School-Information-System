<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Grail - Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>@layer base{html,body{margin:0;padding:0;}body{overscroll-behavior:none;}}::-webkit-scrollbar{display:none;}</style>
    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
        tailwind.config = { theme: { extend: {
            colors: {"surface":"#f8f9ff","surface-container-lowest":"#ffffff","surface-container-low":"#eff4ff","surface-container":"#e5eeff","surface-container-high":"#dce9ff","surface-container-highest":"#d3e4fe","on-surface":"#0b1c30","on-surface-variant":"#444651","outline":"#757682","outline-variant":"#c5c5d3","primary":"#00236f","on-primary":"#ffffff","primary-container":"#1e3a8a","primary-fixed":"#dce1ff","secondary":"#0051d5","on-secondary":"#ffffff","secondary-container":"#316bf3","error":"#ba1a1a","on-error":"#ffffff","error-container":"#ffdad6","on-error-container":"#93000a"},
            borderRadius: {DEFAULT:"0.25rem",lg:"0.5rem",xl:"0.75rem",full:"9999px"},
            spacing: {"gutter-sm":"1rem","space-md":"1rem",gutter:"1.5rem","space-lg":"1.5rem","margin-sm":"1rem","space-xl":"2.5rem","space-sm":"0.5rem","space-xs":"0.25rem",margin:"2rem"},
            fontFamily: {"headline-md":["Plus Jakarta Sans"],"headline-sm":["Plus Jakarta Sans"],"body-md":["Plus Jakarta Sans"],"body-sm":["Plus Jakarta Sans"],"label-lg":["Plus Jakarta Sans"],"label-md":["Plus Jakarta Sans"],"label-sm":["Plus Jakarta Sans"]},
            fontSize: {"headline-md":["22px",{lineHeight:"28px",letterSpacing:"-0.01em",fontWeight:"600"}],"headline-sm":["18px",{lineHeight:"24px",fontWeight:"600"}],"body-md":["14px",{lineHeight:"20px",fontWeight:"400"}],"body-sm":["12px",{lineHeight:"16px",fontWeight:"400"}],"label-lg":["14px",{lineHeight:"20px",letterSpacing:"0.01em",fontWeight:"600"}],"label-md":["12px",{lineHeight:"16px",letterSpacing:"0.02em",fontWeight:"600"}],"label-sm":["11px",{lineHeight:"14px",letterSpacing:"0.04em",fontWeight:"700"}]},
        } } };
    </script>
</head>

<body class="bg-surface font-body-md text-body-md text-on-surface antialiased min-h-screen flex flex-col">

    <header class="w-full bg-surface-container-lowest/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(15,23,42,0.05)]">
        <div class="h-20 w-full px-margin flex items-center">
            <div class="flex items-center gap-space-md">
                <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center shadow-sm">
                    <span class="material-symbols-outlined text-on-primary text-[22px]">school</span>
                </div>
                <div class="flex flex-col">
                    <span class="font-headline-sm text-headline-sm text-primary tracking-tight">Grail SIS</span>
                    <span class="font-label-sm text-label-sm text-on-surface-variant">School Information System</span>
                </div>
            </div>
        </div>
    </header>

    <main class="w-full flex-1 bg-surface">
        <div class="w-full px-margin py-space-lg max-w-4xl mx-auto flex flex-col gap-space-lg">

            <div class="flex flex-wrap items-center justify-between gap-space-md">
                <a class="inline-flex items-center gap-space-xs text-secondary hover:text-primary font-label-md text-label-md transition-colors" href="{{ route('welcome') }}">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Back to Home
                </a>
                <div class="flex items-center gap-space-sm text-on-surface-variant font-body-sm text-body-sm">
                    <span>Already have an account?</span>
                    <a class="font-label-md text-label-md text-secondary hover:text-primary underline decoration-secondary/30 underline-offset-4 font-semibold transition-colors" href="{{ route('login') }}">
                        Sign In to Portal
                    </a>
                </div>
            </div>

            <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm">
                <h1 class="font-headline-md text-headline-md text-on-surface">New Parent &amp; Student Registration</h1>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">Register yourself and your child for admission review.</p>
            </div>

            <div class="bg-surface-container-low rounded-xl p-space-md shadow-sm flex items-start gap-space-md">
                <div class="w-10 h-10 rounded-lg bg-surface-container-highest flex-shrink-0 flex items-center justify-center text-primary mt-0.5">
                    <span class="material-symbols-outlined text-[24px]">info</span>
                </div>
                <div class="flex flex-col gap-space-xs flex-1">
                    <span class="font-label-md text-label-md text-primary font-bold">Notice</span>
                    <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                        This submits a request for administrative review — it does <strong>not</strong> create an active account immediately. An administrator will review it before anything becomes active.
                    </p>
                </div>
            </div>

            @if ($errors->any())
                <div class="rounded-xl bg-error-container/60 p-space-md">
                    <p class="font-label-sm text-label-sm text-error font-bold uppercase tracking-wider mb-1">Please review the following</p>
                    <ul class="list-disc pl-5 font-body-sm text-body-sm text-on-error-container">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="flex flex-col gap-space-lg" method="POST" action="{{ route('register') }}">
                @csrf

                {{-- Section 1: Parent/Guardian --}}
                <section class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm flex flex-col gap-space-lg">
                    <div class="flex items-start justify-between flex-wrap gap-space-sm pb-space-sm bg-surface-container-low/40 p-space-md rounded-lg">
                        <div class="flex items-center gap-space-sm">
                            <span class="w-8 h-8 rounded-lg bg-primary-container text-on-primary flex items-center justify-center font-headline-sm text-headline-sm">1</span>
                            <div>
                                <h2 class="font-headline-sm text-headline-sm text-primary">Parent or Guardian Details</h2>
                                <p class="font-body-sm text-body-sm text-on-surface-variant">Your details as the primary account holder</p>
                            </div>
                        </div>
                        <span class="font-label-sm text-label-sm text-outline px-space-sm py-1 rounded bg-surface-container-low">All marked (*) are required</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-md text-label-md text-on-surface font-semibold flex items-center gap-1" for="parent_first_name">
                                First Name <span class="text-error">*</span>
                            </label>
                            <input class="h-10 px-3 rounded-lg bg-surface-container-low border border-outline-variant text-on-surface font-body-md text-body-md placeholder:text-outline focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary transition-all" id="parent_first_name" name="parent_first_name" placeholder="e.g. Eleanor" required type="text" value="{{ old('parent_first_name') }}" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-md text-label-md text-on-surface font-semibold flex items-center gap-1" for="parent_last_name">
                                Last Name <span class="text-error">*</span>
                            </label>
                            <input class="h-10 px-3 rounded-lg bg-surface-container-low border border-outline-variant text-on-surface font-body-md text-body-md placeholder:text-outline focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary transition-all" id="parent_last_name" name="parent_last_name" placeholder="e.g. Vance" required type="text" value="{{ old('parent_last_name') }}" />
                        </div>
                        <div class="md:col-span-2 flex flex-col gap-1.5">
                            <label class="font-label-md text-label-md text-on-surface font-semibold flex items-center gap-1" for="parent_email">
                                Email Address <span class="text-error">*</span>
                            </label>
                            <input class="h-10 px-3 rounded-lg bg-surface-container-low border border-outline-variant text-on-surface font-body-md text-body-md placeholder:text-outline focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary transition-all" id="parent_email" name="parent_email" placeholder="eleanor.vance@example.com" required type="email" value="{{ old('parent_email') }}" />
                            <span class="font-body-sm text-body-sm text-on-surface-variant flex items-center gap-1 mt-0.5">
                                <span class="material-symbols-outlined text-[14px] text-secondary">mail</span>
                                Used for your login once approved
                            </span>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-md text-label-md text-on-surface font-semibold flex items-center gap-1" for="parent_password">
                                Password <span class="text-error">*</span>
                            </label>
                            <div class="relative">
                                <input class="w-full h-10 px-3 pr-10 rounded-lg bg-surface-container-low border border-outline-variant text-on-surface font-body-md text-body-md placeholder:text-outline focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary transition-all" id="parent_password" name="parent_password" placeholder="Minimum 8 characters" required type="password" autocomplete="new-password" oninput="checkPasswordStrength(this.value)" />
                                <button class="absolute right-2 top-2 text-on-surface-variant hover:text-on-surface" onclick="togglePasswordVisibility('parent_password', this)" type="button">
                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                </button>
                            </div>
                            <div class="flex items-center gap-1.5 mt-1">
                                <div class="h-1 flex-1 rounded-full bg-surface-container-highest transition-colors" id="strength_bar_1"></div>
                                <div class="h-1 flex-1 rounded-full bg-surface-container-highest transition-colors" id="strength_bar_2"></div>
                                <div class="h-1 flex-1 rounded-full bg-surface-container-highest transition-colors" id="strength_bar_3"></div>
                                <div class="h-1 flex-1 rounded-full bg-surface-container-highest transition-colors" id="strength_bar_4"></div>
                                <span class="font-label-sm text-label-sm text-on-surface-variant ml-1" id="strength_text">Strength</span>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-md text-label-md text-on-surface font-semibold flex items-center gap-1" for="parent_password_confirmation">
                                Confirm Password <span class="text-error">*</span>
                            </label>
                            <div class="relative">
                                <input class="w-full h-10 px-3 pr-10 rounded-lg bg-surface-container-low border border-outline-variant text-on-surface font-body-md text-body-md placeholder:text-outline focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary transition-all" id="parent_password_confirmation" name="parent_password_confirmation" placeholder="Re-type password" required type="password" autocomplete="new-password" />
                                <button class="absolute right-2 top-2 text-on-surface-variant hover:text-on-surface" onclick="togglePasswordVisibility('parent_password_confirmation', this)" type="button">
                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                </button>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-md text-label-md text-on-surface font-semibold flex items-center justify-between" for="parent_phone">
                                <span>Phone Number</span>
                                <span class="font-label-sm text-label-sm text-outline">Optional</span>
                            </label>
                            <input class="h-10 px-3 rounded-lg bg-surface-container-low border border-outline-variant text-on-surface font-body-md text-body-md placeholder:text-outline focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary transition-all" id="parent_phone" name="parent_phone" placeholder="e.g. 0961 234 567" type="tel" value="{{ old('parent_phone') }}" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-md text-label-md text-on-surface font-semibold flex items-center justify-between" for="parent_national_id">
                                <span>National ID</span>
                                <span class="font-label-sm text-label-sm text-outline">Optional</span>
                            </label>
                            <input class="h-10 px-3 rounded-lg bg-surface-container-low border border-outline-variant text-on-surface font-body-md text-body-md placeholder:text-outline focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary transition-all" id="parent_national_id" name="parent_national_id" placeholder="e.g. 123456/78/9" type="text" value="{{ old('parent_national_id') }}" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-md text-label-md text-on-surface font-semibold flex items-center justify-between" for="parent_occupation">
                                <span>Occupation</span>
                                <span class="font-label-sm text-label-sm text-outline">Optional</span>
                            </label>
                            <input class="h-10 px-3 rounded-lg bg-surface-container-low border border-outline-variant text-on-surface font-body-md text-body-md placeholder:text-outline focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary transition-all" id="parent_occupation" name="parent_occupation" placeholder="e.g. Teacher" type="text" value="{{ old('parent_occupation') }}" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-md text-label-md text-on-surface font-semibold flex items-center justify-between" for="parent_address">
                                <span>Address</span>
                                <span class="font-label-sm text-label-sm text-outline">Optional</span>
                            </label>
                            <input class="h-10 px-3 rounded-lg bg-surface-container-low border border-outline-variant text-on-surface font-body-md text-body-md placeholder:text-outline focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary transition-all" id="parent_address" name="parent_address" placeholder="Street, city" type="text" value="{{ old('parent_address') }}" />
                        </div>
                    </div>
                </section>

                {{-- Section 2: Child --}}
                <section class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm flex flex-col gap-space-lg">
                    <div class="flex items-start justify-between flex-wrap gap-space-sm pb-space-sm bg-surface-container-low/40 p-space-md rounded-lg">
                        <div class="flex items-center gap-space-sm">
                            <span class="w-8 h-8 rounded-lg bg-secondary text-on-secondary flex items-center justify-center font-headline-sm text-headline-sm">2</span>
                            <div>
                                <h2 class="font-headline-sm text-headline-sm text-primary">Your Child's Details</h2>
                                <p class="font-body-sm text-body-sm text-on-surface-variant">This is a new admission request — an admin will place your child in a class once approved</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-md text-label-md text-on-surface font-semibold flex items-center gap-1" for="child_first_name">
                                First Name <span class="text-error">*</span>
                            </label>
                            <input class="h-10 px-3 rounded-lg bg-surface-container-low border border-outline-variant text-on-surface font-body-md text-body-md placeholder:text-outline focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary transition-all" id="child_first_name" name="child_first_name" placeholder="e.g. Lucas" required type="text" value="{{ old('child_first_name') }}" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-md text-label-md text-on-surface font-semibold flex items-center gap-1" for="child_last_name">
                                Last Name <span class="text-error">*</span>
                            </label>
                            <input class="h-10 px-3 rounded-lg bg-surface-container-low border border-outline-variant text-on-surface font-body-md text-body-md placeholder:text-outline focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary transition-all" id="child_last_name" name="child_last_name" placeholder="e.g. Vance" required type="text" value="{{ old('child_last_name') }}" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-md text-label-md text-on-surface font-semibold flex items-center gap-1" for="child_date_of_birth">
                                Date of Birth <span class="text-error">*</span>
                            </label>
                            <input class="w-full h-10 px-3 rounded-lg bg-surface-container-low border border-outline-variant text-on-surface font-body-md text-body-md focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary transition-all" id="child_date_of_birth" name="child_date_of_birth" required type="date" value="{{ old('child_date_of_birth') }}" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-md text-label-md text-on-surface font-semibold flex items-center gap-1" for="child_gender">
                                Gender <span class="text-error">*</span>
                            </label>
                            <select class="h-10 px-3 rounded-lg bg-surface-container-low border border-outline-variant text-on-surface font-body-md text-body-md focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary transition-all" id="child_gender" name="child_gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('child_gender') === 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('child_gender') === 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        <div class="md:col-span-2 flex flex-col gap-1.5">
                            <label class="font-label-md text-label-md text-on-surface font-semibold flex items-center gap-1" for="child_email">
                                Child's Email <span class="text-error">*</span>
                            </label>
                            <input class="h-10 px-3 rounded-lg bg-surface-container-low border border-outline-variant text-on-surface font-body-md text-body-md placeholder:text-outline focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary transition-all" id="child_email" name="child_email" placeholder="student.name@example.com" required type="email" value="{{ old('child_email') }}" />
                            <div class="bg-surface-container p-space-sm rounded-lg flex items-start gap-space-xs mt-1">
                                <span class="material-symbols-outlined text-[16px] text-secondary mt-0.5">lightbulb</span>
                                <p class="font-body-sm text-body-sm text-on-surface-variant">
                                    Used for your child's own login once approved. A one-time password will be sent to you.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Submit --}}
                <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-md flex items-center justify-end">
                    <button class="h-11 px-space-xl rounded-lg bg-primary hover:bg-primary-container text-on-primary font-label-lg text-label-lg flex items-center justify-center gap-space-sm shadow-md hover:shadow-lg transition-all" type="submit">
                        <span>Submit for Review</span>
                        <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('.material-symbols-outlined');
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility';
            }
        }

        function checkPasswordStrength(val) {
            const bars = ['strength_bar_1', 'strength_bar_2', 'strength_bar_3', 'strength_bar_4'].map(id => document.getElementById(id));
            const text = document.getElementById('strength_text');

            let score = 0;
            if (val.length > 5) score++;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val) && /[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            const colors = ['bg-surface-container-highest', 'bg-error', 'bg-secondary', 'bg-secondary', 'bg-primary'];
            const labels = ['Empty', 'Weak', 'Fair', 'Good', 'Strong'];

            bars.forEach((b, idx) => {
                b.className = 'h-1 flex-1 rounded-full transition-colors ' + (idx < score ? colors[score] : 'bg-surface-container-highest');
            });

            text.textContent = val ? labels[score] : 'Strength';
            text.className = 'font-label-sm text-label-sm ml-1 ' + (score >= 3 ? 'text-secondary font-semibold' : 'text-on-surface-variant');
        }
    </script>

</body>
</html>
