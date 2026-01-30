<?php

namespace Database\Seeders;

use App\Models\GrowBuilder\SiteTemplate;
use App\Models\GrowBuilder\SiteTemplatePage;
use Illuminate\Database\Seeder;

class FlamesOfHopeChurchSeeder extends Seeder
{
    public function run(): void
    {
        $template = SiteTemplate::updateOrCreate(
            ['slug' => 'flames-of-hope-church'],
            [
                'name' => 'Flames of Hope Church',
                'description' => 'Elegant, professional, and modern church template with animated hero slideshow, comprehensive service information, event calendar, ministries showcase, and inspiring testimonials. Perfect for contemporary churches.',
                'industry' => 'church',
                'thumbnail' => 'https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=800&q=80',
                'is_premium' => false,
                'is_active' => true,
                'sort_order' => 9,
                'theme' => [
                    'primaryColor' => '#7c3aed',
                    'secondaryColor' => '#f59e0b',
                    'accentColor' => '#ec4899',
                ],
                'settings' => [
                    'navigation' => [
                        'logoText' => 'Flames of Hope',
                        'sticky' => true,
                        'showCta' => true,
                        'ctaText' => 'Join Us',
                        'ctaLink' => '/contact',
                    ],
                    'footer' => [
                        'copyrightText' => '© 2024 Flames of Hope Church. Igniting Faith, Inspiring Hope.',
                    ],
                ],
            ]
        );

        $template->pages()->delete();

        // Home Page with Animated Hero Slideshow
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Home',
            'slug' => 'home',
            'is_homepage' => true,
            'show_in_nav' => true,
            'sort_order' => 1,
            'content' => ['sections' => [
                // Hero Slideshow - 5 Inspiring Slides
                ['type' => 'hero', 'content' => ['layout' => 'slideshow', 'autoPlay' => true, 'slideInterval' => 7000, 'slides' => [
                    ['title' => 'Welcome to Flames of Hope', 'subtitle' => 'Where faith ignites and hope inspires. Join us in worship every Sunday at 9:00 AM', 'buttonText' => 'Plan Your Visit', 'buttonLink' => '/visit', 'backgroundImage' => 'https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=1920&q=80'],
                    ['title' => 'Experience Transforming Worship', 'subtitle' => 'Encounter God\'s presence through powerful praise and authentic worship', 'buttonText' => 'Our Services', 'buttonLink' => '/services', 'backgroundImage' => 'https://images.unsplash.com/photo-1507692049790-de58290a4334?w=1920&q=80'],
                    ['title' => 'Grow in Community', 'subtitle' => 'Connect with others through life groups, ministries, and fellowship', 'buttonText' => 'Join a Ministry', 'buttonLink' => '/ministries', 'backgroundImage' => 'https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=1920&q=80'],
                    ['title' => 'Discover Your Purpose', 'subtitle' => 'Find your calling and make a difference in the kingdom of God', 'buttonText' => 'Get Involved', 'buttonLink' => '/ministries', 'backgroundImage' => 'https://images.unsplash.com/photo-1529070538774-1843cb3265df?w=1920&q=80'],
                    ['title' => 'Join Us This Sunday', 'subtitle' => 'Experience life-changing messages and uplifting worship. All are welcome!', 'buttonText' => 'Service Times', 'buttonLink' => '/services', 'backgroundImage' => 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=1920&q=80'],
                ]], 'style' => ['minHeight' => 750]],
                
                // Welcome Section
                ['type' => 'about', 'content' => ['layout' => 'image-right', 'title' => 'Welcome Home', 'description' => 'At Flames of Hope Church, we believe that everyone has a place in God\'s family. Whether you\'re exploring faith for the first time or have walked with Jesus for years, you\'ll find a warm, welcoming community here. Our mission is simple: to ignite faith, inspire hope, and impact lives through the transforming power of God\'s love.', 'image' => 'https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=800&q=80', 'features' => ['Bible-Based Teaching', 'Spirit-Led Worship', 'Authentic Community', 'Practical Faith']], 'style' => ['backgroundColor' => '#ffffff']],
                
                // Service Times
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Join Us for Worship', 'subtitle' => 'Experience God\'s presence with us', 'items' => [
                    ['title' => 'Sunday Morning Service', 'description' => '9:00 AM - 11:00 AM | Main Sanctuary | Contemporary worship and powerful preaching'],
                    ['title' => 'Sunday Evening Service', 'description' => '5:00 PM - 6:30 PM | Chapel | Intimate worship and prayer'],
                    ['title' => 'Wednesday Bible Study', 'description' => '6:30 PM - 8:00 PM | Fellowship Hall | Deep dive into God\'s Word'],
                    ['title' => 'Friday Youth Service', 'description' => '7:00 PM - 9:00 PM | Youth Center | Dynamic worship for teens'],
                ]], 'style' => ['backgroundColor' => '#f8fafc']],
                
                // Stats Section
                ['type' => 'stats', 'content' => ['layout' => 'row', 'items' => [
                    ['value' => '2,500+', 'label' => 'Church Family'],
                    ['value' => '25+', 'label' => 'Active Ministries'],
                    ['value' => '15', 'label' => 'Years of Ministry'],
                    ['value' => '100+', 'label' => 'Weekly Life Groups'],
                ]], 'style' => ['backgroundColor' => '#7c3aed', 'textColor' => '#ffffff']],
                
                // Ministries Preview
                ['type' => 'services', 'content' => ['layout' => 'cards-images', 'title' => 'Get Connected', 'subtitle' => 'Find your place in our church family', 'items' => [
                    ['title' => 'Children\'s Ministry', 'description' => 'Fun, age-appropriate programs that teach kids about Jesus in engaging ways', 'icon' => 'users', 'image' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=600&q=80'],
                    ['title' => 'Youth Ministry', 'description' => 'Empowering the next generation through worship, discipleship, and community', 'icon' => 'star', 'image' => 'https://images.unsplash.com/photo-1529070538774-1843cb3265df?w=600&q=80'],
                    ['title' => 'Worship Team', 'description' => 'Leading God\'s people into His presence through anointed music and song', 'icon' => 'sparkles', 'image' => 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=600&q=80'],
                    ['title' => 'Prayer Ministry', 'description' => 'Interceding for our church, community, and world through powerful prayer', 'icon' => 'light-bulb', 'image' => 'https://images.unsplash.com/photo-1507692049790-de58290a4334?w=600&q=80'],
                    ['title' => 'Outreach Ministry', 'description' => 'Sharing God\'s love through community service and evangelism', 'icon' => 'globe', 'image' => 'https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=600&q=80'],
                    ['title' => 'Life Groups', 'description' => 'Small groups meeting throughout the week for fellowship and growth', 'icon' => 'users', 'image' => 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=600&q=80'],
                ]], 'style' => ['backgroundColor' => '#ffffff']],
                
                // Testimonials
                ['type' => 'testimonials', 'content' => ['layout' => 'carousel', 'title' => 'Lives Transformed', 'subtitle' => 'Hear what God is doing in our community', 'items' => [
                    ['name' => 'Sarah Mwanza', 'text' => 'Flames of Hope changed my life. I found not just a church, but a family that loves and supports me. The teaching is powerful and practical.', 'rating' => 5, 'role' => 'Member since 2020'],
                    ['name' => 'John Banda', 'text' => 'The worship here is incredible! You can feel God\'s presence every service. This church has helped me grow deeper in my faith.', 'rating' => 5, 'role' => 'Worship Team Member'],
                    ['name' => 'Grace Phiri', 'text' => 'My children love the kids ministry, and I\'ve found amazing friendships in my life group. This is truly a church that cares.', 'rating' => 5, 'role' => 'Life Group Leader'],
                    ['name' => 'David Tembo', 'text' => 'I came as a visitor and stayed because of the genuine love and acceptance I experienced. Best decision I ever made!', 'rating' => 5, 'role' => 'New Member'],
                ]], 'style' => ['backgroundColor' => '#f8fafc']],
                
                // CTA Section
                ['type' => 'cta', 'content' => ['layout' => 'centered', 'title' => 'Take Your Next Step', 'description' => 'Whether you\'re new to faith or looking for a church home, we\'d love to connect with you. Join us this Sunday!', 'buttonText' => 'Plan Your Visit', 'buttonLink' => '/visit', 'secondaryButtonText' => 'Contact Us', 'secondaryButtonLink' => '/contact'], 'style' => ['backgroundColor' => '#7c3aed']],
            ]],
        ]);

        // About Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'About',
            'slug' => 'about',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 2,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Our Story', 'subtitle' => 'Igniting Faith, Inspiring Hope Since 2009', 'backgroundImage' => 'https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=1920&q=80', 'backgroundColor' => '#7c3aed', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 400]],
                
                ['type' => 'about', 'content' => ['layout' => 'image-left', 'title' => 'Our Mission', 'description' => 'Flames of Hope Church exists to ignite faith in the hearts of believers, inspire hope in those who are searching, and impact our community through the transforming power of God\'s love. We are committed to creating an environment where people can encounter God, grow in their faith, and discover their purpose.', 'image' => 'https://images.unsplash.com/photo-1507692049790-de58290a4334?w=800&q=80', 'features' => ['Worship God Passionately', 'Love People Genuinely', 'Serve Others Selflessly', 'Share Christ Boldly']], 'style' => ['backgroundColor' => '#ffffff']],
                
                ['type' => 'about', 'content' => ['layout' => 'image-right', 'title' => 'Our Vision', 'description' => 'We envision a church where every person discovers their God-given purpose, where families are strengthened, where the lost find hope, and where our community is transformed by the gospel. We believe in raising up leaders, equipping believers, and sending out disciples who will impact the world for Christ.', 'image' => 'https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=800&q=80', 'features' => ['Spirit-Led Ministry', 'Biblical Foundation', 'Cultural Relevance', 'Kingdom Impact']], 'style' => ['backgroundColor' => '#f8fafc']],
                
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Our Core Values', 'subtitle' => 'What we believe and how we live', 'items' => [
                    ['title' => 'Biblical Truth', 'description' => 'We believe the Bible is God\'s inspired Word and the foundation for all we do'],
                    ['title' => 'Authentic Worship', 'description' => 'We pursue genuine encounters with God through Spirit-led worship'],
                    ['title' => 'Loving Community', 'description' => 'We create a family atmosphere where everyone belongs and is valued'],
                    ['title' => 'Generous Living', 'description' => 'We give freely of our time, talents, and resources to advance God\'s kingdom'],
                    ['title' => 'Passionate Prayer', 'description' => 'We depend on God through consistent, fervent prayer'],
                    ['title' => 'Bold Evangelism', 'description' => 'We share the good news of Jesus with courage and compassion'],
                ]], 'style' => ['backgroundColor' => '#ffffff']],
                
                ['type' => 'stats', 'content' => ['layout' => 'row', 'items' => [
                    ['value' => '15', 'label' => 'Years of Ministry'],
                    ['value' => '2,500+', 'label' => 'Church Members'],
                    ['value' => '500+', 'label' => 'Salvations This Year'],
                    ['value' => '25+', 'label' => 'Active Ministries'],
                ]], 'style' => ['backgroundColor' => '#7c3aed', 'textColor' => '#ffffff']],
            ]],
        ]);

        // Services Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Services',
            'slug' => 'services',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 3,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Service Times', 'subtitle' => 'Join Us for Worship', 'backgroundImage' => 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=1920&q=80', 'backgroundColor' => '#7c3aed', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 350]],
                
                ['type' => 'services', 'content' => ['layout' => 'cards-images', 'title' => 'Weekly Services', 'subtitle' => 'Experience God\'s presence with us', 'items' => [
                    ['title' => 'Sunday Morning Celebration', 'description' => '9:00 AM - 11:00 AM | Main Sanctuary | Contemporary worship, powerful preaching, and children\'s programs. Come as you are and experience God\'s love!', 'icon' => 'sparkles', 'image' => 'https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=600&q=80'],
                    ['title' => 'Sunday Evening Service', 'description' => '5:00 PM - 6:30 PM | Chapel | Intimate worship, prayer, and teaching. A perfect way to end your weekend in God\'s presence.', 'icon' => 'star', 'image' => 'https://images.unsplash.com/photo-1507692049790-de58290a4334?w=600&q=80'],
                    ['title' => 'Wednesday Bible Study', 'description' => '6:30 PM - 8:00 PM | Fellowship Hall | Deep dive into God\'s Word with practical application for daily living. All ages welcome.', 'icon' => 'document', 'image' => 'https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=600&q=80'],
                    ['title' => 'Friday Youth Service', 'description' => '7:00 PM - 9:00 PM | Youth Center | High-energy worship and relevant teaching for teens. Games, fellowship, and fun!', 'icon' => 'users', 'image' => 'https://images.unsplash.com/photo-1529070538774-1843cb3265df?w=600&q=80'],
                ]], 'style' => ['backgroundColor' => '#ffffff']],
                
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'What to Expect', 'subtitle' => 'Your first visit made easy', 'items' => [
                    ['title' => 'Warm Welcome', 'description' => 'Our greeters will welcome you and help you find your way'],
                    ['title' => 'Casual Atmosphere', 'description' => 'Come as you are - jeans and t-shirts are perfectly fine!'],
                    ['title' => 'Kids Programs', 'description' => 'Age-appropriate programs for infants through 5th grade'],
                    ['title' => 'Free Coffee', 'description' => 'Enjoy complimentary coffee and refreshments before service'],
                    ['title' => 'Ample Parking', 'description' => 'Free parking available with designated visitor spots'],
                    ['title' => 'No Pressure', 'description' => 'Feel free to observe - we won\'t put you on the spot!'],
                ]], 'style' => ['backgroundColor' => '#f8fafc']],
            ]],
        ]);

        // Ministries Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Ministries',
            'slug' => 'ministries',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 4,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Get Involved', 'subtitle' => 'Find Your Place to Serve', 'backgroundImage' => 'https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=1920&q=80', 'backgroundColor' => '#7c3aed', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 350]],
                
                ['type' => 'services', 'content' => ['layout' => 'grid', 'title' => 'Ministry Opportunities', 'subtitle' => 'Discover where you can make a difference', 'columns' => 3, 'items' => [
                    ['title' => 'Worship Ministry', 'description' => 'Lead others into God\'s presence through music and song', 'icon' => 'sparkles'],
                    ['title' => 'Children\'s Ministry', 'description' => 'Shape young hearts and minds for Jesus', 'icon' => 'users'],
                    ['title' => 'Youth Ministry', 'description' => 'Mentor and disciple the next generation', 'icon' => 'star'],
                    ['title' => 'Prayer Team', 'description' => 'Intercede for our church and community', 'icon' => 'light-bulb'],
                    ['title' => 'Hospitality Team', 'description' => 'Welcome guests and create a warm atmosphere', 'icon' => 'users'],
                    ['title' => 'Media Team', 'description' => 'Use technology to enhance worship experiences', 'icon' => 'film'],
                    ['title' => 'Outreach Ministry', 'description' => 'Share God\'s love in our community', 'icon' => 'globe'],
                    ['title' => 'Life Groups', 'description' => 'Lead small groups for fellowship and growth', 'icon' => 'users'],
                    ['title' => 'Counseling Ministry', 'description' => 'Provide biblical guidance and support', 'icon' => 'light-bulb'],
                ]], 'style' => ['backgroundColor' => '#ffffff']],
                
                ['type' => 'cta', 'content' => ['layout' => 'banner', 'title' => 'Ready to Serve?', 'description' => 'God has given you unique gifts and talents. Let\'s discover how you can use them to impact lives and advance His kingdom.', 'buttonText' => 'Get Connected', 'buttonLink' => '/contact'], 'style' => ['backgroundColor' => '#f59e0b']],
            ]],
        ]);

        // Visit Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Plan Your Visit',
            'slug' => 'visit',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 5,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'We Can\'t Wait to Meet You', 'subtitle' => 'Everything You Need to Know for Your First Visit', 'backgroundColor' => '#7c3aed', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 300]],
                
                ['type' => 'features', 'content' => ['layout' => 'steps', 'title' => 'Your First Visit', 'subtitle' => 'Here\'s what to expect', 'items' => [
                    ['title' => '1. Arrive Early', 'description' => 'Come 15 minutes before service to find parking, grab coffee, and get settled'],
                    ['title' => '2. Check In Kids', 'description' => 'Visit our children\'s check-in area for safe, fun programs for your little ones'],
                    ['title' => '3. Find a Seat', 'description' => 'Sit anywhere you like! Our ushers can help you find a great spot'],
                    ['title' => '4. Enjoy the Service', 'description' => 'Experience uplifting worship and a relevant, biblical message'],
                    ['title' => '5. Connect After', 'description' => 'Stick around for coffee and meet some of our friendly members'],
                ]], 'style' => ['backgroundColor' => '#ffffff']],
                
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Frequently Asked Questions', 'subtitle' => 'We\'ve got answers', 'items' => [
                    ['title' => 'What should I wear?', 'description' => 'Come as you are! We have people in jeans and people in suits. Wear what\'s comfortable.'],
                    ['title' => 'Where do I park?', 'description' => 'We have free parking with designated visitor spots near the main entrance.'],
                    ['title' => 'How long is the service?', 'description' => 'Our Sunday morning service is about 90 minutes, including worship and message.'],
                    ['title' => 'Is there childcare?', 'description' => 'Yes! We have programs for infants through 5th grade during all services.'],
                    ['title' => 'Will I be singled out?', 'description' => 'Never! While we love meeting new people, we won\'t put you on the spot.'],
                    ['title' => 'What about giving?', 'description' => 'Giving is for members. As a guest, just enjoy the service - no pressure!'],
                ]], 'style' => ['backgroundColor' => '#f8fafc']],
            ]],
        ]);

        // Contact Page
        SiteTemplatePage::create([
            'site_template_id' => $template->id,
            'title' => 'Contact',
            'slug' => 'contact',
            'is_homepage' => false,
            'show_in_nav' => true,
            'sort_order' => 6,
            'content' => ['sections' => [
                ['type' => 'page-header', 'content' => ['title' => 'Get In Touch', 'subtitle' => 'We\'d Love to Hear From You', 'backgroundColor' => '#7c3aed', 'textColor' => '#ffffff'], 'style' => ['minHeight' => 300]],
                ['type' => 'contact', 'content' => ['layout' => 'side-by-side', 'title' => 'Connect With Us', 'description' => 'Have questions? Need prayer? Want to get involved? Fill out the form and we\'ll get back to you within 24 hours.', 'showForm' => true, 'email' => 'info@flamesofhope.org', 'phone' => '+260 97 123 4567', 'address' => 'Plot 456, Church Road, Lusaka, Zambia', 'hours' => 'Office Hours: Mon-Fri 9:00 AM - 5:00 PM'], 'style' => ['backgroundColor' => '#ffffff']],
                
                ['type' => 'features', 'content' => ['layout' => 'grid', 'title' => 'Other Ways to Connect', 'subtitle' => 'Stay in touch with our church family', 'items' => [
                    ['title' => 'Join Our Newsletter', 'description' => 'Get weekly updates, event announcements, and encouraging devotionals'],
                    ['title' => 'Follow on Social Media', 'description' => 'Stay connected through Facebook, Instagram, and YouTube'],
                    ['title' => 'Prayer Requests', 'description' => 'Submit prayer requests and our team will pray for you'],
                    ['title' => 'Schedule a Meeting', 'description' => 'Meet with our pastoral team for guidance and support'],
                ]], 'style' => ['backgroundColor' => '#f8fafc']],
            ]],
        ]);
    }
}
