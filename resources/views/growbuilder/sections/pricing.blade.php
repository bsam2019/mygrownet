<section class="py-16 md:py-24 bg-gray-50" id="pricing">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12" style="color: var(--primary-color)">
            {{ $content['title'] ?? 'Pricing' }}
        </h2>
        
        @if(isset($content['plans']) && count($content['plans']) > 0)
        <div class="grid grid-cols-1 md:grid-cols-{{ min(count($content['plans']), 3) }} gap-8 max-w-4xl mx-auto">
            @foreach($content['plans'] as $index => $plan)
            <div class="bg-white rounded-xl shadow-sm border-2 {{ $index === 1 ? 'border-blue-500' : 'border-gray-200' }} p-6">
                @if($index === 1)
                <span class="inline-block px-3 py-1 text-xs font-semibold text-white rounded-full mb-4" style="background-color: var(--primary-color)">
                    Popular
                </span>
                @endif
                
                <h3 class="text-xl font-bold mb-2">{{ $plan['name'] ?? 'Plan' }}</h3>
                <p class="text-3xl font-bold mb-6" style="color: var(--primary-color)">
                    {{ $plan['price'] ?? 'K0' }}
                    @if(isset($plan['period']))
                    <span class="text-sm font-normal text-gray-500">/{{ $plan['period'] }}</span>
                    @endif
                </p>
                
                @if(isset($plan['features']) && count($plan['features']) > 0)
                <ul class="space-y-3 mb-6">
                    @foreach($plan['features'] as $feature)
                    <li class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-gray-600">{{ $feature }}</span>
                    </li>
                    @endforeach
                </ul>
                @endif
                
                <a 
                    href="#contact"
                    class="block w-full py-3 text-center font-semibold rounded-lg transition {{ $index === 1 ? 'text-white' : 'border-2' }}"
                    style="{{ $index === 1 ? 'background-color: var(--primary-color)' : 'border-color: var(--primary-color); color: var(--primary-color)' }}"
                >
                    Get Started
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</section>
