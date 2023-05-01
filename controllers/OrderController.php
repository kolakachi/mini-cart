<?php

namespace app\controllers;

use app\core\Controller;
use app\core\Request;
use app\models\Product;
use app\models\Order;
use app\models\Transaction;
use Stripe\Customer;
use \Stripe\Charge;
use \Stripe\Stripe;

class OrderController extends Controller
{
    public function add(Request $request) {
        try {
            $body = $request->getBody();
            $item = $body['item'];
            if(!isset($body['order_id']) || $body['order_id'] == '') {
                return $this->json([
                    "message" => "Order Id is required",
                ], 422);
            }
            $orderId = $body['order_id'];


            $order = Order::firstOrCreate([
                "order_id" => $orderId
            ]);
            $items = [$item];
            $order->cart = $items;
            $order->total = $this->getTotal($items);
            $order->save();

            return $this->json([
                "message" => "Created order",
                "order" => $order
            ], 201);

        } catch (\Exception $error) {
            return $this->json([
                "message" => $error->getMessage(),
            ], 500);
        }
    }

    public function getOrders(Request $request) {
        try {
            $params = $request->getRouteParams();

            $order = Order::where('order_id', $params['id'])->first();
            if(!$order) {
                return $this->json([
                    "message" => "Order not found"
                ], 404);
            }
            return $this->json([
                "order" => $order
            ], 200);

        } catch (\Exception $error) {
            return $this->json([
                "message" => $error->getMessage(),
            ], 500);
        }
    }

    public function addItemToOrder(Request $request) {
        try {
            $body = $request->getBody();
            $selectedItem = $body['item'];
            $params = $request->getRouteParams();

            $order = Order::where('order_id', $params['id'])->first();
            if(!$order) {
                return $this->json([
                    "message" => "Order not found"
                ], 404);
            }

            $items = $order->cart;
            $itemExists = false;
            foreach($items as &$item) {
                if($item['product_id'] == $selectedItem['product_id']) {
                    $item['quantity'] += $selectedItem['quantity'];
                    $itemExists = true;
                }
            }
            if(!$itemExists) {
                $items[] = $selectedItem;
            }

            $order->cart = $items;
            $order->total = $this->getTotal($items);
            $order->save();

            return $this->json([
                "message" => "Order Updated successfully",
                "order" => $order
            ], 201);

        } catch (\Exception $error) {
            return $this->json([
                "message" => $error->getMessage(),
            ], 500);
        }

    }

    public function deleteItemFromOrder(Request $request) {
        try {
            $body = $request->getBody();
            $selectedItem = $body['item'];
            $params = $request->getRouteParams();

            $order = Order::where('order_id', $params['id'])->first();
            if(!$order) {
                return $this->json([
                    "message" => "Order not found"
                ], 404);
            }

            $items = $order->cart;
            foreach($items as $key => &$item) {
                if($item['product_id'] == $selectedItem['product_id']) {
                    $item['quantity'] -= 1;
                    if($item['quantity'] < 1){
                        unset($items[$key]);
                    }
                }
            }

            $order->cart = $items;
            $order->total = $this->getTotal($items);
            $order->save();

            return $this->json([
                "message" => "Order Updated successfully",
                "order" => $order
            ], 201);

        } catch (\Exception $error) {
            return $this->json([
                "message" => $error->getMessage(),
            ], 500);
        }

    }

    private function getTotal($items) {
        $total = 0;
        foreach($items as $item) {
            $product = Product::where('id', $item['product_id'])->first();
            if($product) {
                $total += $product->price * $item['quantity'];
            }
        }

        return $total;

    }

    public function makePayment(Request $request) {
        try {

            $body = $request->getBody();
            $params = $request->getRouteParams();
            $email = $body['email'];
            $stripeToken = $body['stripe_token']['id'];

            $order = Order::where('order_id', $params['id'])->first();
            if(!$order) {
                return $this->json([
                    "message" => "Order not found"
                ], 404);
            }

            Stripe::setApiKey($_ENV['STRIP_SECRET_KEY']);

            $stripeCustomer = Customer::create([
                'email' => $email,
                'source' => $stripeToken
            ]);
            $requestBody = [
                'customer' => $stripeCustomer->id,
                'amount' => $order->total * 100,
                'currency' => 'eur',
                'description' => "test purchase",
                'metadata' => [
                    'order_id' => $order->order_id
                ]
            ];
            $chargeDetails = Charge::create($requestBody);
            $chargeDetails = $chargeDetails->jsonSerialize();

            $order->status = $chargeDetails['status'];
            $order->save();

            $transaction = new Transaction();
            $transaction->order_id = $order->order_id;
            $transaction->total = $order->total;
            $transaction->payload = json_encode($chargeDetails);
            $transaction->status = $chargeDetails['status'];
            $transaction->save();

            if ($chargeDetails['status'] != 'failed') {
                return $this->json([
                    "message" => "Transaction made successfully",
                ], 201);
            }

            return $this->json([
                "message" => "Transaction failed",
            ], 503);

        } catch (\Exception $error) {
            return $this->json([
                "message" => $error->getMessage(),
            ], 500);
        }
    }
}