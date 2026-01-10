<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'naroda_group';

// Create connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Select database
$conn->select_db($dbname);

// SQL to create blogs table
$sql = "CREATE TABLE IF NOT EXISTS `blogs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title_en` varchar(255) NOT NULL,
    `title_hi` varchar(255) DEFAULT NULL,
    `title_gu` varchar(255) DEFAULT NULL,
    `excerpt_en` text DEFAULT NULL,
    `excerpt_hi` text DEFAULT NULL,
    `excerpt_gu` text DEFAULT NULL,
    `content_en` longtext DEFAULT NULL,
    `content_hi` longtext DEFAULT NULL,
    `content_gu` longtext DEFAULT NULL,
    `category` varchar(50) NOT NULL,
    `author_en` varchar(100) DEFAULT 'Admin',
    `author_hi` varchar(100) DEFAULT 'Admin',
    `author_gu` varchar(100) DEFAULT 'Admin',
    `status` varchar(20) DEFAULT 'draft',
    `date` date DEFAULT NULL,
    `views` int(11) DEFAULT 0,
    `likes` int(11) DEFAULT 0,
    `shares` int(11) DEFAULT 0,
    `read_time` varchar(20) DEFAULT NULL,
    `tags` varchar(255) DEFAULT NULL,
    `image` varchar(255) DEFAULT NULL,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "Table 'blogs' created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Seed initial data if empty
$check_sql = "SELECT count(*) as count FROM blogs";
$result = $conn->query($check_sql);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Sample Data from blogs.html
    $title_en = 'The Future of Real Estate: Smart Homes and Sustainable Living';
    $excerpt_en = 'Explore how technology and sustainability are reshaping the real estate landscape, making homes smarter and more eco-friendly.';
    $content_en = '<p>The real estate industry is undergoing a significant transformation, driven by advancements in technology and a growing awareness of environmental issues. Smart homes and sustainable living are no longer just buzzwords; they are becoming the new standard for modern housing.</p><p>Smart home technology allows homeowners to control various aspects of their property, such as lighting, heating, and security, remotely via their smartphones. This not only enhances convenience but also improves energy efficiency, leading to cost savings and a reduced carbon footprint.</p>';
    
    $title_hi = 'रियल एस्टेट का भविष्य: स्मार्ट होम और टिकाऊ जीवन';
    $excerpt_hi = 'जानें कि कैसे तकनीक और स्थिरता रियल एस्टेट परिदृश्य को नया आकार दे रही है, घरों को स्मार्ट और अधिक पर्यावरण-अनुकूल बना रही है।';
    $content_hi = '<p>रियल एस्टेट उद्योग एक महत्वपूर्ण परिवर्तन के दौर से गुजर रहा है, जो प्रौद्योगिकी में प्रगति और पर्यावरणीय मुद्दों के प्रति बढ़ती जागरूकता से प्रेरित है। स्मार्ट होम और टिकाऊ जीवन अब केवल चर्चा के शब्द नहीं रह गए हैं; वे आधुनिक आवास के लिए नया मानक बनते जा रहे हैं।</p><p>स्मार्ट होम तकनीक घर के मालिकों को अपने स्मार्टफोन के माध्यम से अपनी संपत्ति के विभिन्न पहलुओं, जैसे प्रकाश, हीटिंग और सुरक्षा को दूर से नियंत्रित करने की अनुमति देती है। यह न केवल सुविधा बढ़ाता है बल्कि ऊर्जा दक्षता में भी सुधार करता है, जिससे लागत में बचत होती है और कार्बन फुटप्रिंट कम होता है।</p>';

    $title_gu = 'રીઅલ એસ્ટેટનું ભવિષ્ય: સ્માર્ટ હોમ્સ અને સસ્ટેનેબલ લિવિંગ';
    $excerpt_gu = 'ટેક્નોલોજી અને ટકાઉપણું રીઅલ એસ્ટેટ લેન્ડસ્કેપને કેવી રીતે પુનઃઆકાર આપી રહ્યા છે તે જાણો, ઘરોને વધુ સ્માર્ટ અને વધુ પર્યાવરણ-મૈત્રીપૂર્ણ બનાવે છે.';
    $content_gu = '<p>રિયલ એસ્ટેટ ઉદ્યોગ નોંધપાત્ર પરિવર્તનમાંથી પસાર થઈ રહ્યો છે, જે ટેક્નોલોજીમાં પ્રગતિ અને પર્યાવરણીય મુદ્દાઓ વિશે વધતી જતી જાગૃતિ દ્વારા સંચાલિત છે. સ્માર્ટ હોમ્સ અને સસ્ટેનેબલ લિવિંગ હવે માત્ર બઝવર્ડ્સ નથી રહ્યાં; તેઓ આધુનિક આવાસ માટે નવું ધોરણ બની રહ્યા છે.</p><p>સ્માર્ટ હોમ ટેક્નોલોજી ઘરમાલિકોને તેમના સ્માર્ટફોન દ્વારા તેમની મિલકતના વિવિધ પાસાઓ, જેમ કે લાઇટિંગ, હીટિંગ અને સુરક્ષાને દૂરસ્થ રીતે નિયંત્રિત કરવાની મંજૂરી આપે છે. આ માત્ર સગવડમાં વધારો કરતું નથી પણ ઊર્જા કાર્યક્ષમતામાં પણ સુધારો કરે છે, જેનાથી ખર્ચમાં બચત થાય છે અને કાર્બન ફૂટપ્રિન્ટમાં ઘટાડો થાય છે.</p>';

    // Prepare statement
    $stmt = $conn->prepare("INSERT INTO blogs (title_en, title_hi, title_gu, excerpt_en, excerpt_hi, excerpt_gu, content_en, content_hi, content_gu, category, status, date, views, tags, image, read_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $category = 'trends';
    $status = 'published';
    $date = '2024-03-15';
    $views = 1250;
    $tags = 'Real Estate,Smart Homes,Sustainability';
    $image = 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80';
    $read_time = '5 min read';

    $stmt->bind_param("ssssssssssssssss", $title_en, $title_hi, $title_gu, $excerpt_en, $excerpt_hi, $excerpt_gu, $content_en, $content_hi, $content_gu, $category, $status, $date, $views, $tags, $image, $read_time);
    
    if ($stmt->execute()) {
        echo "Sample data inserted successfully.<br>";
    } else {
        echo "Error inserting sample data: " . $stmt->error . "<br>";
    }
    $stmt->close();
} else {
    echo "Table 'blogs' already has data.<br>";
}

$conn->close();
?>
