<?php
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    echo "Clearing old data...\n";
    $conn->exec("TRUNCATE TABLE naroda_irish_apartments");
    $conn->exec("TRUNCATE TABLE naroda_irish_pricing");
    
    // Seed Apartments
    echo "Seeding Apartments...\n";
    $apartments = [
        [
            'type' => '1bhk',
            'area' => 750,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'balconies' => 1,
            'description_en' => 'Cozy 1BHK apartment perfect for singles or couples.',
            'description_hi' => 'एकल या जोड़ों के लिए आरामदायक 1BHK अपार्टमेंट।',
            'description_gu' => 'સિંગલ્સ અથવા કપલ્સ માટે આરામદાયક 1BHK એપાર્ટમેન્ટ.',
            'image_url' => 'assets/img/plans/1bhk.jpg'
        ],
        [
            'type' => '2bhk',
            'area' => 1100,
            'bedrooms' => 2,
            'bathrooms' => 2,
            'balconies' => 1,
            'description_en' => 'Spacious 2BHK with modern amenities.',
            'description_hi' => 'आधुनिक सुविधाओं के साथ विशाल 2BHK।',
            'description_gu' => 'આધુનિક સુવિધાઓ સાથે વિશાળ 2BHK.',
            'image_url' => 'assets/img/plans/2bhk.jpg'
        ],
        [
            'type' => '3bhk',
            'area' => 1450,
            'bedrooms' => 3,
            'bathrooms' => 3,
            'balconies' => 2,
            'description_en' => 'Luxury 3BHK for a premium lifestyle.',
            'description_hi' => 'प्रीमियम जीवनशैली के लिए लक्ज़री 3BHK।',
            'description_gu' => 'પ્રીમિયમ જીવનશૈલી માટે લક્ઝરી 3BHK.',
            'image_url' => 'assets/img/plans/3bhk.jpg'
        ]
    ];

    $stmt = $conn->prepare("INSERT INTO naroda_irish_apartments (type, area, bedrooms, bathrooms, balconies, description_en, description_hi, description_gu, image_url) VALUES (:type, :area, :bedrooms, :bathrooms, :balconies, :description_en, :description_hi, :description_gu, :image_url)");
    foreach ($apartments as $apt) {
        $stmt->execute($apt);
    }

    // Seed Pricing
    echo "Seeding Pricing...\n";
    $pricing = [
        [
            'type' => '1bhk',
            'starting_price' => '35,00,000',
            'sqft' => '750 sq.ft',
            'bedrooms' => 1,
            'bathrooms' => 1,
            'parking' => '1 Covered',
            'available_units' => 10,
            'status' => 'Available',
            'features_en' => json_encode(['Modular Kitchen', 'Vitrified Tiles']),
            'features_hi' => json_encode(['मॉड्यूलर किचन', 'विट्रिफाइड टाइलें']),
            'features_gu' => json_encode(['મોડ્યુલર કિચન', 'વિટ્રિફાઈડ ટાઇલ્સ'])
        ],
        [
            'type' => '2bhk',
            'starting_price' => '55,00,000',
            'sqft' => '1100 sq.ft',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'parking' => '1 Covered',
            'available_units' => 8,
            'status' => 'Available',
            'features_en' => json_encode(['Master Bedroom Balcony', 'Video Door Phone']),
            'features_hi' => json_encode(['मास्टर बेडरूम बालकनी', 'वीडियो डोर फोन']),
            'features_gu' => json_encode(['માસ્ટર બેડરૂમ બાલ્કની', 'વિડિઓ ડોર ફોન'])
        ],
        [
            'type' => '3bhk',
            'starting_price' => '75,00,000',
            'sqft' => '1450 sq.ft',
            'bedrooms' => 3,
            'bathrooms' => 3,
            'parking' => '2 Covered',
            'available_units' => 5,
            'status' => 'Limited Availability',
            'features_en' => json_encode(['Servant Room', 'Italian Marble Flooring']),
            'features_hi' => json_encode(['नौकर का कमरा', 'इतालवी संगमरमर का फर्श']),
            'features_gu' => json_encode(['સર્વન્ટ રૂમ', 'ઇટાલિયન માર્બલ ફ્લોરિંગ'])
        ]
    ];

    $stmt = $conn->prepare("INSERT INTO naroda_irish_pricing (type, starting_price, sqft, bedrooms, bathrooms, parking, available_units, status, features_en, features_hi, features_gu) VALUES (:type, :starting_price, :sqft, :bedrooms, :bathrooms, :parking, :available_units, :status, :features_en, :features_hi, :features_gu)");
    foreach ($pricing as $p) {
        $stmt->execute($p);
    }

    echo "Database seeded successfully!\n";

} catch (PDOException $e) {
    die("SEED ERROR: " . $e->getMessage());
}
?>
