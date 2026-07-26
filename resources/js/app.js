

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

/**
 * Salary Slip create/edit form — replaces ajax_get_salary_data.php's
 * pipe-delimited response with clean JSON. On a *new* slip, changing
 * employee/month/year prefills Days Worked/Overtime from attendance
 * (only for new slips, matching legacy — an existing slip's entered
 * values are never silently overwritten by attendance data on edit).
 * Rate and outstanding-advance figures are always kept live since
 * they're read-only display fields, not form inputs.
 */
Alpine.data('salarySlipForm', (dataUrl, isNew, initial) => ({
    employeeId: initial.employee_id ?? '',
    month: initial.salary_slip_month ?? '',
    year: initial.salary_slip_year ?? '',
    dayWork: initial.day_work ?? 0,
    overTime: initial.over_time ?? 0,
    ledgerBalance: 0,
    parDayAmount: 0,
    perDayExtra: 0,

    init() {
        this.fetchData();
    },

    async fetchData() {
        if (!this.employeeId || !this.month || !this.year) {
            return;
        }

        const params = new URLSearchParams({ employee_id: this.employeeId, month: this.month, year: this.year });
        const response = await fetch(`${dataUrl}?${params}`);

        if (!response.ok) {
            return;
        }

        const data = await response.json();

        if (isNew) {
            this.dayWork = data.total_days;
            this.overTime = data.total_over_time;
        }

        this.ledgerBalance = data.ledger_balance;
        this.parDayAmount = data.par_day_amount;
        this.perDayExtra = data.per_day_extra;
    },
}));

/**
 * Bill/Estimate measurement-sheet editor — replaces
 * add_edit_bill_measurement.php/add_edit_estimate_measurement.php's
 * dynamic "box"/"line" jQuery DOM manipulation. A measurement sheet is a
 * list of product groups ("boxes"), each with its own list of
 * length x breadth measurement lines. Quantity-per-line and total-per-
 * group formulas match count_total_bill() in the legacy JS exactly:
 * quantity = no x length x breadth x unit (blank fields default to 1),
 * group total = sum of that group's line quantities.
 */
Alpine.data('measurementForm', (initialGroups) => ({
    groups: initialGroups,

    blankLine() {
        return { service_no: '', description: '', no: '', length: '', breath: '', unit: '', quantity: '' };
    },

    blankGroup() {
        return { total: '', total_text: '', total_unit: '', lines: [this.blankLine()] };
    },

    addGroup() {
        this.groups.push(this.blankGroup());
    },

    removeGroup(groupIndex) {
        this.groups.splice(groupIndex, 1);
    },

    addLine(groupIndex) {
        this.groups[groupIndex].lines.push(this.blankLine());
    },

    removeLine(groupIndex, lineIndex) {
        this.groups[groupIndex].lines.splice(lineIndex, 1);
        this.recalcGroupTotal(groupIndex);
    },

    recalcLine(groupIndex, lineIndex) {
        const line = this.groups[groupIndex].lines[lineIndex];
        const no = parseFloat(line.no) || (line.no === '' ? null : 0);
        const length = parseFloat(line.length) || (line.length === '' ? null : 0);
        const breath = parseFloat(line.breath) || (line.breath === '' ? null : 0);
        const unit = parseFloat(line.unit) || (line.unit === '' ? null : 0);

        if (no === null && length === null && breath === null && unit === null) {
            return;
        }

        const factor = (v) => (v === null || v === '' ? 1 : v);
        line.quantity = (factor(no) * factor(length) * factor(breath) * factor(unit)).toFixed(3);

        this.recalcGroupTotal(groupIndex);
    },

    recalcGroupTotal(groupIndex) {
        const total = this.groups[groupIndex].lines
            .reduce((sum, line) => sum + (parseFloat(line.quantity) || 0), 0);
        this.groups[groupIndex].total = total.toFixed(3);
    },
}));

Alpine.start();
