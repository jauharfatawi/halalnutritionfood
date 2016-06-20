<?php namespace App\Http\Controllers;

use App\Models\FoodProduct;
use App\Models\Ingredient;

class PagesController extends Controller {

    public function getHome()
    {

        $foodproducts = FoodProduct::all();
        $addictives = Ingredient::where('itype',1)->get();
        return view('pages.home',compact('foodproducts','addictives'));
    }
    public function getAbout()
    {
        return view('pages.about');   
    }

    // public function submit()
    // {
    //     $ingredients = Ingredient::lists('iname','id');
    //     return view('pages.frontend.submit',compact('ingredients'));
    // }

    // public function foodlist()
    // {
    //     $foodproducts = FoodProduct::all();

    // }

    // public function addiclist()
    // {

    // }
}
