<x-app-layout>
    <!--Ai code-->
    <div class="py-4" style="max-width: 360px; margin: 0 auto; background: #f8f9fa; min-height: 100vh;">
        <!-- Header -->
        <div class="px-3 mb-4">
            <h2 class="text-xl font-bold text-gray-800">{{ $assignment->subject->name }}</h2>
            <p class="text-sm text-gray-500">Class: {{ $assignment->schoolClass->full_name }} | Term 1</p>
        </div>

        <!-- Student List -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            @foreach($students as $student)
            <div class="p-3 border-b d-flex align-items-center justify-content-between" x-data="{ mark: '' }">
                <div style="flex: 1;">
                    <span class="d-block font-semibold text-gray-700">{{ $student->first_name }} {{ $student->last_name }}</span>
                    <small class="text-gray-400">{{ $student->student_id_number }}</small>
                </div>
                
                <!-- Quick Mark Input -->
                <div style="width: 80px;">
                    <input type="number" 
                           class="form-control form-control-sm text-center border-gray-300 rounded" 
                           placeholder="0-100"
                           x-model="mark"
                           @change="console.log('Saving ' + mark + ' for {{ $student->first_name }}')">
                </div>
                
                <!-- Status Icon (Reactive) -->
                <div class="ms-2">
                    <template x-if="mark > 0">
                        <span class="text-success">●</span>
                    </template>
                    <template x-if="mark == ''">
                        <span class="text-gray-300">○</span>
                    </template>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Submit Button (Fixed at Bottom for Mobile) -->
        <div class="fixed-bottom p-3 bg-white border-top" style="max-width: 360px; margin: 0 auto;">
            <button class="btn btn-primary w-full py-2 font-bold uppercase tracking-wide">
                Sync Marks (Offline Ready)
            </button>
        </div>
    </div>
</x-app-layout>