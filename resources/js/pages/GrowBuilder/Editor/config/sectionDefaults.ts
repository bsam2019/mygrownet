/**
 * Section Default Content
 * Default content for each section type when created
 */

import type { SectionType } from '../types';

interface PageInfo {
    id: number;
    title: string;
    slug: string;
    isHomepage: boolean;
    showInNav: boolean;
}

/**
 * Get default content for a section type
 */
export function getDefaultContent(
    type: SectionType,
    siteName: string,
    pages: PageInfo[] = []
): Record<string, any> {
    const defaults: Record<SectionType, Record<string, any>> = {
        'hero': {
            title: 'Welcome to Our Business',
            subtitle: 'We help you grow and succeed',
            buttonText: 'Get Started',
            buttonLink: '#contact',
            textPosition: 'center',
            backgroundImage: '',
        },
        'page-header': {
            title: 'Page Title',
            subtitle: 'A brief description of this page',
            backgroundImage: '',
            backgroundColor: '#1e40af',
            textColor: '#ffffff',
            textPosition: 'center',
        },
        'about': {
            title: 'About Us',
            description: 'Tell your story here. Share your mission, values, and what makes you unique.',
            image: '',
            imagePosition: 'right',
        },
        'services': {
            title: 'Our Services',
            items: [
                { title: 'Service 1', description: 'Description of service 1' },
                { title: 'Service 2', description: 'Description of service 2' },
                { title: 'Service 3', description: 'Description of service 3' },
            ],
        },
        'features': {
            title: 'Why Choose Us',
            items: [
                { title: 'Feature 1', description: 'Description' },
                { title: 'Feature 2', description: 'Description' },
            ],
        },
        'gallery': {
            title: 'Our Gallery',
            images: [],
        },
        'testimonials': {
            title: 'What Our Clients Say',
            items: [
                { name: 'John Doe', text: 'Great service!', role: 'Customer' },
            ],
        },
        'pricing': {
            title: 'Pricing Plans',
            plans: [
                { name: 'Basic', price: 'K99', features: ['Feature 1', 'Feature 2'] },
            ],
        },
        'products': {
            title: 'Our Products',
            showAll: true,
            limit: 6,
        },
        'contact': {
            title: 'Contact Us',
            description: 'Get in touch with us',
            showForm: true,
            email: '',
            phone: '',
            address: '',
        },
        'cta': {
            title: 'Ready to Get Started?',
            description: 'Contact us today for a free consultation',
            buttonText: 'Contact Us',
            buttonLink: '#contact',
        },
        'member-cta': {
            title: 'Join Our Community',
            subtitle: 'Become a member and unlock exclusive benefits',
            description: 'Get access to premium content, special offers, and connect with like-minded people.',
            benefits: [
                'Access to exclusive content',
                'Member-only discounts',
                'Priority support',
                'Community access',
            ],
            loginText: 'Already a member? Login',
            registerText: 'Sign Up Now',
            registerButtonStyle: 'solid', // solid, outline
            showLoginLink: true,
            backgroundColor: '#1e40af',
            textColor: '#ffffff',
        },
        'text': {
            content: '<p>Enter your text content here...</p>',
        },
        'faq': {
            title: 'Frequently Asked Questions',
            items: [
                { question: 'What services do you offer?', answer: 'We offer a wide range of services to meet your needs.' },
                { question: 'How can I contact you?', answer: 'You can reach us through our contact form or call us directly.' },
                { question: 'What are your business hours?', answer: 'We are open Monday to Friday, 9am to 5pm.' },
            ],
        },
        'team': {
            title: 'Meet Our Team',
            items: [
                { name: 'John Smith', role: 'CEO', image: '', bio: 'Leading our company with vision and passion.' },
                { name: 'Jane Doe', role: 'CTO', image: '', bio: 'Driving innovation and technology.' },
            ],
        },
        'blog': {
            title: 'Latest News',
            showLatest: true,
            limit: 3,
            posts: [
                { title: 'Blog Post Title', excerpt: 'A brief excerpt of the blog post...', date: new Date().toISOString().split('T')[0], image: '' },
            ],
        },
        'stats': {
            title: 'Our Impact',
            items: [
                { value: '500+', label: 'Happy Clients' },
                { value: '10+', label: 'Years Experience' },
                { value: '50+', label: 'Projects Completed' },
                { value: '24/7', label: 'Support' },
            ],
        },
        'map': {
            title: 'Find Us',
            address: '123 Business Street, City, Country',
            embedUrl: '',
            showAddress: true,
        },
        'video': {
            title: 'Watch Our Story',
            videoUrl: '',
            videoType: 'youtube',
            autoplay: false,
            description: 'Learn more about what we do.',
        },
        'divider': {
            style: 'line',
            height: 40,
            color: '#e5e7eb',
        },
    };

    return defaults[type] || {};
}
