@extends('layouts.master')

@section('title', 'Edit Food Product')

@section('css')
    @parent
@endsection

@section('body')
    <div class="container" ng-app="validationApp">
        <div class="row">
            <h1 class="page-header">Edit: {!! $foodProduct->fName !!}</h1>
        </div>
        <div class="row" ng-controller="foodProductValidate">
            {!! Form::model($foodProduct, ['method' => 'PATCH', 'route' => ['foodproduct.update', $foodProduct->id], 'name'=>'foodForm', 'id' => 'foodProductForm', 'data-parsley-validate', 'data-parsley-excluded'=>'input[type=number]']) !!}
            @include('includes.errors')
            @include('foodproducts.form', ['SubmitButtonText' => 'Edit Food Product'])
            {!! Form::hidden('fVerify', 0) !!}
            {!! Form::close() !!}
        </div>
    </div>
@endsection

@section('js')
    @parent
@endsection


