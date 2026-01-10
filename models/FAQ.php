<?php
require_once '../config/database.php';

class FAQ {
    private $conn;
    private $table_faqs = 'faqs';
    private $table_translations = 'faq_translations';
    private $table_categories = 'faq_categories';
    private $table_languages = 'translation_languages';
    private $table_translation_strings = 'translation_strings';
    private $table_ui_translations = 'translations';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Get translations from database
    public function getUITranslations($language = 'en') {
        $query = "SELECT ts.string_key, t.translated_text
                  FROM {$this->table_translation_strings} ts
                  LEFT JOIN {$this->table_ui_translations} t ON ts.id = t.string_id AND t.language_code = :language
                  WHERE ts.string_key LIKE 'faq.%' 
                  OR ts.string_key IN ('faq_management', 'multilingual', 'add_new_faq', 'refresh', 'all_faq_items', 
                                       'english', 'hindi', 'gujarati', 'search_questions_placeholder', 'hash', 
                                       'question', 'category', 'status', 'translations', 'last_updated', 'actions',
                                       'showing', 'to', 'of', 'faq_items', 'previous', 'next', 'select_category',
                                       'active', 'inactive', 'pending_review', 'answer', 'english_small',
                                       'enter_question_english', 'enter_answer_english', 'display_order',
                                       'tags', 'tags_placeholder', 'tags_example', 'automatic_translation',
                                       'automatic_translation_description', 'cancel', 'save_faq', 'auto_translate',
                                       'auto_translated_hint', 'auto_translated_content_hint', 'view_translations',
                                       'confirm_delete', 'confirm_logout', 'logout_success', 'translation_complete',
                                       'faq_added', 'faq_updated', 'faq_deleted', 'fill_english_first',
                                       'no_faq_found', 'translation_failed', 'dashboard', 'users', 'blogs',
                                       'team', 'gallery', 'career', 'news', 'log_out', 'admin_name',
                                       'system_administrator', 'naroda_group')";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['language' => $language]);
            $results = $stmt->fetchAll();
            
            $translations = [];
            foreach ($results as $row) {
                $translations[$row['string_key']] = $row['translated_text'] ?: $row['string_key'];
            }
            
            return $translations;
        } catch(PDOException $e) {
            error_log("Translation error: " . $e->getMessage());
            return [];
        }
    }

    // Get FAQ with all translations
    public function getFAQWithAllTranslations($id) {
        $query = "SELECT f.*,
                    MAX(CASE WHEN ft.language_code = 'en' THEN ft.question END) as question_en,
                    MAX(CASE WHEN ft.language_code = 'hi' THEN ft.question END) as question_hi,
                    MAX(CASE WHEN ft.language_code = 'gu' THEN ft.question END) as question_gu,
                    MAX(CASE WHEN ft.language_code = 'en' THEN ft.answer END) as answer_en,
                    MAX(CASE WHEN ft.language_code = 'hi' THEN ft.answer END) as answer_hi,
                    MAX(CASE WHEN ft.language_code = 'gu' THEN ft.answer END) as answer_gu,
                    GROUP_CONCAT(DISTINCT ft.language_code) as available_languages
                  FROM {$this->table_faqs} f
                  LEFT JOIN {$this->table_translations} ft ON f.id = ft.faq_id
                  WHERE f.id = :id
                  GROUP BY f.id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch();
            
            if ($result) {
                $result['translations'] = [];
                $availableLanguages = explode(',', $result['available_languages'] ?: '');
                
                foreach (['en', 'hi', 'gu'] as $lang) {
                    $result['translations'][$lang] = [
                        'has_translation' => in_array($lang, $availableLanguages),
                        'question' => $result["question_{$lang}"] ?? '',
                        'answer' => $result["answer_{$lang}"] ?? ''
                    ];
                }
                
                // Remove individual language columns
                unset($result['question_en'], $result['question_hi'], $result['question_gu'],
                      $result['answer_en'], $result['answer_hi'], $result['answer_gu'],
                      $result['available_languages']);
            }
            
            return $result;
        } catch(PDOException $e) {
            error_log("Error getting FAQ translations: " . $e->getMessage());
            return null;
        }
    }

    // Save FAQ with translations
    public function saveFAQWithTranslations($data) {
        try {
            $this->conn->beginTransaction();
            
            if (isset($data['id']) && $data['id']) {
                // Update existing FAQ
                $faqQuery = "UPDATE {$this->table_faqs} 
                            SET category_key = :category, 
                                display_order = :display_order,
                                status = :status,
                                tags = :tags,
                                updated_at = CURRENT_TIMESTAMP
                            WHERE id = :id";
                $faqParams = [
                    'category' => $data['category'],
                    'display_order' => $data['order'],
                    'status' => $data['status'],
                    'tags' => $data['tags'],
                    'id' => $data['id']
                ];
                $faqId = $data['id'];
            } else {
                // Insert new FAQ
                $faqQuery = "INSERT INTO {$this->table_faqs} 
                            (category_key, display_order, status, tags, created_by) 
                            VALUES (:category, :display_order, :status, :tags, :created_by)";
                $faqParams = [
                    'category' => $data['category'],
                    'display_order' => $data['order'],
                    'status' => $data['status'],
                    'tags' => $data['tags'],
                    'created_by' => $data['created_by'] ?? 1 // Default to admin user
                ];
            }
            
            $stmt = $this->conn->prepare($faqQuery);
            $stmt->execute($faqParams);
            
            if (!isset($faqId)) {
                $faqId = $this->conn->lastInsertId();
            }
            
            // Save translations
            $transQuery = "INSERT INTO {$this->table_translations} 
                          (faq_id, language_code, question, answer) 
                          VALUES (:faq_id, :language_code, :question, :answer)
                          ON DUPLICATE KEY UPDATE 
                          question = VALUES(question),
                          answer = VALUES(answer)";
            
            $stmt = $this->conn->prepare($transQuery);
            
            foreach (['en', 'hi', 'gu'] as $lang) {
                $question = $data["question_{$lang}"] ?? '';
                $answer = $data["answer_{$lang}"] ?? '';
                
                if ($question || $answer) {
                    $stmt->execute([
                        'faq_id' => $faqId,
                        'language_code' => $lang,
                        'question' => $question,
                        'answer' => $answer
                    ]);
                }
            }
            
            $this->conn->commit();
            return ['success' => true, 'id' => $faqId];
            
        } catch(PDOException $e) {
            $this->conn->rollBack();
            error_log("Error saving FAQ: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Get all FAQs for a specific language
    public function getFAQsByLanguage($language = 'en', $category = null, $search = null, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT SQL_CALC_FOUND_ROWS 
                    f.id, f.category_key, f.display_order, f.status, f.tags, 
                    f.created_at, f.updated_at,
                    ft.question, ft.answer,
                    c.icon, c.color,
                    GROUP_CONCAT(DISTINCT ft2.language_code) as available_translations
                  FROM {$this->table_faqs} f
                  INNER JOIN {$this->table_translations} ft ON f.id = ft.faq_id AND ft.language_code = :language
                  LEFT JOIN {$this->table_translations} ft2 ON f.id = ft2.faq_id
                  LEFT JOIN {$this->table_categories} c ON f.category_key = c.category_key
                  WHERE f.status = 'active'";
        
        $params = ['language' => $language];
        
        if ($category && $category !== 'all') {
            $query .= " AND f.category_key = :category";
            $params['category'] = $category;
        }
        
        if ($search) {
            $query .= " AND (ft.question LIKE :search OR ft.answer LIKE :search OR f.tags LIKE :search)";
            $params['search'] = "%{$search}%";
        }
        
        $query .= " GROUP BY f.id, ft.question, ft.answer, c.icon, c.color
                    ORDER BY f.display_order ASC, f.id DESC
                    LIMIT :offset, :limit";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':language', $language, PDO::PARAM_STR);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            
            if ($category && $category !== 'all') {
                $stmt->bindValue(':category', $category, PDO::PARAM_STR);
            }
            
            if ($search) {
                $stmt->bindValue(':search', "%{$search}%", PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $faqs = $stmt->fetchAll();
            
            // Get total count
            $totalStmt = $this->conn->query("SELECT FOUND_ROWS() as total");
            $totalResult = $totalStmt->fetch();
            $total = $totalResult['total'];
            
            // Process available translations
            foreach ($faqs as &$faq) {
                $available = explode(',', $faq['available_translations'] ?: '');
                $faq['translations'] = [
                    'hi' => in_array('hi', $available),
                    'gu' => in_array('gu', $available)
                ];
                unset($faq['available_translations']);
            }
            
            return [
                'faqs' => $faqs,
                'total' => $total,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => ceil($total / $perPage)
            ];
            
        } catch(PDOException $e) {
            error_log("Error getting FAQs: " . $e->getMessage());
            return [
                'faqs' => [],
                'total' => 0,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => 0
            ];
        }
    }

    // Get categories with counts
    public function getCategoriesWithCounts($language = 'en') {
        $query = "SELECT c.*, 
                    COALESCE(COUNT(DISTINCT f.id), 0) as faq_count,
                    MAX(t.translated_text) as category_name
                  FROM {$this->table_categories} c
                  LEFT JOIN {$this->table_faqs} f ON c.category_key = f.category_key AND f.status = 'active'
                  LEFT JOIN {$this->table_translation_strings} ts ON ts.string_key = CONCAT('faq.category', 
                      CASE c.category_key
                          WHEN 'projects' THEN 1
                          WHEN 'booking' THEN 2
                          WHEN 'payments' THEN 3
                          WHEN 'legal' THEN 4
                          WHEN 'amenities' THEN 5
                          WHEN 'location' THEN 6
                          ELSE 1
                      END, '.title')
                  LEFT JOIN {$this->table_ui_translations} t ON ts.id = t.string_id AND t.language_code = :language
                  GROUP BY c.id, c.category_key, c.icon, c.color, c.display_order
                  ORDER BY c.display_order ASC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['language' => $language]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Error getting categories: " . $e->getMessage());
            return [];
        }
    }

    // Delete FAQ
    public function deleteFAQ($id) {
        try {
            $this->conn->beginTransaction();
            
            // Delete translations first (due to foreign key constraint)
            $deleteTransQuery = "DELETE FROM {$this->table_translations} WHERE faq_id = :id";
            $stmt = $this->conn->prepare($deleteTransQuery);
            $stmt->execute(['id' => $id]);
            
            // Delete FAQ
            $deleteFaqQuery = "DELETE FROM {$this->table_faqs} WHERE id = :id";
            $stmt = $this->conn->prepare($deleteFaqQuery);
            $stmt->execute(['id' => $id]);
            
            $this->conn->commit();
            return ['success' => true];
            
        } catch(PDOException $e) {
            $this->conn->rollBack();
            error_log("Error deleting FAQ: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Get translation status for an FAQ
    public function getFAQTranslationStatus($id) {
        $query = "SELECT 
                    MAX(CASE WHEN language_code = 'hi' THEN 1 ELSE 0 END) as has_hindi,
                    MAX(CASE WHEN language_code = 'gu' THEN 1 ELSE 0 END) as has_gujarati
                  FROM {$this->table_translations}
                  WHERE faq_id = :id AND (question IS NOT NULL AND question != '')";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch();
            
            return [
                'hi' => (bool)($result['has_hindi'] ?? false),
                'gu' => (bool)($result['has_gujarati'] ?? false)
            ];
        } catch(PDOException $e) {
            error_log("Error getting translation status: " . $e->getMessage());
            return ['hi' => false, 'gu' => false];
        }
    }
}
?>