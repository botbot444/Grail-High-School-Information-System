@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">
        @include('admin.sidebar')
        @include('admin.header')

        <div class="main-content main-transition pt-[88px]" id="mainContent">
            <div class="p-8 max-w-7xl mx-auto">
                <div class="mb-8">
                    <h1 class="text-2xl font-extrabold text-on-surface tracking-tight">System Settings</h1>
                    <p class="text-on-surface-variant text-sm mt-1">
                        Configure global parameters and administrative profiles for the institution.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    <nav
                        class="lg:col-span-3 space-y-2 bg-white p-4 rounded-xl border border-outline-variant shadow-sm sticky top-24">
                        <button
                            class="w-full flex items-center gap-3 px-4 py-3 text-left rounded-lg bg-primary-container/10 text-primary font-semibold">
                            <span class="material-symbols-outlined"
                                style="font-variation-settings: 'FILL' 1">account_balance</span>
                            School Profile
                        </button>
                        <button
                            class="w-full flex items-center gap-3 px-4 py-3 text-left rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors">
                            <span class="material-symbols-outlined">model_training</span>
                            Academic Settings
                        </button>
                        <button
                            class="w-full flex items-center gap-3 px-4 py-3 text-left rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors">
                            <span class="material-symbols-outlined">admin_panel_settings</span>
                            Roles & Permissions
                        </button>
                        <button
                            class="w-full flex items-center gap-3 px-4 py-3 text-left rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors">
                            <span class="material-symbols-outlined">notifications</span>
                            Notifications
                        </button>
                    </nav>

                    <div class="lg:col-span-9 space-y-6">
                        <section class="bg-white rounded-xl border border-outline-variant shadow-sm p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h2 class="text-lg font-bold text-on-surface">School Profile</h2>
                                    <p class="text-sm text-on-surface-variant">Core institution information and contact
                                        details.</p>
                                </div>
                                <button class="px-4 py-2 bg-primary text-on-primary rounded-lg text-sm font-semibold">Save
                                    Changes</button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-on-surface mb-2">School Name</label>
                                    <input type="text" value="Grail International School"
                                        class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-on-surface mb-2">School Code</label>
                                    <input type="text" value="GRL-2024"
                                        class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-on-surface mb-2">Email Address</label>
                                    <input type="email" value="admin@grail.school"
                                        class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-on-surface mb-2">Phone Number</label>
                                    <input type="text" value="+254 700 000 000"
                                        class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-on-surface mb-2">Address</label>
                                    <textarea rows="3"
                                        class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary">123 Academic Avenue, Nairobi, Kenya</textarea>
                                </div>
                            </div>
                        </section>

                        <section class="bg-white rounded-xl border border-outline-variant shadow-sm p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h2 class="text-lg font-bold text-on-surface">Security & Access</h2>
                                    <p class="text-sm text-on-surface-variant">View and manage the most common
                                        administrative controls.</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center justify-between rounded-lg border border-outline-variant p-4">
                                    <div>
                                        <p class="font-semibold text-on-surface">Two-factor authentication</p>
                                        <p class="text-sm text-on-surface-variant">Require a second step for privileged
                                            admin access.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" checked>
                                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-primary"></div>
                                        <div
                                            class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition peer-checked:translate-x-full">
                                        </div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between rounded-lg border border-outline-variant p-4">
                                    <div>
                                        <p class="font-semibold text-on-surface">Session timeout</p>
                                        <p class="text-sm text-on-surface-variant">Automatically sign out inactive
                                            administrators after 30 minutes.</p>
                                    </div>
                                    <select class="rounded-lg border border-outline-variant px-3 py-2">
                                        <option>15 minutes</option>
                                        <option selected>30 minutes</option>
                                        <option>60 minutes</option>
                                    </select>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
