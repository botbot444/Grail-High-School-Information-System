@extends('layouts.app')

@section('title', $title ?? 'Parent Portal')

@section('content')
    @php
        $selectedChild = $selectedChild ?? null;
        $selectedChildId = $selectedChild ? ($selectedChild->student_id ?? null) : null;
    @endphp

    <div id="parent-portal" class="min-h-screen bg-surface">

        @include('parent.sidebar')
        @include('parent.header')

        {{-- Sidebar toggle state helpers (mirrors admin.header behaviour) --}}
        <script>
            function toggleSidebar() {
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('mainContent');
                const header = document.getElementById('header');
                if (!sidebar) return;
                const isCollapsed = sidebar.classList.toggle('sidebar-collapsed');
                if (mainContent) mainContent.classList.toggle('main-expanded', isCollapsed);
                if (header) header.classList.toggle('header-expanded', isCollapsed);
            }
            document.addEventListener('DOMContentLoaded', function () {
                const sidebar = document.getElementById('sidebar');
                if (!sidebar) return;
                if (window.innerWidth >= 768) {
                    sidebar.classList.remove('sidebar-collapsed');
                } else {
                    sidebar.classList.add('sidebar-collapsed');
                }
            });
            window.addEventListener('resize', function () {
                const sidebar = document.getElementById('sidebar');
                if (!sidebar) return;
                if (window.innerWidth >= 768) {
                    sidebar.classList.remove('sidebar-collapsed');
                } else if (!sidebar.classList.contains('sidebar-collapsed')) {
                    sidebar.classList.add('sidebar-collapsed');
                }
            });
            // Close sidebar on mobile with Escape
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    const sidebar = document.getElementById('sidebar');
                    if (sidebar && window.innerWidth < 768 && !sidebar.classList.contains('sidebar-collapsed')) {
                        sidebar.classList.add('sidebar-collapsed');
                    }
                }
            });
            // Child switcher dropdown (parent.header)
            function toggleChildSwitcher(e) {
                e.stopPropagation();
                document.getElementById('childSwitcherMenu')?.classList.toggle('hidden');
            }
            document.addEventListener('click', function (ev) {
                const menu = document.getElementById('childSwitcherMenu');
                if (menu && !menu.classList.contains('hidden')) {
                    menu.classList.add('hidden');
                }
            });
        </script>

        <main id="mainContent"
              class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">
            <x-flash />
            @yield('page')
        </main>

    </div>

@endsection