/**
 * Professional Copy Library
 * Industry-specific, benefit-driven copy for templates
 */

export interface CopySet {
    hero: {
        title: string;
        subtitle: string;
        cta: string;
    };
    about: {
        title: string;
        description: string;
    };
    services: {
        title: string;
        subtitle: string;
    };
    testimonial: {
        quote: string;
        author: string;
        role: string;
    };
    cta: {
        title: string;
        subtitle: string;
        button: string;
    };
    stats: Array<{
        number: string;
        suffix: string;
        label: string;
    }>;
}

/**
 * Professional copy by industry
 */
export const industryCopy: Record<string, CopySet> = {
    consulting: {
        hero: {
            title: 'Transform Your Business with Expert Consulting',
            subtitle: 'Strategic guidance that drives measurable results. Join 500+ companies that increased revenue by 40% in 12 months.',
            cta: 'Book Your Free Strategy Session',
        },
        about: {
            title: 'Your Partner in Business Excellence',
            description: 'With over 15 years of experience, we help businesses navigate complex challenges and unlock their full potential. Our proven methodologies have transformed organizations across industries.',
        },
        services: {
            title: 'Comprehensive Business Solutions',
            subtitle: 'From strategy to execution, we deliver results that matter',
        },
        testimonial: {
            quote: 'Their strategic insights helped us increase efficiency by 60% and cut costs by K2M annually. Best investment we ever made.',
            author: 'James Mwansa',
            role: 'CEO, TechCorp Zambia',
        },
        cta: {
            title: 'Ready to Transform Your Business?',
            subtitle: 'Schedule a complimentary consultation and discover how we can help you achieve your goals',
            button: 'Get Started Today',
        },
        stats: [
            { number: '500', suffix: '+', label: 'Clients Served' },
            { number: '40', suffix: '%', label: 'Average Revenue Growth' },
            { number: '15', suffix: '+', label: 'Years Experience' },
            { number: '98', suffix: '%', label: 'Client Satisfaction' },
        ],
    },
    
    restaurant: {
        hero: {
            title: 'Experience Authentic Zambian Cuisine',
            subtitle: 'Fresh ingredients, traditional recipes, and unforgettable flavors. Dine with us and taste the difference.',
            cta: 'Reserve Your Table',
        },
        about: {
            title: 'A Culinary Journey Through Zambia',
            description: 'For over 10 years, we\'ve been serving authentic Zambian dishes made with love and the freshest local ingredients. Every meal tells a story of our rich culinary heritage.',
        },
        services: {
            title: 'Our Menu Highlights',
            subtitle: 'From traditional favorites to modern interpretations',
        },
        testimonial: {
            quote: 'The best nshima and relish in Lusaka! The atmosphere is warm, the service is excellent, and the food is absolutely delicious.',
            author: 'Grace Banda',
            role: 'Food Blogger',
        },
        cta: {
            title: 'Join Us for an Unforgettable Meal',
            subtitle: 'Book your table now and experience the finest Zambian cuisine',
            button: 'Make a Reservation',
        },
        stats: [
            { number: '10', suffix: '+', label: 'Years Serving' },
            { number: '50', suffix: '+', label: 'Menu Items' },
            { number: '1000', suffix: '+', label: 'Happy Customers' },
            { number: '4.9', suffix: '/5', label: 'Average Rating' },
        ],
    },
    
    tech: {
        hero: {
            title: 'Build the Future with Cutting-Edge Technology',
            subtitle: 'Innovative software solutions that scale with your business. Trusted by leading companies across Africa.',
            cta: 'Start Your Free Trial',
        },
        about: {
            title: 'Innovation Meets Excellence',
            description: 'We\'re a team of passionate developers and designers creating software that solves real problems. Our mission is to empower businesses with technology that works.',
        },
        services: {
            title: 'Our Solutions',
            subtitle: 'Powerful tools designed for modern businesses',
        },
        testimonial: {
            quote: 'Their platform reduced our operational costs by 50% and improved customer satisfaction dramatically. Game-changing technology.',
            author: 'Peter Phiri',
            role: 'CTO, FinTech Solutions',
        },
        cta: {
            title: 'Ready to Scale Your Business?',
            subtitle: 'Join thousands of companies using our platform to grow faster',
            button: 'Get Started Free',
        },
        stats: [
            { number: '10K', suffix: '+', label: 'Active Users' },
            { number: '99.9', suffix: '%', label: 'Uptime' },
            { number: '50', suffix: '%', label: 'Cost Reduction' },
            { number: '24/7', suffix: '', label: 'Support' },
        ],
    },
    
    fitness: {
        hero: {
            title: 'Transform Your Body, Transform Your Life',
            subtitle: 'Join Zambia\'s premier fitness center. Expert trainers, modern equipment, and a community that motivates.',
            cta: 'Start Your Fitness Journey',
        },
        about: {
            title: 'Your Fitness Goals, Our Mission',
            description: 'We believe everyone deserves to feel strong, healthy, and confident. Our certified trainers and state-of-the-art facility provide everything you need to succeed.',
        },
        services: {
            title: 'Membership Options',
            subtitle: 'Flexible plans designed for your lifestyle',
        },
        testimonial: {
            quote: 'Lost 20kg in 6 months! The trainers are amazing, the equipment is top-notch, and the community keeps me motivated every day.',
            author: 'Sarah Mulenga',
            role: 'Member since 2022',
        },
        cta: {
            title: 'Start Your Transformation Today',
            subtitle: 'First week free for new members. No commitment required.',
            button: 'Claim Your Free Week',
        },
        stats: [
            { number: '2000', suffix: '+', label: 'Active Members' },
            { number: '15', suffix: '', label: 'Expert Trainers' },
            { number: '50', suffix: '+', label: 'Classes Weekly' },
            { number: '5', suffix: '', label: 'Star Rating' },
        ],
    },
    
    legal: {
        hero: {
            title: 'Expert Legal Counsel You Can Trust',
            subtitle: 'Protecting your rights and interests with integrity, expertise, and dedication. Serving Zambia for over 20 years.',
            cta: 'Schedule a Consultation',
        },
        about: {
            title: 'Committed to Justice and Excellence',
            description: 'Our experienced legal team provides comprehensive services across corporate law, litigation, and advisory. We combine deep legal knowledge with practical business understanding.',
        },
        services: {
            title: 'Practice Areas',
            subtitle: 'Comprehensive legal services for individuals and businesses',
        },
        testimonial: {
            quote: 'Professional, knowledgeable, and always available. They handled our complex case with expertise and achieved an excellent outcome.',
            author: 'David Tembo',
            role: 'Business Owner',
        },
        cta: {
            title: 'Need Legal Assistance?',
            subtitle: 'Contact us today for a confidential consultation',
            button: 'Contact Us Now',
        },
        stats: [
            { number: '20', suffix: '+', label: 'Years Experience' },
            { number: '500', suffix: '+', label: 'Cases Won' },
            { number: '10', suffix: '', label: 'Expert Lawyers' },
            { number: '100', suffix: '%', label: 'Confidential' },
        ],
    },
    
    ecommerce: {
        hero: {
            title: 'Shop Quality Products at Unbeatable Prices',
            subtitle: 'Fast delivery across Zambia. Secure payment. 30-day money-back guarantee.',
            cta: 'Shop Now',
        },
        about: {
            title: 'Your Trusted Online Store',
            description: 'We curate the best products from around the world and deliver them right to your door. Quality guaranteed, prices you\'ll love.',
        },
        services: {
            title: 'Why Shop With Us',
            subtitle: 'The benefits that set us apart',
        },
        testimonial: {
            quote: 'Fast delivery, great prices, and excellent customer service. I order everything from here now!',
            author: 'Mary Lungu',
            role: 'Verified Customer',
        },
        cta: {
            title: 'Ready to Start Shopping?',
            subtitle: 'Browse thousands of products with free delivery on orders over K500',
            button: 'Browse Products',
        },
        stats: [
            { number: '10K', suffix: '+', label: 'Products' },
            { number: '50K', suffix: '+', label: 'Happy Customers' },
            { number: '24', suffix: 'hrs', label: 'Fast Delivery' },
            { number: '4.8', suffix: '/5', label: 'Customer Rating' },
        ],
    },
};

