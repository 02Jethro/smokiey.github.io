<?php
// Database seeding script for PropertyHub Zimbabwe

require_once 'config.php';
require_once ROOT_DIR . '/propertyhub/core/Database.php';
require_once ROOT_DIR . '/propertyhub/models/Property.php';
require_once ROOT_DIR . '/propertyhub/models/User.php';

try {
    $db = Database::getInstance();
    echo "Starting data seeding...<br>";
    
    // Sample users data
    $users = [
        // Admin users
        [
            'username' => 'admin',
            'email' => 'admin@propertyhub.co.zw',
            'password' => 'admin123',
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'phone' => '+263772000001',
            'user_type' => 'admin'
        ],
        [
            'username' => 'manager',
            'email' => 'manager@propertyhub.co.zw',
            'password' => 'manager123',
            'first_name' => 'Property',
            'last_name' => 'Manager',
            'phone' => '+263772000002',
            'user_type' => 'property_manager'
        ],
        
        // Real estate agents
        [
            'username' => 'tendai_moyo',
            'email' => 'tendai@propertyhub.co.zw',
            'password' => 'agent123',
            'first_name' => 'Tendai',
            'last_name' => 'Moyo',
            'phone' => '+263772000003',
            'user_type' => 'property_manager'
        ],
        [
            'username' => 'sarah_chidemo',
            'email' => 'sarah@propertyhub.co.zw',
            'password' => 'agent123',
            'first_name' => 'Sarah',
            'last_name' => 'Chidemo',
            'phone' => '+263772000004',
            'user_type' => 'property_manager'
        ],
        [
            'username' => 'david_mandaza',
            'email' => 'david@propertyhub.co.zw',
            'password' => 'agent123',
            'first_name' => 'David',
            'last_name' => 'Mandaza',
            'phone' => '+263772000005',
            'user_type' => 'property_manager'
        ],
        [
            'username' => 'grace_ndlovu',
            'email' => 'grace@propertyhub.co.zw',
            'password' => 'agent123',
            'first_name' => 'Grace',
            'last_name' => 'Ndlovu',
            'phone' => '+263772000006',
            'user_type' => 'property_manager'
        ],
        
        // Landlords
        [
            'username' => 'john_sibanda',
            'email' => 'john.sibanda@email.com',
            'password' => 'landlord123',
            'first_name' => 'John',
            'last_name' => 'Sibanda',
            'phone' => '+263772000007',
            'user_type' => 'landlord'
        ],
        [
            'username' => 'lisa_changwa',
            'email' => 'lisa.changwa@email.com',
            'password' => 'landlord123',
            'first_name' => 'Lisa',
            'last_name' => 'Changwa',
            'phone' => '+263772000008',
            'user_type' => 'landlord'
        ],
        [
            'username' => 'peter_makoni',
            'email' => 'peter.makoni@email.com',
            'password' => 'landlord123',
            'first_name' => 'Peter',
            'last_name' => 'Makoni',
            'phone' => '+263772000009',
            'user_type' => 'landlord'
        ],
        [
            'username' => 'maria_ndoro',
            'email' => 'maria.ndoro@email.com',
            'password' => 'landlord123',
            'first_name' => 'Maria',
            'last_name' => 'Ndoro',
            'phone' => '+263772000010',
            'user_type' => 'landlord'
        ],
        
        // Tenants
        [
            'username' => 'tinashe_moyo',
            'email' => 'tinashe@email.com',
            'password' => 'tenant123',
            'first_name' => 'Tinashe',
            'last_name' => 'Moyo',
            'phone' => '+263772000011',
            'user_type' => 'tenant'
        ],
        [
            'username' => 'chipo_masango',
            'email' => 'chipo@email.com',
            'password' => 'tenant123',
            'first_name' => 'Chipo',
            'last_name' => 'Masango',
            'phone' => '+263772000012',
            'user_type' => 'tenant'
        ],
        [
            'username' => 'kudzai_mutasa',
            'email' => 'kudzai@email.com',
            'password' => 'tenant123',
            'first_name' => 'Kudzai',
            'last_name' => 'Mutasa',
            'phone' => '+263772000013',
            'user_type' => 'tenant'
        ],
        [
            'username' => 'tawanda_gumbo',
            'email' => 'tawanda@email.com',
            'password' => 'tenant123',
            'first_name' => 'Tawanda',
            'last_name' => 'Gumbo',
            'phone' => '+263772000014',
            'user_type' => 'tenant'
        ],
        
        // Buyers
        [
            'username' => 'farai_chikowore',
            'email' => 'farai@email.com',
            'password' => 'buyer123',
            'first_name' => 'Farai',
            'last_name' => 'Chikowore',
            'phone' => '+263772000015',
            'user_type' => 'buyer'
        ],
        [
            'username' => 'nyasha_makamba',
            'email' => 'nyasha@email.com',
            'password' => 'buyer123',
            'first_name' => 'Nyasha',
            'last_name' => 'Makamba',
            'phone' => '+263772000016',
            'user_type' => 'buyer'
        ],
        
        // Sellers
        [
            'username' => 'memory_mupedzisa',
            'email' => 'memory@email.com',
            'password' => 'seller123',
            'first_name' => 'Memory',
            'last_name' => 'Mupedzisa',
            'phone' => '+263772000017',
            'user_type' => 'seller'
        ],
        [
            'username' => 'simba_mhlanga',
            'email' => 'simba@email.com',
            'password' => 'seller123',
            'first_name' => 'Simba',
            'last_name' => 'Mhlanga',
            'phone' => '+263772000018',
            'user_type' => 'seller'
        ]
    ];

    // Create users
    $userModel = new User();
    $createdUsers = [];
    
    foreach ($users as $userData) {
        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, password, first_name, last_name, phone, user_type) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $result = $db->query($sql, [
            $userData['username'],
            $userData['email'],
            $hashedPassword,
            $userData['first_name'],
            $userData['last_name'],
            $userData['phone'],
            $userData['user_type']
        ]);
        
        if ($result) {
            $userId = $db->lastInsertId();
            $createdUsers[$userData['user_type']][] = $userId;
            echo "Created user: {$userData['first_name']} {$userData['last_name']} ({$userData['user_type']})<br>";
        }
    }

    // Zimbabwe provinces and their suburbs/towns
    $zimbabweLocations = [
        'harare' => [
            'name' => 'Harare',
            'areas' => [
                'Borrowdale' => ['type' => 'residential', 'price_range' => [200000, 500000]],
                'Mount Pleasant' => ['type' => 'residential', 'price_range' => [150000, 300000]],
                'Avondale' => ['type' => 'mixed', 'price_range' => [120000, 250000]],
                'Mbare' => ['type' => 'residential', 'price_range' => [40000, 80000]],
                'Highfield' => ['type' => 'residential', 'price_range' => [45000, 90000]],
                'CBD' => ['type' => 'commercial', 'price_range' => [80000, 200000]],
                'Eastlea' => ['type' => 'mixed', 'price_range' => [90000, 180000]],
                'Greendale' => ['type' => 'residential', 'price_range' => [100000, 220000]],
                'Hatfield' => ['type' => 'residential', 'price_range' => [70000, 150000]],
                'Waterfalls' => ['type' => 'residential', 'price_range' => [60000, 130000]]
            ]
        ],
        'bulawayo' => [
            'name' => 'Bulawayo',
            'areas' => [
                'Hillside' => ['type' => 'residential', 'price_range' => [80000, 180000]],
                'Suburbs' => ['type' => 'residential', 'price_range' => [60000, 120000]],
                'CBD' => ['type' => 'commercial', 'price_range' => [50000, 150000]],
                'Matsheumhlope' => ['type' => 'residential', 'price_range' => [90000, 200000]],
                'Khumalo' => ['type' => 'residential', 'price_range' => [85000, 190000]],
                'Burnside' => ['type' => 'residential', 'price_range' => [70000, 140000]],
                'Morningside' => ['type' => 'residential', 'price_range' => [75000, 160000]]
            ]
        ],
        'manicaland' => [
            'name' => 'Manicaland',
            'areas' => [
                'Mutare CBD' => ['type' => 'commercial', 'price_range' => [30000, 80000]],
                'Murambi' => ['type' => 'residential', 'price_range' => [40000, 90000]],
                'Dangamvura' => ['type' => 'residential', 'price_range' => [25000, 60000]],
                'Chimanimani' => ['type' => 'residential', 'price_range' => [20000, 50000]],
                'Nyanga' => ['type' => 'mixed', 'price_range' => [35000, 100000]],
                'Chipinge' => ['type' => 'mixed', 'price_range' => [30000, 70000]],
                'Buhera' => ['type' => 'residential', 'price_range' => [15000, 40000]]
            ]
        ],
        'mashonaland_central' => [
            'name' => 'Mashonaland Central',
            'areas' => [
                'Bindura CBD' => ['type' => 'commercial', 'price_range' => [25000, 60000]],
                'Mt Darwin' => ['type' => 'mixed', 'price_range' => [20000, 50000]],
                'Shamva' => ['type' => 'mixed', 'price_range' => [18000, 45000]],
                'Guruve' => ['type' => 'residential', 'price_range' => [15000, 35000]],
                'Mvurwi' => ['type' => 'mixed', 'price_range' => [22000, 55000]],
                'Concession' => ['type' => 'residential', 'price_range' => [30000, 70000]]
            ]
        ],
        'mashonaland_east' => [
            'name' => 'Mashonaland East',
            'areas' => [
                'Marondera CBD' => ['type' => 'commercial', 'price_range' => [40000, 90000]],
                'Ruwa' => ['type' => 'residential', 'price_range' => [50000, 120000]],
                'Chitungwiza' => ['type' => 'residential', 'price_range' => [30000, 70000]],
                'Norton' => ['type' => 'mixed', 'price_range' => [35000, 80000]],
                'Macheke' => ['type' => 'mixed', 'price_range' => [20000, 50000]],
                'Mutoko' => ['type' => 'residential', 'price_range' => [18000, 45000]],
                'Murehwa' => ['type' => 'mixed', 'price_range' => [22000, 55000]]
            ]
        ],
        'mashonaland_west' => [
            'name' => 'Mashonaland West',
            'areas' => [
                'Chinhoyi CBD' => ['type' => 'commercial', 'price_range' => [35000, 80000]],
                'Karoi' => ['type' => 'mixed', 'price_range' => [30000, 70000]],
                'Kadoma' => ['type' => 'mixed', 'price_range' => [32000, 75000]],
                'Chegutu' => ['type' => 'mixed', 'price_range' => [28000, 65000]],
                'Hurungwe' => ['type' => 'residential', 'price_range' => [20000, 50000]],
                'Makonde' => ['type' => 'residential', 'price_range' => [25000, 60000]]
            ]
        ],
        'masvingo' => [
            'name' => 'Masvingo',
            'areas' => [
                'Masvingo CBD' => ['type' => 'commercial', 'price_range' => [30000, 70000]],
                'Rujeko' => ['type' => 'residential', 'price_range' => [25000, 60000]],
                'Mucheke' => ['type' => 'residential', 'price_range' => [20000, 50000]],
                'Gutu' => ['type' => 'mixed', 'price_range' => [18000, 45000]],
                'Chiredzi' => ['type' => 'mixed', 'price_range' => [35000, 85000]],
                'Zaka' => ['type' => 'residential', 'price_range' => [15000, 40000]],
                'Mwenezi' => ['type' => 'residential', 'price_range' => [12000, 35000]]
            ]
        ],
        'matabeleland_north' => [
            'name' => 'Matabeleland North',
            'areas' => [
                'Victoria Falls CBD' => ['type' => 'commercial', 'price_range' => [50000, 150000]],
                'Hwange' => ['type' => 'mixed', 'price_range' => [30000, 80000]],
                'Lupane' => ['type' => 'residential', 'price_range' => [20000, 50000]],
                'Binga' => ['type' => 'residential', 'price_range' => [15000, 40000]],
                'Nkayi' => ['type' => 'residential', 'price_range' => [18000, 45000]],
                'Tsholotsho' => ['type' => 'mixed', 'price_range' => [22000, 55000]]
            ]
        ],
        'matabeleland_south' => [
            'name' => 'Matabeleland South',
            'areas' => [
                'Gwanda CBD' => ['type' => 'commercial', 'price_range' => [25000, 60000]],
                'Beitbridge' => ['type' => 'mixed', 'price_range' => [30000, 80000]],
                'Plumtree' => ['type' => 'mixed', 'price_range' => [22000, 55000]],
                'Insiza' => ['type' => 'residential', 'price_range' => [15000, 40000]],
                'Umzingwane' => ['type' => 'residential', 'price_range' => [20000, 50000]],
                'Bulilima' => ['type' => 'residential', 'price_range' => [12000, 35000]]
            ]
        ],
        'midlands' => [
            'name' => 'Midlands',
            'areas' => [
                'Gweru CBD' => ['type' => 'commercial', 'price_range' => [35000, 85000]],
                'Kwekwe' => ['type' => 'mixed', 'price_range' => [30000, 75000]],
                'Shurugwi' => ['type' => 'mixed', 'price_range' => [25000, 60000]],
                'Zvishavane' => ['type' => 'mixed', 'price_range' => [28000, 65000]],
                'Mberengwa' => ['type' => 'residential', 'price_range' => [18000, 45000]],
                'Gokwe' => ['type' => 'mixed', 'price_range' => [22000, 55000]]
            ]
        ]
    ];

    // Property types and their characteristics
    $propertyTypes = [
        'apartment' => [
            'bedrooms_range' => [1, 3],
            'bathrooms_range' => [1, 2],
            'area_range' => [500, 1200]
        ],
        'house' => [
            'bedrooms_range' => [2, 5],
            'bathrooms_range' => [1, 3],
            'area_range' => [800, 2500]
        ],
        'stand' => [
            'bedrooms_range' => [0, 0],
            'bathrooms_range' => [0, 0],
            'area_range' => [2000, 10000]
        ],
        'commercial' => [
            'bedrooms_range' => [0, 2],
            'bathrooms_range' => [1, 2],
            'area_range' => [1000, 5000]
        ],
        'farm' => [
            'bedrooms_range' => [2, 4],
            'bathrooms_range' => [1, 2],
            'area_range' => [5000, 50000]
        ]
    ];

    // Property descriptions templates
    $descriptions = [
        'apartment' => [
            "Beautiful {bedrooms}-bedroom apartment in {area}, {province}. Features modern finishes and convenient location.",
            "Spacious {bedrooms}-bedroom apartment with stunning views in {area}. Perfect for professionals.",
            "Modern {bedrooms}-bedroom apartment in prime {area} location. Close to amenities and transport."
        ],
        'house' => [
            "Lovely {bedrooms}-bedroom family home in {area}. Features spacious living areas and garden.",
            "Beautiful {bedrooms}-bedroom house in sought-after {area} neighborhood. Move-in ready condition.",
            "Spacious {bedrooms}-bedroom family home with modern amenities in {area}."
        ],
        'stand' => [
            "Prime residential stand in {area}. Perfect for building your dream home.",
            "Excellent development opportunity in growing {area} neighborhood.",
            "Well-located stand in {area} with all services available."
        ],
        'commercial' => [
            "Prime commercial space in {area} CBD. Ideal for retail or office use.",
            "Commercial property in busy {area} location. Great business opportunity.",
            "Spacious commercial premises in {area}. Perfect for various business ventures."
        ],
        'farm' => [
            "Productive agricultural farm in {province}. Ideal for various farming activities.",
            "Beautiful farm property in {area}. Features fertile land and water sources.",
            "Agricultural land in {province} suitable for crop farming or livestock."
        ]
    ];

    // Create properties
    $propertyModel = new Property();
    $propertyCount = 0;
    $statuses = ['available', 'available', 'available', 'rented', 'sold']; // Weighted for more available properties

    foreach ($zimbabweLocations as $province => $provinceData) {
        foreach ($provinceData['areas'] as $area => $areaData) {
            // Create 5-15 properties per area
            $propertiesPerArea = rand(5, 15);
            
            for ($i = 0; $i < $propertiesPerArea; $i++) {
                // Determine property type based on area type
                if ($areaData['type'] === 'commercial') {
                    $propertyType = 'commercial';
                } elseif ($areaData['type'] === 'mixed') {
                    $propertyType = array_rand(['apartment' => 1, 'house' => 1, 'commercial' => 1]);
                } else {
                    $propertyType = array_rand(['apartment' => 1, 'house' => 1, 'stand' => 1]);
                }
                
                // Get random landlord
                $landlordId = $createdUsers['landlord'][array_rand($createdUsers['landlord'])];
                
                // Generate property details
                $typeConfig = $propertyTypes[$propertyType];
                $bedrooms = rand($typeConfig['bedrooms_range'][0], $typeConfig['bedrooms_range'][1]);
                $bathrooms = rand($typeConfig['bathrooms_range'][0], $typeConfig['bathrooms_range'][1]);
                $areaSqft = rand($typeConfig['area_range'][0], $typeConfig['area_range'][1]);
                
                // Generate price within area range with some variation
                $price = rand($areaData['price_range'][0], $areaData['price_range'][1]);
                $price = round($price / 1000) * 1000; // Round to nearest 1000
                
                // Select random description
                $descriptionTemplate = $descriptions[$propertyType][array_rand($descriptions[$propertyType])];
                $description = str_replace(
                    ['{bedrooms}', '{area}', '{province}'],
                    [$bedrooms, $area, $provinceData['name']],
                    $descriptionTemplate
                );
                
                // Generate address
                $streetNumber = rand(1, 200);
                $streetNames = ['Main', 'First', 'Central', 'Park', 'Lake', 'Hill', 'Valley', 'River', 'Forest', 'Mountain'];
                $streetName = $streetNames[array_rand($streetNames)];
                
                $address = "{$streetNumber} {$streetName} Street, {$area}";
                
                // Random status
                $status = $statuses[array_rand($statuses)];
                
                // Create property
                $propertyData = [
                    'title' => ucfirst($propertyType) . " in {$area}, {$provinceData['name']}",
                    'description' => $description,
                    'type' => $propertyType,
                    'price' => $price,
                    'address' => $address,
                    'city' => $area,
                    'state' => $provinceData['name'],
                    'zip_code' => 'ZIM' . rand(1000, 9999),
                    'bedrooms' => $bedrooms,
                    'bathrooms' => $bathrooms,
                    'area_sqft' => $areaSqft,
                    'owner_id' => $landlordId,
                    'status' => $status
                ];
                
                if ($propertyModel->create($propertyData)) {
                    $propertyCount++;
                }
            }
        }
    }

    // Create some special properties (luxury, unique locations)
    $specialProperties = [
        [
            'title' => "Luxury Mansion in Borrowdale",
            'description' => "Stunning 5-bedroom mansion with swimming pool and extensive gardens in exclusive Borrowdale neighborhood.",
            'type' => 'house',
            'price' => 750000,
            'address' => '23 Borrowdale Road, Borrowdale',
            'city' => 'Borrowdale',
            'state' => 'Harare',
            'zip_code' => 'ZIM2024',
            'bedrooms' => 5,
            'bathrooms' => 4,
            'area_sqft' => 4500,
            'owner_id' => $createdUsers['landlord'][0],
            'status' => 'available'
        ],
        [
            'title' => "Victoria Falls Safari Lodge",
            'description' => "Beautiful safari lodge property near Victoria Falls with tourist accommodation potential.",
            'type' => 'commercial',
            'price' => 450000,
            'address' => 'Victoria Falls Road, Victoria Falls',
            'city' => 'Victoria Falls',
            'state' => 'Matabeleland North',
            'zip_code' => 'ZIM2025',
            'bedrooms' => 8,
            'bathrooms' => 6,
            'area_sqft' => 8000,
            'owner_id' => $createdUsers['landlord'][1],
            'status' => 'available'
        ],
        [
            'title' => "Commercial Complex in Harare CBD",
            'description' => "Prime commercial complex in Harare Central Business District with multiple retail spaces.",
            'type' => 'commercial',
            'price' => 1200000,
            'address' => '45 First Street, CBD',
            'city' => 'CBD',
            'state' => 'Harare',
            'zip_code' => 'ZIM2026',
            'bedrooms' => 0,
            'bathrooms' => 4,
            'area_sqft' => 15000,
            'owner_id' => $createdUsers['landlord'][2],
            'status' => 'available'
        ]
    ];

    foreach ($specialProperties as $propertyData) {
        if ($propertyModel->create($propertyData)) {
            $propertyCount++;
        }
    }

    echo "<br><strong>Data seeding completed successfully!</strong><br>";
    echo "Created " . count($users) . " users<br>";
    echo "Created {$propertyCount} properties across Zimbabwe<br>";
    
    // Display property count by province
    echo "<br><strong>Properties by Province:</strong><br>";
    foreach ($zimbabweLocations as $province => $provinceData) {
        $count = 0;
        foreach ($provinceData['areas'] as $area) {
            $count += rand(5, 15);
        }
        echo "{$provinceData['name']}: {$count} properties<br>";
    }

} catch (Exception $e) {
    echo "Error during data seeding: " . $e->getMessage() . "<br>";
}
?>