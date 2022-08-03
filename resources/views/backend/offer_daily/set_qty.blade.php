@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">Quantification</h1>
</div>
        
    </div>
</div>
<br>



<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">Setting of number of daily offer for customers</h5>
    </div>
    <div class="card-body">

        <form action="{{ route('offer_daily_qty.save') }}" method="post">
            @csrf
            <div class="form-group ">
                <div class="row">
                    <div class="col-md-6">
                        <input type="number" class="form-control" name="qty" placeholder="Number of offer quantity">
                        @error('qty')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <select name="customer_type" class="form-control">
                            <option  selected disabled>Choose customer type</option>
                            <option value="customer">Customer</option>
                            <option value="wholesale">Wholesale</option>
                        </select>
                        @error('customer_type')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="form-group text-right">
                <input type="submit" value="Save & Publish" class="btn btn-success">
            </div>
        </form>

        <br>

        <table class="table text-center">
            <thead>
                <tr>
                    <th>User Type</th>
                    <th>Number of offer quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($res as $item)
                    <tr>
                        <td scope="row">{{$item->customer_type}}</td>
                        <td>{{$item->qty}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

       
        <div>
            
            <br>
        </div>

        <div class="customer_choice_options" id="customer_choice_options">

        </div>
    </div>
</div>



@endsection
