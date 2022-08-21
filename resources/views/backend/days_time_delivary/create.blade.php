@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">Days & times for delivary</h1>
        </div>
    </div>
</div>
<br>

<div class="card">

    <div class="card-header">
        <h5 class="mb-0 h6">Setting of Days & times for delivary</h5>
    </div>

    <div class="card-body">

        <form action="{{route('days_time_delivary.store')}}" method="post">

            @csrf

            <div class="form-group ">

                <div class="row">
                    <div class="col-md-6"></div>
                    <div class="col-md-3">
                        <strong>{{translate('Order Count Retail')}}</strong>
                    </div>
                    <div class="col-md-3">
                        <strong>{{translate('Order Count Wholesale')}}</strong>
                    </div>
                </div>

                @foreach ($days as $key => $item)
                <div class="row mt-3">

                    <div class="col-md-2 d-flex align-items-center">
                        <label class="aiz-switch aiz-switch-success mb-0">
                            <input value="{{ $item->id }}" name="days[]" type="checkbox" {{ $item->active == 1 ? '
                            checked ' : '' }} id="{{ $item->code }}">
                            <span class="slider round"></span>
                        </label>
                        <p class="h6 m-0">&nbsp;&nbsp;{{
                            $item->name }}</p>
                    </div>

                    <div class="col-md-2">
                        <input type="time" class="form-control" value="{{ $item->start_time }}" name="start_time[]">
                        @error('start_time')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-2">
                        <input type="time" class="form-control" value="{{ $item->end_time }}" name="end_time[]">
                        @error('end_time')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <input type="number" class="form-control" value="{{ $item->order_count_customer??0 }}"
                            name="order_count_customer[]" placeholder="{{translate('Order Count Retail')}}">
                        @error('order_count_customer')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <input type="number" class="form-control" value="{{ $item->order_count_wholesale??0 }}"
                            name="order_count_wholesale[]" placeholder="{{translate('Order Count Wholesale')}}">
                        @error('order_count_wholesale')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                </div>
                @error('days')
                <span class="text-danger">{{ $message }}</span>
                @enderror
                @endforeach

            </div>

            <div class="form-group text-right">
                <input type="submit" value="Save" class="btn btn-success">
            </div>

        </form>

        <div>
            <br>
        </div>

        <div class="customer_choice_options" id="customer_choice_options">
        </div>

    </div>
</div>

@endsection