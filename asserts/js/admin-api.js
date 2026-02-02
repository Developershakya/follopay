/**
 * Admin API Handler
 * Centralized API calls with proper error handling
 * FOR AJAX FOLDER SETUP
 */

const AdminAPI = {
    // ⭐ IMPORTANT: Change this path according to your setup
    // If follo is in root of domain: use '/ajax/'
    // If follo is subfolder: use '/follo/ajax/'
    baseURL: '/follo/ajax/',  // 👈 CHANGE THIS if needed
    
    /**
     * Make an API call
     */
    async call(action, data = {}, method = 'GET') {
        try {
            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                }
            };
            
            let url = this.baseURL + 'admin.php';
            
            if (method === 'GET') {
                const params = new URLSearchParams({action, ...data});
                url += '?' + params.toString();
            } else {
                const formData = new URLSearchParams({action, ...data});
                options.body = formData.toString();
            }
            
            console.log(`API Call: ${method} ${url}`);
            
            const response = await fetch(url, options);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (!result.success && result.message) {
                throw new Error(result.message);
            }
            
            return result;
            
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },
    
    /**
     * Make a GET request
     */
    async get(action, params = {}) {
        return this.call(action, params, 'GET');
    },
    
    /**
     * Make a POST request
     */
    async post(action, data = {}) {
        return this.call(action, data, 'POST');
    },
    
    /**
     * Upload file with form data
     */
    async upload(action, formData) {
        try {
            formData.append('action', action);
            
            const url = this.baseURL + 'admin.php';
            
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (!result.success && result.message) {
                throw new Error(result.message);
            }
            
            return result;
            
        } catch (error) {
            console.error('Upload Error:', error);
            throw error;
        }
    },
    
    // ============ POST MANAGEMENT ============
    
    async getPosts(status = 'all', search = '') {
        return this.get('get_posts', { status, search });
    },
    
    async createPost(data) {
        return this.post('create_post', data);
    },
    
    async updatePost(postId, data) {
        return this.post('update_post', { post_id: postId, ...data });
    },
    
    async deletePost(postId) {
        return this.post('delete_post', { post_id: postId });
    },
    
    async addComment(postId, commentText) {
        return this.post('add_comment', { post_id: postId, comment_text: commentText });
    },
    
    async getPostComments(postId) {
        return this.get('get_post_comments', { post_id: postId });
    },
    
    // ============ WITHDRAWAL MANAGEMENT ============
    
    async getWithdrawals(status = 'all', search = '', type = '') {
        return this.get('get_withdrawals', { status, search, type });
    },
    
    async getPendingWithdrawals() {
        return this.get('get_pending_withdrawals');
    },
    
    async getWithdrawalDetails(id) {
        return this.get('get_withdrawal_details', { id });
    },
    
    async processWithdrawal(withdrawalId, action, notes = '', refund = true) {
        return this.post('process_withdrawal', {
            withdrawal_id: withdrawalId,
            process_action: action,
            notes: notes,
            refund: refund ? '1' : '0'
        });
    },
    
    // ============ SUBMISSION MANAGEMENT ============
    
    async getPendingSubmissions() {
        return this.get('get_pending_submissions');
    },
    
    async getAllSubmissions(status = 'all') {
        return this.get('get_all_submissions', { status });
    },
    
    async approveSubmission(assignmentId) {
        return this.post('approve_submission', { assignment_id: assignmentId });
    },
    
    async rejectSubmission(assignmentId, reason) {
        return this.post('reject_submission', { assignment_id: assignmentId, reason });
    },
    
    // ============ USER MANAGEMENT ============
    
    async getUsers(status = 'all', search = '') {
        return this.get('get_users', { status, search });
    },
    
    async getUserDetails(userId) {
        return this.get('get_user_details', { user_id: userId });
    },
    
    async banUser(userId, reason) {
        return this.post('ban_user', { user_id: userId, reason });
    },
    
    async unbanUser(userId) {
        return this.post('unban_user', { user_id: userId });
    },
    
    // ============ STATS ============
    
    async getDashboardStats() {
        return this.get('get_dashboard_stats');
    },
    
    async getStats(type) {
        return this.get('get_stats', { type });
    }
};

// Helper functions for UI

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        'bg-blue-500'
    } text-white`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('opacity-0', 'transition-opacity');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function showLoading(show = true) {
    let loader = document.getElementById('globalLoader');
    if (!loader && show) {
        loader = document.createElement('div');
        loader.id = 'globalLoader';
        loader.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        loader.innerHTML = `
            <div class="bg-white rounded-lg p-6">
                <i class="fas fa-spinner fa-spin text-4xl text-blue-500"></i>
                <p class="mt-3 text-gray-700">Loading...</p>
            </div>
        `;
        document.body.appendChild(loader);
    } else if (loader && !show) {
        loader.remove();
    }
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-IN', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatCurrency(amount) {
    return '₹' + parseFloat(amount || 0).toFixed(2);
}

function getStatusBadge(status) {
    const badges = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'failed': 'bg-red-100 text-red-800',
        'rejected': 'bg-red-100 text-red-800',
        'submitted': 'bg-blue-100 text-blue-800',
        'active': 'bg-green-100 text-green-800',
        'inactive': 'bg-gray-100 text-gray-800'
    };
    
    return `<span class="px-2 py-1 rounded-full text-xs font-medium ${badges[status] || 'bg-gray-100 text-gray-800'}">
        ${status.charAt(0).toUpperCase() + status.slice(1)}
    </span>`;
}