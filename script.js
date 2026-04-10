// TradeVault Main Marketplace Script
let currentUser = null;
let allProducts = [];
let currentProductPage = 0;
const productsPerPage = 12;

// DOM Elements
const productGrid = document.getElementById('productGrid');
const categoryFilter = document.getElementById('categoryFilter');
const moqFilter = document.getElementById('moqFilter');
const sortFilter = document.getElementById('sortFilter');
const searchInput = document.getElementById('searchInput');
const loadMoreBtn = document.getElementById('loadMoreBtn');
const cartCount = document.getElementById('cartCount');
const loginBtn = document.getElementById('loginBtn');
const registerBtn = document.getElementById('registerBtn');
const userMenu = document.getElementById('userMenu');
const userName = document.getElementById('userName');
const logoutBtn = document.getElementById('logoutBtn');
const authModal = document.getElementById('authModal');
const closeAuthModal = document.getElementById('closeAuthModal');
const authModalTitle = document.getElementById('authModalTitle');
const authSubmitBtn = document.getElementById('authSubmitBtn');
const nameGroup = document.getElementById('nameGroup');
const roleGroup = document.getElementById('roleGroup');
const adminLink = document.getElementById('adminLink');
const supplierLink = document.getElementById('supplierLink');

// Initialize
function init() {
    loadProducts();
    renderSupplierCarousel();
    updateCartCount();
    checkAuthStatus();
    attachEventListeners();
}

function loadProducts(reset = true) {
    if (reset) currentProductPage = 0;
    let products = [...getProducts()];
    
    // Filter by category
    const category = categoryFilter.value;
    if (category !== 'all') products = products.filter(p => p.category === category);
    
    // Filter by MOQ
    const moq = parseInt(moqFilter.value);
    if (moq > 0) products = products.filter(p => p.moq <= moq);
    
    // Search
    const searchTerm = searchInput.value.toLowerCase();
    if (searchTerm) products = products.filter(p => p.name.toLowerCase().includes(searchTerm));
    
    // Sort
    switch(sortFilter.value) {
        case 'price_low': products.sort((a,b) => a.price - b.price); break;
        case 'price_high': products.sort((a,b) => b.price - a.price); break;
        case 'name': products.sort((a,b) => a.name.localeCompare(b.name)); break;
        default: products.sort((a,b) => b.id - a.id);
    }
    
    allProducts = products;
    renderProducts();
}

function renderProducts() {
    const start = currentProductPage * productsPerPage;
    const end = start + productsPerPage;
    const pageProducts = allProducts.slice(start, end);
    
    if (currentProductPage === 0) productGrid.innerHTML = '';
    
    pageProducts.forEach(product => {
        const card = document.createElement('div');
        card.className = 'product-card';
        card.innerHTML = `
            <div class="product-image"><img src="${product.image}" alt="${product.name}"></div>
            <div class="product-badge">MOQ: ${product.moq}+</div>
            <div class="product-info"><h3>${product.name}</h3><p class="product-price">From R${product.price.toFixed(2)}</p><button class="btn-view" onclick="viewProduct(${product.id})">View Details</button><button class="btn-cart" onclick="addToCart(${product.id})"><i class="fas fa-shopping-cart"></i> Add</button></div>
        `;
        productGrid.appendChild(card);
    });
    
    if (loadMoreBtn) loadMoreBtn.style.display = end >= allProducts.length ? 'none' : 'block';
}

function viewProduct(id) { window.location.href = `product.html?id=${id}`; }

function addToCart(productId) {
    if (!currentUser) { alert('Please login to add items to cart'); showAuthModal('login'); return; }
    addToCart(currentUser.id, productId, 1);
    updateCartCount();
    alert('Added to cart!');
}

function updateCartCount() {
    if (currentUser) {
        const cart = getCart(currentUser.id);
        const count = cart.reduce((sum, item) => sum + item.quantity, 0);
        if (cartCount) cartCount.textContent = count;
    } else if (cartCount) cartCount.textContent = '0';
}

