/**
 * Page Templates Configuration
 * Pre-designed page templates for quick page creation
 */

import {
    DocumentIcon,
    HomeIcon,
    SparklesIcon,
    BriefcaseIcon,
    UserGroupIcon,
    Cog6ToothIcon,
    EnvelopeIcon,
    QuestionMarkCircleIcon,
    CurrencyDollarIcon,
    NewspaperIcon,
    PhotoIcon,
    StarIcon,
} from '@heroicons/vue/24/outline';
import type { Component } from 'vue';

export interface PageTemplate {
    id: string;
    name: string;
    description: string;
    iconType: string;
    isHomepage?: boolean;
    sections: Array<{
        type: string;
        content: Record<string, any>;
        style: Record<string, any>;
    }>;
}

/**
 * Icon mapping for templates
 */
export const templateIcons: Record<string, Component> = {
    'document': DocumentIcon,
    'home': HomeIcon,
    'sparkles': SparklesIcon,
    'briefcase': BriefcaseIcon,
    'users': UserGroupIcon,
    'cog': Cog6ToothIcon,
    'envelope': EnvelopeIcon,
    'question': QuestionMarkCircleIcon,
    'currency': CurrencyDollarIcon,
    'newspaper': NewspaperIcon,
    'photo': PhotoIcon,
    'star': StarIcon,
};

/**
 * Get icon component for a template
 */
export function getTemplateIcon(iconType: string): Component {
    return templateIcons[iconType] || DocumentIcon;
}

/**
 * All available page templates
 */
