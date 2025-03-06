// Enable Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Add fade-in animation to cards
    document.querySelectorAll('.card, .client-card, .dashboard-card').forEach(function(element) {
        element.classList.add('fade-in');
    });

    // Handle form submissions with AJAX
    document.querySelectorAll('form[data-ajax="true"]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            const submitButton = form.querySelector('[type="submit"]');
            const originalText = submitButton.innerHTML;
            
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Feldolgozás...';

            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    if (data.redirect) {
                        setTimeout(() => window.location.href = data.redirect, 1500);
                    }
                } else {
                    showAlert('danger', data.message || 'Hiba történt a feldolgozás során.');
                }
            })
            .catch(error => {
                showAlert('danger', 'Hiba történt a kérés feldolgozása során.');
                console.error('Error:', error);
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
        });
    });

    // Delete confirmation
    document.querySelectorAll('[data-confirm]').forEach(function(element) {
        element.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });
});

// Show alert message
function showAlert(type, message) {
    const alertHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    const alertContainer = document.createElement('div');
    alertContainer.innerHTML = alertHTML;
    document.querySelector('.container').insertBefore(alertContainer.firstChild, document.querySelector('.container').firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Format date inputs
document.querySelectorAll('input[type="date"]').forEach(function(input) {
    input.addEventListener('change', function() {
        this.setAttribute('data-date', this.value);
    });
});

// Dynamic search filtering
document.querySelectorAll('[data-search]').forEach(function(input) {
    input.addEventListener('input', function() {
        const searchValue = this.value.toLowerCase();
        const target = this.dataset.search;
        
        document.querySelectorAll(target).forEach(function(item) {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchValue) ? '' : 'none';
        });
    });
});
