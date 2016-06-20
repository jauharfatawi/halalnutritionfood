<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Input;
use App\Http\Controllers\Controller;

class RDFBrowserController extends Controller
{
    public function getIndex()
    {
        $uri = Input::get('uri', 'http://halalnutritionfood.com/resources.ttl');
        if (isset($uri)) {
            $newUri = strstr($uri, '#', true);
            if(!$newUri){
                if(substr($uri, -4) == ".ttl") {
                    $newUri = $uri;
                }
                else{
                    $newUri = $uri.'.ttl';
                }
            }
            else{
                if(substr($newUri, -4) == ".ttl") {
                    $newUri = $newUri.strstr($uri, '#');
                }
                else{
                    $newUri = $newUri.'.ttl'.strstr($uri, '#');   
                }
            }
        }
        $graph = \EasyRdf_Graph::newAndLoad($newUri);

        return view('pages/browser', compact('graph'));
    }
}
