<?php

function cart_get()
{
    if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    return $_SESSION['cart'];
}

function cart_set(array $cart)
{
    $_SESSION['cart'] = $cart;
}

function cart_add($productId, $qty = 1)
{
    $productId = (int)$productId;
    $qty = (int)$qty;

    if ($productId <= 0 || $qty <= 0) {
        return;
    }

    $cart = cart_get();
    $cart[$productId] = (int)($cart[$productId] ?? 0) + $qty;
    cart_set($cart);
}

function cart_update($productId, $qty)
{
    $productId = (int)$productId;
    $qty = (int)$qty;

    if ($productId <= 0) {
        return;
    }

    $cart = cart_get();

    if ($qty <= 0) {
        unset($cart[$productId]);
    } else {
        $cart[$productId] = $qty;
    }

    cart_set($cart);
}

function cart_remove($productId)
{
    $productId = (int)$productId;
    if ($productId <= 0) {
        return;
    }
    $cart = cart_get();
    unset($cart[$productId]);
    cart_set($cart);
}

function cart_clear()
{
    $_SESSION['cart'] = [];
}

function cart_items_with_products()
{
    $cart = cart_get();
    if (!$cart) {
        return [];
    }

    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = db()->prepare('SELECT id, name, price, image, is_available FROM products WHERE id IN (' . $placeholders . ')');
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    $byId = [];
    foreach ($products as $p) {
        $byId[(int)$p['id']] = $p;
    }

    $items = [];
    foreach ($cart as $id => $qty) {
        $id = (int)$id;
        $qty = (int)$qty;
        if (!isset($byId[$id])) {
            continue;
        }

        $price = (float)$byId[$id]['price'];
        $subtotal = $price * $qty;
        $items[] = [
            'product' => $byId[$id],
            'quantity' => $qty,
            'subtotal' => $subtotal,
        ];
    }

    return $items;
}

function cart_total_amount()
{
    $total = 0.0;
    foreach (cart_items_with_products() as $item) {
        $total += (float)$item['subtotal'];
    }
    return $total;
}
