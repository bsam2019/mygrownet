/**
 * Section Default Content
 * Default content for each section type when created
 */

import type { SectionType } from '../types';
import { getIndustryCopy, genericCopy } from './copyLibrary';

interface PageInfo {
    id: number;
    title: string;
    slug: string;
    isHomepage: boolean;
    showInNav: boolean;
}

/**
 * Get default content for a section type
 * Now uses professional copy from copyLibrary
 */
export function getDefaultContent(
    type: SectionType,
    siteName: string,
    pages: PageInfo[] = [],
    industry?: string
): Record<string, any> {
    // Get industry-specific copy or fallback to generic
    const copy = industry ? getIndustryCopy(industry) : genericCopy;
    
    const defaults: Record<SectionType, Record<string, any>> = {
        'hero': {
            title: copy.hero.title,
            subtitle: copy.hero.subtitle,
            buttonText: copy.hero.cta,
            buttonLink: '#contact',
            secondaryButtonText: 'Learn More',
            secondaryButtonLink: '#about',
            textPosition: 'center',
            backgroundImage: '',
            layout: 'centered',
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
            title: copy.about.title,
            description: copy.about.description,
            image: '',
            imagePosition: 'right',
            layout: 'image-right',
        },
        'services': {
            title: copy.services.title,
            subtitle: copy.services.subtitle,
            layout: 'grid',
            columns: 3,
            items: [
                { 
                    title: 'Strategic Planning', 
                    description: 'Comprehensive strategies tailored to your business goals and market position.',
                    icon: 'chart',
                },
                { 
                    title: 'Process Optimization', 
                    description: 'Streamline operations and maximize efficiency across your organization.',
                    icon: 'cog',
                },
                { 
                    title: 'Growth Solutions', 
                    description: 'Scalable solutions designed to accelerate your business growth.',
                    icon: 'rocket',
                },
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
            subtitle: 'Real results from real customers',
            layout: 'grid',
            items: [
                { 
                    name: copy.testimonial.author, 
                    text: copy.testimonial.quote, 
                    role: copy.testimonial.role,
                    rating: 5,
                },
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
            title: copy.cta.title,
            description: copy.cta.subtitle,
            buttonText: copy.cta.button,
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
            title: copy.stats[0] ? 'Our Impact in Numbers' : 'Our Impact',
            subtitle: 'Proven results that speak for themselves',
            layout: 'horizontal',
            animated: true,
            items: copy.stats.length > 0 ? copy.stats.map(stat => ({
                number: stat.number,
                suffix: stat.suffix,
                label: stat.label,
                icon: 'chart',
            })) : [
                { number: '500', suffix: '+', label: 'Happy Clients', icon: 'users' },
                { number: '10', suffix: '+', label: 'Years Experience', icon: 'star' },
                { number: '1000', suffix: '+', label: 'Projects Completed', icon: 'chart' },
                { number: '99', suffix: '%', label: 'Satisfaction Rate', icon: 'heart' },
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
        'timeline': {
            title: 'Our Journey',
            subtitle: 'Milestones that shaped our success',
            textAlign: 'center',
            layout: 'vertical',
            items: [
                { year: '2020', title: 'Company Founded', description: 'Started with a vision to transform the industry', icon: 'star' },
                { year: '2021', title: 'First Major Client', description: 'Secured partnership with leading organization', icon: 'rocket' },
                { year: '2022', title: 'Award Winning', description: 'Recognized as industry leader', icon: 'trophy' },
                { year: '2023', title: 'Expansion', description: 'Opened new offices and doubled team size', icon: 'sparkles' },
            ],
        },
        'cta-banner': {
            title: copy.cta.title,
            subtitle: copy.cta.subtitle,
            layout: 'centered',
            buttonText: copy.cta.button,
            buttonLink: '#contact',
            secondaryButtonText: 'Learn More',
            secondaryButtonLink: '#about',
            image: '',
        },
        'logo-cloud': {
            title: 'Trusted by Leading Companies',
            subtitle: 'Join hundreds of businesses that trust us',
            textAlign: 'center',
            layout: 'grid',
            grayscale: true,
            items: [
                { image: '', name: 'Company 1', link: '' },
                { image: '', name: 'Company 2', link: '' },
                { image: '', name: 'Company 3', link: '' },
                { image: '', name: 'Company 4', link: '' },
                { image: '', name: 'Company 5', link: '' },
                { image: '', name: 'Company 6', link: '' },
            ],
        },
        'video-hero': {
            title: 'Watch Our Story',
            subtitle: 'Discover how we transform businesses',
            layout: 'fullscreen',
            videoUrl: '',
            posterImage: '',
            autoPlay: false,
            muted: true,
            loop: true,
            buttonText: 'Learn More',
            buttonLink: '#about',
        },
    };

    return defaults[type] || {};
}