export const pageTemplates: PageTemplate[] = [
    {
        id: 'blank',
        name: 'Blank Page',
        description: 'Start from scratch with a clean canvas',
        iconType: 'document',
        sections: [],
    },
    {
        id: 'home',
        name: 'Home Page',
        description: 'Premium landing page with hero, services, and social proof',
        iconType: 'home',
        isHomepage: true,
        sections: [
            {
                type: 'hero',
                content: {
                    title: 'Build Something Extraordinary',
                    subtitle: 'We partner with ambitious businesses to create digital experiences that captivate audiences, drive growth, and deliver measurable results.',
                    buttonText: 'Start Your Project',
                    buttonLink: '#contact',
                    secondaryButtonText: 'View Our Work',
                    secondaryButtonLink: '#portfolio',
                    textPosition: 'center',
                    backgroundImage: '',
                },
                style: { minHeight: 600, backgroundColor: '#0a0a0a' },
            },
            {
                type: 'stats',
                content: {
                    title: 'Trusted by Leading Brands',
                    items: [
                        { value: '500+', label: 'Projects Delivered' },
                        { value: '98%', label: 'Client Satisfaction' },
                        { value: 'K50M+', label: 'Revenue Generated' },
                        { value: '24/7', label: 'Support Available' },
                    ],
                },
                style: { backgroundColor: '#ffffff' },
            },
            {
                type: 'services',
                content: {
                    title: 'Solutions That Drive Results',
                    subtitle: 'Comprehensive services tailored to your unique business needs',
                    items: [
                        { title: 'Strategic Consulting', description: 'Data-driven strategies that align your vision with market opportunities for sustainable competitive advantage.', icon: 'chart' },
                        { title: 'Digital Transformation', description: 'End-to-end modernization of your technology stack, processes, and customer experiences.', icon: 'code' },
                        { title: 'Brand Development', description: 'Compelling brand identities that resonate with your audience and differentiate you in the market.', icon: 'sparkles' },
                    ],
                },
                style: { backgroundColor: '#fafafa' },
            },
            {
                type: 'about',
                content: {
                    title: 'Why Choose Us?',
                    description: 'For over a decade, we\'ve helped businesses across Zambia and beyond transform their operations and achieve breakthrough results. Our approach combines deep industry expertise with innovative thinking, ensuring every solution is tailored to your specific goals. We don\'t just deliver projects—we build lasting partnerships.',
                    image: '',
                    imagePosition: 'right',
                    features: ['Proven track record with 500+ successful projects', 'Dedicated team of certified professionals', 'Transparent pricing with no hidden fees', 'Ongoing support and optimization'],
                },
                style: { backgroundColor: '#ffffff' },
            },
            {
                type: 'testimonials',
                content: {
                    title: 'What Our Clients Say',
                    subtitle: 'Don\'t just take our word for it',
                    items: [
                        { name: 'Chanda Mwila', text: 'Working with this team transformed our entire digital presence. We saw a 300% increase in online leads within the first quarter. Their strategic approach is unmatched.', role: 'CEO, Mwila Technologies', image: '', rating: 5 },
                        { name: 'Grace Banda', text: 'The professionalism and attention to detail exceeded all expectations. They truly understood our vision and delivered beyond what we imagined possible.', role: 'Founder, Banda Ventures', image: '', rating: 5 },
                        { name: 'Dr. James Phiri', text: 'From strategy to execution, every step was handled with excellence. Our revenue has doubled since implementing their recommendations.', role: 'Director, Phiri Healthcare Group', image: '', rating: 5 },
                    ],
                },
                style: { backgroundColor: '#f8fafc' },
            },
            {
                type: 'cta',
                content: {
                    title: 'Ready to Transform Your Business?',
                    description: 'Book a free 30-minute strategy session and discover how we can help you achieve your goals.',
                    buttonText: 'Schedule Free Consultation',
                    buttonLink: '#contact',
                },
                style: { backgroundColor: '#1e3a8a' },
            },
            {
                type: 'contact',
                content: {
                    title: 'Let\'s Start a Conversation',
                    description: 'Tell us about your project. Our team responds within 24 hours.',
                    showForm: true,
                    email: 'hello@yourbusiness.com',
                    phone: '+260 97X XXX XXX',
                    address: 'Cairo Road, Lusaka, Zambia',
                },
                style: { backgroundColor: '#ffffff' },
            },
        ],
    },
    {
        id: 'home-minimal',
        name: 'Minimal Home',
        description: 'Clean, elegant design with focused messaging',
        iconType: 'sparkles',
        isHomepage: true,
        sections: [
            {
                type: 'hero',
                content: {
                    title: 'Less Noise. More Impact.',
                    subtitle: 'We help forward-thinking brands cut through the clutter with elegant solutions that simply work.',
                    buttonText: 'Let\'s Talk',
                    buttonLink: '#contact',
                    textPosition: 'center',
                    backgroundImage: '',
                },
                style: { minHeight: 550, backgroundColor: '#111111' },
            },
            {
                type: 'features',
                content: {
                    title: 'Our Philosophy',
                    subtitle: 'Simple principles that guide everything we do',
                    items: [
                        { title: 'Clarity', description: 'We strip away complexity to reveal what truly matters for your business.', icon: 'eye' },
                        { title: 'Craft', description: 'Every detail is considered. Every pixel has purpose.', icon: 'sparkles' },
                        { title: 'Results', description: 'Beautiful design means nothing without measurable outcomes.', icon: 'chart' },
                        { title: 'Partnership', description: 'Your success is our success. We\'re in this together.', icon: 'users' },
                    ],
                },
                style: { backgroundColor: '#ffffff' },
            },
            {
                type: 'about',
                content: {
                    title: 'Thoughtful Design. Tangible Results.',
                    description: 'We believe the best solutions are elegantly simple. Our team combines strategic thinking with meticulous execution to create experiences that resonate with your audience and drive real business growth.',
                    image: '',
                    imagePosition: 'left',
                },
                style: { backgroundColor: '#fafafa' },
            },
            {
                type: 'cta',
                content: {
                    title: 'Ready to Simplify?',
                    description: 'Let\'s create something remarkable together.',
                    buttonText: 'Start a Project',
                    buttonLink: '#contact',
                },
                style: { backgroundColor: '#0a0a0a' },
            },
        ],
    },
    {
        id: 'home-business',
        name: 'Business Home',
        description: 'Professional corporate landing page',
        iconType: 'briefcase',
        isHomepage: true,
        sections: [
            {
                type: 'hero',
                content: {
                    title: 'Empowering Zambian Businesses to Compete Globally',
                    subtitle: 'Strategic consulting, digital solutions, and operational excellence for organizations ready to scale.',
                    buttonText: 'Request Proposal',
                    buttonLink: '#contact',
                    secondaryButtonText: 'Our Services',
                    secondaryButtonLink: '#services',
                    textPosition: 'left',
                    backgroundImage: '',
                },
                style: { minHeight: 580, backgroundColor: '#0c2340' },
            },
            {
                type: 'stats',
                content: {
                    title: '',
                    items: [
                        { value: 'K75M+', label: 'Client Revenue Generated' },
                        { value: '200+', label: 'Enterprise Clients' },
                        { value: '15+', label: 'Years in Business' },
                        { value: '99.9%', label: 'Project Success Rate' },
                    ],
                },
                style: { backgroundColor: '#ffffff' },
            },
            {
                type: 'services',
                content: {
                    title: 'Enterprise Solutions',
                    subtitle: 'Comprehensive services for growing organizations',
                    items: [
                        { title: 'Executive Advisory', description: 'C-suite consulting that aligns strategy with execution.', icon: 'briefcase' },
                        { title: 'Digital Transformation', description: 'Modernize your technology infrastructure and processes.', icon: 'code' },
                        { title: 'Market Expansion', description: 'Research-driven strategies to enter new markets.', icon: 'globe' },
                        { title: 'Operational Excellence', description: 'Lean methodologies and process optimization.', icon: 'cog' },
                        { title: 'Talent & Culture', description: 'Leadership development and organizational design.', icon: 'users' },
                        { title: 'Risk & Compliance', description: 'Comprehensive frameworks for risk mitigation.', icon: 'shield' },
                    ],
                },
                style: { backgroundColor: '#f8fafc' },
            },
            {
                type: 'testimonials',
                content: {
                    title: 'Trusted by Industry Leaders',
                    items: [
                        { name: 'Jonathan Mwale', text: 'Their strategic guidance was instrumental in our expansion. We grew from 50 to 200 employees.', role: 'Managing Director, Mwale Holdings', image: '', rating: 5 },
                        { name: 'Patricia Tembo', text: 'The digital transformation they delivered has completely changed how we operate.', role: 'COO, Tembo Financial Services', image: '', rating: 5 },
                        { name: 'Robert Sakala', text: 'Professional, thorough, and genuinely invested in our success.', role: 'CEO, Sakala Industries', image: '', rating: 5 },
                    ],
                },
                style: { backgroundColor: '#ffffff' },
            },
            {
                type: 'cta',
                content: {
                    title: 'Let\'s Discuss Your Growth Strategy',
                    description: 'Schedule a confidential executive briefing with our senior team.',
                    buttonText: 'Book Executive Briefing',
                    buttonLink: '#contact',
                },
                style: { backgroundColor: '#0c2340' },
            },
        ],
    },
    {
        id: 'about',
        name: 'About Us',
        description: 'Company story, mission, and team showcase',
        iconType: 'users',
        sections: [
            { type: 'page-header', content: { title: 'Our Story', subtitle: 'Building tomorrow\'s solutions with today\'s passion', backgroundColor: '#0f172a', textColor: '#ffffff', textPosition: 'center' }, style: { minHeight: 220 } },
            { type: 'about', content: { title: 'A Legacy of Excellence', description: 'Founded on the belief that every business deserves access to world-class expertise, we have spent over a decade helping organizations transform challenges into opportunities.', image: '', imagePosition: 'right' }, style: { backgroundColor: '#ffffff' } },
            { type: 'features', content: { title: 'Our Core Values', items: [{ title: 'Excellence', description: 'We pursue the highest standards in everything we do.' }, { title: 'Integrity', description: 'Honesty and transparency form the foundation of every relationship.' }, { title: 'Innovation', description: 'We embrace change and continuously seek better ways.' }, { title: 'Impact', description: 'We measure success by the tangible difference we make.' }] }, style: { backgroundColor: '#f8fafc' } },
            { type: 'stats', content: { title: 'Our Impact in Numbers', items: [{ value: '15+', label: 'Years of Service' }, { value: '500+', label: 'Success Stories' }, { value: '50+', label: 'Expert Team Members' }, { value: '12', label: 'Countries Served' }] }, style: { backgroundColor: '#ffffff' } },
            { type: 'team', content: { title: 'Meet the Leadership', items: [{ name: 'David Mulenga', role: 'Founder & CEO', image: '', bio: 'Visionary entrepreneur with 20+ years experience.' }, { name: 'Chipo Banda', role: 'Chief Operations Officer', image: '', bio: 'Operations expert who has streamlined processes for Fortune 500 companies.' }, { name: 'Emmanuel Phiri', role: 'Head of Innovation', image: '', bio: 'Technology leader passionate about digital solutions.' }] }, style: { backgroundColor: '#f8fafc' } },
            { type: 'cta', content: { title: 'Become Part of Our Story', description: 'Let\'s write the next chapter together', buttonText: 'Start the Conversation', buttonLink: '/contact' }, style: { backgroundColor: '#1e40af' } },
        ],
    },
    {
        id: 'services',
        name: 'Services',
        description: 'Detailed service offerings and benefits',
        iconType: 'cog',
        sections: [
            { type: 'page-header', content: { title: 'Our Services', subtitle: 'Tailored solutions designed to accelerate your success', backgroundColor: '#0f172a', textColor: '#ffffff', textPosition: 'center' }, style: { minHeight: 220 } },
            { type: 'services', content: { title: 'What We Offer', items: [{ title: 'Strategic Advisory', description: 'Expert guidance on business strategy and growth planning.' }, { title: 'Digital Solutions', description: 'Custom software development and system integration.' }, { title: 'Brand & Marketing', description: 'Comprehensive branding and digital marketing strategies.' }, { title: 'Training & Development', description: 'Professional development programs and workshops.' }, { title: 'Financial Consulting', description: 'Financial planning and advisory services.' }, { title: 'Project Management', description: 'End-to-end project delivery with proven methodologies.' }] }, style: { backgroundColor: '#ffffff' } },
            { type: 'features', content: { title: 'The Advantage of Working With Us', items: [{ title: 'Proven Methodology', description: 'Battle-tested frameworks refined through hundreds of engagements.' }, { title: 'Dedicated Teams', description: 'Senior professionals assigned to your project.' }, { title: 'Measurable Results', description: 'Clear KPIs and regular reporting.' }, { title: 'Ongoing Support', description: 'Long-term partnership with continuous optimization.' }] }, style: { backgroundColor: '#f8fafc' } },
            { type: 'cta', content: { title: 'Ready to Get Started?', description: 'Let\'s discuss how our services can address your specific needs', buttonText: 'Request a Custom Proposal', buttonLink: '/contact' }, style: { backgroundColor: '#1e40af' } },
        ],
    },
    {
        id: 'contact',
        name: 'Contact',
        description: 'Professional contact page with form and details',
        iconType: 'envelope',
        sections: [
            { type: 'page-header', content: { title: 'Get in Touch', subtitle: 'We\'re here to help you succeed', backgroundColor: '#0f172a', textColor: '#ffffff', textPosition: 'center' }, style: { minHeight: 220 } },
            { type: 'contact', content: { title: 'Send Us a Message', description: 'Fill out the form below and a member of our team will respond within 24 hours.', showForm: true, email: 'hello@yourbusiness.com', phone: '+260 97X XXX XXX', address: 'Plot 123, Cairo Road, Lusaka, Zambia' }, style: { backgroundColor: '#ffffff' } },
            { type: 'features', content: { title: 'Why Reach Out?', items: [{ title: 'Free Consultation', description: 'No obligation discussion about your needs.' }, { title: 'Quick Response', description: 'Our team responds within 24 business hours.' }, { title: 'Expert Guidance', description: 'Speak directly with specialists.' }, { title: 'Flexible Options', description: 'Solutions tailored to your budget.' }] }, style: { backgroundColor: '#f8fafc' } },
            { type: 'map', content: { title: 'Visit Our Office', address: 'Plot 123, Cairo Road, Lusaka, Zambia', embedUrl: '', showAddress: true }, style: { backgroundColor: '#ffffff' } },
        ],
    },
    {
        id: 'faq',
        name: 'FAQ',
        description: 'Comprehensive frequently asked questions',
        iconType: 'question',
        sections: [
            { type: 'page-header', content: { title: 'Frequently Asked Questions', subtitle: 'Everything you need to know about working with us', backgroundColor: '#0f172a', textColor: '#ffffff', textPosition: 'center' }, style: { minHeight: 220 } },
            { type: 'faq', content: { title: 'General Questions', items: [{ question: 'What industries do you serve?', answer: 'We work with clients across diverse sectors including technology, finance, healthcare, manufacturing, retail, and professional services.' }, { question: 'How do you ensure project success?', answer: 'Every engagement begins with a thorough discovery phase. We use proven project management methodologies and maintain transparent communication.' }, { question: 'What is your typical engagement process?', answer: '1) Initial consultation, 2) Proposal with detailed scope, 3) Kick-off and discovery, 4) Iterative development, 5) Delivery and knowledge transfer, 6) Ongoing support.' }, { question: 'Do you offer ongoing support?', answer: 'Absolutely. We offer various support packages including maintenance, optimization, training, and strategic advisory services.' }, { question: 'How do you handle confidentiality?', answer: 'We take confidentiality extremely seriously. All team members sign NDAs, and we implement strict data security protocols.' }] }, style: { backgroundColor: '#ffffff' } },
            { type: 'cta', content: { title: 'Still Have Questions?', description: 'Our team is happy to provide personalized answers', buttonText: 'Contact Our Team', buttonLink: '/contact' }, style: { backgroundColor: '#1e40af' } },
        ],
    },
    {
        id: 'pricing',
        name: 'Pricing',
        description: 'Clear pricing plans and packages',
        iconType: 'currency',
        sections: [
            { type: 'page-header', content: { title: 'Simple, Transparent Pricing', subtitle: 'Choose the plan that fits your needs. No hidden fees.', backgroundColor: '#0f172a', textColor: '#ffffff', textPosition: 'center' }, style: { minHeight: 220 } },
            { type: 'pricing', content: { title: 'Our Plans', plans: [{ name: 'Starter', price: 'K2,500/mo', features: ['Up to 5 team members', 'Core platform features', 'Email support (48hr)', 'Monthly reports', 'Basic analytics'] }, { name: 'Professional', price: 'K7,500/mo', features: ['Up to 25 team members', 'All Starter features', 'Priority support (24hr)', 'Weekly strategy calls', 'Advanced analytics', 'Custom integrations'] }, { name: 'Enterprise', price: 'Custom', features: ['Unlimited team members', 'All Professional features', '24/7 premium support', 'On-site consultations', 'Custom development', 'SLA guarantees'] }] }, style: { backgroundColor: '#ffffff' } },
            { type: 'features', content: { title: 'All Plans Include', items: [{ title: 'No Long-Term Contracts', description: 'Month-to-month flexibility.' }, { title: 'Free Onboarding', description: 'Personalized setup and training.' }, { title: 'Regular Updates', description: 'Continuous improvements included.' }, { title: 'Data Security', description: 'Enterprise-grade security.' }] }, style: { backgroundColor: '#f8fafc' } },
            { type: 'faq', content: { title: 'Pricing FAQ', items: [{ question: 'Can I switch plans anytime?', answer: 'Yes! Upgrade or downgrade at any time.' }, { question: 'Is there a free trial?', answer: 'We offer a 14-day free trial on Starter and Professional plans.' }, { question: 'What payment methods do you accept?', answer: 'MTN Mobile Money, Airtel Money, bank transfers, Visa, and Mastercard.' }, { question: 'Do you offer annual discounts?', answer: 'Yes! Save 20% when you choose annual billing.' }] }, style: { backgroundColor: '#ffffff' } },
            { type: 'cta', content: { title: 'Need a Custom Solution?', description: 'Let\'s design a package tailored to your requirements', buttonText: 'Talk to Sales', buttonLink: '/contact' }, style: { backgroundColor: '#1e40af' } },
        ],
    },
    {
        id: 'blog',
        name: 'Blog/News',
        description: 'Latest insights and company updates',
        iconType: 'newspaper',
        sections: [
            { type: 'page-header', content: { title: 'Insights & News', subtitle: 'Thought leadership, industry trends, and company updates', backgroundColor: '#0f172a', textColor: '#ffffff', textPosition: 'center' }, style: { minHeight: 220 } },
            { type: 'blog', content: { title: 'Latest Articles', showLatest: true, limit: 6, posts: [{ title: 'The Future of Digital Transformation in Africa', excerpt: 'Exploring how African businesses are leveraging technology.', date: new Date().toISOString().split('T')[0], image: '' }, { title: '5 Strategies for Sustainable Business Growth', excerpt: 'Proven approaches that successful companies use to scale.', date: new Date().toISOString().split('T')[0], image: '' }, { title: 'Building High-Performance Teams', excerpt: 'Insights from our experience helping organizations develop teams.', date: new Date().toISOString().split('T')[0], image: '' }] }, style: { backgroundColor: '#ffffff' } },
            { type: 'cta', content: { title: 'Stay Informed', description: 'Subscribe to our newsletter for weekly insights', buttonText: 'Subscribe Now', buttonLink: '#subscribe' }, style: { backgroundColor: '#f8fafc' } },
        ],
    },
    {
        id: 'gallery',
        name: 'Gallery/Portfolio',
        description: 'Showcase your best work and projects',
        iconType: 'photo',
        sections: [
            { type: 'page-header', content: { title: 'Our Portfolio', subtitle: 'A showcase of projects we\'re proud to have delivered', backgroundColor: '#0f172a', textColor: '#ffffff', textPosition: 'center' }, style: { minHeight: 220 } },
            { type: 'about', content: { title: 'Excellence in Every Project', description: 'Each project represents a unique challenge met with creativity, expertise, and dedication.', image: '', imagePosition: 'left' }, style: { backgroundColor: '#ffffff' } },
            { type: 'gallery', content: { title: 'Featured Projects', images: [] }, style: { backgroundColor: '#f8fafc' } },
            { type: 'testimonials', content: { title: 'Client Feedback', items: [{ name: 'Natasha Mwanza', text: 'The attention to detail was extraordinary.', role: 'Marketing Director, Mwanza Group' }, { name: 'Joseph Lungu', text: 'Professional, creative, and incredibly responsive.', role: 'Founder, Lungu Innovations' }] }, style: { backgroundColor: '#ffffff' } },
            { type: 'cta', content: { title: 'Ready to Create Something Amazing?', description: 'Let\'s discuss your project and bring your vision to life', buttonText: 'Start Your Project', buttonLink: '/contact' }, style: { backgroundColor: '#1e40af' } },
        ],
    },
    {
        id: 'testimonials',
        name: 'Testimonials',
        description: 'Customer success stories and reviews',
        iconType: 'star',
        sections: [
            { type: 'page-header', content: { title: 'Client Success Stories', subtitle: 'Hear from the businesses we\'ve helped transform', backgroundColor: '#0f172a', textColor: '#ffffff', textPosition: 'center' }, style: { minHeight: 220 } },
            { type: 'about', content: { title: 'Our Clients\' Success Is Our Greatest Achievement', description: 'We measure our success not by the projects we complete, but by the lasting impact we create.', image: '', imagePosition: 'right' }, style: { backgroundColor: '#ffffff' } },
            { type: 'testimonials', content: { title: 'What Our Clients Say', items: [{ name: 'Catherine Mumba', text: 'Partnering with this team was a game-changer. Their strategic insights helped us expand into two new markets.', role: 'CEO, Mumba Holdings' }, { name: 'Richard Sakala', text: 'The level of professionalism is unmatched. They took time to understand our business.', role: 'Managing Director, Sakala Technologies' }, { name: 'Florence Tembo', text: 'What impressed me most was their commitment to our success.', role: 'Founder, Tembo Consulting' }, { name: 'Michael Zulu', text: 'From consultation to delivery, every interaction was marked by excellence.', role: 'Operations Director, Zulu Enterprises' }] }, style: { backgroundColor: '#f8fafc' } },
            { type: 'stats', content: { title: 'The Numbers Speak', items: [{ value: '98%', label: 'Client Satisfaction' }, { value: '500+', label: 'Projects Completed' }, { value: '150%', label: 'Average ROI' }, { value: '95%', label: 'Client Retention' }] }, style: { backgroundColor: '#ffffff' } },
            { type: 'cta', content: { title: 'Join Our Success Stories', description: 'Become our next satisfied client', buttonText: 'Get Started Today', buttonLink: '/contact' }, style: { backgroundColor: '#1e40af' } },
        ],
    },
];

/**
 * Get templates suitable for new pages (excludes homepage templates)
 */
export function getNewPageTemplates(): PageTemplate[] {
    return pageTemplates.filter(t => !t.isHomepage);
}

/**
 * Get homepage templates only
 */
export function getHomepageTemplates(): PageTemplate[] {
    return pageTemplates.filter(t => t.isHomepage);
}

/**
 * Find a template by ID
 */
export function findTemplate(id: string): PageTemplate | undefined {
    return pageTemplates.find(t => t.id === id);
}
