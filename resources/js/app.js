

import Alpine from 'alpinejs';

window.Alpine = Alpine;

/**
 * Dynamic line-item editor for the Bill/Estimate forms — replaces the
 * legacy jQuery AJAX-appended-row pattern (ajax_get_product.php /
 * ajax_get_price.php) with a client-side product search against
 * /products/search, matching the "qty blank defaults to 1" and
 * "total = price × qty" rules from the legacy count_total_bill() JS.
 */
Alpine.data('lineItemForm', (initialItems, searchUrl) => ({
    items: initialItems,
    search: '',
    results: [],
    showResults: false,

    blankItem() {
        return {
            product_id: null, service_no: '', product_name: '',
            hsn_code: '', per_unit: '', price: 0, qty: 1, total: 0,
        };
    },

    addBlank() {
        this.items.push(this.blankItem());
    },

    removeItem(index) {
        this.items.splice(index, 1);
    },

    recalcTotal(index) {
        const item = this.items[index];
        const qty = parseFloat(item.qty) || 1;
        const price = parseFloat(item.price) || 0;
        item.total = Math.round(price * qty * 100) / 100;
    },

    get subtotal() {
        return this.items
            .reduce((sum, item) => sum + (parseFloat(item.total) || 0), 0)
            .toFixed(2);
    },

    async searchProducts() {
        if (this.search.trim().length === 0) {
            this.results = [];
            this.showResults = false;
            return;
        }

        const response = await fetch(`${searchUrl}?q=${encodeURIComponent(this.search)}`);
        this.results = await response.json();
        this.showResults = true;
    },

    pickProduct(product) {
        this.items.push({
            product_id: product.id,
            service_no: product.service_no || '',
            product_name: product.product_name,
            hsn_code: product.hsn_code || '',
            per_unit: product.per_unit || '',
            price: product.price,
            qty: 1,
            total: product.price,
        });
        this.search = '';
        this.results = [];
        this.showResults = false;
    },
}));

Alpine.start();
