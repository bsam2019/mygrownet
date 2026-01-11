<section class="py-16 md:py-24" id="team" style="background-color: {{ $style['backgroundColor'] ?? '#f9fafb' }};">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(isset($content['title']))
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-4" style="color: var(--primary-color)">
            {{ $content['title'] }}
        </h2>
        @endif
        
        @if(isset($content['subtitle']))
        <p class="text-lg text-gray-600 text-center mb-12">
            {{ $content['subtitle'] }}
        </p>
        @endif
        
        @if(isset($content['items']) && count($content['items']) > 0)
        @php $columns = $content['columns'] ?? 4; @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-{{ min($columns, 4) }} gap-8">
            @foreach($content['items'] as $member)
            <div class="text-center">
                <div class="w-32 h-32 mx-auto mb-4 rounded-full overflow-hidden bg-gray-200">
                    @if(isset($member['image']) && $member['image'])
                    <img src="{{ $member['image'] }}" alt="{{ $member['name'] ?? '' }}" class="w-full h-full object-cover">
                    @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                    @endif
                </div>
                <h3 class="text-lg font-semibold">{{ $member['name'] ?? '' }}</h3>
                <p class="text-sm text-gray-500 mb-2">{{ $member['role'] ?? '' }}</p>
                @if(isset($member['bio']))
                <p class="text-sm text-gray-600">{{ $member['bio'] }}</p>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
</section>
