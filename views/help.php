<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <?php include 'header.php'; ?>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="m-8">
    <div class="max-w-6xl mx-auto">
    <!-- Page Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Help & Support</h1>
        <p class="text-gray-600">Find answers to common questions and get support</p>
    </div>

    <!-- Search Box -->
    <div class="bg-white rounded-xl shadow p-6 mb-8">
        <div class="relative">
            <input type="text" id="helpSearch" placeholder="Search for help..." 
                   class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        </div>
    </div>

    <!-- FAQ Categories -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <a href="#earning" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition-shadow">
            <div class="text-center">
                <i class="fas fa-money-bill-wave text-4xl text-green-500 mb-4"></i>
                <h3 class="font-bold text-lg mb-2">Earning Money</h3>
                <p class="text-gray-600 text-sm">How to earn money step by step</p>
            </div>
        </a>
        
        <a href="#withdrawal" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition-shadow">
            <div class="text-center">
                <i class="fas fa-credit-card text-4xl text-blue-500 mb-4"></i>
                <h3 class="font-bold text-lg mb-2">Withdrawal</h3>
                <p class="text-gray-600 text-sm">How to withdraw money to UPI/Free Fire</p>
            </div>
        </a>
        
        <a href="#troubleshoot" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition-shadow">
            <div class="text-center">
                <i class="fas fa-tools text-4xl text-purple-500 mb-4"></i>
                <h3 class="font-bold text-lg mb-2">Troubleshooting</h3>
                <p class="text-gray-600 text-sm">Common issues and solutions</p>
            </div>
        </a>
    </div>

    <!-- FAQ Sections -->
    <div class="space-y-6">
        <!-- Earning Section -->
        <div id="earning" class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-bold mb-4 flex items-center">
                <i class="fas fa-money-bill-wave text-green-500 mr-3"></i>
                How to Earn Money
            </h2>
            
            <div class="space-y-4">
                <div class="faq-item">
                    <h3 class="font-bold text-lg mb-2">Step 1: Choose a Task</h3>
                    <p class="text-gray-700">Go to Dashboard and select an available task. Each task shows the app name, link, and earning amount.</p>
                </div>
                
                <div class="faq-item">
                    <h3 class="font-bold text-lg mb-2">Step 2: Get Assigned Comment</h3>
                    <p class="text-gray-700">Click "Start Earning" to get a unique comment assigned to you. This comment is valid for 5 minutes only.</p>
                </div>
                
                <div class="faq-item">
                    <h3 class="font-bold text-lg mb-2">Step 3: Copy and Post</h3>
                    <p class="text-gray-700">Copy the assigned comment, open the Play Store app link, and post the comment as a review.</p>
                </div>
                
                <div class="faq-item">
                    <h3 class="font-bold text-lg mb-2">Step 4: Upload Screenshot</h3>
                    <p class="text-gray-700">Take a screenshot of your posted comment and upload it within 5 minutes. The screenshot must clearly show your comment.</p>
                </div>
                
                <div class="faq-item">
                    <h3 class="font-bold text-lg mb-2">Step 5: Wait for Review</h3>
                    <p class="text-gray-700">Your submission will be reviewed within 24-48 hours. If approved, money will be added to your wallet.</p>
                </div>
            </div>
        </div>

        <!-- Withdrawal Section -->
        <div id="withdrawal" class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-bold mb-4 flex items-center">
                <i class="fas fa-credit-card text-blue-500 mr-3"></i>
                Withdrawal Process
            </h2>
            
            <div class="space-y-6">
                <!-- UPI Withdrawal -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-bold text-blue-700 mb-3">UPI Withdrawal Rules</h3>
                    
                    <div class="mb-4">
                        <h4 class="font-bold mb-2">Instant Withdrawal</h4>
                        <ul class="list-disc pl-5 text-blue-800 space-y-1">
                            <li>Minimum: ₹80</li>
                            <li>40% deduction charge</li>
                            <li>Processing time: 1-7 working days</li>
                            <li>Not guaranteed instant despite the name</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="font-bold mb-2">Duration Withdrawal</h4>
                        <ul class="list-disc pl-5 text-blue-800 space-y-1">
                            <li>Available only: 10th - 17th of every month</li>
                            <li>Minimum: ₹50</li>
                            <li>No charges</li>
                            <li>Processing time: 5-7 working days</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Free Fire Withdrawal -->
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h3 class="font-bold text-purple-700 mb-3">Free Fire Diamond Rules</h3>
                    <ul class="list-disc pl-5 text-purple-800 space-y-2">
                        <li>Diamond top-up is NEVER instant</li>
                        <li>Processing time: 5-7 working days (always)</li>
                        <li>Default card: ₹80 = 100 diamonds</li>
                        <li>More cards available in withdrawal section</li>
                        <li>Enter correct Free Fire UID (8-10 digits)</li>
                    </ul>
                </div>
                
                <!-- Important Notice -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h3 class="font-bold text-yellow-700 mb-2 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        IMPORTANT DISCLAIMER
                    </h3>
                    <p class="text-yellow-800 font-bold">
                        "If you enter wrong UPI ID or Free Fire UID, we are NOT responsible. Please enter correct details."
                    </p>
                </div>
            </div>
        </div>

        <!-- Troubleshooting Section -->
        <div id="troubleshoot" class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-bold mb-4 flex items-center">
                <i class="fas fa-tools text-purple-500 mr-3"></i>
                Troubleshooting
            </h2>
            
            <div class="space-y-4">
                <div class="faq-item">
                    <h3 class="font-bold text-lg mb-2">Q: Why can't I upload screenshot?</h3>
                    <p class="text-gray-700">
                        A: Make sure you're uploading within 5 minutes of getting the comment. 
                        The screenshot must be in JPG, PNG, or WebP format and under 5MB. 
                        Also ensure you have a stable internet connection.
                    </p>
                </div>
                
                <div class="faq-item">
                    <h3 class="font-bold text-lg mb-2">Q: Why was my submission rejected?</h3>
                    <p class="text-gray-700">
                        A: Common reasons: 1) Wrong screenshot (not showing the comment), 
                        2) Comment not posted in Play Store, 3) Submitted after 5 minutes,
                        4) Poor quality screenshot, 5) Wrong comment used.
                    </p>
                </div>
                
                <div class="faq-item">
                    <h3 class="font-bold text-lg mb-2">Q: Why is my withdrawal taking so long?</h3>
                    <p class="text-gray-700">
                        A: Processing times vary: Instant (1-7 days), Duration (5-7 days), 
                        Free Fire (5-7 days). Weekends and holidays are not counted. 
                        If it's beyond the timeframe, contact support.
                    </p>
                </div>
                
                <div class="faq-item">
                    <h3 class="font-bold text-lg mb-2">Q: Why can't I get a comment assigned?</h3>
                    <p class="text-gray-700">
                        A: You may already have an active assignment. Check the Earn page. 
                        If not, there might be no available comments for that post. 
                        Try another post or check back later.
                    </p>
                </div>
                
                <div class="faq-item">
                    <h3 class="font-bold text-lg mb-2">Q: Why is my account banned?</h3>
                    <p class="text-gray-700">
                        A: Common reasons: 1) Multiple accounts, 2) Fake submissions,
                        3) Violation of terms, 4) Suspicious activity. 
                        Contact support for details.
                    </p>
                </div>
            </div>
        </div>

        <!-- Rules Section -->
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-bold mb-4 flex items-center">
                <i class="fas fa-gavel text-red-500 mr-3"></i>
                Important Rules
            </h2>
            
            <div class="space-y-3">
                <div class="flex items-start">
                    <i class="fas fa-ban text-red-500 mr-3 mt-1"></i>
                    <p class="text-gray-700">One user = one comment at a time. You cannot work on multiple tasks simultaneously.</p>
                </div>
                
                <div class="flex items-start">
                    <i class="fas fa-ban text-red-500 mr-3 mt-1"></i>
                    <p class="text-gray-700">One comment = one user at a time. Comments cannot be shared or reused.</p>
                </div>
                
                <div class="flex items-start">
                    <i class="fas fa-clock text-yellow-500 mr-3 mt-1"></i>
                    <p class="text-gray-700">Comment validity = 5 minutes. Submit screenshot within this time.</p>
                </div>
                
                <div class="flex items-start">
                    <i class="fas fa-sync-alt text-blue-500 mr-3 mt-1"></i>
                    <p class="text-gray-700">If you refresh page/close tab, comment returns to pool immediately.</p>
                </div>
                
                <div class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                    <p class="text-gray-700">Once screenshot is approved, comment becomes USED permanently.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Support -->
    <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl shadow p-8 text-white text-center mt-8">
        <h2 class="text-2xl font-bold mb-4">Still Need Help?</h2>
        <p class="mb-6 opacity-90">Our support team is here to help you 24/7</p>
        
        <div class="flex flex-col md:flex-row justify-center space-y-4 md:space-y-0 md:space-x-6">
            <a href="mailto:follopayhelp@gmail.com" 
               class="bg-white text-blue-600 px-6 py-3 rounded-lg font-bold hover:bg-gray-100">
                <i class="fas fa-envelope mr-2"></i> Email Support
            </a>
            
