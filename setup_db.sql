-- Create Database
CREATE DATABASE IF NOT EXISTS iries1_db;
USE iries1_db;

-- Apartment Plans Table
CREATE TABLE IF NOT EXISTS apartment_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bhk_type VARCHAR(10) NOT NULL, -- '1bhk', '2bhk', '3bhk'
    area VARCHAR(255),
    bedrooms VARCHAR(10),
    bathrooms VARCHAR(10),
    balconies VARCHAR(10),
    description JSON, -- Multi-language object
    image TEXT,
    status VARCHAR(20) DEFAULT 'available',
    last_updated DATE
);

-- Gallery Table
CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    url TEXT NOT NULL,
    title VARCHAR(255),
    description TEXT,
    uploaded_date DATE,
    is_featured BOOLEAN DEFAULT FALSE
);

-- Pricing Plans Table
CREATE TABLE IF NOT EXISTS pricing_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bhk_type VARCHAR(10) NOT NULL,
    starting_price VARCHAR(255),
    sqft VARCHAR(255),
    bedrooms VARCHAR(10),
    bathrooms VARCHAR(10),
    parking VARCHAR(255),
    available VARCHAR(255),
    availability_status VARCHAR(20),
    features JSON, -- Stored as JSON array
    last_updated DATE,
    status VARCHAR(20) DEFAULT 'active',
    UNIQUE(bhk_type)
);

-- Featured Properties Table
CREATE TABLE IF NOT EXISTS featured_properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_type VARCHAR(20), -- 'for-sale', 'for-rent'
    title JSON, -- Multi-language object
    location JSON, -- Multi-language object
    type VARCHAR(255),
    category VARCHAR(255),
    area VARCHAR(255),
    floor VARCHAR(255),
    parking VARCHAR(255),
    status VARCHAR(50),
    price VARCHAR(255),
    price_unit VARCHAR(50),
    bedrooms VARCHAR(10),
    bathrooms VARCHAR(10),
    property_id VARCHAR(100),
    facing VARCHAR(100),
    description JSON, -- Multi-language object
    images JSON, -- Array of image URLs
    brochure JSON, -- Object {name, url, type, size}
    overview JSON, -- Object {description: {en, hi, gu}, features: {en: [], hi: [], gu: []}}
    amenities JSON, -- Object {en: sections[], hi: sections[], gu: sections[]}
    floor_plans_dimensions JSON,
    location_details JSON, -- Object {fullAddress: {en, hi, gu}, mapIframe}
    documents JSON, -- Object {propertyDocuments[], approvalsDocuments[]}
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inquiries Table
CREATE TABLE IF NOT EXISTS inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inquiry_category VARCHAR(10), -- 'sale', 'rent'
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    inquiry_type VARCHAR(100),
    property_id VARCHAR(100),
    property_title VARCHAR(255),
    message TEXT,
    preferred_date DATE,
    status VARCHAR(20) DEFAULT 'new',
    submitted_date DATETIME DEFAULT CURRENT_TIMESTAMP
);
