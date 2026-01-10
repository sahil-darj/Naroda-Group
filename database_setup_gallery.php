<?php
// ... (your existing connection code)

// 1. Add this before table creation for better error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// 2. Add these additional indexes for better performance
// After creating gallery_images table:
$conn->query("CREATE INDEX idx_project ON gallery_images(project)");

// After creating gallery_videos table:
$conn->query("CREATE INDEX idx_uploaded_date ON gallery_videos(uploaded_date)");

// 3. Consider adding these constraints (optional but good for data integrity)
// For gallery_images table (optional foreign key if you want strict referential integrity):
/*
$sql = "ALTER TABLE gallery_images 
        ADD CONSTRAINT fk_gallery_category 
        FOREIGN KEY (category) 
        REFERENCES gallery_categories(name) 
        ON DELETE SET NULL";
$conn->query($sql);
*/

// 4. Add these missing fields to gallery_images for better data tracking
$conn->query("ALTER TABLE gallery_images 
    ADD COLUMN IF NOT EXISTS alt_text_en VARCHAR(255) DEFAULT NULL AFTER description_gu,
    ADD COLUMN IF NOT EXISTS alt_text_hi VARCHAR(255) DEFAULT NULL AFTER alt_text_en,
    ADD COLUMN IF NOT EXISTS alt_text_gu VARCHAR(255) DEFAULT NULL AFTER alt_text_hi,
    ADD COLUMN IF NOT EXISTS file_name VARCHAR(255) DEFAULT NULL AFTER image_url,
    ADD COLUMN IF NOT EXISTS file_format VARCHAR(10) DEFAULT NULL AFTER file_name");

// 5. Add these to gallery_videos
$conn->query("ALTER TABLE gallery_videos 
    ADD COLUMN IF NOT EXISTS aspect_ratio VARCHAR(20) DEFAULT '16:9' AFTER duration,
    ADD COLUMN IF NOT EXISTS resolution VARCHAR(20) DEFAULT '1920x1080' AFTER aspect_ratio");

// 6. Add a settings table for gallery configuration
$sql = "CREATE TABLE IF NOT EXISTS gallery_settings (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "Table 'gallery_settings' created successfully\n";
    
    // Insert default settings
    $default_settings = [
        ['max_upload_size', '10485760', 'number', 'Maximum upload size in bytes (10MB)'],
        ['allowed_image_formats', '["jpg", "jpeg", "png", "webp", "gif"]', 'json', 'Allowed image formats'],
        ['allowed_video_formats', '["mp4", "webm", "mov"]', 'json', 'Allowed video formats'],
        ['default_image_quality', '85', 'number', 'Default image compression quality'],
        ['carousel_interval', '5', 'number', 'Carousel auto-play interval in seconds'],
        ['enable_watermark', '0', 'boolean', 'Enable watermark on uploaded images'],
        ['thumbnail_width', '300', 'number', 'Thumbnail width in pixels'],
        ['thumbnail_height', '200', 'number', 'Thumbnail height in pixels']
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO gallery_settings (setting_key, setting_value, setting_type, description) VALUES (?, ?, ?, ?)");
    
    foreach ($default_settings as $setting) {
        $stmt->bind_param("ssss", $setting[0], $setting[1], $setting[2], $setting[3]);
        $stmt->execute();
    }
    echo "Default settings added successfully\n";
    $stmt->close();
}

// 7. Add a logs table for tracking actions (optional but useful)
$sql = "CREATE TABLE IF NOT EXISTS gallery_logs (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) DEFAULT NULL,
    action VARCHAR(50) NOT NULL,
    item_type VARCHAR(50) NOT NULL,
    item_id INT(11) DEFAULT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "Table 'gallery_logs' created successfully\n";
}

// 8. Add more comprehensive sample data
$check_videos = $conn->query("SELECT id FROM gallery_videos LIMIT 1");
if ($check_videos->num_rows == 0) {
    $sample_videos = [
        ['Project Overview Video', 'प्रोजेक्ट ओवरव्यू वीडियो', 'પ્રોજેક્ટ ઓવરવ્યૂ વીડિયો', 'landmark', 'A comprehensive overview of Naroda Landmark project', 'नरोदा लैंडमार्क प्रोजेक्ट का व्यापक अवलोकन', 'નારોદા લેન્ડમાર્ક પ્રોજેક્ટનો વ્યાપક ઝલક', '3:45', '45.2 MB', 125, 'https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'youtube', 1, '2023-10-10'],
        ['Construction Time-lapse', 'निर्माण टाइम-लैप्स', 'કન્સ્ટ્રક્શન ટાઇમ-લેપ્સ', 'construction', 'Construction progress time-lapse', 'निर्माण प्रगति टाइम-लैप्स', 'કન્સ્ટ્રક્શન પ્રોગ્રેસ ટાઇમ-લેપ્સ', '2:30', '32.1 MB', 89, 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 'https://example.com/videos/construction.mp4', 'upload', 0, '2023-10-18']
    ];
    
    $stmt = $conn->prepare("INSERT INTO gallery_videos (title_en, title_hi, title_gu, category, description_en, description_hi, description_gu, duration, size, views, thumbnail_url, video_url, source, featured, uploaded_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($sample_videos as $video) {
        $stmt->bind_param("sssssssssisssis", $video[0], $video[1], $video[2], $video[3], $video[4], $video[5], $video[6], $video[7], $video[8], $video[9], $video[10], $video[11], $video[12], $video[13], $video[14]);
        $stmt->execute();
    }
    echo "Sample videos added successfully\n";
    $stmt->close();
}

// 9. Add sample highlights
$check_highlights = $conn->query("SELECT id FROM gallery_highlights LIMIT 1");
if ($check_highlights->num_rows == 0) {
    $sample_highlights = [
        ['Luxury Amenities Showcase', 'लक्जरी सुविधाएं प्रदर्शनी', 'લક્ઝરી સુવિધાઓ પ્રદર્શન', 'Explore our state-of-the-art amenities', 'हमारी अत्याधुनिक सुविधाओं का अन्वेषण करें', 'અમારી સ્ટેટ-ઓફ-ધ-આર્ટ સુવિધાઓનું અન્વેષણ કરો', 'Club House, Pool Area', 'क्लब हाउस, पूल एरिया', 'ક્લબ હાઉસ, પૂલ એરિયા', 'landmark', '["https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=300", "https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=300"]', 1, 1, 'active'],
        ['Modern Architecture Design', 'आधुनिक वास्तुकला डिजाइन', 'આધુનિક આર્કિટેક્ચર ડિઝાઇન', 'Contemporary architectural marvel', 'समकालीन वास्तुशिल्प चमत्कार', 'સમકાલીન આર્કિટેક્ચરલ માર્વેલ', 'Main Building, Facade', 'मुख्य भवन, मुखौटा', 'મુખ્ય ઇમારત, ફેસાડ', 'irish', '["https://images.unsplash.com/photo-1497366754035-f200968a6e72?w=300"]', 2, 0, 'active']
    ];
    
    $stmt = $conn->prepare("INSERT INTO gallery_highlights (title_en, title_hi, title_gu, description_en, description_hi, description_gu, location_en, location_hi, location_gu, project, images, sort_order, featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($sample_highlights as $hl) {
        $stmt->bind_param("ssssssssssssii", $hl[0], $hl[1], $hl[2], $hl[3], $hl[4], $hl[5], $hl[6], $hl[7], $hl[8], $hl[9], $hl[10], $hl[11], $hl[12], $hl[13]);
        $stmt->execute();
    }
    echo "Sample highlights added successfully\n";
    $stmt->close();
}

$conn->close();
echo "\n✅ Database setup completed successfully with enhanced features!\n";
echo "📊 Created tables:\n";
echo "   - gallery_categories\n";
echo "   - gallery_images\n";
echo "   - gallery_videos\n";
echo "   - gallery_highlights\n";
echo "   - gallery_settings\n";
echo "   - gallery_logs\n";
echo "🌱 Seeded initial data for all tables\n";
?>