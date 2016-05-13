@extends('layouts.master')

@section('title', 'Submit Food Product')

@section('css')
    @parent
@endsection

@section('body')
    <div class="container" ng-app="validationApp">
        <div class="row">
            <h1 class="page-header">Submit Food Product</h1>
        </div>
        <div class="row">
            {!! Form::open(['route' => 'foodproduct.store', 'name'=>'foodForm', 'id' => 'foodProductForm', 'data-parsley-validate']) !!}
            @include('includes.errors')
            @include('foodproducts/form', ['SubmitButtonText' => 'Add Food Product'])
            {!! Form::close() !!}
        </div>
    </div>
@endsection

@section('js')
    @parent
@endsection
