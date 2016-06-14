<?php
    /**
     * Display the contents of a graph
     *
     * Data from the chosen URI is loaded into an EasyRdf_Graph object.
     * Then the graph is dumped and printed to the page using the
     * $graph->dump() method.
     *
     * The call to preg_replace() replaces links in the page with
     * links back to this dump script.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */
?>
<!DOCTYPE html>
<html lang="en">
<head><title>EasyRdf Graph Browser</title></head>
<body>
<h1>EasyRdf Graph Browser</h1>

<div style="margin: 10px">
    {!! Form::open(['url'=>'RDFBrowser','method' => 'get']) !!}
    URI: 
    {!! Form::text('uri', 'http://halalnutritionfood.com/resources.ttl', ['size'=>80])!!} </br>
    Format: 
    {!! Form::label('format_html','HTML',['id'=>'label_for_format_html']) !!}
    {!! Form::radio('format', 'HTML', ['id'=>'format_html', 'checked'=>'checked']) !!}</br>
    
    {!! Form::submit('submit') !!}
    {!! Form::close() !!}
</div>

<?php
    if (isset($_REQUEST['uri'])) {
        $graph = EasyRdf_Graph::newAndLoad($_REQUEST['uri']);
        if ($graph) {
            if (isset($_REQUEST['format']) && $_REQUEST['format'] == 'text') {
                print "<pre>".$graph->dump('text')."</pre>";
            } else {
                $dump = $graph->dump('html');
                print preg_replace_callback("/ href='([^#][^']*)'/", 'makeLinkLocal', $dump);
            }
        } else {
            print "<p>Failed to create graph.</p>";
        }
    }

    # Callback function to re-write links in the dump to point back to this script
    function makeLinkLocal($matches)
    {
        $href = $matches[1];
        return " href='?uri=".urlencode($href)."#$href'";
    }
?>

</body>
</html>
