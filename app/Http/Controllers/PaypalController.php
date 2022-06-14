<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

use App\Models\{
    Shoppingcart,
    Order
};

class PaypalController extends Controller
{
    public function cancel(Request $request){
        if($request->token){
            (new Shoppingcart)->where('payment_id', $request->token)
                ->update([
                    'payment_id' => '',
                    'status'      => Shoppingcart::STATUS['pending']
                ]);
        }

        return redirect()
                    ->route('shoppingcart')
                    ->with('error', 'Your payment has been cancelled !!!');

    }

    public function success(Request $request){
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request['token']);

        if(isset($response['status']) && $response['status'] == 'COMPLETED'){
            $items = Shoppingcart::where([
                'user_id' => auth()->user()->id,
                'payment_id' => $response['id']
            ])
            ->with('product')
            ->get();

            foreach($items as $item){
                $order = new Order;
                $order->user_id = auth()->user()->id;
                $order->product_id = $item->product_id;
                $order->payment_id = $item->payment_id;
                $order->amount = $item->product->price * $item->quantity;
                $order->save();

                $item->status = Shoppingcart::STATUS['success'];
                $item->save();
            }
            return redirect()
                    ->route('shoppingcart')
                    ->with('success','Transaction Completed !!!!!');
        }

        return redirect()
                    ->route('shoppingcart')
                    ->with('error', $response['messsage'] ?? 'Something went wrong. !!!!');
    }
}
