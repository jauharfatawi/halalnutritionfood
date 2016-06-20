@extends('layouts.master')

@section('title', 'RDF Browser')

@section('css')
    @parent
@endsection

@section('body')
    <div class="container">
        <div class="row">
            <h1>RDF Browser</h1>
        </div>
        <div class="row">
        <?php
            if ($graph) {
                $dump = $graph->dump('html');
                print preg_replace_callback("/ href='([^#][^']*)'/", 'makeLinkLocal', $dump);
            } else {
                print "<p>Failed to create graph.</p>";
            }
        
            // Callback function to re-write links in the dump to point back to this script
            function makeLinkLocal($matches)
            {
                $href = $matches[1];
                return " href='?uri=".urlencode($href)."#$href'";
            }
        ?>
        </div>
    </div>
@endsection

@section('js')
    @parent
@endsection
