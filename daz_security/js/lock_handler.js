// Lock handler for login page
class LockHandler {
    constructor() {
        this.lockedUsers = JSON.parse(localStorage.getItem('locked_users') || '{}');
        this.countdownInterval = null;
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.checkInitialLockStatus();
    }
    
    bindEvents() {
        const usernameInput = document.getElementById('username');
        if (usernameInput) {
            usernameInput.addEventListener('input', () => this.checkUserLockStatus());
            usernameInput.addEventListener('change', () => this.checkUserLockStatus());
        }
        
        const loginForm = document.getElementById('login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => {
                if (!this.checkLockStatus()) {
                    e.preventDefault();
                }
            });
        }
    }
    
    checkInitialLockStatus() {
        const username = document.getElementById('username')?.value;
        if (username) {
            this.checkUserLockStatus();
        }
    }
    
    checkUserLockStatus() {
        const username = document.getElementById('username')?.value;
        const loginBtn = document.getElementById('login-btn');
        const lockoutMessage = document.getElementById('lockout-message');
        const lockoutText = document.getElementById('lockout-text');
        const countdownDiv = document.getElementById('countdown');
        const btnText = document.getElementById('btn-text');
        
        // Clear previous interval
        if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
            this.countdownInterval = null;
        }
        
        if (username && this.lockedUsers[username] && Date.now() < this.lockedUsers[username]) {
            const remaining = Math.ceil((this.lockedUsers[username] - Date.now()) / 1000);
            this.updateLockoutDisplay(remaining, username);
            return false;
        }
        
        // Enable button if not locked
        if (lockoutMessage) lockoutMessage.style.display = 'none';
        if (countdownDiv) countdownDiv.style.display = 'none';
        if (loginBtn) {
            loginBtn.disabled = false;
            loginBtn.classList.remove('btn-locked');
        }
        if (btnText) btnText.textContent = 'LOGIN';
        
        return true;
    }
    
    updateLockoutDisplay(remainingSeconds, username) {
        const loginBtn = document.getElementById('login-btn');
        const lockoutMessage = document.getElementById('lockout-message');
        const lockoutText = document.getElementById('lockout-text');
        const countdownDiv = document.getElementById('countdown');
        const btnText = document.getElementById('btn-text');
        
        // Disable button
        if (loginBtn) {
            loginBtn.disabled = true;
            loginBtn.classList.add('btn-locked');
        }
        
        // Update button text
        if (btnText) btnText.textContent = 'ACCOUNT LOCKED';
        
        // Show lockout message
        if (lockoutMessage && lockoutText) {
            lockoutMessage.style.display = 'block';
            lockoutText.textContent = `Account locked. Too many failed attempts.`;
        }
        
        // Show countdown
        if (countdownDiv) {
            countdownDiv.style.display = 'block';
            countdownDiv.textContent = `Try again in: ${remainingSeconds}s`;
        }
        
        // Start countdown
        this.countdownInterval = setInterval(() => {
            remainingSeconds--;
            
            if (remainingSeconds <= 0) {
                clearInterval(this.countdownInterval);
                this.countdownInterval = null;
                
                if (lockoutMessage) lockoutMessage.style.display = 'none';
                if (countdownDiv) countdownDiv.style.display = 'none';
                if (loginBtn) {
                    loginBtn.disabled = false;
                    loginBtn.classList.remove('btn-locked');
                }
                if (btnText) btnText.textContent = 'LOGIN';
                
                // Remove from local storage
                delete this.lockedUsers[username];
                localStorage.setItem('locked_users', JSON.stringify(this.lockedUsers));
            } else {
                if (countdownDiv) {
                    countdownDiv.textContent = `Try again in: ${remainingSeconds}s`;
                }
            }
        }, 1000);
    }
    
    checkLockStatus() {
        const username = document.getElementById('username')?.value;
        
        if (username && this.lockedUsers[username] && Date.now() < this.lockedUsers[username]) {
            const remaining = Math.ceil((this.lockedUsers[username] - Date.now()) / 1000);
            this.updateLockoutDisplay(remaining, username);
            return false;
        }
        
        return true;
    }
    
    lockUser(username, durationSeconds = 60) {
        const lockUntil = Date.now() + (durationSeconds * 1000);
        this.lockedUsers[username] = lockUntil;
        localStorage.setItem('locked_users', JSON.stringify(this.lockedUsers));
        this.checkUserLockStatus();
    }
    
    unlockUser(username) {
        delete this.lockedUsers[username];
        localStorage.setItem('locked_users', JSON.stringify(this.lockedUsers));
        this.checkUserLockStatus();
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    window.lockHandler = new LockHandler();
});