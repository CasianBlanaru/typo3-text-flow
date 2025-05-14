/**
 * TextFlow - Advanced Hyphenation and Text Optimization
 * 
 * This script provides client-side hyphenation and text optimization
 * for dynamic content that might be loaded after the initial page load.
 */
(function() {
    'use strict';

    // Configuration
    const config = {
        enableDynamicHyphenation: true,
        minWordLength: 5,
        observeContentChanges: true,
        hyphenChar: '\u00AD', // Soft hyphen character
        selectors: [
            '.text-flow-content',
            '.ce-text',
            '.ce-bodytext',
            '.ce-textpic',
            '.textmedia'
        ]
    };
    
    // Cache for already processed words
    const wordCache = new Map();
    
    // Patterns loaded from server (will be populated via AJAX)
    let patterns = {};
    let currentLanguage = document.documentElement.lang || 'de';
    
    /**
     * Initialize TextFlow
     */
    function init() {
        // Load configuration from data attribute if available
        const configElement = document.querySelector('script[data-textflow-config]');
        if (configElement) {
            try {
                const customConfig = JSON.parse(configElement.dataset.textflowConfig);
                Object.assign(config, customConfig);
            } catch (e) {
                console.warn('TextFlow: Invalid configuration', e);
            }
        }
        
        // Load patterns for current language
        loadPatterns(currentLanguage).then(() => {
            // Process initial content
            processContent();
            
            // Set up observer for dynamic content
            if (config.observeContentChanges) {
                observeContentChanges();
            }
        });
        
        // Handle dynamic content loaded via AJAX
        document.addEventListener('tx-textflow-reload', processContent);
    }
    
    /**
     * Load hyphenation patterns for a given language
     */
    function loadPatterns(language) {
        // If patterns are already loaded, return immediately
        if (patterns[language]) {
            return Promise.resolve(patterns[language]);
        }
        
        return fetch(`/index.php?eID=tx_textflow_patterns&language=${language}`)
            .then(response => response.json())
            .then(data => {
                patterns[language] = data;
                return data;
            })
            .catch(error => {
                console.error('TextFlow: Failed to load patterns', error);
                patterns[language] = []; // Set empty patterns to avoid repeated failed requests
                return [];
            });
    }
    
    /**
     * Process all content matching the configured selectors
     */
    function processContent() {
        config.selectors.forEach(selector => {
            document.querySelectorAll(selector).forEach(element => {
                processElement(element);
            });
        });
    }
    
    /**
     * Process a single DOM element
     */
    function processElement(element) {
        // Skip already processed elements
        if (element.dataset.textflowProcessed === 'true') {
            return;
        }
        
        // Process text nodes only
        walkTextNodes(element, textNode => {
            if (textNode.nodeValue.trim().length > 0) {
                textNode.nodeValue = processText(textNode.nodeValue);
            }
        });
        
        // Mark as processed
        element.dataset.textflowProcessed = 'true';
    }
    
    /**
     * Process text and apply hyphenation
     */
    function processText(text) {
        // Split into words and non-words
        return text.replace(/\w+/g, word => {
            if (word.length >= config.minWordLength) {
                return hyphenateWord(word);
            }
            return word;
        });
    }
    
    /**
     * Apply hyphenation to a single word
     */
    function hyphenateWord(word) {
        // Check cache first
        const key = `${currentLanguage}_${word}`;
        if (wordCache.has(key)) {
            return wordCache.get(key);
        }
        
        // Apply patterns and add soft hyphens
        let result = word;
        const langPatterns = patterns[currentLanguage] || [];
        
        langPatterns.forEach(pattern => {
            const pos = word.indexOf(pattern);
            if (pos !== -1 && pos > 0 && pos + pattern.length < word.length) {
                // Insert hyphen after pattern
                result = word.slice(0, pos + pattern.length) + config.hyphenChar + word.slice(pos + pattern.length);
            }
        });
        
        // Cache result
        wordCache.set(key, result);
        return result;
    }
    
    /**
     * Walk through all text nodes in an element
     */
    function walkTextNodes(element, callback) {
        const walker = document.createTreeWalker(
            element,
            NodeFilter.SHOW_TEXT,
            { acceptNode: node => NodeFilter.FILTER_ACCEPT }
        );
        
        let node;
        while (node = walker.nextNode()) {
            callback(node);
        }
    }
    
    /**
     * Observe DOM for content changes
     */
    function observeContentChanges() {
        // Setup mutation observer to detect new content
        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            // Check if the node matches any of our selectors
                            if (config.selectors.some(selector => node.matches(selector))) {
                                processElement(node);
                            } else {
                                // Check children
                                config.selectors.forEach(selector => {
                                    node.querySelectorAll(selector).forEach(processElement);
                                });
                            }
                        }
                    });
                }
            });
        });
        
        // Start observing
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Expose API
    window.TextFlow = {
        reload: processContent,
        setLanguage: function(language) {
            currentLanguage = language;
            loadPatterns(language).then(processContent);
        },
        clearCache: function() {
            wordCache.clear();
        }
    };
})(); 