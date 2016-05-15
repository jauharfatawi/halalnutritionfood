<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Validator, Requests;
use JavaScript;

use Carbon\Carbon;
use App\Models\FoodProduct;
use App\Models\Ingredient;
use App\Models\Certificate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FoodProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $foodProducts = FoodProduct::all();
        return view('foodproducts/list',compact('foodProducts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('foodproducts/create');
    }

    public static $rules = [
        'fCode' => ['required','min:8','max:13'],
        'fName' => ['required','min:5'],
        'fManufacture' => ['required','min:3'],
        'ingredient_list' => ['required'],
    ];

    public static $messages = [
        'fCode.required' => 'Food code is required',
        'fCode.min' => 'Food code too short',
        'fCode.max' => 'Food code too long',
        'fName.required' => 'Food name is required',
        'fName.min' => 'Food name too short',
        'fManufacture.required' => 'Food manufacture is required',
        'fManufacture.min' => 'Food manufacture too short',
        'ingredient_list.required' => 'Food ingredient is required',
    ];

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = Input::all();
        $addRules = ['unique:foodProducts'];
        $addMessages = [
            'fCode.unique' => 'Food code must unique',
        ];
        $rules = [
            'fCode' => array_merge(self::$rules['fCode'], $addRules),
            'fName' => self::$rules['fName'],
            'fManufacture' => self::$rules['fManufacture'],
            'ingredient_list' => self::$rules['ingredient_list']
        ]; 
        $messages =  array_merge(self::$messages, $addMessages);
        $validator = Validator::make($input, $rules, $messages);
        if($validator->fails())
        {
            return redirect()->back()
                ->withErrors($validator);
        }
        //Get Ingredient
        foreach($request->input('ingredient_list') as $ing => $i){
            $storeIngredient = $this->storeIngredient($request, $ing);
            if(is_object($storeIngredient)){
                // return redirect()->back()
                // ->withErrors($storeCertificate);    
            }
            else{
                $ingredient[$ing] = $storeIngredient;
            }
        }
        //Get Certificate
        foreach ($request->input('cCode') as $key => $c){
            if (!empty($request->input('cCode')[$key])) {
                $storeCertificate = $this->storeCertificate($request, $key);
                if(is_object($storeCertificate)){
                    return redirect()->back()
                    ->withErrors($storeCertificate);    
                }
                else{
                $certificate[$key] = $storeCertificate;
                }
            }
        }
        //Store
        $foodProduct = FoodProduct::create($request->all());
        if (isset($ingredient)) {
            foreach ($ingredient as $key => $ing){
                $foodProduct->ingredient()->attach($ingredient[$key]);
            }
        }
        if (isset($certificate)) {
            foreach ($certificate as $key => $c){
                $foodProduct->certificate()->attach($certificate[$key]);
            }
        }
        //Redirect
        flash()->success('Food Product Has Successful Added');
        return redirect()->route('foodproduct.index');
    }

    public function storeIngredient(Request $request, $ing)
    {
        $ingredient = Ingredient::where('id',$request->input('ingredient_list')[$ing])->lists('id');
        if(!$ingredient->isEmpty()){
            return $request->input('ingredient_list')[$ing];
        }
        else{
            $ingredient = Ingredient::where('iName',$request->input('ingredient_list')[$ing])->lists('id');
            if(!$ingredient->isEmpty()){
                return $ingredient[0];
            }
            else {
                $ingredient = new Ingredient;
                $ingredient->iName = ucwords($request->input('ingredient_list')[$ing]);
                $ingredient->save();                
                return $ingredient->id;
            }
        }
    }

    public function storeCertificate(Request $request, $key)
    {
        //Validation
        $input = $request->all();
        $rules = [
            'cCode.'.$key => ['required','numeric','min:3'],
            'cExpire.'.$key => ['required','date'],
            'cOrganization.'.$key => ['required']
        ];
        $messages = [
            'cCode.'.$key.'.required' => 'Certificate code is required',
            'cCode.'.$key.'.number' => 'Certificate code format is invalid',
            'cCode.'.$key.'.min' => 'Certificate code too short',
            'cExpire.'.$key.'.required' => 'Certificate expire is required',
            'cCode.'.$key.'.date' => 'Certificate code format is invalid',
            'cOrganization.'.$key.'.required' => 'Certificate organization is required'
        ];
        $validator = Validator::make($input, $rules, $messages);
        if($validator->fails())
        {
            return $validator;
        }
        else{
            //Store
            $certificate = Certificate::where('cCode', $request->input('cCode')[$key])->lists('id');
            if (!$certificate->isEmpty()) {
                return $certificate[0];
            }
            else {
                $certificate = new Certificate;
                $certificate->cCode = $request->input('cCode')[$key];
                $certificate->cExpire = Carbon::parse($request->input('cExpire')[$key]);
                $certificate->cStatus = $request->input('cStatus')[$key];
                $certificate->cOrganization = $request->input('cOrganization')[$key];
                $certificate->save();
                return $certificate->id;
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $foodProduct = FoodProduct::find($id);
        if (!empty($foodProduct)) {
            $foodWarning = $this->foodWarning($foodProduct);
            $fullness = $this->fullness($foodProduct);
            $view = array(
                'fView' => $foodProduct->fView+1,
            );
            $foodProduct->update($view);
            $ingredients = $foodProduct->ingredient->all();
            foreach ($ingredients as $ing) {
                if($ing->iType==0){
                    $inglist[] = $ing->iName;
                }
                else{
                    $addlist[] = [
                        'addName' => $ing->iName, 
                        'addId' => $ing->id
                    ];
                }
            }

            $certificate = $foodProduct->certificate->all();
            return view('foodproducts/show',compact('foodProduct','inglist','addlist','certificate','foodWarning','fullness'));    
        }
        abort(404);
    }

    public function foodWarning($foodProduct)
    {
        $nutrient = ['fat', 'saturated fat', 'trans fat', 'cholesterol', 'sodium', 'carbohydrates', 'fiber', 'sugar', 'protein', 'vitamin A', 'vitamin C',
     'calcium', 'iron'];
        //DVs based on a caloric intake of 2,000 calories, for adults and children four or more years of age.
        $check[0] = [
            round($foodProduct['totalFat']*100/65),
            round($foodProduct['saturatedFat']*100/20),
            0,
            round($foodProduct['cholesterol']*100/300),
            round($foodProduct['sodium']*100/2400),
            round($foodProduct['totalCarbohydrates']*100/300),
            round($foodProduct['dietaryFiber']*100/25),
            0,
            round($foodProduct['protein']*100/50),
            $foodProduct['vitaminA'],
            $foodProduct['vitaminC'],
            $foodProduct['calcium'],
            $foodProduct['iron'],
        ];
        $check[1] = [
            $check[0][0],
            $check[0][1],
            $check[0][2],
            $check[0][3],
            $check[0][4],
            $check[0][5],
            $check[0][6],
            $check[0][7],
            $check[0][8],
            round(($foodProduct['vitaminA']/100*5000)*100/1500),
            round(($foodProduct['vitaminC']/100*60)*100/35),
            round(($foodProduct['calcium']/100*1000)*100/600),
            round(($foodProduct['iron']/100*18)*100/15),
        ];
        $check[2] = [
            $check[0][0],
            $check[0][1],
            $check[0][2],
            $check[0][3],
            $check[0][4],
            $check[0][5],
            $check[0][6],
            $check[0][7],
            $check[0][8],
            round(($foodProduct['vitaminA']/100*5000)*100/2500),
            round(($foodProduct['vitaminC']/100*60)*100/40),
            round(($foodProduct['calcium']/100*1000)*100/800),
            round(($foodProduct['iron']/100*18)*100/10),
        ];
        $check[3] = [
            $check[0][0],
            $check[0][1],
            $check[0][2],
            $check[0][3],
            $check[0][4],
            $check[0][5],
            $check[0][6],
            $check[0][7],
            $check[0][8],
            round(($foodProduct['vitaminA']/100*5000)*100/8000),
            round(($foodProduct['vitaminC']/100*60)*100/60),
            round(($foodProduct['calcium']/100*1000)*100/1300),
            round(($foodProduct['iron']/100*18)*100/18),
        ];
        $count=0;
        for ($i=0; $i < 4 ; $i++) { 
            foreach ($check[$i] as $key => $val) {
                if ($check[$i][$key]>15) {
                    $warning[$i][$key] = 'This food contains high '.$nutrient[$key];
                }
                else{
                    $warning[$i][$key] = null;
                    $count++;
                }
            }
        }
        if($count==52){
            $warning=null;
        }        
        return $warning;
        
        
    }

    public function fullness($foodProduct)
    {
        $CAL=$foodProduct['calories']*100/$foodProduct['weight'];
        if($CAL<30)$CAL=30;
        $PR=$foodProduct['protein']*100/$foodProduct['weight'];
        if($PR>30)$PR=30;
        $DF=$foodProduct['dietaryFiber']*100/$foodProduct['weight'];
        if($DF>12)$DF=12;
        $TF=$foodProduct['totalFat']*100/$foodProduct['weight'];
        if($TF>50)$TF=50;

        $FF=max(0.5, min(5.0, 41.7/$CAL^0.7 + 0.05*$PR + 6.17E-4*$DF^3 - 7.25E-6*$TF^3 + 0.617));
        // FF=MAX(0.5, MIN(5.0, 41.7/CAL^0.7 + 0.05*PR + 6.17E-4*DF^3 - 7.25E-6*TF^3 + 0.617))
        return $FF;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $foodProduct = FoodProduct::find($id);
        if (!empty($foodProduct)) {
            $certificate = $foodProduct->certificate->all();
            $ingredient = $foodProduct->ingredient->all();
            JavaScript::put([
                'foodProduct' => $foodProduct,
            ]);
            return view('foodproducts/edit',compact('foodProduct','certificate'));
        }
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $input = Input::all();
        //Validation
        $validator = Validator::make($input, self::$rules, self::$messages);
        if($validator->fails())
        {
            return redirect()->back()
                ->withErrors($validator);
        }
        $update = array(
            'fName' => $request->input('fName'),
            'fCode' => $request->input('fCode'),
            'fManufacture' => $request->input('fManufacture'),

            'weight' => $request->input('weight'),
            'calories' => $request->input('calories'),
            'totalFat' => $request->input('totalFat'),
            'saturatedFat' => $request->input('saturatedFat'),
            'transFat' => $request->input('transFat'),
            'cholesterol' => $request->input('cholesterol'),
            'sodium' => $request->input('sodium'),
            'totalCarbohydrates' => $request->input('totalCarbohydrates'),
            'dietaryFiber' => $request->input('dietaryFiber'),
            'sugar' => $request->input('sugar'),
            'protein' => $request->input('protein'),
            'vitaminA' => $request->input('vitaminA'),
            'vitaminC' => $request->input('vitaminC'),
            'calcium' => $request->input('calcium'),
            'iron' => $request->input('iron'),
        );
        //Get Ingredient
        foreach($request->input('ingredient_list') as $ing => $i){
            $storeIngredient = $this->storeIngredient($request, $ing);
            if(is_object($storeIngredient)){
                // return redirect()->back()
                // ->withErrors($storeCertificate);    
            }
            else{
                $ingredient[$ing] = $storeIngredient;
            }
        }
        //Edit Certificate
        $ucCount = 0;
        $ucID = 0;
        if (!empty($request->input('ucID'))) {
            foreach ($request->input('ucID') as $key => $uc){
                $ucCount++;
            }
        }
        //Get Certificate
        foreach ($request->input('cCode') as $key => $c){
            if (!empty($request->input('cCode')[$key])) {
                $storeCertificate = $this->storeCertificate($request, $key);
                if(is_object($storeCertificate)){
                    return redirect()->back()
                    ->withErrors($storeCertificate);    
                }
                else{
                $certificate[$key] = $storeCertificate;
                }
            }
        }
        //Update
        $foodProduct = FoodProduct::findOrFail($id);
        $foodProduct->update($update);
        $foodProduct->ingredient()->detach();
        if (isset($ingredient)) {
            foreach ($ingredient as $key => $ing){
                $foodProduct->ingredient()->attach($ingredient[$key]);
            }
        }
        if (isset($certificate)) {
            foreach ($certificate as $key => $c){
                if($ucCount>0){
                    $update = [
                        'cCode' => $request->input('cCode')[$key],
                        'cExpire' => Carbon::parse($request->input('cExpire')[$key]),
                        'cStatus' => $request->input('cStatus')[$key],
                    ];
                    Certificate::findOrFail($request->input('ucID')[$ucID])->update($update);
                    $ucCount--;
                    $ucID++;
                }
                else{
                    $foodProduct->certificate()->attach($certificate[$key]);   
                }
            }
        }
        flash()->success('Food Product Has Successful Edited');
        return redirect()->route('foodproduct.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        FoodProduct::findOrFail($id)->delete();
        flash()->success('Food Product Has Successful Deleted');
        return redirect()->back();
    }

    public function certificateDestroy($id)
    {
        Certificate::findOrFail($id)->delete();
        return redirect()->back();
    }

    public function verify($id)
    {
        $update = array(
            'fVerify' => 1,
        );
        FoodProduct::findOrFail($id)->update($update);
        flash()->success('Food Product Has Successful Verified');
        return redirect()->back();
    }
}
