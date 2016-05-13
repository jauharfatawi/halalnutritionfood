@extends('layouts.master')

@section('title', 'Food Product List')

@section('css')
    @parent
@endsection

@section('body')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                @include('flash::message')
                <h1>Food Product List</h1>
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @if(Auth::check())
                @if(Auth::user()->roles[0]->name == "administrator")
                <table class="table table-striped table-bordered table-hover dataTable">
                    <thead>
                    <tr>
                        <th>Food Number</th>
                        <th>Food Name</th>
                        <th>Food Manufacture</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($foodProducts as $fp)
                        <tr>
                            <td>{{ $fp->fCode }}</td>
                            <td>{{ $fp->fName }}</td>
                            <td>{{ $fp->fManufacture }}</td>
                            @if($fp->fVerify == 0)
                                <td><p class="btn btn-danger btn-xs">No</p></td>
                            @else
                                <td><p class="btn btn-success btn-xs">Yes</p></td>
                            @endif
                            <td class="action">
                                <a class="btn btn-success btn-xs" href="{{ route('foodproduct.show',$fp->id) }}"><i class="fa fa-eye"></i></a>
                                <a class="btn btn-info btn-xs" href="{{ route('foodproduct.edit',$fp->id) }}"><i class="fa fa-pencil"></i></a>
                                <a class="btn btn-primary btn-xs" href="{{route('foodproduct.verify', $fp->id)}}" data-method="post" name="verify_item"><i class="fa fa-check"></i></a>
                                <a class="btn btn-danger btn-xs" href="{{route('foodproduct.destroy', $fp->id)}}" data-method="delete" name="delete_item"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <table class="table table-striped table-bordered table-hover" id="foodProduct-table">
                    <thead>
                        <tr>
                            <th>Food Number</th>
                            <th>Food Name</th>
                            <th>Food Manufacture</th>
                        </tr>
                    </thead>
                </table>
                @endif
                @else
                <table class="table table-striped table-bordered table-hover" id="foodProduct-table">
                    <thead>
                    <tr>
                        <th>Food Number</th>
                        <th>Food Name</th>
                        <th>Food Manufacture</th>
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
