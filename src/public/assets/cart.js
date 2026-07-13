(function () {
    'use strict';

    const csrfToken = document.body.dataset.csrf;
    const root = document.getElementById('cart-root');

    const money = (cents) => '$' + (cents / 100).toFixed(2);

    async function callApi(body) {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(Object.assign({ csrf_token: csrfToken }, body)),
        });
        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.error || 'Request failed');
        }
        return data;
    }

    function render(summary) {
        if (summary.items.length === 0) {
            root.innerHTML = '<p class="cart-empty">Your cart is empty.</p>';
            return;
        }

        const rows = summary.items.map((item) => `
            <tr data-product="${item.product_id}">
                <td>
                    <span class="item-name">${escapeHtml(item.name)}</span>
                    <span class="item-sku">${escapeHtml(item.sku)}</span>
                </td>
                <td class="num">${money(item.price_cents)}</td>
                <td class="qty-cell">
                    <button class="btn btn-qty" data-step="-1" aria-label="Decrease quantity">&minus;</button>
                    <input class="qty-input" type="number" min="0" max="99" value="${item.qty}"
                           aria-label="Quantity for ${escapeHtml(item.name)}">
                    <button class="btn btn-qty" data-step="1" aria-label="Increase quantity">+</button>
                </td>
                <td class="num">${money(item.row_total_cents)}</td>
                <td><button class="btn btn-remove" data-remove aria-label="Remove item">&times;</button></td>
            </tr>`).join('');

        root.innerHTML = `
            <table class="cart-table">
                <thead>
                    <tr><th>Item</th><th class="num">Price</th><th>Qty</th><th class="num">Total</th><th></th></tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
            <dl class="totals">
                <dt>Subtotal</dt><dd>${money(summary.subtotal_cents)}</dd>
                <dt>GST (5%)</dt><dd>${money(summary.gst_cents)}</dd>
                <dt>QST (9.975%)</dt><dd>${money(summary.qst_cents)}</dd>
                <dt class="grand">Grand Total</dt><dd class="grand">${money(summary.total_cents)}</dd>
            </dl>
            <button class="btn btn-checkout" disabled title="Next step — not part of this test">Proceed to Checkout</button>`;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function currentQty(row) {
        return parseInt(row.querySelector('.qty-input').value, 10) || 0;
    }

    async function setQty(productId, qty) {
        try {
            render(await callApi({ action: 'set_qty', product_id: productId, qty: qty }));
        } catch (err) {
            alert(err.message);
        }
    }

    document.addEventListener('click', async (event) => {
        const addButton = event.target.closest('[data-add]');
        if (addButton) {
            addButton.disabled = true;
            try {
                render(await callApi({ action: 'add', product_id: Number(addButton.dataset.add), qty: 1 }));
            } catch (err) {
                alert(err.message);
            } finally {
                addButton.disabled = false;
            }
            return;
        }

        const row = event.target.closest('tr[data-product]');
        if (!row) {
            return;
        }
        const productId = Number(row.dataset.product);

        if (event.target.closest('[data-remove]')) {
            await setQty(productId, 0);
        } else if (event.target.closest('[data-step]')) {
            const step = Number(event.target.closest('[data-step]').dataset.step);
            await setQty(productId, currentQty(row) + step);
        }
    });

    root.addEventListener('change', (event) => {
        const row = event.target.closest('tr[data-product]');
        if (row && event.target.classList.contains('qty-input')) {
            setQty(Number(row.dataset.product), currentQty(row));
        }
    });

    render(JSON.parse(root.dataset.initial));
})();
