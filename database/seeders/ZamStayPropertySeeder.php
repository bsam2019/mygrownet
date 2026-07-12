<?php

namespace Database\Seeders;

use App\Models\ZamStay\ZamStayProperty;
use App\Models\User;
use Illuminate\Database\Seeder;

class ZamStayPropertySeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::first() ?? User::factory()->create(['name' => 'ZamStay Host']);

        $properties = [
            // Lusaka Province
            [
                'owner_id' => $owner->id, 'title' => 'Civic Center Lodge', 'description' => 'Modern lodge in the heart of Lusaka\'s central business district. Walking distance to shopping malls, restaurants, and nightlife. Features a swimming pool, conference facilities, and 24-hour security. Ideal for both business and leisure travelers seeking comfort and convenience.',
                'location' => 'Lusaka', 'price_per_night' => 450, 'property_type' => 'lodge', 'max_guests' => 4, 'bedrooms' => 2, 'bathrooms' => 1,
                'amenities' => ['Free WiFi', 'Swimming Pool', 'Parking', 'Restaurant', 'Room Service', 'Air Conditioning', 'TV', 'Conference Room'],
                'images' => ['https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800'],
            ],
            [
                'owner_id' => $owner->id, 'title' => 'Manda Hill Guest House', 'description' => 'Elegant guest house located in the prestigious Kabulonga area. Quiet neighborhood with beautiful gardens. Close to Manda Hill Shopping Centre and American Embassy. Each room is en-suite with premium bedding.',
                'location' => 'Lusaka', 'price_per_night' => 350, 'property_type' => 'guest_house', 'max_guests' => 3, 'bedrooms' => 1, 'bathrooms' => 1,
                'amenities' => ['Free WiFi', 'Parking', 'Breakfast Included', 'Garden', 'Laundry Service', 'DStv', 'Air Conditioning'],
                'images' => ['https://images.unsplash.com/photo-1582719508461-905c673771fd?w=800'],
            ],
            [
                'owner_id' => $owner->id, 'title' => 'Kafue River Hotel', 'description' => 'Full-service hotel overlooking the Kafue River. Perfect for conferences and weekend getaways. On-site restaurant serves Zambian and international cuisine. Beautiful river views from every room.',
                'location' => 'Kafue', 'price_per_night' => 650, 'property_type' => 'hotel', 'max_guests' => 5, 'bedrooms' => 2, 'bathrooms' => 2,
                'amenities' => ['Free WiFi', 'Swimming Pool', 'Parking', 'Restaurant', 'Bar', 'Room Service', 'Air Conditioning', 'River View', 'Conference Room', 'Gym'],
                'images' => ['https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=800'],
            ],
            [
                'owner_id' => $owner->id, 'title' => 'Olympia Home Stay', 'description' => 'Warm and welcoming home stay in a quiet Lusaka suburb. Experience true Zambian hospitality with a family that treats you like one of their own. Home-cooked meals included. Perfect for solo travelers and cultural immersion.',
                'location' => 'Lusaka', 'price_per_night' => 180, 'property_type' => 'home_stay', 'max_guests' => 2, 'bedrooms' => 1, 'bathrooms' => 1,
                'amenities' => ['Free WiFi', 'Breakfast Included', 'Dinner Included', 'Laundry', 'Garden'],
                'images' => ['https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800'],
            ],

            // Copperbelt Province
            [
                'owner_id' => $owner->id, 'title' => 'Ndola Executive Lodge', 'description' => 'Premium lodge in Ndola\'s exclusive area. Features well-appointed suites with modern amenities. Popular with business executives and diplomats. Excellent restaurant and bar on premises.',
                'location' => 'Ndola', 'price_per_night' => 500, 'property_type' => 'lodge', 'max_guests' => 4, 'bedrooms' => 2, 'bathrooms' => 1,
                'amenities' => ['Free WiFi', 'Parking', 'Restaurant', 'Bar', 'Air Conditioning', 'DStv', 'Room Service', 'Generator Backup'],
                'images' => ['https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800'],
            ],
            [
                'owner_id' => $owner->id, 'title' => 'Kitwe City Hotel', 'description' => 'Centrally located hotel in Kitwe\'s business district. Modern rooms with city views. Rooftop terrace restaurant, gym, and conference facilities. The preferred choice for corporate travelers on the Copperbelt.',
                'location' => 'Kitwe', 'price_per_night' => 550, 'property_type' => 'hotel', 'max_guests' => 4, 'bedrooms' => 2, 'bathrooms' => 1,
                'amenities' => ['Free WiFi', 'Parking', 'Restaurant', 'Bar', 'Gym', 'Air Conditioning', 'Conference Room', 'Room Service'],
                'images' => ['https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=800'],
            ],

            // Southern Province (Livingstone - Victoria Falls)
            [
                'owner_id' => $owner->id, 'title' => 'Victoria Falls Waterfront Lodge', 'description' => 'Stunning lodge overlooking the Zambezi River just 5km from Victoria Falls. Thatched-roof chalets with river views. Watch the sunset over the river from our deck. Activities include game drives, boat cruises, and bungee jumping.',
                'location' => 'Livingstone', 'price_per_night' => 1200, 'property_type' => 'lodge', 'max_guests' => 6, 'bedrooms' => 3, 'bathrooms' => 2,
                'amenities' => ['Free WiFi', 'Swimming Pool', 'Parking', 'Restaurant', 'Bar', 'Air Conditioning', 'River View', 'Game Drives', 'Airport Shuttle', 'Laundry'],
                'images' => ['https://images.unsplash.com/photo-1590490360182-c33d57733427?w=800'],
            ],
            [
                'owner_id' => $owner->id, 'title' => 'Mosi-oa-Tunya Guest Lodge', 'description' => 'Affordable and comfortable guest lodge in Livingstone town. Walking distance to shops and restaurants. We arrange Victoria Falls tours, sunset cruises, and safari adventures. Clean rooms with friendly staff.',
                'location' => 'Livingstone', 'price_per_night' => 400, 'property_type' => 'guest_house', 'max_guests' => 4, 'bedrooms' => 2, 'bathrooms' => 1,
                'amenities' => ['Free WiFi', 'Parking', 'Breakfast Included', 'Swimming Pool', 'Tour Desk', 'Air Conditioning', 'Garden'],
                'images' => ['https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=800'],
            ],
            [
                'owner_id' => $owner->id, 'title' => 'Choma Farm Stay', 'description' => 'Working farm experience in the heart of Zambia\'s cattle country. Stay in a traditional farmhouse and experience rural life. Fresh milk, free-range eggs, and organic vegetables from the farm. Great for families and nature lovers.',
                'location' => 'Choma', 'price_per_night' => 250, 'property_type' => 'home_stay', 'max_guests' => 6, 'bedrooms' => 3, 'bathrooms' => 1,
                'amenities' => ['Parking', 'Farm Tours', 'Home-cooked Meals', 'Garden', 'Fire Pit', 'Kids Play Area'],
                'images' => ['https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=800'],
            ],

            // Eastern Province
            [
                'owner_id' => $owner->id, 'title' => 'Chipata Hills Hotel', 'description' => 'Hilltop hotel with panoramic views of Chipata town and the surrounding valleys. Known for its Sunday brunch and outdoor terrace. Gateway to South Luangwa National Park. Peaceful and scenic.',
                'location' => 'Chipata', 'price_per_night' => 480, 'property_type' => 'hotel', 'max_guests' => 4, 'bedrooms' => 2, 'bathrooms' => 1,
                'amenities' => ['Free WiFi', 'Parking', 'Restaurant', 'Bar', 'Terrace', 'Air Conditioning', 'Garden', 'Mountain View'],
                'images' => ['https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800'],
            ],
            [
                'owner_id' => $owner->id, 'title' => 'South Luangwa Bush Camp', 'description' => 'Eco-friendly safari camp inside South Luangwa National Park. Solar-powered luxury tents with en-suite bathrooms. Fall asleep to the sounds of the African bush. Game drives with expert guides included.',
                'location' => 'Mfuwe', 'price_per_night' => 2500, 'property_type' => 'lodge', 'max_guests' => 4, 'bedrooms' => 2, 'bathrooms' => 2,
                'amenities' => ['Swimming Pool', 'Restaurant', 'Bar', 'Game Drives', 'Guided Walks', 'Laundry', 'Sundowner Deck', 'All Meals Included'],
                'images' => ['https://images.unsplash.com/photo-1602002418082-a4443e081dd1?w=800'],
            ],

            // Northern Province
            [
                'owner_id' => $owner->id, 'title' => 'Kasama Lake View Lodge', 'description' => 'Beautiful lodge on the shores of Lake Tanganyika. Stunning sunsets and pristine beaches. Perfect for a relaxing getaway. Kayaking, fishing, and boat trips available. Fresh fish straight from the lake.',
                'location' => 'Kasama', 'price_per_night' => 600, 'property_type' => 'lodge', 'max_guests' => 4, 'bedrooms' => 2, 'bathrooms' => 1,
                'amenities' => ['Free WiFi', 'Parking', 'Restaurant', 'Bar', 'Lake View', 'Kayaking', 'Fishing', 'Boat Trips', 'Generator Backup'],
                'images' => ['https://images.unsplash.com/photo-1540541338287-41700207dee6?w=800'],
            ],

            // Western Province
            [
                'owner_id' => $owner->id, 'title' => 'Mongu Zambezi River Lodge', 'description' => 'Experience the beauty of the Upper Zambezi from our riverside lodge in Mongu. Traditional architecture with modern comforts. Visit the Kuomboka ceremony if you time it right. Peaceful and off the beaten path.',
                'location' => 'Mongu', 'price_per_night' => 380, 'property_type' => 'lodge', 'max_guests' => 4, 'bedrooms' => 2, 'bathrooms' => 1,
                'amenities' => ['Free WiFi', 'Parking', 'Restaurant', 'Bar', 'River View', 'Boat Trips', 'Garden', 'Generator Backup'],
                'images' => ['https://images.unsplash.com/photo-1545156521-77bd85671d30?w=800'],
            ],
            [
                'owner_id' => $owner->id, 'title' => 'Senanga Home Stay', 'description' => 'Experience authentic Lozi culture with a family in Senanga. Learn traditional cooking, visit the local market, and explore the Barotse Floodplain by canoe. Simple but comfortable accommodation with genuine hospitality.',
                'location' => 'Senanga', 'price_per_night' => 150, 'property_type' => 'home_stay', 'max_guests' => 2, 'bedrooms' => 1, 'bathrooms' => 1,
                'amenities' => ['Home-cooked Meals', 'Cultural Tours', 'Canoeing', 'Garden'],
                'images' => ['https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800'],
            ],

            // Muchinga Province
            [
                'owner_id' => $owner->id, 'title' => 'Shiwa Ng\'andu Manor', 'description' => 'Stay at the historic Shiwa Ng\'andu Estate, a grand English manor built in the heart of Muchinga in the 1920s by Sir Stewart Gore-Browne. Explore the estate, gardens, and nearby waterfalls. A truly unique Zambian experience.',
                'location' => 'Mpika', 'price_per_night' => 1800, 'property_type' => 'hotel', 'max_guests' => 6, 'bedrooms' => 3, 'bathrooms' => 3,
                'amenities' => ['Free WiFi', 'Parking', 'Restaurant', 'Bar', 'Garden', 'Estate Tours', 'Horse Riding', 'Bird Watching', 'Library', 'Fireplace'],
                'images' => ['https://images.unsplash.com/photo-1564501049412-61c2a3083791?w=800'],
            ],
            [
                'owner_id' => $owner->id, 'title' => 'Nakonde Transit Lodge', 'description' => 'Convenient lodge near the Nakonde border post. Clean rooms, hot showers, and secure parking. Popular with cross-border traders and travelers. Simple but comfortable after a long journey.',
                'location' => 'Nakonde', 'price_per_night' => 200, 'property_type' => 'guest_house', 'max_guests' => 2, 'bedrooms' => 1, 'bathrooms' => 1,
                'amenities' => ['Free WiFi', 'Parking', 'Restaurant', '24hr Security', 'Generator Backup'],
                'images' => ['https://images.unsplash.com/photo-1554995207-c18c203602cb?w=800'],
            ],

            // Luapula Province
            [
                'owner_id' => $owner->id, 'title' => 'Mansa Lake Lodge', 'description' => 'Serene lodge on the banks of Lake Bangweulu. Known for its incredible birdlife and the black lechwe antelope. Boat trips to the Bangweulu swamps. Unwind in nature with no distractions.',
                'location' => 'Mansa', 'price_per_night' => 450, 'property_type' => 'lodge', 'max_guests' => 4, 'bedrooms' => 2, 'bathrooms' => 1,
                'amenities' => ['Parking', 'Restaurant', 'Bar', 'Lake View', 'Boat Trips', 'Bird Watching', 'Fishing', 'Generator Backup'],
                'images' => ['https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?w=800'],
            ],

            // North-Western Province
            [
                'owner_id' => $owner->id, 'title' => 'Solwezi Executive Lodge', 'description' => 'Modern lodge catering to mining executives and travelers in Solwezi. Quiet setting with beautiful gardens. Fully equipped gym and steam room. The best accommodation in the North-Western Province.',
                'location' => 'Solwezi', 'price_per_night' => 550, 'property_type' => 'lodge', 'max_guests' => 3, 'bedrooms' => 1, 'bathrooms' => 1,
                'amenities' => ['Free WiFi', 'Parking', 'Restaurant', 'Bar', 'Gym', 'Steam Room', 'Air Conditioning', 'DStv', 'Laundry'],
                'images' => ['https://images.unsplash.com/photo-1590073242678-70ee3fc28f8e?w=800'],
            ],

            // Central Province
            [
                'owner_id' => $owner->id, 'title' => 'Kabwe City Guest House', 'description' => 'Well-maintained guest house in Kabwe town center. Ideal stopover between Lusaka and the Copperbelt. Clean rooms, reliable WiFi, and friendly service. Restaurant serves both local and international dishes.',
                'location' => 'Kabwe', 'price_per_night' => 280, 'property_type' => 'guest_house', 'max_guests' => 3, 'bedrooms' => 1, 'bathrooms' => 1,
                'amenities' => ['Free WiFi', 'Parking', 'Restaurant', 'Air Conditioning', 'DStv', '24hr Security'],
                'images' => ['https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800'],
            ],
            [
                'owner_id' => $owner->id, 'title' => 'Serenje Sunset Lodge', 'description' => 'Hilltop lodge with breathtaking views of the Muchinga Escarpment. Perfect stopover on the Great North Road. Quiet, peaceful, and surrounded by nature. Home-cooked meals using local ingredients.',
                'location' => 'Serenje', 'price_per_night' => 320, 'property_type' => 'lodge', 'max_guests' => 4, 'bedrooms' => 2, 'bathrooms' => 1,
                'amenities' => ['Free WiFi', 'Parking', 'Restaurant', 'Bar', 'Scenic View', 'Garden', 'Campfire', 'Bird Watching'],
                'images' => ['https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?w=800'],
            ],
        ];

        foreach ($properties as $data) {
            ZamStayProperty::create($data + ['is_active' => true, 'status' => 'available']);
        }

        $this->command->info('Created ' . count($properties) . ' ZamStay properties across Zambia.');
    }
}
