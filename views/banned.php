<?php
// Check if user is banned
$banReason = $_SESSION['ban_reason'] ?? 'Violation of terms of service';
$username = $_SESSION['banned_username'] ?? 'User';
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-red-500 to-pink-600 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <!-- Warning Icon -->
        <div class="bg-red-500 p-8 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full">
                <i class="fas fa-ban text-5xl text-red-500"></i>
            </div>
        </div>
        
        <!-- Ban Message -->
        <div class="p-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Account Suspended</h1>
            <p class="text-gray-600 mb-6">Your account has been temporarily suspended</p>
            
            <!-- Ban Details -->
            <div class="bg-red-50 border border-red-200 rounded-xl p-6 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3 mt-1"></i>
                    <div class="text-left">
                        <h3 class="font-bold text-red-700 mb-2">Ban Reason</h3>
                        <p class="text-red-600"><?php echo htmlspecialchars($banReason); ?></p>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['ban_expiry'])): ?>
                <div class="mt-4 flex items-start">
                    <i class="fas fa-calendar-times text-red-500 text-xl mr-3 mt-1"></i>
                    <div class="text-left">
                        <h3 class="font-bold text-red-700 mb-2">Ban Expiry</h3>
                        <p class="text-red-600">
                            <?php echo date('F d, Y h:i A', strtotime($_SESSION['ban_expiry'])); ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- What Happened -->
            <div class="mb-6">
                <h3 class="font-bold text-gray-800 mb-3">What Happened?</h3>
                <p class="text-gray-600 text-sm">
                    Your account was found to be in violation of our terms of service. 
                    This could be due to suspicious activity, multiple accounts, 
                    fake submissions, or other policy violations.
                </p>
            </div>
            
            <!-- Next Steps -->
            <div class="mb-8">
                <h3 class="font-bold text-gray-800 mb-3">What Can You Do?</h3>
                <div class="space-y-3">
                    <div class="flex items-start">
                        <i class="fas fa-envelope text-blue-500 mt-1 mr-3"></i>
                        <div class="text-left">
                            <p class="font-bold text-sm">Contact Support</p>
                            <p class="text-gray-600 text-sm">If you believe this is a mistake, contact our support team.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <i class="fas fa-book text-purple-500 mt-1 mr-3"></i>
                        <div class="text-left">
                            <p class="font-bold text-sm">Review Terms</p>
                            <p class="text-gray-600 text-sm">Read our terms of service to understand our policies.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <i class="fas fa-clock text-yellow-500 mt-1 mr-3"></i>
                        <div class="text-left">
                            <p class="font-bold text-sm">Wait for Review</p>
                            <p class="text-gray-600 text-sm">If temporary, your account will be restored after review period.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="space-y-3">
                <button onclick="contactSupport()" 
                        class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 rounded-xl font-bold hover:from-blue-600 hover:to-blue-700">
                    <i class="fas fa-headset mr-2"></i> Contact Support
                </button>
                
                <button onclick="viewTerms()" 
                        class="w-full border-2 border-gray-300 text-gray-700 py-3 rounded-xl font-bold hover:bg-gray-50">
                    <i class="fas fa-file-contract mr-2"></i> View Terms of Service
                </button>
                
                <button onclick="logout()" 
                        class="w-full border-2 border-red-300 text-red-600 py-3 rounded-xl font-bold hover:bg-red-50">
                    <i class="fas fa-sign-out-alt mr-2"></i> Return to Login
                </button>
            </div>
            
            <!-- Additional Help -->
            <div class="mt-6 pt-6 border-t">
                <p class="text-sm text-gray-600">
                    Need immediate help? Email us at 
                    <a href="mailto:support@earnapp.com" class="text-blue-600 font-bold">support@earnapp.com</a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Appeal Form Modal -->
<div id="appealModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md">
        <div class="p-6">
            <h2 class="text-xl font-bold mb-4">Submit Appeal</h2>
            <form id="appealForm">
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Email Address</label>
                    <input type="email" name="email" 
                           class="w-full px-4 py-2 border rounded-lg" 
                           value="<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>" 
                           required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Username</label>
                    <input type="text" name="username" 
                           class="w-full px-4 py-2 border rounded-lg" 
                           value="<?php echo htmlspecialchars($username); ?>" 
                           required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Appeal Reason</label>
                    <textarea name="reason" rows="4" 
                              class="w-full px-4 py-2 border rounded-lg" 
                              placeholder="Explain why you believe the ban should be lifted..." 
                              required></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Additional Information</label>
                    <textarea name="additional_info" rows="3" 
                              class="w-full px-4 py-2 border rounded-lg" 
                              placeholder="Any additional information or evidence..."></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideModal('appealModal')" 
                            class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        Submit Appeal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function contactSupport() {
    // Open email client with pre-filled message
    const subject = encodeURIComponent('Account Ban Appeal - Username: <?php echo $username; ?>');
    const body = encodeURIComponent(
        `Hello Support Team,\n\n` +
        `My account has been banned and I would like to appeal this decision.\n` +
        `Username: <?php echo $username; ?>\n` +
        `Ban Reason: <?php echo $banReason; ?>\n\n` +
        `Additional Information:\n`
    );
    
    window.location.href = `mailto:support@earnapp.com?subject=${subject}&body=${body}`;
}

function showAppealForm() {
    showModal('appealModal');
}

function viewTerms() {
    window.open('/terms-of-service', '_blank');
}

function logout() {
    // Clear session and redirect to login
    fetch('ajax/auth.php?action=logout')
        .then(() => {
            window.location.href = '?page=login';
        })
        .catch(error => {
            console.error('Error:', error);
            window.location.href = '?page=login';
        });
}

function showModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    document.getElementById(modalId).classList.add('flex');
}

function hideModal(modalId) {
    document.getElementById(modalId).classList.remove('flex');
    document.getElementById(modalId).classList.add('hidden');
}

// Handle appeal form submission
document.getElementById('appealForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('ajax', 'true');
    formData.append('action', 'submit_appeal');
    
    try {
        const response = await fetch('ajax/auth.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Appeal submitted successfully! We will review your case within 48 hours.');
            hideModal('appealModal');
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to submit appeal. Please try emailing us directly.');
    }
});

// Auto-redirect after 30 seconds if user doesn't take action
setTimeout(() => {
    if (confirm('You have been inactive. Would you like to return to login page?')) {
        logout();
    }
}, 30000);
</script>

<style>
/* Animation for warning icon */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.fa-ban {
    animation: pulse 2s infinite;
}

/* Hover effects for buttons */
button:hover {
    transform: translateY(-2px);
    transition: all 0.3s ease;
}

/* Modal animation */
.modal-enter {
    animation: modalEnter 0.3s ease-out;
}

@keyframes modalEnter {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>