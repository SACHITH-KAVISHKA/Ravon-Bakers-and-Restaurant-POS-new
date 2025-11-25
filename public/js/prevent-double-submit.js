/**
 * Prevent Double Submit - Global Solution
 * Prevents accidental double-clicks/taps on forms and buttons that cause duplicate database entries
 * 
 * Features:
 * - Automatically disables submit buttons after first click
 * - Works with regular forms and AJAX submissions
 * - Re-enables buttons on form validation errors
 * - Provides visual feedback during submission
 * - Handles both form submissions and button clicks
 */

(function() {
    'use strict';

    // Track forms that are currently submitting
    const submittingForms = new WeakSet();
    
    // Track buttons that are currently processing
    const processingButtons = new WeakSet();

    /**
     * Disable a button and provide visual feedback
     */
    function disableButton(button) {
        if (!button || processingButtons.has(button)) return false;
        
        processingButtons.add(button);
        button.disabled = true;
        
        // Store original content
        if (!button.dataset.originalContent) {
            button.dataset.originalContent = button.innerHTML;
        }
        
        // Add loading indicator
        const hasIcon = button.querySelector('i');
        if (hasIcon) {
            const iconClass = hasIcon.className;
            button.dataset.originalIcon = iconClass;
            hasIcon.className = 'bi bi-hourglass-split spinner-border spinner-border-sm';
        } else {
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + button.textContent;
        }
        
        // Add visual feedback class
        button.classList.add('btn-submitting');
        
        return true;
    }

    /**
     * Re-enable a button and restore original content
     */
    function enableButton(button) {
        if (!button) return;
        
        processingButtons.delete(button);
        button.disabled = false;
        
        // Restore original content
        if (button.dataset.originalContent) {
            button.innerHTML = button.dataset.originalContent;
        }
        
        // Restore original icon if it existed
        if (button.dataset.originalIcon) {
            const icon = button.querySelector('i');
            if (icon) {
                icon.className = button.dataset.originalIcon;
            }
        }
        
        button.classList.remove('btn-submitting');
    }

    /**
     * Handle form submission
     */
    function handleFormSubmit(event) {
        const form = event.target;
        
        // Check if form is already submitting
        if (submittingForms.has(form)) {
            event.preventDefault();
            event.stopImmediatePropagation();
            return false;
        }
        
        // Check if form has validation errors
        if (!form.checkValidity()) {
            return true; // Allow form to show validation errors
        }
        
        // Mark form as submitting
        submittingForms.add(form);
        
        // Disable all submit buttons in the form
        const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        submitButtons.forEach(button => disableButton(button));
        
        // Re-enable after a timeout as a safety measure (in case submission fails)
        setTimeout(() => {
            submittingForms.delete(form);
            submitButtons.forEach(button => enableButton(button));
        }, 5000);
        
        return true;
    }

    /**
     * Handle button clicks that trigger actions (not form submissions)
     */
    function handleButtonClick(event) {
        const button = event.currentTarget;
        
        // Skip if button is already processing
        if (processingButtons.has(button)) {
            event.preventDefault();
            event.stopImmediatePropagation();
            return false;
        }
        
        // Only handle buttons with data-prevent-double attribute
        if (!button.hasAttribute('data-prevent-double')) {
            return true;
        }
        
        // Disable the button
        if (!disableButton(button)) {
            event.preventDefault();
            event.stopImmediatePropagation();
            return false;
        }
        
        // Re-enable after timeout
        const timeout = parseInt(button.dataset.reenableTimeout) || 3000;
        setTimeout(() => {
            enableButton(button);
        }, timeout);
        
        return true;
    }

    /**
     * Initialize double-submit prevention on page load
     */
    function init() {
        // Attach listeners to all forms
        document.querySelectorAll('form').forEach(form => {
            // Skip forms that explicitly opt-out
            if (form.hasAttribute('data-allow-double-submit')) {
                return;
            }
            
            form.addEventListener('submit', handleFormSubmit, true);
        });
        
        // Attach listeners to buttons with data-prevent-double attribute
        document.querySelectorAll('button[data-prevent-double], a[data-prevent-double]').forEach(button => {
            button.addEventListener('click', handleButtonClick, true);
        });
        
        // Add CSS for visual feedback
        if (!document.getElementById('prevent-double-submit-styles')) {
            const style = document.createElement('style');
            style.id = 'prevent-double-submit-styles';
            style.textContent = `
                .btn-submitting {
                    opacity: 0.7;
                    cursor: not-allowed !important;
                    pointer-events: none;
                }
                
                @keyframes pulse-submit {
                    0%, 100% { opacity: 0.7; }
                    50% { opacity: 0.9; }
                }
                
                .btn-submitting {
                    animation: pulse-submit 1.5s ease-in-out infinite;
                }
            `;
            document.head.appendChild(style);
        }
    }

    /**
     * Observe DOM changes and attach handlers to new forms
     */
    function observeDOMChanges() {
        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1) { // Element node
                        // Check if the node itself is a form
                        if (node.tagName === 'FORM' && !node.hasAttribute('data-allow-double-submit')) {
                            node.addEventListener('submit', handleFormSubmit, true);
                        }
                        
                        // Check for forms within the added node
                        if (node.querySelectorAll) {
                            node.querySelectorAll('form:not([data-allow-double-submit])').forEach(form => {
                                form.addEventListener('submit', handleFormSubmit, true);
                            });
                            
                            // Check for buttons with data-prevent-double
                            node.querySelectorAll('button[data-prevent-double], a[data-prevent-double]').forEach(button => {
                                button.addEventListener('click', handleButtonClick, true);
                            });
                        }
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            init();
            observeDOMChanges();
        });
    } else {
        init();
        observeDOMChanges();
    }

    // Expose utility functions globally for manual use
    window.PreventDoubleSubmit = {
        disableButton: disableButton,
        enableButton: enableButton,
        init: init
    };
})();
