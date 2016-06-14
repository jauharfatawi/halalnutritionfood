<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\FoodProduct;
use App\Models\Ingredient;
use App\Models\Certificate;
use App\Models\HalalSource;

use DB;
use Carbon\Carbon;
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

    public function getWriteToTurtle()
    {
        $turtlefile = fopen("resources.ttl", "w");
        // $vocabfile = file_get_contents('halalv.ttl', true);
        $prefix = "@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
@prefix owl: <http://www.w3.org/2002/07/owl#> .
@prefix dcterms: <http://purl.org/dc/terms/> .
@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .
@prefix vann: <http://purl.org/vocab/vann/> .
@prefix foaf: <http://xmlns.com/foaf/0.1/> .
@prefix dc: <http://purl.org/dc/elements/1.1/> .
@prefix halalv: <http://halalnutritionfood.com/halalv.ttl#> .
@prefix halalf: <http://halalnutritionfood.com/resources/foodproducts/>.ttl .
@prefix halali: <http://halalnutritionfood.com/resources/ingredients/>.ttl .
@prefix halals: <http://halalnutritionfood.com/resources/halalsources/>.ttl .
@prefix halalc: <http://halalnutritionfood.com/resources/certificates/>.ttl .
@prefix halalm: <http://halalnutritionfood.com/resources/manufactures/>.ttl .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .\n";
        

        if(!$turtlefile){
            return "error";
        }
        $ingWritted = 0;
        $foodProducts = FoodProduct::where('fVerify',1)->get()->toArray();

        // fwrite($turtlefile, $vocabfile);
        fwrite($turtlefile, $prefix);

        foreach ($foodProducts as $fp => $val) {
            $list[$fp]="\nhalalf:".$foodProducts[$fp]['id']." a halalv:FoodProduct.";
            fwrite($turtlefile, $list[$fp]);
        }

        foreach ($foodProducts as $fp => $val) {
            $list[$fp]="halalf:".$foodProducts[$fp]['id']." a halalv:FoodProduct;
\thalalv:foodCode \"".$foodProducts[$fp]['fCode']."\";
\trdfs:label \"".$foodProducts[$fp]['fName']."\";
\thalalv:manufacture \"".$foodProducts[$fp]['fManufacture']."\";
\thalalv:netWeight ".$foodProducts[$fp]['weight'].";
\thalalv:calories ".$foodProducts[$fp]['calories'].";
\thalalv:fat ".$foodProducts[$fp]['totalFat'].";
\thalalv:saturatedFat ".$foodProducts[$fp]['saturatedFat'].";
\thalalv:sodium ".$foodProducts[$fp]['sodium'].";
\thalalv:fiber ".$foodProducts[$fp]['dietaryFiber'].";
\thalalv:sugar ".$foodProducts[$fp]['sugar'].";
\thalalv:protein ".$foodProducts[$fp]['protein'].";
\thalalv:vitaminA ".$foodProducts[$fp]['vitaminA'].";
\thalalv:vitaminC ".$foodProducts[$fp]['vitaminC'].";
\thalalv:calcium ".$foodProducts[$fp]['calcium'].";
\thalalv:iron ".$foodProducts[$fp]['iron'].".\n";
            // fwrite($turtlefile, $list[$fp]);

            $resFoodProduct = fopen("resources/foodproducts/".$foodProducts[$fp]['id'].".ttl", "w");
            fwrite($resFoodProduct, $prefix."\n");
            fwrite($resFoodProduct, $list[$fp]);
            // fclose($resFoodProduct);

            $getManufacture = DB::select('select fManufacture from foodProducts where id = ?', [$foodProducts[$fp]['id']]);
            $insertManufacture = "halalm:".$foodProducts[$fp]['id']." a halalv:Manufacture;
\trdfs:label \"".$getManufacture[0]->fManufacture."\".\n";            
            // fwrite($turtlefile, $insertManufacture);
            $resManufacture = fopen("resources/manufactures/".$foodProducts[$fp]['id'].".ttl", "w");
            fwrite($resManufacture, $insertManufacture);
            fclose($resManufacture);

            $hasManufacture = "\nhalalf:".$foodProducts[$fp]['id']." halalv:manufacture halalm:".$foodProducts[$fp]['id'].".";
            // fwrite($turtlefile, $hasManufacture);
            fwrite($resFoodProduct, $hasManufacture."\n");
            // fclose($resFoodProduct);
            
            $getCertFK = DB::select('select * from foodProduct_certificate where foodProduct_id = ?', [$foodProducts[$fp]['id']]);
            foreach ($getCertFK as $id => $val) {
                $certificate[$id] = Certificate::findOrFail($getCertFK[$id]->certificate_id);
                if($certificate[$id]->cStatus == 0){
                    $cStatus = "Development";
                }
                elseif ($certificate[$id]->cStatus == 1) {
                    $cStatus = "New";   
                }
                else{
                    $cStatus = "Renew";
                }

                $insertCertificate = "halalc:".$certificate[$id]->id." a halalv:HalalCertificate;
\thalalv:halalCode \"".$certificate[$id]->cCode."\";
\thalalv:halalExp \"".$certificate[$id]->cExpire->format('Y-m-d')."\"^^xsd:date;
\thalalv:halalStatus \"".$cStatus."\";
\tfoaf:organization \"".$certificate[$id]->cOrganization."\".";
                // fwrite($turtlefile, $insertCertificate);
                $resCertificate = fopen("resources/certificates/".$certificate[$id]->id.".ttl", "w");
                fwrite($resCertificate, $insertCertificate);
                fclose($resCertificate);
            }

            $hasCertificate = "\nhalalf:".$foodProducts[$fp]['id']." halalv:certificate ";
            foreach ($getCertFK as $id => $val) {
                $hasCertificate = $hasCertificate."halalc:".$getCertFK[$id]->certificate_id.". ";
            }
            // fwrite($turtlefile, rtrim($hasCertificate,", \"").".\n");
            fwrite($resFoodProduct, $hasCertificate."\n");
            // fclose($resFoodProduct);

            $getIngFK = DB::select('select * from foodProduct_ingredient where foodProduct_id = ?', [$foodProducts[$fp]['id']]);
            foreach ($getIngFK as $id => $val) {
                $ingredient[$id] = Ingredient::findOrFail($getIngFK[$id]->ingredient_id);
                if($ingredient[$id]->iType == 0){
                    $insertIngredient = "
                    halali:".$ingredient[$id]->id." a halalv:Ingredient;
                    \thalalv:rank ".$ingredient[$id]->id.";
                    \trdfs:label \"".$ingredient[$id]->iName."\".\n";
                }
                else{
                    $insertIngredient = "halali:".$ingredient[$id]->id." a halalv:FoodAdditive;
\thalalv:rank ".$ingredient[$id]->id.";
\trdfs:label \"".$ingredient[$id]->iName."\";
\trdfs:comment \"".$ingredient[$id]->eNumber."\".\n";
                }
                if($ingWritted != $ingredient[$id]->id){
                    // fwrite($turtlefile, $insertIngredient);
                    $resIngredient = fopen("resources/ingredients/".$ingredient[$id]->id.".ttl", "w");
                    fwrite($resIngredient, $insertIngredient);
                    fclose($resIngredient);
                }
                $ingWritted = $ingredient[$id]->id;
            }
            
            $containsIng = "\nhalalf:".$foodProducts[$fp]['id']." halalv:containsIngredient ";
            foreach ($getIngFK as $id => $val) {
                $containsIng = $containsIng."halali:".$getIngFK[$id]->ingredient_id.", ";
            }
            // fwrite($turtlefile, rtrim($containsIng,", \"").".\n");
            fwrite($resFoodProduct, rtrim($containsIng,", \"").".\n");
            // fclose($resFoodProduct);

            foreach ($getIngFK as $id => $val) {
                $ingredient[$id] = Ingredient::findOrFail($getIngFK[$id]->ingredient_id);
                $halalIng = "\nhalali:".$ingredient[$id]['id']." halalv:halalSource ";
                if($ingredient[$id]->iType == 1){
                    $getHalalFK = DB::select('select * from ingredient_halal where ingredient_id = ?', [$ingredient[$id]->id]);
                    foreach ($getHalalFK as $id => $val) {
                        $halal[$id] = HalalSource::findOrFail($getHalalFK[$id]->halal_id);
                        if($halal[$id]->hStatus == 0){
                            $hStatus = "Halal";
                        }
                        elseif ($halal[$id]->hStatus == 1) {
                            $hStatus = "Mushbooh";   
                        }
                        else{
                            $hStatus = "Haraam";
                        }
                        $insertHalal = "halals:".$halal[$id]->id." a halalv:Source;
\trdfs:label \"".$hStatus."\";
\trdfs:comment \"".$halal[$id]->hDescription."\";
\tfoaf:organization \"".$halal[$id]->hOrganization."\";
\trdfs:seeAlso <".$halal[$id]->hUrl.">.\n";
                        // fwrite($turtlefile, $insertHalal);
                        $resHalalSource = fopen("resources/halalsources/".$halal[$id]->id.".ttl", "w");
                        fwrite($resHalalSource, $insertHalal);
                        fclose($resHalalSource);

                        $halalIng = $halalIng."halals:".$getHalalFK[$id]->halal_id.", ";
                        
                    }
                    // fwrite($turtlefile, rtrim($halalIng,", \"").".\n");
                    fwrite($resFoodProduct, rtrim($halalIng,", \""));
                    
                }
            }
            
        }
        fclose($resFoodProduct);
        // fclose("turtle.ttl");
        echo "berhasil";
        //jalankan skrip ke fuseki
        
    }
}
