let cart = [];

function addToCart(name, price) {
    cart = JSON.parse(localStorage.getItem("cart")) || [];
    cart.push({ name, price });
    localStorage.setItem("cart", JSON.stringify(cart));
    
    // Create a temporary toast notification
    const toast = document.createElement('div');
    toast.innerText = `🛒 ${name} added!`;
    toast.style.cssText = `
        position: fixed; top: 20px; right: 20px; background: #7cff1c; color: black;
        padding: 10px 20px; border-radius: 10px; font-weight: bold; z-index: 10000;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3); animation: slideIn 0.3s ease-out;
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.5s';
        setTimeout(() => toast.remove(), 500);
    }, 2000);
}

function loadCart() {
    cart = JSON.parse(localStorage.getItem("cart")) || [];
    let list = document.getElementById("cart-items");
    let total = 0;

    cart.forEach(item => {
        let li = document.createElement("li");
        li.innerText = item.name + " - ₹" + item.price;
        list.appendChild(li);
        total += item.price;
    });

    document.getElementById("total").innerText = "Total: ₹" + total;
}

if (document.getElementById("cart-items")) {
    loadCart();
}

// Authentication check for protected routes
function getCookie(name) {
    let match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    if (match) {
        return match[2];
    }
    return null;
}

document.addEventListener("DOMContentLoaded", () => {
    const isProtectedPage = window.location.pathname.includes('home.html') || window.location.pathname.includes('cart.html');
    const authCookie = getCookie('user_auth');

    if (isProtectedPage && !authCookie) {
        window.location.href = 'index.html';
    }
});