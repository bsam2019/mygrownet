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
            <form id="contact-form" class="space-y-6">
                <div id="form-success" class="hidden p-4 bg-emerald-50 border border-emerald-200 rounded-lg">
                    <p class="text-emerald-700 font-medium">Thank you! Your message has been sent successfully.</p>
                </div>
                <div id="form-error" class="hidden p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-700 font-medium">Something went wrong. Please try again.</p>
                </div>
                
                <div>
                    <label for="contact-name" class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input 
                        type="text" 
                        id="contact-name" 
                        name="name"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
                        style="--tw-ring-color: var(--primary-color)"
                    >
                </div>
                
                <div>
                    <label for="contact-email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input 
                        type="email" 
                        id="contact-email" 
                        name="email"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
                    >
                </div>
                
                <div>
                    <label for="contact-phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input 
                        type="tel" 
                        id="contact-phone" 
                        name="phone"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
                    >
                </div>
                
                <div>
                    <label for="contact-subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                    <input 
                        type="text" 
                        id="contact-subject" 
                        name="subject"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
                    >
                </div>
                
                <div>
                    <label for="contact-message" class="block text-sm font-medium text-gray-700 mb-1">Message *</label>
                    <textarea 
                        id="contact-message" 
                        name="message"
                        rows="4"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
                    ></textarea>
                </div>
                
                <button 
                    type="submit"
                    id="contact-submit"
                    class="w-full py-3 px-6 text-white font-semibold rounded-lg transition hover:opacity-90 disabled:opacity-50"
                    style="background-color: var(--primary-color)"
                >
                    <span id="submit-text">Send Message</span>
                    <span id="submit-loading" class="hidden">Sending...</span>
                </button>
            </form>
        </div>
        
        <script>
            document.getElementById('contact-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const form = e.target;
                const submitBtn = document.getElementById('contact-submit');
                const submitText = document.getElementById('submit-text');
                const submitLoading = document.getElementById('submit-loading');
                const successMsg = document.getElementById('form-success');
                const errorMsg = document.getElementById('form-error');
                
                // Hide previous messages
                successMsg.classList.add('hidden');
                errorMsg.classList.add('hidden');
                
                // Show loading state
                submitBtn.disabled = true;
                submitText.classList.add('hidden');
                submitLoading.classList.remove('hidden');
                
                try {
                    const response = await fetch('/gb-api/{{ $subdomain }}/contact', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({
                            name: document.getElementById('contact-name').value,
                            email: document.getElementById('contact-email').value,
                            phone: document.getElementById('contact-phone').value,
                            subject: document.getElementById('contact-subject').value,
                            message: document.getElementById('contact-message').value
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok && data.success) {
                        successMsg.classList.remove('hidden');
                        form.reset();
                    } else {
                        errorMsg.classList.remove('hidden');
                    }
                } catch (error) {
                    console.error('Contact form error:', error);
                    errorMsg.classList.remove('hidden');
                } finally {
                    submitBtn.disabled = false;
                    submitText.classList.remove('hidden');
                    submitLoading.classList.add('hidden');
                }
            });
        </script>
        @endif
    </div>
</section>
