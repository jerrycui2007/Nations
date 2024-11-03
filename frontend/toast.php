<style>
    .toast-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
    }

    .toast {
        background-color: #333;
        color: white;
        padding: 12px 24px;
        border-radius: 4px;
        margin-bottom: 10px;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .toast.success {
        background-color: #4CAF50;
    }

    .toast.error {
        background-color: #f44336;
    }

    .toast.show {
        opacity: 1;
        transform: translateX(0);
    }
</style>

<script>
function showToast(message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;

    container.appendChild(toast);

    setTimeout(() => toast.classList.add('show'), 10);

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

// Check for stored messages on page load
document.addEventListener('DOMContentLoaded', function() {
    const message = localStorage.getItem('toastMessage');
    const type = localStorage.getItem('toastType');
    
    if (message) {
        showToast(message, type);
        localStorage.removeItem('toastMessage');
        localStorage.removeItem('toastType');
    }
});
</script> 