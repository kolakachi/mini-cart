<?php
namespace app\routes;

class WebRoutes
{
    public static function init($app) {

        $app->route->get('/', [new \app\controllers\HomeController(), 'index']);
        $app->route->get('/products',  [new \app\controllers\ProductController(), 'index']);
        $app->route->post('/orders',  [new \app\controllers\OrderController(), 'add']);
        $app->route->get('/orders/:id', [new \app\controllers\OrderController(), 'getOrders']);
        $app->route->post('/orders/:id/items', [new \app\controllers\OrderController(), 'addItemToOrder']);
        $app->route->delete('/orders/:id/items', [new \app\controllers\OrderController(), 'deleteItemFromOrder']);
        $app->route->post('/orders/:id/payment', [new \app\controllers\OrderController(), 'makePayment']);

    }
}