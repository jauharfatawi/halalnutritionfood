<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\FoodProduct;
use App\Models\Ingredient;
use App\Models\Certificate;
use App\Models\HalalSource;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Datatables;

class ApiController extends Controller
{
    // public function getCheckFCode($fcode)
    // {
    //     $foodproduct = FoodProduct::where('fcode',$fcode)->get();
    //     if($foodproduct->isEmpty()){
    //         $check = 'false';
    //     }
    //     else{
    //         $check = 'true';
    //     }
    //     $check = 'false';
    //     return response($check)->header('Content-Type', 'json');
    // }

    public function getFoodProductList()
    {
        $search = strip_tags(trim($_GET['q']));
        $foodProducts = FoodProduct::where('fName', 'LIKE', '%'.$search.'%')->get();

        if(count($foodProducts) > 0){
            foreach ($foodProducts as $fp => $value) {
                $data[] = array('id' => $value['id'], 'text' => $value['fName']);
            }
        } else {
            $data[] = array('disabled' => 'disabled', 'id' => '0', 'text' => 'No Food Product Found');
        }
        echo json_encode($data);
    }

    public function getAdditiveList()
    {
        $search = strip_tags(trim($_GET['q']));
        $ingredients = Ingredient::where('iName', 'LIKE', '%'.$search.'%')->where('iType',1)->get();

        if(count($ingredients) > 0){
            foreach ($ingredients as $ing => $value) {
                $data[] = array('id' => $value['id'], 'text' => $value['iName']);
            }
        } else {
            $data[] = array('disabled' => 'disabled', 'id' => '0', 'text' => 'No Ingredient Found');
        }
        echo json_encode($data);
    }

    public function getIngredientList()
    {
        $search = strip_tags(trim($_GET['q']));
        $ingredients = Ingredient::where('iName', 'LIKE', '%'.$search.'%')->get();

        if(count($ingredients) > 0){
            foreach ($ingredients as $ing => $value) {
                $data[] = array('id' => $value['id'], 'text' => $value['iName']);
            }
        } else {
            $data[] = array();
        }
        echo json_encode($data);
    }

    public function getAdditiveData()
    {
        return Datatables::of(Ingredient::where('iType',1)->get())->make(true);
    }

    public function getFoodProductData()
    {
        return Datatables::of(FoodProduct::all())->make(true);
    }

    public function getManufactureList()
    {
        $manufacture = FoodProduct::distinct()->lists('fManufacture');
        return json_encode($manufacture);
    }

    public function getHalalOrgList()
    {
        $hOrganization = HalalSource::distinct()->lists('hOrganization');
        return json_encode($hOrganization);
    }

    public function getCertOrgList()
    {
        $certificate = Certificate::distinct()->lists('cOrganization');
        return json_encode($certificate);
    }
}
