window.showToast = function (msg, time = 2500, type = 'info') {
  // Remove existing toast
  let oldToast = document.getElementById('webviewToast');
  if (oldToast) {
    oldToast.remove();
  }

  // Create new toast
  let toast = document.createElement('div');
  toast.id = 'webviewToast';
  
  // Determine colors based on type
  let bgColor = '#1f2937';
  let borderColor = '#374151';
  let icon = 'ℹ️';

  if (type === 'success') {
    bgColor = '#10b981';
    borderColor = '#059669';
    icon = '✓';
  } else if (type === 'error') {
    bgColor = '#ef4444';
    borderColor = '#dc2626';
    icon = '✕';
  } else if (type === 'warning') {
    bgColor = '#f59e0b';
    borderColor = '#d97706';
    icon = '⚠';
  } else if (type === 'info') {
    bgColor = '#3b82f6';
    borderColor = '#2563eb';
    icon = 'ℹ️';
  }

  // Set styles directly
  toast.style.position = 'fixed';
  toast.style.top = '20px';
  toast.style.right = '20px';
  toast.style.backgroundColor = bgColor;
  toast.style.color = '#ffffff';
  toast.style.padding = '14px 18px';
  toast.style.borderRadius = '8px';
  toast.style.fontSize = '14px';
  toast.style.fontWeight = '600';
  toast.style.zIndex = '999999';
  toast.style.boxShadow = '0 8px 16px rgba(0, 0, 0, 0.25)';
  toast.style.border = '1px solid ' + borderColor;
  toast.style.maxWidth = '320px';
  toast.style.display = 'flex';
  toast.style.alignItems = 'center';
  toast.style.gap = '10px';
  toast.style.animation = 'toastSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards';
  toast.style.pointerEvents = 'auto';

  toast.innerHTML = `<span style="font-size:18px;flex-shrink:0">${icon}</span><span style="line-height:1.4">${msg}</span>`;
  
  document.body.appendChild(toast);
  
  // Slide out after time
  setTimeout(() => {
    toast.style.animation = 'toastSlideOut 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards';
    setTimeout(() => {
      if (toast.parentNode) {
        toast.remove();
      }
    }, 400);
  }, time);
};

// Add global animation styles once
if (!document.getElementById('toastStyleSheet')) {
  const style = document.createElement('style');
  style.id = 'toastStyleSheet';
  style.innerHTML = `
    @keyframes toastSlideIn {
      from {
        transform: translateX(400px);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
    
    @keyframes toastSlideOut {
      from {
        transform: translateX(0);
        opacity: 1;
      }
      to {
        transform: translateX(400px);
        opacity: 0;
      }
    }
  `;
  document.head.appendChild(style);
}