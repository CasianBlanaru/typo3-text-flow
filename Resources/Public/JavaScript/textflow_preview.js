/**
 * TextFlow Preview for TYPO3 Backend
 * 
 * This script provides a live preview of text hyphenation in the TYPO3 backend
 * for content editors to visualize the effect of TextFlow before publishing.
 */
(function() {
    'use strict';
    
    // Configuration
    const config = {
        previewContainerId: 'textflow-preview-container',
        textareaSelectors: '.t3js-form-field-text, textarea[name*="bodytext"]',
        refreshDelay: 300, // ms
        apiUrl: TYPO3.settings.ajaxUrls['tx_textflow_preview'] || null,
        languages: {
            'de': 'Deutsch',
            'en': 'English',
            'fr': 'Français',
            'es': 'Español',
            'it': 'Italiano',
            'nl': 'Nederlands',
            'pt': 'Português',
            'zh': '中文',
            'ar': 'العربية',
            'hi': 'हिन्दी'
        }
    };
    
    let previewTimer = null;
    let currentLanguage = 'de';
    
    /**
     * Initialize the preview functionality
     */
    function init() {
        // Check if we're in the TYPO3 backend
        if (typeof TYPO3 === 'undefined') {
            return;
        }
        
        // Find the textarea to monitor
        const textareas = document.querySelectorAll(config.textareaSelectors);
        if (textareas.length === 0) {
            return;
        }
        
        // Create preview container if not exists
        initPreviewContainer(textareas[0]);
        
        // Add event listeners to all textareas
        textareas.forEach(textarea => {
            textarea.addEventListener('input', () => {
                // Debounce the preview update
                clearTimeout(previewTimer);
                previewTimer = setTimeout(() => updatePreview(textarea.value), config.refreshDelay);
            });
            
            // Initial preview
            updatePreview(textarea.value);
        });
    }
    
    /**
     * Initialize the preview container
     */
    function initPreviewContainer(targetElement) {
        if (document.getElementById(config.previewContainerId)) {
            return;
        }
        
        // Create container
        const container = document.createElement('div');
        container.id = config.previewContainerId;
        container.className = 'textflow-preview-container panel panel-default';
        
        // Add header with language selector
        const header = document.createElement('div');
        header.className = 'panel-heading';
        
        const title = document.createElement('div');
        title.className = 'form-inline';
        title.innerHTML = `
            <strong>TextFlow Preview</strong>
            <div class="form-group t3js-formengine-field-item" style="margin-left: 10px;">
                <label class="t3js-formengine-label" style="margin-right: 5px;">
                    Language:
                </label>
                <select id="textflow-language-selector" class="form-control t3js-formengine-input">
                    ${Object.entries(config.languages).map(([code, name]) => 
                        `<option value="${code}" ${code === currentLanguage ? 'selected' : ''}>${name}</option>`
                    ).join('')}
                </select>
            </div>
        `;
        
        // Add content area
        const content = document.createElement('div');
        content.className = 'panel-body';
        content.innerHTML = `
            <div class="textflow-preview-content t3js-formengine-field-item">
                <p class="textflow-preview-text"></p>
            </div>
            <div class="textflow-legend">
                <small>
                    <span class="btn btn-sm btn-default">Soft hyphens</span> are shown in <span class="text-warning">yellow</span>
                </small>
            </div>
        `;
        
        // Assemble container
        container.appendChild(header);
        header.appendChild(title);
        container.appendChild(content);
        
        // Insert after the target element
        targetElement.parentNode.parentNode.appendChild(container);
        
        // Add event listener for language selector
        document.getElementById('textflow-language-selector').addEventListener('change', function() {
            currentLanguage = this.value;
            // Get the latest content
            const textareas = document.querySelectorAll(config.textareaSelectors);
            if (textareas.length > 0) {
                updatePreview(textareas[0].value);
            }
        });
        
        // Add CSS styles
        const style = document.createElement('style');
        style.textContent = `
            .textflow-preview-container {
                margin-top: 20px;
                border: 1px solid #ccc;
            }
            .textflow-preview-content {
                background-color: #f8f8f8;
                padding: 10px;
                min-height: 100px;
                border: 1px solid #eee;
            }
            .textflow-preview-text {
                line-height: 1.6;
                font-size: 14px;
            }
            .textflow-soft-hyphen {
                background-color: #ffeeba;
                color: #000;
                font-weight: bold;
                padding: 0 2px;
                border-radius: 2px;
            }
        `;
        document.head.appendChild(style);
    }
    
    /**
     * Update the preview with hyphenated text
     */
    function updatePreview(text) {
        if (!text || !config.apiUrl) {
            updatePreviewContent('No content to preview');
            return;
        }
        
        // Send to server for processing
        fetch(config.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                text: text,
                language: currentLanguage
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Visualize soft hyphens
                const visualizedText = data.result.replace(/\u00AD/g, 
                    '<span class="textflow-soft-hyphen">&#x00AD;</span>');
                updatePreviewContent(visualizedText);
            } else {
                updatePreviewContent('Error: ' + (data.message || 'Failed to process text'));
            }
        })
        .catch(error => {
            console.error('TextFlow preview error:', error);
            updatePreviewContent('Error processing preview');
        });
    }
    
    /**
     * Update the preview content
     */
    function updatePreviewContent(html) {
        const previewText = document.querySelector('.textflow-preview-text');
        if (previewText) {
            previewText.innerHTML = html;
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
