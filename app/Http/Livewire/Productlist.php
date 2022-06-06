<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Shoppingcart;

class Productlist extends Component
{
    public $products;

    public function render()
    {
        $this->products = Product::get();

        return view('livewire.productlist');
    }

    public function addToCart($id){
        if(auth()->user()){
            // add to cart
            $data = [
                'user_id' => auth()->user()->id,
                'product_id' => $id,
            ];
            Shoppingcart::updateOrCreate($data);

            $this->emit('updateCartCount');

            session()->flash('success','Product added to the cart successfully');
        }else{
            // redirect to login page
            return redirect(route('login'));
        }
    }
}
