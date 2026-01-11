@php
    $title = $content['title'] ?? 'Join Our Community';
    $subtitle = $content['subtitle'] ?? '';
    $description = $content['description'] ?? '';
    $benefits = $content['benefits'] ?? [];
    $registerText = $content['registerText'] ?? 'Sign Up Now';
    $showLoginLink = $content['showLoginLink'] ?? true;
    $loginText = $content['loginText'] ?? 'Already a member? Log in';
    
    $bgColor = $style['backgroundColor'] ?? '#1e40af';
    $textColor = $style['textColor'] ?? '#ffffff';
@endphp

<section class="py-16 md:py-24" style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">{{ $title }}</h2>
        
        @if($subtitle)
            <p class="text-xl opacity-90 mb-6">{{ $subtitle }}</p>
        @endif
        
        @if($description)
            <p class="text-lg opacity-80 mb-8 max-w-2xl mx-auto">{{ $description }}</p>
        @endif
        
        @if(count($benefits) > 0)
            <div class="flex flex-wrap justify-center gap-4 mb-8">
                @foreach($benefits as $benefit)
                    <div class="flex items-center gap-2 bg-white/10 px-4 py-2 rounded-full">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ $benefit }}</span>
                    </div>
                @endforeach
            </div>
        @endif
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <a 
                href="{{ url('/register') }}"
                class="btn-primary px-8 py-4 text-lg font-semibold text-white rounded-lg hover:opacity-90 transition"
                style="background-color: var(--primary-color, #2563eb);"
            >
                {{ $registerText }}
            </a>
            
            @if($showLoginLink)
                <a href="{{ url('/login') }}" class="text-white/80 hover:text-white underline">
                    {{ $loginText }}
                </a>
            @endif
        </div>
    </div>
</section>