<a href="https://t.me/FolloPaySupport" target="_blank"
    class="bg-transparent border-2 border-white px-6 py-3 rounded-lg text-center font-bold  gap-2 hover:bg-white hover:text-blue-600">
    
    <i class="fab fa-telegram-plane text-lg"></i>
    <span>Telegram </span>
</a>


        </div>
        
        <p class="mt-6 text-sm opacity-80">
            <i class="fas fa-clock mr-2"></i> Response time: Usually within 24 hours
        </p>
    </div>
</div>

<script>
// Search functionality
document.getElementById('helpSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const faqItems = document.querySelectorAll('.faq-item');
    
    if (searchTerm.length < 2) {
        faqItems.forEach(item => item.style.display = 'block');
        return;
    }
    
    faqItems.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(searchTerm) ? 'block' : 'none';
    });
});

// Smooth scroll to sections
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        const targetElement = document.querySelector(targetId);
        
        if (targetElement) {
            window.scrollTo({
                top: targetElement.offsetTop - 100,
                behavior: 'smooth'
            });
        }
    });
});

function showLiveChat() {
    alert('Live chat feature coming soon! For now, please email us at support@earnapp.com');
}

// Add active class to clicked FAQ items
document.querySelectorAll('.faq-item').forEach(item => {
    item.addEventListener('click', function() {
        this.classList.toggle('active');
    });
});
</script>

<style>
.faq-item {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    transition: all 0.3s ease;
    cursor: pointer;
}

.faq-item:last-child {
    border-bottom: none;
}

.faq-item:hover {
    background-color: #f9fafb;
    padding-left: 1.5rem;
}

.faq-item.active {
    background-color: #eff6ff;
    border-left: 4px solid #3b82f6;
}
</style>
</body>
</html>