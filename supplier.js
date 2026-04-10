let currentSupplier = null;
let salesChart = null;

function initSupplier() {
    const stored = localStorage.getItem('tradevault_user');
    if (!stored) { window.location.href = 'index.html'; return; }
    currentSupplier = JSON.parse(stored);
    if (currentSupplier.role !== 'supplier') { window.location.href = 'index.html'; return; }
    loadDashboardStats();
    loadProductsTable();
    setupEventListeners();
}

function loadDashboardStats() {
    const products = getProductsBySupplier(currentSupplier.id);
    const orders = getOrders().filter(o => o.supplierId === currentSupplier.id);
    const revenue = orders.reduce((sum, o) => sum + o.total, 0);
    document.getElementById('totalProducts').textContent = products.length;
    document.getElementById('totalOrders').textContent = orders.length;
    document.getElementById('totalRevenue').textContent = `R${revenue.toFixed(2)}`;
    document.getElementById('totalViews').textContent = Math.floor(Math.random() * 1000) + 100;
}

function loadProductsTable() {
    const products = getProductsBySupplier(currentSupplier.id);
    const tbody = document.querySelector('#productsTable tbody');
    tbody.innerHTML = products.map(p => `
        <tr><td>${p.id}</td><td><img src="${p.image}" width="40"></td><td>${p.name}</td><td>R${p.price}</td><td>${p.moq}</td><td>${Math.floor(Math.random() * 50)}</td>
        <td><button class="btn-edit" onclick="editProduct(${p.id})">Edit</button><button class="btn-delete" onclick="deleteProductItem(${p.id})">Delete</button></td></tr>
    `).join('');
}

function editProduct(id) {
    const product = getProductById(id);
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

function deleteProductItem(id) {
    if (confirm('Delete this product?')) { deleteProduct(id); loadProductsTable(); loadDashboardStats(); }
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
        supplierId: currentSupplier.id
    };
    if (id) updateProduct(id, productData);
    else createProduct(productData);
    document.getElementById('productFormContainer').style.display = 'none';
    loadProductsTable();
    loadDashboardStats();
}

function loadOrdersTable() {
    const orders = getOrders().filter(o => o.supplierId === currentSupplier.id);
    const tbody = document.querySelector('#ordersTable tbody');
    tbody.innerHTML = orders.map(o => `<tr><td>#${o.id}</td><td>${o.buyerName}</td><td>${o.productName}</td><td>${o.quantity}</td><td>R${o.total}</td><td><span style="color:#FFB81C">${o.status}</span></td><td>${new Date(o.createdAt).toLocaleDateString()}</td></tr>`).join('');
}

function setupEventListeners() {
    document.querySelectorAll('.supplier-sidebar nav a[data-tab]').forEach(a => {
        a.addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelectorAll('.supplier-sidebar nav a').forEach(link => link.classList.remove('active'));
            a.classList.add('active');
            const tab = a.dataset.tab;
            document.getElementById('dashboardTab').style.display = tab === 'dashboard' ? 'block' : 'none';
            document.getElementById('productsTab').style.display = tab === 'products' ? 'block' : 'none';
            document.getElementById('ordersTab').style.display = tab === 'orders' ? 'block' : 'none';
            document.getElementById('analyticsTab').style.display = tab === 'analytics' ? 'block' : 'none';
            if (tab === 'orders') loadOrdersTable();
            if (tab === 'analytics') initChart();
        });
    });
    document.getElementById('showAddProductForm')?.addEventListener('click', () => {
        document.getElementById('productId').value = '';
        document.getElementById('productFormContainer').style.display = 'block';
    });
    document.getElementById('saveProductBtn')?.addEventListener('click', saveProduct);
    document.getElementById('askAI')?.addEventListener('click', askAI);
}

function askAI() {
    const query = document.getElementById('aiQuery').value;
    const products = getProductsBySupplier(currentSupplier.id);
    const orders = getOrders().filter(o => o.supplierId === currentSupplier.id);
    const responseDiv = document.getElementById('aiResponse');
    let response = '';
    if (query.toLowerCase().includes('best selling') || query.toLowerCase().includes('selling best')) {
        response = `📊 Based on your data, your top-selling product is "${products[0]?.name || 'N/A'}" with approximately ${Math.floor(Math.random() * 100)} units sold this month.`;
    } else if (query.toLowerCase().includes('inventory') || query.toLowerCase().includes('restock')) {
        response = `📦 Inventory recommendation: Restock "${products[0]?.name || 'your products'}" within 2 weeks based on current sales velocity. Consider ordering ${products[0]?.moq || 50}+ units for best pricing.`;
    } else if (query.toLowerCase().includes('buyer') || query.toLowerCase().includes('behavior')) {
        response = `👥 Buyer insights: Most orders come from the ${orders.length > 0 ? 'Gauteng' : 'Johannesburg'} region. Average order value is R${orders.length > 0 ? (orders.reduce((s,o)=>s+o.total,0)/orders.length).toFixed(2) : '0'}.`;
    } else {
        response = `🤖 AI Analysis: Your ${products.length} products have generated ${orders.length} orders. Top category: ${products[0]?.category || 'N/A'}. Consider adding more products in this category to boost sales.`;
    }
    responseDiv.style.display = 'block';
    responseDiv.innerHTML = `<i class="fas fa-robot"></i> ${response}`;
}

function initChart() {
    const ctx = document.getElementById('salesChart')?.getContext('2d');
    if (!ctx) return;
    if (salesChart) salesChart.destroy();
    salesChart = new Chart(ctx, {
        type: 'line',
        data: { labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'], datasets: [{ label: 'Monthly Sales (R)', data: [12000, 19000, 15000, 25000, 22000, 30000], borderColor: '#FFB81C', backgroundColor: 'rgba(255,184,28,0.1)', fill: true }] },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { labels: { color: '#fff' } } } }
    });
}

initSupplier();