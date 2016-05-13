@extends('layouts.master')

@section('title', 'E-Numbers List')

@section('css')
    @parent

@endsection

@section('body')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                @include('flash::message')
                <h1>E-Numbers List</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @if(Auth::check())
                    @if(Auth::user()->roles[0]->name == "administrator")
                        <table class="table table-striped table-bordered table-hover dataTable">
                            <thead>
                            <tr>
                                <th>E-Number</th>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($additive as $ad)
                                <tr>
                                    <td>{{ $ad->eNumber }}</td>
                                    <td>{{ $ad->iName }}</td>
                                    <td class="action">
                                        <a class="btn btn-success btn-xs" href="{{ route('additive.show',$ad->id) }}"><i class="fa fa-eye"></i></a>
                                        <a class="btn btn-info btn-xs" href="{{ route('additive.edit',$ad->id) }}"><i class="fa fa-pencil"></i></a>
                                        <a class="btn btn-danger btn-xs" href="{{route('additive.destroy', $ad->id)}}" data-method="delete" name="delete_item"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <table class="table table-striped table-bordered table-hover" id="additive-table">
                            <thead>
                            <tr>
                                <th>E-Number</th>
                                <th>Name</th>
                            </tr>
                            </thead>
                        </table>
                    @endif
                @else
                    <table class="table table-striped table-bordered table-hover" id="additive-table">
                        <thead>
                        <tr>
                            <th>E-Number</th>
                            <th>Name</th>
                        </tr>
                        </thead>
                    </table>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('js')
    @parent
@endsection
