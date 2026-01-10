<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'naroda_group'; // Updated to main database

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

// SQL to create news table
$sql = "CREATE TABLE IF NOT EXISTS `news` (
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
    `author` varchar(100) DEFAULT 'Admin',
    `status` varchar(20) DEFAULT 'draft',
    `date` date DEFAULT NULL,
    `views` int(11) DEFAULT 0,
    `tags` varchar(255) DEFAULT NULL,
    `featured` tinyint(1) DEFAULT 0,
    `image` varchar(255) DEFAULT NULL,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "Table 'news' created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Seed initial data if empty
$check_sql = "SELECT count(*) as count FROM news";
$result = $conn->query($check_sql);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Sample Data
    $title_en = 'Naroda Group Wins "Best Real Estate Developer" Award 2024';
    $excerpt_en = 'Naroda Group has been honored with the prestigious "Best Real Estate Developer" award at the National Real Estate Excellence Awards 2024.';
    $content_en = '<p>We are thrilled to announce that Naroda Group has been recognized as the "Best Real Estate Developer" for the year 2024. This award is a testament to our commitment to quality, innovation, and customer satisfaction.</p>';
    
    $title_hi = 'नरोदा ग्रुप ने जीता "सर्वश्रेष्ठ रियल एस्टेट डेवलपर" पुरस्कार 2024';
    $excerpt_hi = 'नरोदा ग्रुप को राष्ट्रीय रियल एस्टेट एक्सीलेंस अवार्ड्स 2024 में प्रतिष्ठित "सर्वश्रेष्ठ रियल एस्टेट डेवलपर" पुरस्कार से सम्मानित किया गया है।';
    $content_hi = '<p>हमें यह घोषणा करते हुए बहुत खुशी हो रही है कि नरोदा ग्रुप को वर्ष 2024 के लिए "सर्वश्रेष्ठ रियल एस्टेट डेवलपर" के रूप में मान्यता दी गई है। यह पुरस्कार गुणवत्ता, नवाचार और ग्राहक संतुष्टि के प्रति हमारी प्रतिबद्धता का प्रमाण है।</p>';

    $title_gu = 'નારોદા ગ્રુપે જીત્યો "શ્રેષ્ઠ રીઅલ એસ્ટેટ ડેવલપર" પુરસ્કાર 2024';
    $excerpt_gu = 'નારોદા ગ્રુપને રાષ્ટ્રીય રીઅલ એસ્ટેટ એક્સેલન્સ એવોર્ડ્સ 2024 માં પ્રતિષ્ઠિત "શ્રેષ્ઠ રીઅલ એસ્ટેટ ડેવલપર" એવોર્ડથી સન્માનિત કરવામાં આવ્યો છે.';
    $content_gu = '<p>અમને એ જાહેર કરવામાં ખૂબ ગર્વ છે કે નારોદા ગ્રુપને વર્ષ 2024 માટે "શ્રેષ્ઠ રીઅલ એસ્ટેટ ડેવલપર" તરીકે ઓળખવામાં આવ્યું છે. આ પુરસ્કાર ગુણવત્તા, નવીનતા અને ગ્રાહક સંતોષ પ્રત્યેની અમારી પ્રતિબદ્ધતાનું પ્રમાણ છે.</p>';

    // Prepare statement
    $stmt = $conn->prepare("INSERT INTO news (title_en, title_hi, title_gu, excerpt_en, excerpt_hi, excerpt_gu, content_en, content_hi, content_gu, category, status, date, views, tags, featured, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $category = 'awards';
    $status = 'published';
    $date = date('Y-m-d');
    $views = 1250;
    $tags = 'award,excellence,2024';
    $featured = 1;
    $image = 'https://images.unsplash.com/photo-1531403009284-440f080d1e12?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80';

    $stmt->bind_param("ssssssssssssssis", $title_en, $title_hi, $title_gu, $excerpt_en, $excerpt_hi, $excerpt_gu, $content_en, $content_hi, $content_gu, $category, $status, $date, $views, $tags, $featured, $image);
    
    if ($stmt->execute()) {
        echo "Sample data inserted successfully.<br>";
    } else {
        echo "Error inserting sample data: " . $stmt->error . "<br>";
    }
    $stmt->close();
} else {
    echo "Table 'news' already has data.<br>";
}

$conn->close();
?>
