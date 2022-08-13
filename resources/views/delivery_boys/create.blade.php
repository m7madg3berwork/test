@extends('backend.layouts.app')

@section('content')

<div class="col-lg-8 mx-auto">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('Delivery Boy Information')}}</h5>
        </div>

        <form action="{{ route('delivery-boys.store') }}" method="POST">
            @csrf
            <div class="card-body">

                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="form-group row">
                    <label class="col-sm-2 col-from-label" for="name">
                        {{translate('Name')}}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Name"
                            required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-from-label" for="phone">
                        {{translate('Phone')}}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="phone" value="{{ old('phone') }}"
                            placeholder="Phone" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-from-label" for="type">
                        {{translate('Delivery Type')}}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-10">
                        <select class="form-control aiz-selectpicker" data-live-search="true" name="delivery_type"
                            required>
                            <option value="">{{translate('Select Delivery Type')}}</option>
                            <option value="1">{{translate('Delivery Type 1')}}</option>
                            <option value="2">{{translate('Delivery Type 2')}}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-from-label" for="type">
                        {{translate('Country')}}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-10">
                        <select class="form-control aiz-selectpicker" data-live-search="true" name="country_id"
                            id="country_id" required>
                            <option value="">{{translate('Select Country')}}</option>
                            @foreach ($countries as $country)
                            <option value="{{ $country->id }}">
                                {{ $country->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2">
                        <label>
                            {{ translate('City')}}
                            <span class="text-danger">*</span>
                        </label>
                    </div>
                    <div class="col-md-10">
                        <select class="form-control mb-3 aiz-selectpicker" data-live-search="true" name="state_id"
                            required>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-from-label">
                        {{ translate('National ID') }}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="national_id" value="{{ old('national_id') }}"
                            placeholder="{{ translate('National ID') }}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-from-label">
                        {{ translate('National ID Expired') }}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-10">
                        <input type="date" class="form-control" name="national_id_expired"
                            value="{{ old('national_id_expired') }}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-from-label">
                        {{ translate('License ID') }}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="license_id" value="{{ old('license_id') }}"
                            placeholder="{{ translate('License ID') }}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-from-label">
                        {{ translate('License ID Expired') }}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-10">
                        <input type="date" class="form-control" name="license_id_expired"
                            value="{{ old('license_id_expired') }}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-from-label">
                        {{ translate('License Car') }}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="license_car" value="{{ old('license_car') }}"
                            placeholder="{{ translate('License Car') }}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-from-label">
                        {{ translate('License Car Expired') }}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-10">
                        <input type="date" class="form-control" name="license_car_expired"
                            value="{{ old('license_car_expired') }}" required>
                    </div>
                </div>

                <div class="form-group mb-3 text-right">
                    <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
    (function($) {
			"use strict";

            $(document).on('change', '[name=country_id]', function() {
                var country_id = $(this).val();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{route('get-state')}}",
                    type: 'POST',
                    data: {
                        country_id  : country_id
                    },
                    success: function (response) {
                        var obj = JSON.parse(response);
                        if(obj != '') {
                            $('[name="state_id"]').html(obj);
                            AIZ.plugins.bootstrapSelect('refresh');
                        }
                    }
                });
            });

            $(document).on('change', '[name=state_id]', function() {
                var state_id = $(this).val();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{route('get-city')}}",
                    type: 'POST',
                    data: {
                        state_id: state_id
                    },
                    success: function (response) {
                        var obj = JSON.parse(response);
                        if(obj != '') {
                            $('[name="city_id"]').html(obj);
                            AIZ.plugins.bootstrapSelect('refresh');
                        }
                    }
                });
            });

            $(document).on('change', '[name=city_id]', function() {
                var city_id = $(this).val();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{route('get-zone')}}",
                    type: 'POST',
                    data: {
                        city_id: city_id
                    },
                    success: function (response) {
                        var obj = JSON.parse(response);
                        if(obj != '') {
                            $('[name="zone_id"]').html(obj);
                            AIZ.plugins.bootstrapSelect('refresh');
                        }
                    }
                });
            });
        })(jQuery);

</script>
@endsection