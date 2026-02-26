<?php
// Enhanced How to Use Modal with Video Chapters - WebView Optimized
// File: modals/how-to-use-modal-advanced.php
// ✅ ANDROID WEBVIEW COMPATIBLE - Uses modal-backdrop pattern

if (!isset($isLoggedIn) || !$isLoggedIn) {
    exit;
}
?>

<!-- How To Use Modal - Android WebView Compatible -->
<div id="howToUseModal" class="modal-backdrop hidden">
    <div class="modal-content">
        
        <!-- Modal Header -->
        <div style="background: linear-gradient(to right, #2563eb, #1d4ed8); padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; color: white; border-radius: 12px 12px 0 0;">
            <div>
                <h2 id="modalTitle" style="margin: 0; font-size: 20px; font-weight: bold;">How to Use FolloPay?</h2>
                <p style="color: #dbeafe; font-size: 12px; margin: 8px 0 0 0;">Learn how to start earning money from reviews</p>
            </div>
            <button 
                onclick="closeHowToUseModal()" 
                style="background: transparent; border: none; color: white; font-size: 28px; cursor: pointer; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: background-color 0.2s;"
                aria-label="Close modal"
            >
                ×
            </button>
        </div>

        <!-- Modal Body - Scrollable -->
        <div style="overflow-y: auto; padding: 24px 20px; display: flex; flex-direction: column; gap: 16px; -webkit-overflow-scrolling: touch; max-height: calc(90vh - 150px);">
            
            <!-- YouTube Video Container -->
            <div style="position: relative; width: 100%; padding-bottom: 56.25%; background-color: #000; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);">
                <iframe
                    id="tutorialVideo"
                    width="100%"
                    height="100%"
                    src="https://www.youtube.com/embed/heWVuy0R6G8?controls=0&fs=0&modestbranding=1&rel=0&showinfo=0&iv_load_policy=3&disablekb=1&playsinline=1&autoplay=0"
                    title="How to Use FolloPay - Tutorial"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;"
                    loading="lazy"
                >
                </iframe>
            </div>

            <!-- Video Info with Icons -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                <div style="background-color: #f0fdf4; border-left: 4px solid #10b981; padding: 16px; border-radius: 8px; display: flex; gap: 12px;">
                    <i class="fas fa-check-circle" style="color: #10b981; font-size: 20px; flex-shrink: 0; margin-top: 4px;" aria-hidden="true"></i>
                    <div>
                        <p style="font-weight: 600; color: #1f2937; font-size: 14px; margin: 0;">Easy to Get Started</p>
                        <p style="font-size: 12px; color: #6b7280; margin: 8px 0 0 0;">Sign up in minutes and start earning</p>
                    </div>
                </div>

                <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 16px; border-radius: 8px; display: flex; gap: 12px;">
                    <i class="fas fa-money-bill-wave" style="color: #3b82f6; font-size: 20px; flex-shrink: 0; margin-top: 4px;" aria-hidden="true"></i>
                    <div>
                        <p style="font-weight: 600; color: #1f2937; font-size: 14px; margin: 0;">Earn Real Money</p>
                        <p style="font-size: 12px; color: #6b7280; margin: 8px 0 0 0;">Get paid for genuine reviews</p>
                    </div>
                </div>

                <div style="background-color: #f3e8ff; border-left: 4px solid #a855f7; padding: 16px; border-radius: 8px; display: flex; gap: 12px;">
                    <i class="fas fa-shield-alt" style="color: #a855f7; font-size: 20px; flex-shrink: 0; margin-top: 4px;" aria-hidden="true"></i>
                    <div>
                        <p style="font-weight: 600; color: #1f2937; font-size: 14px; margin: 0;">100% Secure</p>
                        <p style="font-size: 12px; color: #6b7280; margin: 8px 0 0 0;">Your data and earnings are safe</p>
                    </div>
                </div>

                <div style="background-color: #fffbeb; border-left: 4px solid #eab308; padding: 16px; border-radius: 8px; display: flex; gap: 12px;">
                    <i class="fas fa-clock" style="color: #eab308; font-size: 20px; flex-shrink: 0; margin-top: 4px;" aria-hidden="true"></i>
                    <div>
                        <p style="font-weight: 600; color: #1f2937; font-size: 14px; margin: 0;">Instant Payouts</p>
                        <p style="font-size: 12px; color: #6b7280; margin: 8px 0 0 0;">Withdraw anytime to your bank</p>
                    </div>
                </div>
            </div>

            <!-- Quick Steps -->
            <div style="background-color: #f9fafb; padding: 16px; border-radius: 8px; border: 1px solid #e5e7eb;">
                <h3 style="font-weight: 600; color: #1f2937; margin: 0 0 12px 0; display: flex; align-items: center; gap: 8px; font-size: 14px;">
                    <i class="fas fa-list-ol" style="color: #2563eb;" aria-hidden="true"></i>
                    Quick Steps to Start Earning
                </h3>
                <ol style="list-style: decimal; padding-left: 20px; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                    <li style="font-size: 13px; color: #4b5563; line-height: 1.5;">Complete your profile with valid information</li>
                    <li style="font-size: 13px; color: #4b5563; line-height: 1.5;">Browse available apps to review in the "Earn Money" section</li>
                    <li style="font-size: 13px; color: #4b5563; line-height: 1.5;">Write genuine, detailed reviews (minimum 50 characters)</li>
                    <li style="font-size: 13px; color: #4b5563; line-height: 1.5;">Submit screenshots of your review for verification</li>
                    <li style="font-size: 13px; color: #4b5563; line-height: 1.5;">Get paid once your review is approved (usually within 24 hours)</li>
                </ol>
            </div>

            <!-- Important Info -->
            <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 16px; border-radius: 8px;">
                <p style="font-size: 13px; font-weight: 600; color: #991b1b; margin: 0; display: flex; align-items: flex-start; gap: 8px;">
                    <i class="fas fa-exclamation-triangle" style="flex-shrink: 0; margin-top: 2px;" aria-hidden="true"></i>
                    Important Guidelines
                </p>
                <ul style="list-style-type: disc; list-style-position: inside; margin: 8px 0 0 0; padding: 0; display: flex; flex-direction: column; gap: 4px;">
                    <li style="font-size: 12px; color: #b91c1c;">Write only honest and genuine reviews</li>
                    <li style="font-size: 12px; color: #b91c1c;">Follow the app's review guidelines</li>
                    <li style="font-size: 12px; color: #b91c1c;">Submit valid screenshots for verification</li>
                    <li style="font-size: 12px; color: #b91c1c;">Don't write multiple reviews for the same app</li>
                </ul>
            </div>
        </div>

        <!-- Modal Footer -->
        <div style="background-color: #f3f4f6; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #e5e7eb; gap: 12px; flex-wrap: wrap; border-radius: 0 0 12px 12px;">
            <label style="display: flex; align-items: center; cursor: pointer;">
                <input 
                    type="checkbox" 
                    id="dontShowAgainCheckbox"
                    style="width: 18px; height: 18px; accent-color: #2563eb; cursor: pointer;"
                    aria-label="Don't show this modal again"
                >
                <span style="margin-left: 8px; font-size: 13px; color: #374151; cursor: pointer;">Don't show again</span>
            </label>
            <button 
                onclick="closeHowToUseModal()"
                style="background-color: #2563eb; color: white; padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; font-size: 14px; transition: background-color 0.2s;"
            >
                Got it
            </button>
        </div>
    </div>
