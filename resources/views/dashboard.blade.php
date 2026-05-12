<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(auth()->user()->isTeacher())
                        <h3 class="text-lg font-semibold mb-4">Your Classes</h3>
                        @if($assignments->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($assignments as $assignment)
                                    <div class="border rounded-lg p-4">
                                        <h4 class="font-bold">{{ $assignment->subject->name }}</h4>
                                        <p class="text-sm text-gray-600">{{ $assignment->schoolClass->full_name }}</p>
                                        <p class="text-xs text-gray-500">Academic Year: {{ $assignment->academic_year }}</p>
                                        <div class="mt-3">
                                            <h5 class="font-semibold text-sm">Students ({{ $assignment->schoolClass->students->count() }})</h5>
                                            <ul class="text-xs text-gray-700 mt-1 space-y-1">
                                                @foreach($assignment->schoolClass->students as $student)
                                                    <li>{{ $student->first_name }} {{ $student->last_name }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p>You have no assigned classes yet.</p>
                        @endif
                    @else
                        {{ __("You're logged in!") }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
