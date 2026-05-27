const searchInput = document.getElementById('liveSearch');
if (searchInput) {
    searchInput.addEventListener('keyup', function () {
        const query = this.value.toLowerCase();
        const products = document.querySelectorAll('.product');
        products.forEach(function (product) {
            const title = product.querySelector('h3').textContent.toLowerCase();
            product.style.display = title.includes(query) ? 'block' : 'none';
        });
    });
}


function updateCartTotal() {
    const items = document.querySelectorAll('.cart-item');
    let subtotal = 0;

    items.forEach(function (item) {
        const priceText = item.querySelector('.item-price');
        const qtyInput = item.querySelector('input[type="number"]');

        if (priceText && qtyInput) {
            const price = parseFloat(priceText.dataset.price);
            const qty = parseInt(qtyInput.value);
            const lineTotal = price * qty;
            const lineTotalEl = item.querySelector('.line-total');
            if (lineTotalEl) lineTotalEl.textContent = 'R' + lineTotal.toFixed(2);
            subtotal += lineTotal;
        }
    });

    const subtotalEl = document.getElementById('cart-subtotal');
    const totalEl = document.getElementById('cart-total');
    const delivery = 50;

    if (subtotalEl) subtotalEl.textContent = 'R' + subtotal.toFixed(2);
    if (totalEl) totalEl.textContent = 'R' + (subtotal + delivery).toFixed(2);
}


document.querySelectorAll('.cart-item input[type="number"]').forEach(function (input) {
    input.addEventListener('change', updateCartTotal);
});


const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', function (e) {
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();
        let valid = true;

        clearErrors();

        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showError('emailError', 'Please enter a valid email address.');
            valid = false;
        }
        if (!password || password.length < 6) {
            showError('passwordError', 'Password must be at least 6 characters.');
            valid = false;
        }
        if (!valid) e.preventDefault();
    });
}

const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', function (e) {
        const name = document.getElementById('fullname') ? document.getElementById('fullname').value.trim() : '';
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('confirm') ? document.getElementById('confirm').value : '';
        let valid = true;

        clearErrors();

        if (!name || name.length < 2) {
            showError('nameError', 'Please enter your full name.');
            valid = false;
        }
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showError('emailError', 'Please enter a valid email address.');
            valid = false;
        }
        if (!password || password.length < 6) {
            showError('passwordError', 'Password must be at least 6 characters.');
            valid = false;
        }
        if (confirm !== password) {
            showError('confirmError', 'Passwords do not match.');
            valid = false;
        }
        if (!valid) e.preventDefault();
    });
}

function showError(id, message) {
    const el = document.getElementById(id);
    if (el) {
        el.textContent = message;
        el.style.display = 'block';
    }
}

function clearErrors() {
    document.querySelectorAll('.error-msg').forEach(function (el) {
        el.style.display = 'none';
    });
}


document.querySelectorAll('.confirm-delete').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
        if (!confirm('Are you sure you want to delete this? This cannot be undone.')) {
            e.preventDefault();
        }
    });
});


document.querySelectorAll('.toggle-pw').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const targetId = this.dataset.target;
        const input = document.getElementById(targetId);
        if (input) {
            input.type = input.type === 'password' ? 'text' : 'password';
            this.textContent = input.type === 'password' ? '👁' : '🙈';
        }
    });
});


setTimeout(function () {
    document.querySelectorAll('.alert-success, .alert-error').forEach(function (el) {
        el.style.transition = 'opacity 0.5s';
        el.style.opacity = '0';
        setTimeout(function () { el.style.display = 'none'; }, 500);
    });
}, 4000);


const currentPage = window.location.pathname;
document.querySelectorAll('.navbar nav a').forEach(function (link) {
    if (link.href.includes(currentPage) && currentPage !== '/') {
        link.style.color = '#8ed1c6';
        link.style.fontWeight = '700';
    }
});


document.querySelectorAll('img').forEach(function (img) {
    img.addEventListener('error', function () {
        if (!this.dataset.errored) {
            this.dataset.errored = 'true';
            this.src = 'https://via.placeholder.com/200x200?text=No+Image';
        }
    });
});


const scrollBtn = document.getElementById('scrollTopBtn');
if (scrollBtn) {
    window.addEventListener('scroll', function () {
        scrollBtn.style.display = window.scrollY > 300 ? 'block' : 'none';
    });
    scrollBtn.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}