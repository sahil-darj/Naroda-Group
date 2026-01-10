/**
 * FAQ Management - Full CRUD Integration
 * Handles loading, creating, updating, and deleting FAQs via API
 */

const FAQManager = {
    // Use lowercase path to match server routing (case-insensitive on Windows, but consistent)
    baseUrl: window.location.origin + '/ng/api',
    currentEditId: null,
    faqList: [],
    isTranslating: false,

    /**
     * Load all FAQs from the database
     */
    async loadFAQs() {
        try {
            const url = `${this.baseUrl}/faq_api.php`;
            console.log('Loading FAQs from:', url);

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response:', text.substring(0, 200));
                throw new Error('Server returned non-JSON response. Check PHP configuration.');
            }

            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status}`);
            }

            const result = await response.json();

            if (result.success) {
                this.faqList = result.data || [];
                this.renderFAQs();
                this.updateCategoryCards();
                return result.data;
            } else {
                this.showError(result.error || 'Failed to load FAQs');
                return [];
            }
        } catch (error) {
            console.error('Error loading FAQs:', error);
            this.showError('Error loading FAQs: ' + error.message);
            return [];
        }
    },

    /**
     * Update category cards with FAQ counts
     */
    updateCategoryCards() {
        const categories = {};
        this.faqList.forEach(faq => {
            const cat = faq.category || 'general';
            categories[cat] = (categories[cat] || 0) + 1;
        });

        // Update category count badges if they exist
        document.querySelectorAll('.category-card').forEach(card => {
            const catKey = card.dataset.category;
            if (catKey) {
                const countEl = card.querySelector('.category-count');
                if (countEl) {
                    countEl.textContent = `${categories[catKey] || 0} FAQs`;
                }
            }
        });
    },

    /**
     * Render FAQs in the table
     */
    renderFAQs() {
        const container = document.getElementById('faq-table-body');
        if (!container) {
            console.warn('FAQ table body not found');
            return;
        }

        if (!this.faqList || this.faqList.length === 0) {
            container.innerHTML = '<tr><td colspan="6" class="text-center py-4">No FAQs found</td></tr>';
            return;
        }

        container.innerHTML = this.faqList.map(faq => {
            const question = faq.question || '';
            const displayQuestion = question.length > 50 ? question.substring(0, 50) + '...' : question;

            return `
            <tr>
                <td>
                    <input type="checkbox" class="faq-checkbox" data-id="${faq.id}">
                </td>
                <td><strong>${this.escapeHtml(displayQuestion)}</strong></td>
                <td>${this.escapeHtml(faq.category || 'general')}</td>
                <td>
                    ${faq.question_hi ? '<span class="badge bg-info me-1">HI</span>' : ''}
                    ${faq.question_gu ? '<span class="badge bg-info">GU</span>' : ''}
                </td>
                <td>
                    <span class="badge ${faq.status === 'active' ? 'bg-success' : 'bg-secondary'}">
                        ${faq.status || 'active'}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-primary me-1" onclick="FAQManager.editFAQ(${faq.id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="FAQManager.deleteFAQ(${faq.id})" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `}).join('');
    },

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    /**
     * Open the FAQ modal for create/edit
     */
    openModal(id = null) {
        this.currentEditId = id;
        const modalEl = document.getElementById('faqModal');

        if (!modalEl) {
            console.error('FAQ Modal not found');
            return;
        }

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

        // Reset form first
        const form = document.getElementById('faqForm');
        if (form) form.reset();

        if (id) {
            // Edit mode - populate form with existing data
            const faq = this.faqList.find(f => f.id == id);
            if (faq) {
                this.setFormValue('faqQuestion', faq.question);
                this.setFormValue('faqQuestionHi', faq.question_hi);
                this.setFormValue('faqQuestionGu', faq.question_gu);
                this.setFormValue('faqAnswer', faq.answer);
                this.setFormValue('faqAnswerHi', faq.answer_hi);
                this.setFormValue('faqAnswerGu', faq.answer_gu);
                this.setFormValue('faqCategory', faq.category || 'general');
                this.setFormValue('faqStatus', faq.status || 'active');
                this.setFormValue('faqTags', faq.tags);
                this.setFormValue('faqOrder', faq.display_order || 1);
            }
        }

        modal.show();
    },

    /**
     * Helper to safely set form field values
     */
    setFormValue(elementId, value) {
        const el = document.getElementById(elementId);
        if (el) el.value = value || '';
    },

    /**
     * Save FAQ (create or update)
     */
    async saveFAQ() {
        const data = {
            question: document.getElementById('faqQuestion')?.value || '',
            question_hi: document.getElementById('faqQuestionHi')?.value || '',
            question_gu: document.getElementById('faqQuestionGu')?.value || '',
            answer: document.getElementById('faqAnswer')?.value || '',
            answer_hi: document.getElementById('faqAnswerHi')?.value || '',
            answer_gu: document.getElementById('faqAnswerGu')?.value || '',
            category: document.getElementById('faqCategory')?.value || 'general',
            status: document.getElementById('faqStatus')?.value || 'active',
            tags: document.getElementById('faqTags')?.value || '',
            order: parseInt(document.getElementById('faqOrder')?.value || 1)
        };

        // Validation
        if (!data.question.trim() || !data.answer.trim()) {
            this.showError('Question and Answer are required');
            return;
        }

        try {
            let url = `${this.baseUrl}/faq_api.php`;
            let method = 'POST';

            if (this.currentEditId) {
                url += `?id=${this.currentEditId}`;
                method = 'PUT';
                data.id = this.currentEditId;
            }

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            // Check for JSON response
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response:', text.substring(0, 200));
                throw new Error('Server returned non-JSON response');
            }

            const result = await response.json();

            if (result.success) {
                this.showSuccess(result.message || 'FAQ saved successfully');

                // Close modal
                const modalEl = document.getElementById('faqModal');
                if (modalEl) {
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();
                }

                // Reload FAQs
                this.loadFAQs();
            } else {
                this.showError(result.error || 'Failed to save FAQ');
            }
        } catch (error) {
            console.error('Error saving FAQ:', error);
            this.showError('Error saving FAQ: ' + error.message);
        }
    },

    /**
     * Edit an FAQ
     */
    editFAQ(id) {
        this.openModal(id);
    },

    /**
     * Delete an FAQ
     */
    async deleteFAQ(id) {
        if (!confirm('Are you sure you want to delete this FAQ?')) return;

        try {
            const response = await fetch(`${this.baseUrl}/faq_api.php?id=${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response:', text.substring(0, 200));
                throw new Error('Server returned non-JSON response');
            }

            const result = await response.json();

            if (result.success) {
                this.showSuccess(result.message || 'FAQ deleted successfully');
                this.loadFAQs();
            } else {
                this.showError(result.error || 'Failed to delete FAQ');
            }
        } catch (error) {
            console.error('Error deleting FAQ:', error);
            this.showError('Error deleting FAQ: ' + error.message);
        }
    },

    /**
     * Auto-translate FAQ to Hindi & Gujarati using Google Translate API
     */
    async autoTranslateFAQ() {
        if (this.isTranslating) return;

        const question = document.getElementById('faqQuestion')?.value;
        const answer = document.getElementById('faqAnswer')?.value;

        if (!question || !answer) {
            this.showError('Please fill English Question and Answer first');
            return;
        }

        this.isTranslating = true;

        try {
            // Translate to Hindi
            const [questionHi, answerHi] = await Promise.all([
                this.translateText(question, 'hi'),
                this.translateText(answer, 'hi')
            ]);

            // Translate to Gujarati
            const [questionGu, answerGu] = await Promise.all([
                this.translateText(question, 'gu'),
                this.translateText(answer, 'gu')
            ]);

            // Set translated values
            this.setFormValue('faqQuestionHi', questionHi);
            this.setFormValue('faqAnswerHi', answerHi);
            this.setFormValue('faqQuestionGu', questionGu);
            this.setFormValue('faqAnswerGu', answerGu);

            this.showSuccess('Auto-translation completed!');
        } catch (error) {
            console.error('Translation error:', error);
            this.showError('Translation failed. Please enter translations manually.');
        } finally {
            this.isTranslating = false;
        }
    },

    /**
     * Translate text using Google Translate GTX API
     */
    async translateText(text, targetLang) {
        try {
            const url = `https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=${targetLang}&dt=t&q=${encodeURIComponent(text)}`;
            const response = await fetch(url);
            const data = await response.json();

            // Extract translated text from response
            let translated = '';
            if (data && data[0]) {
                data[0].forEach(segment => {
                    if (segment[0]) translated += segment[0];
                });
            }
            return translated || text;
        } catch (error) {
            console.error('Translation API error:', error);
            return text;
        }
    },

    /**
     * Search FAQs
     */
    searchFAQs(query) {
        if (!query || !query.trim()) {
            this.renderFAQs();
            return;
        }

        const searchTerm = query.toLowerCase().trim();
        const filtered = this.faqList.filter(faq =>
            (faq.question && faq.question.toLowerCase().includes(searchTerm)) ||
            (faq.answer && faq.answer.toLowerCase().includes(searchTerm)) ||
            (faq.category && faq.category.toLowerCase().includes(searchTerm)) ||
            (faq.tags && faq.tags.toLowerCase().includes(searchTerm))
        );

        const container = document.getElementById('faq-table-body');
        if (!container) return;

        if (filtered.length === 0) {
            container.innerHTML = '<tr><td colspan="6" class="text-center py-4">No FAQs match your search</td></tr>';
            return;
        }

        // Temporarily replace faqList for rendering
        const originalList = this.faqList;
        this.faqList = filtered;
        this.renderFAQs();
        this.faqList = originalList;
    },

    /**
     * Filter FAQs by category
     */
    filterByCategory(category) {
        if (!category || category === 'all') {
            this.renderFAQs();
            return;
        }

        const filtered = this.faqList.filter(faq => faq.category === category);

        const container = document.getElementById('faq-table-body');
        if (!container) return;

        if (filtered.length === 0) {
            container.innerHTML = '<tr><td colspan="6" class="text-center py-4">No FAQs in this category</td></tr>';
            return;
        }

        const originalList = this.faqList;
        this.faqList = filtered;
        this.renderFAQs();
        this.faqList = originalList;
    },

    /**
     * Show success message
     */
    showSuccess(message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
        alert.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 3000);
    },

    /**
     * Show error message
     */
    showError(message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show position-fixed';
        alert.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.innerHTML = `
            <i class="fas fa-exclamation-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    console.log('FAQManager initializing...');

    // Load FAQs
    FAQManager.loadFAQs();

    // Setup form submit handler
    const form = document.getElementById('faqForm');
    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            FAQManager.saveFAQ();
        });
    }

    // Setup search input
    const searchInput = document.getElementById('faqSearch');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                FAQManager.searchFAQs(e.target.value);
            }, 300);
        });
    }

    // Auto-translate button
    const autoTranslateBtn = document.getElementById('faqAutoTranslate');
    if (autoTranslateBtn) {
        autoTranslateBtn.addEventListener('click', () => {
            FAQManager.autoTranslateFAQ();
        });
    }

    // Category card click handlers
    document.querySelectorAll('.category-card').forEach(card => {
        card.addEventListener('click', () => {
            const category = card.dataset.category;
            if (category) {
                // Update active state
                document.querySelectorAll('.category-card').forEach(c => c.classList.remove('active'));
                card.classList.add('active');
                FAQManager.filterByCategory(category);
            }
        });
    });

    // Add FAQ button
    const addFaqBtn = document.getElementById('addFaqBtn');
    if (addFaqBtn) {
        addFaqBtn.addEventListener('click', () => {
            FAQManager.openModal();
        });
    }

    console.log('FAQManager initialized successfully');
});
