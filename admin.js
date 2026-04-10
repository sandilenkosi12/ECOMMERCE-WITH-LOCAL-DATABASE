let currentTab = 'dashboard';

function switchTab(tab) {
    currentTab = tab;
    document.querySelectorAll('.admin-sidebar nav a').forEach(a => a.classList.remove('active'));
    document.querySelector(`.admin-sidebar nav a[data-tab="${tab}"]`).classList.add('active');
    document.getElementById('dashboardTab').style.display = tab === 'dashboard' ? 'block' : 'none';
    document.getElementById('productsTab').style.display = tab === 'products' ? 'block' : 'none';
    document.getElementById('usersTab').style.display = tab === 'users' ? 'block' : 'none';
    document.getElementById('ordersTab').style.display = tab === 'orders' ? 'block' : 'none';
    document.getElementById('suppliersTab').style.display = tab === 'suppliers' ? 'block' : 'none';
    if (tab === 'dashboard') loadDashboardStats();
    if (tab === 'products') loadProductsTable();
    if (tab === 'users') loadUsersTable();
    if (tab === 'orders') loadOrdersTable();
    if (tab === 'suppliers') loadSuppliersTable();
}

function loadDashboardStats() {
    const products = getProducts();
    const users = getUsers();
    const orders = getOrders();
    document.getElementById('totalProducts').textContent = products.length;
    document.getElementById('totalUsers').textContent = users.length;
    document.getElementById('totalSuppliers').textContent = users.filter(u => u.role === 'supplier').length;
    document.getElementById('totalOrders').textContent = orders.length;
}

function loadProductsTable() {
    const tbody = document.querySelector('#productsTable tbody');
    const products = getProducts();
    tbody.innerHTML = products.map(p => `
        <tr><td>${p.id}</td><td><img src="${p.image}" width="40"></td><td>${p.name}</td><td>${p.category}</td><td>R${p.price}</td><td>${p.moq}</td><td><button class="btn-edit" onclick="editProduct(${p.id})">Edit</button><button class="btn-delete" onclick="deleteProductItem(${p.id})">Delete</button></td></tr>
    `).join('');
}

function editProduct(id) {
    const product = getProductById(id);
    if (product) {
        document.getElementById('productId').value = product.id;
        document.getElementById('productName').value = product.name;
        document.getElementById('productCategory').value = product.category;
        document.getElementById('productPrice').value = product.price;
        document.getElementById('productMOQ').value = product.moq;
        document.getElementById('productDesc').value = product.description;
        document.getElementById('productImage').value = product.image;
        document.getElementById('productTiers').value = JSON.stringify(product.tiers);
        document.getElementById('productFormContainer').style.display = 'block';
    }
}

function deleteProductItem(id) {
    if (confirm('Delete this product?')) {
        deleteProduct(id);
        loadProductsTable();
        loadDashboardStats();
    }
}

function saveProduct() {
    const id = document.getElementById('productId').value;
    const productData = {
        name: document.getElementById('productName').value,
        category: document.getElementById('productCategory').value,
        price: parseFloat(document.getElementById('productPrice').value),
        moq: parseInt(document.getElementById('productMOQ').value),
        description: document.getElementById('productDesc').value,
        image: document.getElementById('productImage').value,
        tiers: JSON.parse(document.getElementById('productTiers').value || '[]'),
        supplierId: 2
    };
    if (id) updateProduct(id, productData);
    else createProduct(productData);
    document.getElementById('productFormContainer').style.display = 'none';
    loadProductsTable();
    loadDashboardStats();
}

function loadUsersTable() {
    const tbody = document.querySelector('#usersTable tbody');
    const users = getUsers();
    tbody.innerHTML = users.map(u => `<tr><td>${u.id}</td><td>${u.name}</td><td>${u.email}</td><td>${u.role}</td><td><button class="btn-delete" onclick="deleteUserItem(${u.id})">Delete</button></td></tr>`).join('');
}

function deleteUserItem(id) { if (confirm('Delete user?')) { deleteUser(id); loadUsersTable(); loadDashboardStats(); } }

function loadOrdersTable() {
    const tbody = document.querySelector('#ordersTable tbody');
    const orders = getOrders();
    tbody.innerHTML = orders.map(o => `<tr><td>#${o.id}</td><td>${o.buyerName}</td><td>${o.items}</td><td>R${o.total}</td><td><span style="color:#FFB81C">${o.status}</span></td><td>${new Date(o.createdAt).toLocaleDateString()}</td></tr>`).join('');
}

function loadSuppliersTable() {
    const tbody = document.querySelector('#suppliersTable tbody');
    const suppliers = getUsers().filter(u => u.role === 'supplier');
    tbody.innerHTML = suppliers.map(s => `<tr><td>${s.id}</td><td>${s.company || s.name}</td><td>${s.email}</td><td>${getProductsBySupplier(s.id).length}</td><td><span style="color:#10b981">Active</span></td></tr>`).join('');
}

document.querySelectorAll('.admin-sidebar nav a[data-tab]').forEach(a => a.addEventListener('click', (e) => { e.preventDefault(); switchTab(a.dataset.tab); }));
document.getElementById('showAddProductForm')?.addEventListener('click', () => { document.getElementById('productId').value = ''; document.getElementById('productFormContainer').style.display = 'block'; });
document.getElementById('saveProductBtn')?.addEventListener('click', saveProduct);
switchTab('dashboard');