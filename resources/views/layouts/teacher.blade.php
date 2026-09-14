@extends('layouts.app')
@section('title', $title ?? 'Teacher Portal')
@section('content')
    <div id="teacher-portal" class="min-h-screen bg-surface">
        @include('teacher.sidebar')
        @include('teacher.header')
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
        </script>
        <main id="mainContent"
              class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">
            <x-flash />
            @yield('page')
        </main>
    </div>
@endsection