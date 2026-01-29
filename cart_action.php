<?php

require_once __DIR__ . '/includes/bootstrap.php';

$action = post('action', '');
$productId = (int)post('product_id', 0);
$qty = (int)post('quantity', 1);
$return = post('return', get('return', 'cart.php'));

if ($action === 'add') {
    if ($productId > 0 && $qty > 0) {
        $stmt = db()->prepare('SELECT id FROM products WHERE id = ? AND is_available = 1');
        $stmt->execute([$productId]);
        if ($stmt->fetch()) {
            cart_add($productId, $qty);
        }
    }
}

if ($action === 'update') {
    if ($productId > 0) {
        cart_update($productId, $qty);
    }
}

if ($action === 'remove') {
    if ($productId > 0) {
        cart_remove($productId);
    }
}

if ($action === 'clear') {
    cart_clear();
}

redirect_to($return);
