<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class RDFBrowserController extends Controller
{
    public static $uri = 'http://njh.me/foaf.rdf';

    public function getIndex()
    {
        // $foaf = \EasyRdf_Graph::newAndLoad(self::$uri);
        // $me = $foaf->primaryTopic();
        // dd($foaf);
        return view('pages/browser');
    }
}
