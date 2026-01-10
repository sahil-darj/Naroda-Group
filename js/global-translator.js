// Global Language Translator
const Translator = {
    // Supported languages
    languages: ['en', 'hi', 'gu'],
    currentLanguage: localStorage.getItem('preferred-language') || 'en',

    // Translations dictionary
    translations: {
        en: {
            // General & Sidebar
            "basic_info": "Basic Information",
            "property_title": "Property Title (English)",
            "location": "Location (English)",
            "property_type": "Property Type (English)",
            "description": "Description (English)",
            "detailed_description": "Detailed Description (English)",
            "auto_translate": "Auto-Translate All Fields",
            "translating": "Translating...",
            "office_pricing_details": "Office Pricing Details",
            "retail_pricing_details": "Retail Pricing Details",
            "property_overview": "Property Overview",
            "dashboard": "Dashboard",
            "users": "Users",
            "projects": "Projects",
            "blogs": "Blogs",
            "faq": "FAQ",
            "team": "Team",
            "gallery": "Gallery",
            "career": "Career",
            "news": "News",
            "logout": "Log Out",
            "project_management": "Project Management",
            "view_live_project": "View Live Project",

            // Tabs & Headers
            "tab_floor_plans": "Floor Plans",
            "tab_gallery": "Gallery",
            "tab_office_pricing": "Office Pricing",
            "tab_retail_pricing": "Retail Pricing",
            "tab_featured_properties": "Featured Properties",
            "header_floor_plans": "Floor Plans Management",
            "header_gallery": "Gallery Management",
            "header_office_pricing": "Office Spaces Pricing",
            "header_retail_pricing": "Retail Shops Pricing",
            "header_featured_properties": "Featured Properties Management",
            "header_upload_images": "Upload Property Images",
            "header_brochure": "Brochure Document",
            "header_upload_floor_plan": "Upload Floor Plan Image",
            "header_property_documents": "Property Documents",
            "header_property_cards": "Property Cards",
            "header_sale_inquiries": "Schedule Visit & Inquiries - For Sale Properties",
            "header_rent_inquiries": "Schedule Visit & Inquiries - For Rent Properties",

            // Sub-tabs & Sections
            "tab_for_sale": "For Sale",
            "tab_for_rent": "For Rent",
            "tab_schedule_visit": "Schedule Visit & Inquiries",
            "tab_sale_inquiries": "For Sale Inquiries",
            "tab_rent_inquiries": "For Rent Inquiries",
            "current_office_pricing": "Current Office Pricing",
            "current_retail_pricing": "Current Retail Pricing",
            "property_amenities": "Property Amenities",
            "floor_plans_dimensions": "Floor Plans & Dimensions",
            "location_details": "Location Details",
            "property_documents": "Property Documents",
            "key_features": "Key Features",
            "approvals_certificates": "Approvals & Certificates",

            // Table Headers
            "th_rental_price": "Rental Price",
            "th_features": "Features",
            "th_last_updated": "Last Updated",
            "th_status": "Status",
            "th_floor": "Floor",
            "th_image": "Image",
            "th_office_size": "Office Size",
            "th_shops": "Shops",
            "th_elevators": "Elevators",
            "th_total_floors": "Total Floors",
            "th_actions": "Actions",
            "th_date": "Date",
            "th_name": "Name",
            "th_email": "Email",
            "th_phone": "Phone",
            "th_inquiry_type": "Inquiry Type",
            "th_property": "Property",
            "th_message": "Message",

            // Buttons & Labels
            "save_changes": "Save Changes",
            "clear_form": "Clear Form",
            "browse_images": "Browse Images",
            "browse_brochure": "Browse Brochure",
            "upload_images": "Upload Images",
            "rental_price": "Rental Price",
            "sale_price": "Sale Price",
            "features": "Features",
            "no_images": "No images yet. Upload images above.",
            "msg_no_properties": "No properties added yet",
            "msg_add_properties_instruction": "Add properties using the form above",
            "drag_drop_images": "Drag and drop images here or click to browse",
            "btn_add_feature": "Add Feature",
            "btn_save_office_pricing": "Save Office Pricing",
            "btn_save_retail_pricing": "Save Retail Pricing",
            "btn_add_amenity_section": "Add New Amenity Section",
            "btn_add_room": "Add Another Room",
            "btn_browse_documents": "Browse Documents",
            "btn_auto_translate_features": "Auto-Translate Features",
            "btn_previous": "Previous",
            "btn_next": "Next",
            "btn_save_complete_property": "Save Complete Property",

            // Placeholders
            "lbl_office_size": "Office Size (Sq. Ft.)",
            "lbl_shops_per_floor": "Shops per Floor",
            "lbl_elevators": "Elevators",
            "lbl_total_floors": "Total Floors Above",
            "lbl_floor_description": "Floor Description",
            "lbl_upload_floor_plan": "Upload Floor Plan Image",
            "lbl_click_to_upload": "Click to upload floor plan image",
            "lbl_property_images": "Property Images",
            "lbl_click_to_upload_images": "Click to upload multiple property images",
            "lbl_upload_brochure": "Upload Brochure Document",
            "lbl_click_to_upload_brochure": "Click to upload property brochure",
            "lbl_area": "Area (Sq. Ft.)",
            "lbl_floor": "Floor",
            "lbl_facing": "Facing",
            "lbl_room_dimensions": "Room Dimensions",
            "lbl_living_room": "Living Room:",
            "lbl_master_bedroom": "Master Bedroom:",
            "lbl_bedroom_2": "Bedroom 2:",
            "lbl_full_address": "Full Address",
            "lbl_upload_documents": "Upload Property Documents",
            "lbl_upload_approvals": "Upload Approvals & Certificates",
            "lbl_click_to_upload_documents": "Click to upload multiple documents",
            "lbl_click_to_upload_approvals": "Click to upload multiple approval documents",

            // Projects Page
            "naroda_landmark": "Naroda Landmark",
            "premium_residential": "Premium Residential Project",
            "naroda_landmark_desc": "Naroda Landmark is a premium residential project offering luxurious 2 & 3 BHK apartments with modern amenities, located in the heart of the city with excellent connectivity and scenic views.",
            "naroda_irish": "Naroda Irish",
            "luxury_township": "Luxury Township Project",
            "naroda_irish_desc": "Naroda Irish is an expansive township project featuring modern villas and apartments with world-class amenities including clubhouse, swimming pool, parks, and commercial spaces.",
            "view_details": "View Details",
            "admin_user": "Admin User",
            "system_admin": "System Administrator",
            "company_name": "Naroda Group",

            // Featured Property Card Labels
            "lbl_parking": "Parking",
            "sq_ft": "sq.ft",
            "brochure_available": "Brochure Available",
            "status_available": "Available",
            "status_limited": "Limited Available",
            "status_sold": "Sold Out",
            "status_coming": "Coming Soon"
        },
        hi: {
            // General & Sidebar
            "basic_info": "मूलभूत जानकारी",
            "property_title": "संपत्ति शीर्षक (हिन्दी)",
            "location": "स्थान (हिन्दी)",
            "property_type": "संपत्ति का प्रकार (हिन्दी)",
            "description": "विवरण (हिन्दी)",
            "detailed_description": "विस्तृत विवरण (हिन्दी)",
            "auto_translate": "सभी फ़ील्ड्स का स्वचालित अनुवाद करें",
            "translating": "अनुवाद हो रहा है...",
            "office_pricing_details": "कार्यालय मूल्य निर्धारण विवरण",
            "retail_pricing_details": "खुदरा मूल्य निर्धारण विवरण",
            "property_overview": "संपत्ति अवलोकन",
            "dashboard": "डैशबोर्ड",
            "users": "उपयोगकर्ता",
            "projects": "परियोजनाएं",
            "blogs": "ब्लॉग",
            "faq": "सामान्य प्रश्न",
            "team": "टीम",
            "gallery": "गैलरी",
            "career": "करियर",
            "news": "समाचार",
            "logout": "लॉग आउट",
            "project_management": "परियोजना प्रबंधन",
            "view_live_project": "लाइव प्रोजेक्ट देखें",

            // Featured Property Card Labels
            "lbl_parking": "पार्किंग",
            "sq_ft": "वर्ग फुट",
            "brochure_available": "ब्रोशर उपलब्ध है",
            "status_available": "उपलब्ध",
            "status_limited": "सीमित उपलब्ध",
            "status_sold": "बिक चुकी है",
            "status_coming": "जल्द आ रहा है",

            // Tabs & Headers
            "tab_floor_plans": "फ़्लोर प्लान",
            "tab_gallery": "गैलरी",
            "tab_office_pricing": "कार्यालय मूल्य निर्धारण",
            "tab_retail_pricing": "खुदरा मूल्य निर्धारण",
            "tab_featured_properties": "विशेष संपत्तियां",
            "header_floor_plans": "फ़्लोर प्लान प्रबंधन",
            "header_gallery": "गैलरी प्रबंधन",
            "header_office_pricing": "कार्यालय स्थान मूल्य निर्धारण",
            "header_retail_pricing": "खुदरा दुकानें मूल्य निर्धारण",
            "header_featured_properties": "विशेष संपत्ति प्रबंधन",
            "header_upload_images": "संपत्ति चित्र अपलोड करें",
            "header_brochure": "ब्रोशर दस्तावेज़",
            "header_upload_floor_plan": "फ़्लोर प्लान छवि अपलोड करें",
            "header_property_documents": "संपत्ति दस्तावेज",
            "header_property_cards": "संपत्ति कार्ड",
            "header_sale_inquiries": "यात्रा और पूछताछ अनुसूची - बिक्री के लिए",
            "header_rent_inquiries": "यात्रा और पूछताछ अनुसूची - किराए के लिए",

            // Sub-tabs & Sections
            "tab_for_sale": "बिक्री के लिए",
            "tab_for_rent": "किराए के लिए",
            "tab_schedule_visit": "यात्रा और पूछताछ अनुसूची",
            "tab_sale_inquiries": "बिक्री पूछताछ",
            "tab_rent_inquiries": "किराया पूछताछ",
            "current_office_pricing": "वर्तमान कार्यालय मूल्य",
            "current_retail_pricing": "वर्तमान खुदरा मूल्य",
            "property_amenities": "संपत्ति सुविधाएं",
            "floor_plans_dimensions": "फ़्लोर प्लान और आयाम",
            "location_details": "स्थान विवरण",
            "property_documents": "संपत्ति दस्तावेज",
            "key_features": "मुख्य विशेषताएं",
            "approvals_certificates": "अनुमोदन और प्रमाण पत्र",

            // Table Headers
            "th_rental_price": "किराया मूल्य",
            "th_features": "विशेषताएं",
            "th_last_updated": "अंतिम अद्यतन",
            "th_status": "स्थिति",
            "th_floor": "मंजिल",
            "th_image": "छवि",
            "th_office_size": "कार्यालय का आकार",
            "th_shops": "दुकानें",
            "th_elevators": "लिफ्ट",
            "th_total_floors": "कुल मंजिलें",
            "th_actions": "कार्रवाई",
            "th_date": "तारीख",
            "th_name": "नाम",
            "th_email": "ईमेल",
            "th_phone": "फ़ोन",
            "th_inquiry_type": "पूछताछ का प्रकार",
            "th_property": "संपत्ति",
            "th_message": "संदेश",

            // Buttons & Labels
            "save_changes": "परिवर्तन सहेजें",
            "clear_form": "फ़ॉर्म साफ़ करें",
            "browse_images": "चित्र ब्राउज़ करें",
            "browse_brochure": "ब्रोशर ब्राउज़ करें",
            "upload_images": "चित्र अपलोड करें",
            "rental_price": "किराया मूल्य",
            "sale_price": "बिक्री मूल्य",
            "features": "विशेषताएं",
            "no_images": "अभी तक कोई चित्र नहीं। ऊपर चित्र अपलोड करें।",
            "msg_no_properties": "अभी तक कोई संपत्ति नहीं जोड़ी गई",
            "msg_add_properties_instruction": "ऊपर दिए गए फ़ॉर्म का उपयोग करके संपत्ति जोड़ें",
            "drag_drop_images": "चित्र यहाँ खींचें और छोड़ें या ब्राउज़ करने के लिए क्लिक करें",
            "btn_add_feature": "विशेषता जोड़ें",
            "btn_save_office_pricing": "कार्यालय मूल्य सहेजें",
            "btn_save_retail_pricing": "खुदरा मूल्य सहेजें",
            "btn_add_amenity_section": "नई सुविधा अनुभाग जोड़ें",
            "btn_add_room": "एक और कमरा जोड़ें",
            "btn_browse_documents": "दस्तावेज़ ब्राउज़ करें",
            "btn_auto_translate_features": "विशेषताओं का स्वचालित अनुवाद करें",
            "btn_previous": "पिछला",
            "btn_next": "अगला",
            "btn_save_complete_property": "पूर्ण संपत्ति सहेजें",

            // Placeholders
            "lbl_office_size": "कार्यालय का आकार (वर्ग फुट)",
            "lbl_shops_per_floor": "प्रति मंजिल दुकानें",
            "lbl_elevators": "लिफ्ट",
            "lbl_total_floors": "ऊपर कुल मंजिलें",
            "lbl_floor_description": "मंजिल विवरण",
            "lbl_upload_floor_plan": "फ़्लोर प्लान छवि अपलोड करें",
            "lbl_click_to_upload": "फ़्लोर प्लान छवि अपलोड करने के लिए क्लिक करें",
            "lbl_property_images": "संपत्ति चित्र",
            "lbl_click_to_upload_images": "कई संपत्ति चित्र अपलोड करने के लिए क्लिक करें",
            "lbl_upload_brochure": "ब्रोशर दस्तावेज़ अपलोड करें",
            "lbl_click_to_upload_brochure": "संपत्ति ब्रोशर अपलोड करने के लिए क्लिक करें",
            "lbl_area": "क्षेत्र (वर्ग फुट)",
            "lbl_floor": "मंजिल",
            "lbl_facing": "मुख (Facing)",
            "lbl_room_dimensions": "कमरे के आयाम",
            "lbl_living_room": "बैठक कक्ष:",
            "lbl_master_bedroom": "मास्टर बेडरूम:",
            "lbl_bedroom_2": "बेडरूम 2:",
            "lbl_full_address": "पूरा पता",
            "lbl_upload_documents": "संपत्ति दस्तावेज अपलोड करें",
            "lbl_upload_approvals": "अनुमोदन और प्रमाण पत्र अपलोड करें",
            "lbl_click_to_upload_documents": "कई दस्तावेज़ अपलोड करने के लिए क्लिक करें",
            "lbl_click_to_upload_approvals": "कई अनुमोदन दस्तावेज़ अपलोड करने के लिए क्लिक करें"
        },
        gu: {
            // General & Sidebar
            "basic_info": "મૂળભૂત માહિતી",
            "property_title": "મિલકત શીર્ષક (ગુજરાતી)",
            "location": "સ્થળ (ગુજરાતી)",
            "property_type": "મિલકત પ્રકાર (ગુજરાતી)",
            "description": "વર્ણન (ગુજરાતી)",
            "detailed_description": "વિગતવાર વર્ણન (ગુજરાતી)",
            "auto_translate": "બધા ક્ષેત્રોનું સ્વચાલિત ભાષાંતર કરો",
            "translating": "ભાષાંતર થઈ રહ્યું છે...",
            "office_pricing_details": "ઓફિસ પ્રાઇસીંગ વિગતો",
            "retail_pricing_details": "છૂટક કિંમત વિગતો",
            "property_overview": "મિલકત ઝાંખી",
            "dashboard": "ડેશબોર્ડ",
            "users": "વપરાશકર્તાઓ",
            "projects": "પ્રોજેક્ટ્સ",
            "blogs": "બ્લોગ્સ",
            "faq": "સામાન્ય પ્રશ્નો",
            "team": "ટીમ",
            "gallery": "ગેલેરી",
            "career": "કારકિર્દી",
            "news": "સમાચાર",
            "logout": "લોગ આઉટ",
            "project_management": "પ્રોજેક્ટ મેનેજમેન્ટ",
            "view_live_project": "લાઇવ પ્રોજેક્ટ જુઓ",

            // Featured Property Card Labels
            "lbl_parking": "પાર્કિંગ",
            "sq_ft": "ચો.ફૂટ",
            "brochure_available": "બ્રોશર ઉપલબ્ધ છે",
            "status_available": "ઉપલબ્ધ",
            "status_limited": "મર્યાદિત ઉપલબ્ધ",
            "status_sold": "વેચાઈ ગયું",
            "status_coming": "ટૂંક સમયમાં આવી રહ્યું છે",

            // Tabs & Headers
            "tab_floor_plans": "ફ્લોર પ્લાન",
            "tab_gallery": "ગેલેરી",
            "tab_office_pricing": "ઓફિસ પ્રાઇસીંગ",
            "tab_retail_pricing": "રિટેલ પ્રાઇસીંગ",
            "tab_featured_properties": "ફીચર્ડ પ્રોપર્ટીઝ",
            "header_floor_plans": "ફ્લોર પ્લાન મેનેજમેન્ટ",
            "header_gallery": "ગેલેરી મેનેજમેન્ટ",
            "header_office_pricing": "ઓફિસ સ્પેસ પ્રાઇસીંગ",
            "header_retail_pricing": "રિટેલ શોપ્સ પ્રાઇસીંગ",
            "header_featured_properties": "ફીચર્ડ પ્રોપર્ટી મેનેજમેન્ટ",
            "header_upload_images": "મિલકત છબીઓ અપલોડ કરો",
            "header_brochure": "બ્રોશર દસ્તાવેજ",
            "header_upload_floor_plan": "ફ્લોર પ્લાન છબી અપલોડ કરો",
            "header_property_documents": "મિલકત દસ્તાવેજો",
            "header_property_cards": "મિલકત કાર્ડ્સ",
            "header_sale_inquiries": "મુલાકાત અને પૂછપરછ સુનિશ્ચિત કરો - વેચાણ માટે",
            "header_rent_inquiries": "મુલાકાત અને પૂછપરછ સુનિશ્ચિત કરો - ભાડે માટે",

            // Sub-tabs & Sections
            "tab_for_sale": "વેચાણ માટે",
            "tab_for_rent": "ભાડે",
            "tab_schedule_visit": "મુલાકાત અને પૂછપરછ સુનિશ્ચિત કરો",
            "tab_sale_inquiries": "વેચાણ પૂછપરછ",
            "tab_rent_inquiries": "ભાડા પૂછપરછ",
            "current_office_pricing": "વર્તમાન ઓફિસ કિંમત",
            "current_retail_pricing": "વર્તમાન છૂટક કિંમત",
            "property_amenities": "મિલકત સુવિધાઓ",
            "floor_plans_dimensions": "ફ્લોર પ્લાન અને પરિમાણો",
            "location_details": "સ્થાન વિગતો",
            "property_documents": "મિલકત દસ્તાવેજો",
            "key_features": "મુખ્ય વિશેષતાઓ",
            "approvals_certificates": "મંજૂરીઓ અને પ્રમાણપત્રો",

            // Table Headers
            "th_rental_price": "ભાડાની કિંમત",
            "th_features": "વિશેષતાઓ",
            "th_last_updated": "છેલ્લે અપડેટ",
            "th_status": "સ્થિતિ",
            "th_floor": "માળ",
            "th_image": "છબી",
            "th_office_size": "ઓફિસ માપ",
            "th_shops": "દુકાનો",
            "th_elevators": "એલિવેટર્સ",
            "th_total_floors": "કુલ માળ",
            "th_actions": "ક્રિયાઓ",
            "th_date": "તારીખ",
            "th_name": "નામ",
            "th_email": "ઇમેઇલ",
            "th_phone": "ફોન",
            "th_inquiry_type": "પૂછપરછનો પ્રકાર",
            "th_property": "મિલકત",
            "th_message": "સંદેશ",

            // Buttons & Labels
            "save_changes": "ફેરફારો સાચવો",
            "clear_form": "ફોર્મ સાફ કરો",
            "browse_images": "છબીઓ બ્રાઉઝ કરો",
            "browse_brochure": "બ્રોશર બ્રાઉઝ કરો",
            "upload_images": "છબીઓ અપલોડ કરો",
            "rental_price": "ભાડાની કિંમત",
            "sale_price": "વેચાણ કિંમત",
            "features": "વિશેષતાઓ",
            "no_images": "હજુ સુધી કોઈ છબીઓ નથી. ઉપર છબીઓ અપલોડ કરો.",
            "msg_no_properties": "હજુ સુધી કોઈ મિલકત ઉમેરવામાં આવી નથી",
            "msg_add_properties_instruction": "ઉપરના ફોર્મનો ઉપયોગ કરીને મિલકત ઉમેરો",
            "drag_drop_images": "અહીં છબીઓ ખેંચો અને છોડો અથવા બ્રાઉઝ કરવા માટે ક્લિક કરો",
            "btn_add_feature": "વિશેષતા ઉમેરો",
            "btn_save_office_pricing": "ઓફિસ કિંમત સાચવો",
            "btn_save_retail_pricing": "રિટેલ કિંમત સાચવો",
            "btn_add_amenity_section": "નવો સુવિધા વિભાગ ઉમેરો",
            "btn_add_room": "બીજો રૂમ ઉમેરો",
            "btn_browse_documents": "દસ્તાવેજો બ્રાઉઝ કરો",
            "btn_auto_translate_features": "વિશેષતાઓનું સ્વચાલિત ભાષાંતર કરો",
            "btn_previous": "પાછલું",
            "btn_next": "આગળ",
            "btn_save_complete_property": "પૂર્ણ મિલકત સાચવો",

            // Placeholders
            "lbl_office_size": "ઓફિસ માપ (ચો.ફૂટ)",
            "lbl_shops_per_floor": "માળ દીઠ દુકાનો",
            "lbl_elevators": "એલિવેટર્સ",
            "lbl_total_floors": "ઉપર કુલ માળ",
            "lbl_floor_description": "માળ વર્ણન",
            "lbl_upload_floor_plan": "ફ્લોર પ્લાન છબી અપલોડ કરો",
            "lbl_click_to_upload": "ફ્લોર પ્લાન છબી અપલોડ કરવા માટે ક્લિક કરો",
            "lbl_property_images": "મિલકત છબીઓ",
            "lbl_click_to_upload_images": "ઘણી મિલકત છબીઓ અપલોડ કરવા માટે ક્લિક કરો",
            "lbl_upload_brochure": "બ્રોશર દસ્તાવેજ અપલોડ કરો",
            "lbl_click_to_upload_brochure": "મિલકત બ્રોશર અપલોડ કરવા માટે ક્લિક કરો",
            "lbl_area": "વિસ્તાર (ચો.ફૂટ)",
            "lbl_floor": "માળ",
            "lbl_facing": "મુખ (Facing)",
            "lbl_room_dimensions": "રૂમના પરિમાણો",
            "lbl_living_room": "લિવિંગ રૂમ:",
            "lbl_master_bedroom": "માસ્ટર બેડરૂમ:",
            "lbl_bedroom_2": "બેડરૂમ 2:",
            "lbl_full_address": "પૂર્ણ સરનામું",
            "lbl_upload_documents": "મિલકત દસ્તાવેજો અપલોડ કરો",
            "lbl_upload_approvals": "મંજૂરીઓ અને પ્રમાણપત્રો અપલોડ કરો",
            "lbl_click_to_upload_documents": "ઘણા દસ્તાવેજો અપલોડ કરવા માટે ક્લિક કરો",
            "lbl_click_to_upload_approvals": "ઘણા મંજૂરી દસ્તાવેજો અપલોડ કરવા માટે ક્લિક કરો"
        }
    },

    // Initialize the translator
    init: function () {
        this.injectStyles();
        this.injectNavbarSelector();
        this.applyLanguage(this.currentLanguage);
        console.log('Global Translator Initialized');
    },

    // Inject CSS for the dropdown
    injectStyles: function () {
        const styleId = 'global-translator-styles';
        if (document.getElementById(styleId)) return;

        const style = document.createElement('style');
        style.id = styleId;
        style.textContent = `
            /* Language Selector Styles */
            :root {
                --primary-color: #4361ee;
                --text-dark: #0f172a;
            }
            .language-selector {
                position: relative;
                display: inline-block;
                margin-right: 20px;
            }
            .language-btn {
                background-color: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 8px 16px;
                display: flex;
                align-items: center;
                gap: 8px;
                cursor: pointer;
                font-weight: 500;
                color: #0f172a; /* Fallback for --text-dark */
                transition: all 0.3s ease;
                font-family: 'Inter', sans-serif;
            }
            .language-btn:hover {
                background-color: #f1f5f9;
                border-color: #cbd5e1;
            }
            .language-btn i.fa-globe {
                color: #4361ee; /* Fallback for --primary-color */
                font-size: 1.1rem;
            }
            .language-dropdown {
                position: absolute;
                top: 100%;
                right: 0;
                background-color: white;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                min-width: 150px;
                z-index: 1001;
                display: none;
                margin-top: 5px;
                overflow: hidden;
                border: 1px solid #e2e8f0;
            }
            .language-dropdown.active {
                display: block;
            }
            .language-option {
                padding: 12px 16px;
                display: flex;
                align-items: center;
                gap: 10px;
                cursor: pointer;
                transition: all 0.2s ease;
                color: #0f172a;
                text-decoration: none;
                font-size: 0.95rem;
            }
            .language-option:hover {
                background-color: #f8fafc;
                color: #4361ee;
            }
            .language-option.active {
                background-color: rgba(67, 97, 238, 0.1);
                color: #4361ee;
                font-weight: 600;
            }
            .language-option i {
                width: 20px;
                text-align: center;
            }
        `;
        document.head.appendChild(style);
    },

    // Inject the styled language selector into the navbar
    injectNavbarSelector: function () {
        const navbarRight = document.querySelector('.top-navbar-right');
        if (!navbarRight) return;

        // If a language selector already exists (like in projects.html), we hook into it
        let selectorContainer = navbarRight.querySelector('.language-selector');

        // Check if it's our manually injected one or hardcoded
        // If hardcoded, it usually has inner HTML already. If we created it, it might be stale.
        // We will reconstruct it if it doesn't have the structure we expect or if we need to enforce global style.

        if (!selectorContainer) {
            // Create it if it doesn't exist
            selectorContainer = document.createElement('div');
            selectorContainer.className = 'language-selector global-lang-selector';

            // Insert before the user profile or at the beginning
            const userProfile = navbarRight.querySelector('.user-profile');
            if (userProfile) {
                navbarRight.insertBefore(selectorContainer, userProfile);
            } else {
                navbarRight.appendChild(selectorContainer);
            }
        }

        // Render the inner HTML (ensuring consistency)
        selectorContainer.innerHTML = `
            <button class="language-btn" id="global-language-btn">
                <i class="fas fa-globe"></i>
                <span id="global-current-lang-text">English</span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="language-dropdown" id="global-language-dropdown">
                <a href="#" class="language-option" data-lang="en">
                    <i class="fas fa-language"></i>
                    <span>English</span>
                </a>
                <a href="#" class="language-option" data-lang="hi">
                    <i class="fas fa-language"></i>
                    <span>हिन्दी (Hindi)</span>
                </a>
                <a href="#" class="language-option" data-lang="gu">
                    <i class="fas fa-language"></i>
                    <span>ગુજરાતી (Gujarati)</span>
                </a>
            </div>
        `;

        // Attach Event Listeners
        const btn = selectorContainer.querySelector('#global-language-btn');
        const dropdown = selectorContainer.querySelector('#global-language-dropdown');
        const options = selectorContainer.querySelectorAll('.language-option');

        if (btn && dropdown) {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                // Close other dropdowns if any
                document.querySelectorAll('.language-dropdown').forEach(d => {
                    if (d !== dropdown) d.classList.remove('active');
                });
                dropdown.classList.toggle('active');
            });

            // Close when clicking outside
            document.addEventListener('click', (e) => {
                if (!selectorContainer.contains(e.target)) {
                    dropdown.classList.remove('active');
                }
            });

            // Option selection
            options.forEach(option => {
                option.addEventListener('click', (e) => {
                    e.preventDefault();
                    const lang = option.getAttribute('data-lang');
                    this.setLanguage(lang);
                    dropdown.classList.remove('active');
                });
            });
        }
    },

    // Set the current language
    setLanguage: function (lang) {
        if (!this.languages.includes(lang)) return;

        this.currentLanguage = lang;
        localStorage.setItem('preferred-language', lang);

        // Update UI
        this.updateSelectorUI(lang);
        this.updateStaticContent(lang);

        // Dispatch Event
        const event = new CustomEvent('languageChanged', { detail: { language: lang } });
        window.dispatchEvent(event);
    },

    // Update the selector UI (active state, text)
    updateSelectorUI: function (lang) {
        const langNames = {
            en: 'English',
            hi: 'हिन्दी (Hindi)',
            gu: 'ગુજરાતી (Gujarati)'
        };
        const langShortNames = {
            en: 'English',
            hi: 'हिन्दी',
            gu: 'ગુજરાતી'
        }

        // Update button text
        const textSpan = document.getElementById('global-current-lang-text');
        if (textSpan) {
            textSpan.textContent = langShortNames[lang] || 'English';
        }

        // Update active class in dropdown
        document.querySelectorAll('.language-option').forEach(opt => {
            opt.classList.toggle('active', opt.getAttribute('data-lang') === lang);
        });
    },

    // Apply translations to static content
    applyLanguage: function (lang) {
        this.updateSelectorUI(lang);
        this.updateStaticContent(lang);
    },

    updateStaticContent: function (lang) {
        const dictionary = this.translations[lang] || this.translations['en'];

        // Update data-i18n elements
        document.querySelectorAll('[data-i18n]').forEach(element => {
            const key = element.getAttribute('data-i18n');
            if (dictionary[key]) {
                if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                    if (element.placeholder) {
                        // Optional: translate placeholder if needed
                    }
                } else {
                    element.textContent = dictionary[key];
                }
            }
        });

        console.log(`Language switched to ${lang}`);
    },

    // API Translation (LibreTranslate & MyMemory)
    translateText: async function (text, sourceLang, targetLang) {
        if (!text) return "";

        // Try LibreTranslate (free and reliable)
        try {
            const libreResponse = await fetch(
                "https://libretranslate.de/translate",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        q: text,
                        source: sourceLang,
                        target: targetLang,
                        format: "text",
                    }),
                }
            );

            if (libreResponse.ok) {
                const data = await libreResponse.json();
                if (data.translatedText) {
                    return data.translatedText;
                }
            }
        } catch (error) {
            console.warn("LibreTranslate failed, trying next option", error);
        }

        // Try MyMemory API
        try {
            const myMemoryResponse = await fetch(
                `https://api.mymemory.translated.net/get?q=${encodeURIComponent(
                    text
                )}&langpair=${sourceLang}|${targetLang}`
            );

            if (myMemoryResponse.ok) {
                const data = await myMemoryResponse.json();
                if (data.responseData && data.responseData.translatedText) {
                    let translated = data.responseData.translatedText;
                    // Clean up common issues
                    translated = translated.replace(/\uFFFD/g, "");
                    translated = translated.replace(/&#39;/g, "'");
                    translated = translated.replace(/&quot;/g, '"');
                    return translated;
                }
            }
        } catch (error) {
            console.warn("MyMemory failed, trying Google Translate fallback", error);
        }

        console.error("All translation services failed for:", text);
        return text; // Return original if all fails
    },

    // Auto-translate form fields
    autoTranslateForm: async function (fieldMappings) {
        const originalBtnText = {}; // Store original text if needed

        for (const mapping of fieldMappings) {
            const sourceEl = document.getElementById(mapping.source);
            const targetHi = document.getElementById(mapping.targetHi);
            const targetGu = document.getElementById(mapping.targetGu);

            if (sourceEl && sourceEl.value) {
                const text = sourceEl.value;

                // Translate to Hindi
                if (targetHi && !targetHi.value) {
                    targetHi.value = await this.translateText(text, 'en', 'hi');
                }

                // Translate to Gujarati
                if (targetGu && !targetGu.value) {
                    targetGu.value = await this.translateText(text, 'en', 'gu');
                }
            }
        }
        alert('Auto-translation complete! Please review the fields.');
    }
};

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    Translator.init();
});
