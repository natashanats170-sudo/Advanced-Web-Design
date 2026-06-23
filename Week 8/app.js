// Show success/error messages
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('success')) {
        const msg = document.createElement('div');
        msg.className = 'alert alert-success';
        msg.textContent = '✅ Your vote has been recorded successfully!';
        msg.style.marginBottom = '20px';
        
        const mainContent = document.querySelector('.main-content');
        if (mainContent) {
            mainContent.insertBefore(msg, mainContent.firstChild);
        }
    }
    
    if (urlParams.has('error')) {
        const error = urlParams.get('error');
        let message = 'An error occurred. Please try again.';
        
        if (error === 'already_voted') message = '⚠️ You have already voted!';
        if (error === 'invalid') message = 'Invalid candidate selection.';
        if (error === 'vote_failed') message = 'Failed to cast vote. Please try again.';
        
        const msg = document.createElement('div');
        msg.className = 'alert alert-error';
        msg.textContent = message;
        msg.style.marginBottom = '20px';
        
        const mainContent = document.querySelector('.main-content');
        if (mainContent) {
            mainContent.insertBefore(msg, mainContent.firstChild);
        }
    }
    
    if (urlParams.has('logout')) {
        const msg = document.createElement('div');
        msg.className = 'alert alert-success';
        msg.textContent = 'You have been logged out successfully.';
        msg.style.marginBottom = '20px';
        
        const authCard = document.querySelector('.auth-card');
        if (authCard) {
            authCard.insertBefore(msg, authCard.firstChild);
        }
    }
    
    if (urlParams.has('registered')) {
        const msg = document.createElement('div');
        msg.className = 'alert alert-success';
        msg.textContent = '✅ Account created successfully! Please login.';
        msg.style.marginBottom = '20px';
        
        const authCard = document.querySelector('.auth-card');
        if (authCard) {
            authCard.insertBefore(msg, authCard.firstChild);
        }
    }
});

function animateResultBars() {
    document.querySelectorAll('.result-bar-fill').forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 300);
    });
}

document.addEventListener('DOMContentLoaded', animateResultBars);
