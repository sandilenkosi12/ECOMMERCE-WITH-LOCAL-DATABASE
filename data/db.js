// TradeVault Local Storage Database
const DB_NAME = 'tradevault_db';

// Initialize database
function initDB() {
    if (!localStorage.getItem(DB_NAME)) {
        const initialData = {
            users: [
                { id: 1, name: 'Admin User', email: 'admin@tradevault.com', password: 'admin123', role: 'admin', createdAt: new Date().toISOString() },
                { id: 2, name: 'Tech Supplier', email: 'supplier@tradevault.com', password: 'supplier123', role: 'supplier', company: 'Tech Wholesale SA', createdAt: new Date().toISOString() },
                { id: 3, name: 'John Buyer', email: 'buyer@tradevault.com', password: 'buyer123', role: 'buyer', createdAt: new Date().toISOString() }
            ],
            products: [
                { id: 1, name: 'Wireless Headphones', category: 'electronics', price: 450, moq: 10, description: 'High-quality wireless headphones with noise cancellation', image: 'https://placehold.co/300x300/FFB81C/1a1a2e?text=Headphones', tiers: [{qty:10,price:450},{qty:50,price:420},{qty:100,price:390}], supplierId: 2, createdAt: new Date().toISOString() },
                { id: 2, name: 'Bulk T-Shirts', category: 'clothing', price: 120, moq: 50, description: 'Cotton t-shirts for corporate events', image: 'https://placehold.co/300x300/FFB81C/1a1a2e?text=T-Shirts', tiers: [{qty:50,price:120},{qty:200,price:110},{qty:500,price:95}], supplierId: 2, createdAt: new Date().toISOString() },
                { id: 3, name: 'Coffee Beans', category: 'food', price: 280, moq: 20, description: 'Premium Arabica coffee beans', image: 'https://placehold.co/300x300/FFB81C/1a1a2e?text=Coffee', tiers: [{qty:20,price:280},{qty:100,price:260},{qty:500,price:240}], supplierId: 2, createdAt: new Date().toISOString() }
            ],
            orders: [],
            cart: []
        };
        localStorage.setItem(DB_NAME, JSON.stringify(initialData));
    }
    return JSON.parse(localStorage.getItem(DB_NAME));
}

function saveDB(data) {
    localStorage.setItem(DB_NAME, JSON.stringify(data));
}

// User functions
function getUsers() { return initDB().users; }
function getUserByEmail(email) { return initDB().users.find(u => u.email === email); }
function createUser(userData) { const db = initDB(); const newUser = { id: Date.now(), ...userData, createdAt: new Date().toISOString() }; db.users.push(newUser); saveDB(db); return newUser; }
function updateUser(id, updates) { const db = initDB(); const index = db.users.findIndex(u => u.id == id); if (index !== -1) { db.users[index] = { ...db.users[index], ...updates }; saveDB(db); return db.users[index]; } return null; }
function deleteUser(id) { const db = initDB(); db.users = db.users.filter(u => u.id != id); saveDB(db); }

// Product functions
function getProducts() { return initDB().products; }
function getProductById(id) { return initDB().products.find(p => p.id == id); }
function getProductsBySupplier(supplierId) { return initDB().products.filter(p => p.supplierId == supplierId); }
function createProduct(productData) { const db = initDB(); const newProduct = { id: Date.now(), ...productData, createdAt: new Date().toISOString() }; db.products.push(newProduct); saveDB(db); return newProduct; }
function updateProduct(id, updates) { const db = initDB(); const index = db.products.findIndex(p => p.id == id); if (index !== -1) { db.products[index] = { ...db.products[index], ...updates }; saveDB(db); return db.products[index]; } return null; }
function deleteProduct(id) { const db = initDB(); db.products = db.products.filter(p => p.id != id); saveDB(db); }

// Order functions
function getOrders() { return initDB().orders; }
function getOrdersByUser(userId) { return initDB().orders.filter(o => o.userId == userId); }
function createOrder(orderData) { const db = initDB(); const newOrder = { id: Date.now(), ...orderData, status: 'pending', createdAt: new Date().toISOString() }; db.orders.push(newOrder); saveDB(db); return newOrder; }
function updateOrderStatus(id, status) { const db = initDB(); const index = db.orders.findIndex(o => o.id == id); if (index !== -1) { db.orders[index].status = status; saveDB(db); return db.orders[index]; } return null; }

// Cart functions
function getCart(userId) { const db = initDB(); return db.cart.filter(c => c.userId == userId); }
function addToCart(userId, productId, quantity) { const db = initDB(); const existing = db.cart.find(c => c.userId == userId && c.productId == productId); if (existing) { existing.quantity += quantity; } else { db.cart.push({ userId, productId, quantity }); } saveDB(db); }
function removeFromCart(userId, productId) { const db = initDB(); db.cart = db.cart.filter(c => !(c.userId == userId && c.productId == productId)); saveDB(db); }
function clearCart(userId) { const db = initDB(); db.cart = db.cart.filter(c => c.userId != userId); saveDB(db); }

initDB();