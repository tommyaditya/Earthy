/**
 * Admin Panel Common JavaScript
 * UI/UX Enhancements & Utilities
 */

// Toggle sidebar for mobile
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('active');
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.querySelector('.mobile-menu-toggle');
    
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    }
});

// Close sidebar when clicking nav item on mobile
document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            document.querySelector('.sidebar').classList.remove('active');
        }
    });
});

// Show toast notification
function showToast(message, type = 'success') {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast');
    existingToasts.forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    toast.innerHTML = `
        <i class="fas ${icons[type] || icons.info}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    // Trigger animation
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add toast styles
const toastStyles = document.createElement('style');
toastStyles.textContent = `
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 24px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
        font-weight: 600;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        z-index: 10000;
        transform: translateX(400px);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(10px);
        font-family: 'Poppins', sans-serif;
    }
    
    .toast.show {
        transform: translateX(0);
    }
    
    .toast i {
        font-size: 20px;
    }
    
    .toast-success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border: 1px solid #6ee7b7;
    }
    
    .toast-error {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
        border: 1px solid #f87171;
    }
    
    .toast-warning {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        border: 1px solid #fbbf24;
    }
    
    .toast-info {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
        border: 1px solid #60a5fa;
    }
`;
document.head.appendChild(toastStyles);

// Confirm dialog
function confirmDialog(message, onConfirm, onCancel) {
    const overlay = document.createElement('div');
    overlay.className = 'confirm-overlay';
    overlay.innerHTML = `
        <div class="confirm-dialog">
            <div class="confirm-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>Konfirmasi</h3>
            <p>${message}</p>
            <div class="confirm-actions">
                <button class="btn btn-secondary" onclick="this.closest('.confirm-overlay').remove(); ${onCancel ? 'onCancel()' : ''}">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button class="btn btn-danger" onclick="this.closest('.confirm-overlay').remove(); ${onConfirm}">
                    <i class="fas fa-check"></i> Ya, Lanjutkan
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);
    
    // Animation
    setTimeout(() => overlay.classList.add('show'), 10);
}

// Add confirm dialog styles
const confirmStyles = document.createElement('style');
confirmStyles.textContent = `
    .confirm-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .confirm-overlay.show {
        opacity: 1;
    }
    
    .confirm-dialog {
        background: white;
        padding: 40px;
        border-radius: 20px;
        max-width: 450px;
        width: 90%;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
        text-align: center;
        transform: scale(0.9);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .confirm-overlay.show .confirm-dialog {
        transform: scale(1);
    }
    
    .confirm-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        border-radius: 50%;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        color: #92400e;
    }
    
    .confirm-dialog h3 {
        font-size: 24px;
        margin-bottom: 12px;
        color: #0f172a;
    }
    
    .confirm-dialog p {
        font-size: 15px;
        color: #64748b;
        margin-bottom: 28px;
        line-height: 1.6;
    }
    
    .confirm-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
    }
    
    .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    
    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(239, 68, 68, 0.3);
    }
`;
document.head.appendChild(confirmStyles);

// Loading button state
function setButtonLoading(button, loading = true) {
    if (loading) {
        button.disabled = true;
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = '<span class="loading"></span> Memproses...';
    } else {
        button.disabled = false;
        button.innerHTML = button.dataset.originalText;
    }
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('id-ID', options);
}

// Format number
function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Disalin ke clipboard!', 'success');
    }).catch(() => {
        showToast('Gagal menyalin', 'error');
    });
}

// Smooth scroll to element
function scrollToElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Initialize tooltips
function initTooltips() {
    document.querySelectorAll('[data-tooltip]').forEach(element => {
        element.classList.add('tooltip');
        const tooltipText = document.createElement('span');
        tooltipText.className = 'tooltiptext';
        tooltipText.textContent = element.dataset.tooltip;
        element.appendChild(tooltipText);
    });
}

// Export data to CSV
function exportToCSV(data, filename) {
    const csv = data.map(row => Object.values(row).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
}

// Print page
function printPage() {
    window.print();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initTooltips();
    
    // Add smooth scroll to all anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
    
    // Auto-hide alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateX(20px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
    
    // Add click animation to all buttons
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            ripple.className = 'ripple';
            const rect = this.getBoundingClientRect();
            ripple.style.left = e.clientX - rect.left + 'px';
            ripple.style.top = e.clientY - rect.top + 'px';
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    });
    
    console.log('✨ Admin Panel UI/UX Enhanced - Ready!');
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K for search focus
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('#search-input, .search-input');
        if (searchInput) searchInput.focus();
    }
    
    // ESC to close modals
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.style.display = 'none';
        });
        document.querySelectorAll('.confirm-overlay').forEach(overlay => {
            overlay.remove();
        });
    }
});

// Export functions to global scope
window.showToast = showToast;
window.confirmDialog = confirmDialog;
window.setButtonLoading = setButtonLoading;
window.formatDate = formatDate;
window.formatNumber = formatNumber;
window.copyToClipboard = copyToClipboard;
window.scrollToElement = scrollToElement;
window.exportToCSV = exportToCSV;
window.printPage = printPage;
window.toggleSidebar = toggleSidebar;
