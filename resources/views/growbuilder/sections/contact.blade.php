<section class="py-16 md:py-24 bg-white" id="contact">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4" style="color: var(--primary-color)">
                {{ $content['title'] ?? 'Contact Us' }}
            </h2>
            @if(isset($content['description']))
            <p class="text-lg text-gray-600">{{ $content['description'] }}</p>
            @endif
        </div>
        
        @if(isset($content['showForm']) && $content['showForm'])
        <div class="max-w-xl mx-auto">
            <form class="space-y-6" onsubmit="event.preventDefault(); alert('Form submitted! (Demo)')">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
                        style="--tw-ring-color: var(--primary-color)"
                    >
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
                    >
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
                    >
                </div>
                
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <textarea 
                        id="message" 
                        name="message"
                        rows="4"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
                    ></textarea>
                </div>
                
                <button 
                    type="submit"
                    class="w-full py-3 px-6 text-white font-semibold rounded-lg transition hover:opacity-90"
                    style="background-color: var(--primary-color)"
                >
                    Send Message
                </button>
            </form>
        </div>
        @endif
    </div>
</section>
