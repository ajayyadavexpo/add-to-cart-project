<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Shoppingcart as Cart;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class Shoppingcart extends Component
{
    public $cartitems, $sub_total = 0, $total = 0, $tax = 0;

    public function render()
    {
        $this->cartitems = Cart::with('product')
                ->where(['user_id'=>auth()->user()->id])
                ->where('status', '!=', Cart::STATUS['success'])
                ->get();
        $this->total = 0;$this->sub_total = 0; $this->tax = 0;

        foreach($this->cartitems as $item){
            $this->sub_total += $item->product->price * $item->quantity;
        }
        $this->total = $this->sub_total - $this->tax;

        return view('livewire.shoppingcart');
    }

    public function incrementQty($id){
        $cart = Cart::whereId($id)->first();
        $cart->quantity += 1;
        $cart->save();

        session()->flash('success', 'Product quantity updated !!!');
    }

    public function decrementQty($id){
        $cart = Cart::whereId($id)->first();
        if($cart->quantity > 1){
            $cart->quantity -= 1;
            $cart->save();
            session()->flash('success', 'Product quantity updated !!!');
        }else{
            session()->flash('info','You cannot have less than 1 quantity');
        }
    }

    public function removeItem($id){
        $cart = Cart::whereId($id)->first();

        if($cart){
            $cart->delete();
            $this->emit('updateCartCount');
        }
        session()->flash('success', 'Product removed from cart !!!');
    }

    public function checkout(){
        $provider = new PayPalClient([]);
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);

        $order = $provider->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => 'USD',
                        'value'  => $this->total
                    ]
                ]
            ],
            'application_context' => [
                'cancel_url' => route('payment.cancel'),
                'return_url' => route('payment.success')
            ]

        ]);

        if($order['status'] == 'CREATED'){
            foreach($this->cartitems as $item){
                $item->status = Cart::STATUS['in_process'];
                $item->payment_id = $order['id'];
                $item->save();
            }
            return redirect($order['links'][1]['href']);
        }
        session()->flash('error','Something went wrong, Please Try again');
    }
}