</div>

<!-- Styles for Android WebView ✅ -->
<style>
    /* Modal backdrop - same as profile.php pattern */
    #howToUseModal.modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.6);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 16px;
        overflow: auto;
        -webkit-overflow-scrolling: touch;
    }

    #howToUseModal.modal-backdrop.active {
        display: flex !important;
    }

    #howToUseModal.modal-backdrop.hidden {
        display: none !important;
    }

    #howToUseModal .modal-content {
        background: white;
        border-radius: 12px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 640px;
        max-height: 90vh;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        animation: slideUp 0.3s ease-out;
        display: flex;
        flex-direction: column;
    }

    @keyframes slideUp {
        from {
            transform: translateY(100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Mobile optimization */
    @media (max-width: 640px) {
        #howToUseModal.modal-backdrop {
            padding: 12px;
        }
        
        #howToUseModal .modal-content {
            max-height: 85vh;
        }
    }
</style>

<!-- Scripts - ✅ ANDROID WEBVIEW OPTIMIZED -->
<script>
    // Configuration
    const MODAL_STORAGE_KEY = 'follopay_how_to_use_hidden';
    const MODAL_ELEMENT = document.getElementById('howToUseModal');
    const DONT_SHOW_CHECKBOX = document.getElementById('dontShowAgainCheckbox');
    let modalOpenedByButton = false; // Track if modal was opened by button

    /**
     * Close modal and save preference if "Don't Show Again" is checked
     */
    function closeHowToUseModal() {
        if (MODAL_ELEMENT) {
            MODAL_ELEMENT.classList.remove('active');
            MODAL_ELEMENT.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Check if "Don't Show Again" is checked
        if (DONT_SHOW_CHECKBOX && DONT_SHOW_CHECKBOX.checked) {
            const storageData = {
                hidden: true,
                timestamp: new Date().toISOString(),
                version: '1.0'
            };
            localStorage.setItem(MODAL_STORAGE_KEY, JSON.stringify(storageData));
        }

        // Reset button flag
        modalOpenedByButton = false;
    }

    /**
     * Show modal if not hidden by user (automatic display on dashboard)
     */
    function showHowToUseModal() {
        const savedData = localStorage.getItem(MODAL_STORAGE_KEY);

        if (!savedData) {
            if (MODAL_ELEMENT) {
                MODAL_ELEMENT.classList.remove('hidden');
                MODAL_ELEMENT.classList.add('active');
                document.body.style.overflow = 'hidden';
                // Scroll to top
                setTimeout(() => {
                    MODAL_ELEMENT.scrollTop = 0;
                }, 100);
            }
        }
    }

    /**
     * Open modal from button (question mark) - ignores "Don't Show Again"
     * Resets checkbox so preference isn't saved when closing from button
     */
    function openHelpModalForced() {
        modalOpenedByButton = true;
        
        // Reset checkbox to unchecked
        if (DONT_SHOW_CHECKBOX) {
            DONT_SHOW_CHECKBOX.checked = false;
        }

        if (MODAL_ELEMENT) {
            MODAL_ELEMENT.classList.remove('hidden');
            MODAL_ELEMENT.classList.add('active');
            document.body.style.overflow = 'hidden';
            // Scroll to top
            setTimeout(() => {
                MODAL_ELEMENT.scrollTop = 0;
            }, 100);
        }
    }

    /**
     * Reset the modal - useful for testing
     * Call from console: resetHowToUseModal()
     */
    function resetHowToUseModal() {
        localStorage.removeItem(MODAL_STORAGE_KEY);
        if (DONT_SHOW_CHECKBOX) {
            DONT_SHOW_CHECKBOX.checked = false;
        }
        showHowToUseModal();
        console.log('Modal reset - it will show on next page load');
    }

    /**
     * Close modal when clicking on backdrop
     */
    if (MODAL_ELEMENT) {
        MODAL_ELEMENT.addEventListener('click', function(event) {
            if (event.target === this) {
                closeHowToUseModal();
            }
        });
    }

    /**
     * Close on Escape key
     */
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && MODAL_ELEMENT && MODAL_ELEMENT.classList.contains('active')) {
            closeHowToUseModal();
        }
    });

    /**
     * Initialize modal on page load
     */
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(showHowToUseModal, 500);
    });
</script>