/**
 * Get copy for an industry
 */
export function getIndustryCopy(industry: string): CopySet {
    return industryCopy[industry] || industryCopy.consulting;
}

/**
 * Generic professional copy (fallback)
 */
export const genericCopy: CopySet = {
    hero: {
        title: 'Welcome to Excellence',
        subtitle: 'Delivering quality services that exceed expectations. Your success is our mission.',
        cta: 'Get Started Today',
    },
    about: {
        title: 'About Our Company',
        description: 'We are dedicated to providing exceptional service and building lasting relationships with our clients. Our team of experts brings years of experience and a commitment to excellence.',
    },
    services: {
        title: 'Our Services',
        subtitle: 'Comprehensive solutions tailored to your needs',
    },
    testimonial: {
        quote: 'Outstanding service and exceptional results. Highly recommended!',
        author: 'John Doe',
        role: 'Satisfied Client',
    },
    cta: {
        title: 'Ready to Get Started?',
        subtitle: 'Contact us today and discover how we can help you succeed',
        button: 'Contact Us',
    },
    stats: [
        { number: '100', suffix: '+', label: 'Happy Clients' },
        { number: '10', suffix: '+', label: 'Years Experience' },
        { number: '500', suffix: '+', label: 'Projects Completed' },
        { number: '99', suffix: '%', label: 'Satisfaction Rate' },
    ],
};