function renderSupplierCarousel() {
    const suppliers = getUsers().filter(u => u.role === 'supplier');
    const carousel = document.getElementById('supplierCarousel');
    if (carousel) carousel.innerHTML = suppliers.map(s => `<div class="supplier-card"><i class="fas fa-store"></i><h4>${s.company || s.name}</h4><p>${getProductsBySupplier(s.id).length} products</p></div>`).join('');
}

function checkAuthStatus() {
    const stored = localStorage.getItem('tradevault_user');
    if (stored) {
        currentUser = JSON.parse(stored);
        if (loginBtn) loginBtn.style.display = 'none';
        if (registerBtn) registerBtn.style.display = 'none';
        if (userMenu) userMenu.style.display = 'flex';
        if (userName) userName.textContent = currentUser.name;
        if (adminLink && currentUser.role === 'admin') adminLink.style.display = 'block';
        if (supplierLink && currentUser.role === 'supplier') supplierLink.style.display = 'block';
        updateCartCount();
    }
}

function logout() {
    localStorage.removeItem('tradevault_user');
    currentUser = null;
    window.location.reload();
}

function showAuthModal(mode) {
    authModalTitle.textContent = mode === 'login' ? 'Login' : 'Register';
    nameGroup.style.display = mode === 'register' ? 'block' : 'none';
    roleGroup.style.display = mode === 'register' ? 'block' : 'none';
    authSubmitBtn.textContent = mode === 'login' ? 'Login' : 'Register';
    authModal.classList.add('active');
    window.authMode = mode;
}

function handleAuth(e) {
    e.preventDefault();
    const email = document.getElementById('authEmail').value;
    const password = document.getElementById('authPassword').value;
    
    if (window.authMode === 'login') {
        const user = getUserByEmail(email);
        if (user && user.password === password) {
            localStorage.setItem('tradevault_user', JSON.stringify(user));
            window.location.reload();
        } else alert('Invalid credentials');
    } else {
        const name = document.getElementById('authName').value;
        const role = document.getElementById('authRole').value;
        if (getUserByEmail(email)) alert('Email already exists');
        else {
            const newUser = createUser({ name, email, password, role });
            localStorage.setItem('tradevault_user', JSON.stringify(newUser));
            window.location.reload();
        }
    }
}

function attachEventListeners() {
    if (categoryFilter) categoryFilter.addEventListener('change', () => loadProducts(true));
    if (moqFilter) moqFilter.addEventListener('change', () => loadProducts(true));
    if (sortFilter) sortFilter.addEventListener('change', () => loadProducts(true));
    if (searchInput) searchInput.addEventListener('input', () => loadProducts(true));
    if (loadMoreBtn) loadMoreBtn.addEventListener('click', () => { currentProductPage++; renderProducts(); });
    if (loginBtn) loginBtn.addEventListener('click', () => showAuthModal('login'));
    if (registerBtn) registerBtn.addEventListener('click', () => showAuthModal('register'));
    if (logoutBtn) logoutBtn.addEventListener('click', logout);
    if (closeAuthModal) closeAuthModal.addEventListener('click', () => authModal.classList.remove('active'));
    if (document.getElementById('authForm')) document.getElementById('authForm').addEventListener('submit', handleAuth);
    if (adminLink) adminLink.addEventListener('click', (e) => { e.preventDefault(); if (currentUser?.role === 'admin') window.location.href = 'admin.html'; });
    if (supplierLink) supplierLink.addEventListener('click', (e) => { e.preventDefault(); if (currentUser?.role === 'supplier') window.location.href = 'supplier.html'; });
    if (document.getElementById('cartBtn')) document.getElementById('cartBtn').addEventListener('click', () => { if (currentUser) window.location.href = 'checkout.html'; else showAuthModal('login'); });
}

init();