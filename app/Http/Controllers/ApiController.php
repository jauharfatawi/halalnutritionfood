<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\FoodProduct;
use App\Models\Ingredient;
use App\Models\Certificate;
use App\Models\HalalSource;

use DB, Input;
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
        //Resource Produk
        $productfile = fopen("resources.ttl", "w");

        $prefix = "@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
@prefix owl: <http://www.w3.org/2002/07/owl#> .
@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .
@prefix foaf: <http://xmlns.com/foaf/0.1/> .
@prefix halalv: <http://halalnutritionfood.com/halalv.ttl#> .
@prefix halalf: <http://halalnutritionfood.com/resources/foodproducts/> .
@prefix halali: <http://halalnutritionfood.com/resources/ingredients/> .
@prefix halals: <http://halalnutritionfood.com/resources/halalsources/> .
@prefix halalc: <http://halalnutritionfood.com/resources/certificates/> .
@prefix halalm: <http://halalnutritionfood.com/resources/manufactures/> .\n";
        
        $ingWritted = 0;
        $foodProducts = FoodProduct::where('fVerify',1)->get()->toArray();

        fwrite($productfile, $prefix);
        
        //Menuliskan Resource foodproducts.ttl
        foreach ($foodProducts as $fp => $val) {
            $list[$fp]="\nhalalf:".$foodProducts[$fp]['id']." a halalv:FoodProduct.";
            fwrite($productfile, $list[$fp]);
        }

        foreach ($foodProducts as $fp => $val) {
            //=========================== FOOD PRODUCT ==================================
            //Menuliskan ke turtle
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

            //Menuliskan file resource foodproducts
            $fileFoodProduct = "resources/foodproducts/".$foodProducts[$fp]['id'].".ttl";
            $resFoodProduct = fopen($fileFoodProduct, "w");
            fwrite($resFoodProduct, $prefix."\n");
            fwrite($resFoodProduct, $list[$fp]);

            $hasManufacture = "\nhalalf:".$foodProducts[$fp]['id']." halalv:manufacture halalm:".$foodProducts[$fp]['id'].".";
            
            //Menuliskan hubungan foodproduct dengan manufacture di file resource foodproducts
            fwrite($resFoodProduct, $hasManufacture."\n");

            $getCertFK = DB::select('select * from foodProduct_certificate where foodProduct_id = ?', [$foodProducts[$fp]['id']]);
            $hasCertificate = "\nhalalf:".$foodProducts[$fp]['id']." halalv:certificate ";
            foreach ($getCertFK as $id => $val) {
                $hasCertificate = $hasCertificate."halalc:".$getCertFK[$id]->certificate_id.", ";
            }

            //Menuliskan hubungan foodproduct dengan certificate di file resource foodproducts
            if(substr($hasCertificate, -3,1) !== "e"){
                fwrite($resFoodProduct, rtrim($hasCertificate,", \"").".\n");    
            }

            $getIngFK = DB::select('select * from foodProduct_ingredient where foodProduct_id = ?', [$foodProducts[$fp]['id']]);

            //Menuliskan kandungan komposisi
            $containsIng = "\nhalalf:".$foodProducts[$fp]['id']." halalv:containsIngredient ";
            foreach ($getIngFK as $id => $val) {
                $containsIng = $containsIng."halali:".$getIngFK[$id]->ingredient_id.", ";
            }
            
            //Menuliskan file resource certificates
            fwrite($resFoodProduct, rtrim($containsIng,", \"").".\n");

            //=========================== MANUFACTURE ==================================
            //Get manufacture
            $getManufacture = DB::select('select fManufacture from foodProducts where id = ?', [$foodProducts[$fp]['id']]);
            $insertManufacture = "halalm:".$foodProducts[$fp]['id']." a halalv:Manufacture;
\trdfs:label \"".$getManufacture[0]->fManufacture."\".\n";   
            
            //Menuliskan file resource manufacture
            $fileManufacture = "resources/manufactures/".$foodProducts[$fp]['id'].".ttl";
            $resManufacture = fopen($fileManufacture, "w");
            fwrite($resManufacture, $prefix."\n");
            fwrite($resManufacture, $insertManufacture);
            fclose($resManufacture);

            //=========================== CERTIFICATE ==================================    
            //Get certificate
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
                $insertCertificate = "\nhalalc:".$certificate[$id]->id." a halalv:HalalCertificate;
\thalalv:halalCode \"".$certificate[$id]->cCode."\";
\thalalv:halalExp \"".$certificate[$id]->cExpire->format('Y-m-d')."\"^^xsd:date;
\thalalv:halalStatus \"".$cStatus."\";
\tfoaf:organization \"".$certificate[$id]->cOrganization."\".";

                //Menuliskan file resource certificates
                $fileCertificate = "resources/certificates/".$certificate[$id]->id.".ttl";
                $resCertificate = fopen($fileCertificate, "w");
                fwrite($resCertificate, $prefix."\n");
                fwrite($resCertificate, $insertCertificate);
                fclose($resCertificate);
            }

            //=========================== INGREDIENT ==================================
            //Get Ingredient
            foreach ($getIngFK as $id => $val) {
                $ingredient[$id] = Ingredient::findOrFail($getIngFK[$id]->ingredient_id);
                $halalIng = "\nhalali:".$ingredient[$id]['id']." halalv:halalSource ";
                $halalId = $ingredient[$id]['id'];
                if($ingredient[$id]->iType == 0){
                    $insertIngredient = "\nhalali:".$ingredient[$id]->id." a halalv:Ingredient;
\thalalv:rank ".$ingredient[$id]->id.";
\trdfs:label \"".$ingredient[$id]->iName."\".";
                }
                else{
                    $insertIngredient = "\nhalali:".$ingredient[$id]->id." a halalv:FoodAdditive;
\thalalv:rank ".$ingredient[$id]->id.";
\trdfs:label \"".$ingredient[$id]->iName."\";
\trdfs:comment \"".$ingredient[$id]->eNumber."\";\n";
                    
                    $DBpedia = @file_get_contents("http://dbpedia.org/sparql?default-graph-uri=http%3A%2F%2Fdbpedia.org&query=select+distinct+%3Fresource+where+%0D%0A%7B+%3Fresource+rdfs%3Alabel+%22".str_replace(" ","+",$ingredient[$id]->iName)."%22%40en+%7D&format=application%2Fsparql-results%2Bjson&CXML_redir_for_subjs=121&CXML_redir_for_hrefs=&timeout=30000&debug=on");
                    if($DBpedia) {
                        $insertIngredient = $insertIngredient."\towl:sameAs <http://dbpedia.org/resource/".str_replace(' ', '_', $ingredient[$id]->iName).">.";
                    }
                    else{
                        $insertIngredient = rtrim($insertIngredient,";\n").".";
                    }

                    //=========================== HALAL SOURCE ==================================
                    //Get Halal Source
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
                        $insertHalal = "\nhalals:".$halal[$id]->id." a halalv:Source;
\trdfs:label \"".$hStatus."\";
\trdfs:comment \"".$halal[$id]->hDescription."\";
\tfoaf:organization \"".$halal[$id]->hOrganization."\";
\trdfs:seeAlso <".$halal[$id]->hUrl.">.";
                        
                        //Menuliskan file resource halalsource
                        $fileHalalSource = "resources/halalsources/".$halal[$id]->id.".ttl";
                        $resHalalSource = fopen($fileHalalSource, "w");
                        fwrite($resHalalSource, $prefix);
                        fwrite($resHalalSource, $insertHalal);
                        fclose($resHalalSource);
                        
                        $halalIng = $halalIng."halals:".$getHalalFK[$id]->halal_id.", ";
                    }
                    $halalSource[$halalId] = rtrim($halalIng,", \"").".";
                    
                }
                if($ingWritted != $ingredient[$id]->id){
                    //Menuliskan file resource ingredients
                    $fileIngredient = "resources/ingredients/".$halalId.".ttl";
                    $resIngredient = fopen($fileIngredient, "w");
                    fwrite($resIngredient, $prefix);
                    fwrite($resIngredient, $insertIngredient);
                    if (isset($halalSource[$halalId])) {
                        fwrite($resIngredient, $halalSource[$halalId]);
                    }
                    fclose($resIngredient);
                }
                $ingWritted = $ingredient[$id]->id;
            }
            
            //lanjut ke food product selanjutnya
            fclose($resFoodProduct);
        }
        echo "berhasil";
    }
    public function getSparql()
    {
        return view('pages/sparql');
    }
    public function postSparql(Request $request)
    {
        $query = $request->input('query');
        $output = $request->input('output');
        header('location: http://128.199.237.114:3030/lodhalal/sparql?query='.urlencode($query).'&output='.$output);
    }
}
