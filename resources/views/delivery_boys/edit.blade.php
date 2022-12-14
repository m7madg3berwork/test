@extends('backend.layouts.app')

@section('content')

<div class="col-lg-10 mx-auto">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('Delivery Boy Information')}}</h5>
        </div>

        <form action="{{ route('delivery-boys.update', $delivery_boy->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <input name="_method" type="hidden" value="PATCH">
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
                    <label class="col-sm-3 col-from-label" for="name">
                        {{translate('Name')}}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="name" value="{{$delivery_boy->name}}"
                            placeholder="Name" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="phone">
                        {{translate('Phone')}}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="phone" value="{{$delivery_boy->phone}}"
                            placeholder="Phone" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="type">
                        {{translate('Delivery Type')}}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-9">
                        <select class="form-control aiz-selectpicker" data-live-search="true" name="delivery_type"
                            required>
                            <option value="">{{translate('Select Delivery Type')}}</option>
                            <option value="1" {{ $delivery_boy->delivery_type == 1 ? ' selected ' : '' }}>{{
                                translate('Delivery Type 1') }}</option>
                            <option value="2" {{ $delivery_boy->delivery_type == 2 ? ' selected ' : ''
                                }}>{{ translate('Delivery Type 2') }}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="country">
                        {{translate('Country')}}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-9">
                        <select class="form-control aiz-selectpicker" name="country_id" id="country_id" required>
                            <option value="">{{translate('Select Country')}}</option>
                            @foreach ($countries as $country)
                            <option value="{{ $country->id }}" @if($delivery_boy->country_id == $country->id) selected
                                @endif>
                                {{ $country->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-3">
                        <label>
                            {{ translate('City')}}
                            <span class="text-danger">*</span>
                        </label>
                    </div>
                    <div class="col-sm-9">
                        <select class="form-control mb-3 aiz-selectpicker" name="state_id" id="edit_state"
                            data-live-search="true" required>
                            @foreach ($states as $key => $state)
                            <option value="{{ $state->id }}" @if($delivery_boy->state == $state->name) selected @endif>
                                {{ $state->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label">
                        {{ translate('National ID') }}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="national_id"
                            value="{{ $delivery_boy->national_id }}" placeholder="{{ translate('National ID') }}"
                            required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label">
                        {{ translate('National ID Expired') }}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-9">
                        <input type="date" class="form-control" name="national_id_expired"
                            value="{{ $delivery_boy->national_id_expired }}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label">
                        {{ translate('National ID Attachment') }}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-9">
                        <input type="file" class="form-control" name="national_id_attachment">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label">
                        {{ translate('License ID') }}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="license_id"
                            value="{{ $delivery_boy->license_id }}" placeholder="{{ translate('License ID') }}"
                            required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label">
                        {{ translate('License ID Expired') }}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-9">
                        <input type="date" class="form-control" name="license_id_expired"
                            value="{{ $delivery_boy->license_id_expired }}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label">
                        {{ translate('License ID Attachment') }}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-9">
                        <input type="file" class="form-control" name="license_id_attachment">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label">
                        {{ translate('License Car') }}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="license_car"
                            value="{{ $delivery_boy->license_car }}" placeholder="{{ translate('License Car') }}"
                            required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label">
                        {{ translate('License Car Expired') }}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-9">
                        <input type="date" class="form-control" name="license_car_expired"
                            value="{{ $delivery_boy->license_car_expired }}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-from-label">
                        {{ translate('License Car Attachment') }}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-9">
                        <input type="file" class="form-control" name="license_car_attachment">
                    </div>
                </div>

                <div class="form-group mb-3 text-right">
                    <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ translate('Attachments')}}</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group row">
                    <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('National Id
                        Attachment')
                        }}</label>
                    <div class="col-md-8">
                        <img src="{{ asset($imageNationalIdAttachment) }}" class="img-thumbnail rounded float-start"
                            style="width:200px;height:200px;" alt="...">
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group row">
                    <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('License Id
                        Attachment')
                        }}</label>
                    <div class="col-md-8">
                        <img src="{{ asset($imageLicenseIdAttachment) }}" class="img-thumbnail rounded float-start"
                            style="width:200px;height:200px;" alt="...">
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group row">
                    <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('License Car
                        Attachment')
                        }}</label>
                    <div class="col-md-8">
                        <img src="{{ asset($imageLicenseCarAttachment) }}" class="img-thumbnail rounded float-start"
                            style="width:200px;height:200px;" alt="...">
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
    (function($) {
			"use strict";
            $(document).on('change', '[name=country_id]', function() {
                var country_id = $(this).val();
                get_states(country_id);
            });

            $(document).on('change', '[name=state_id]', function() {
                var state_id = $(this).val();
                get_city(state_id);
            });

            $(document).on('change', '[name=city_id]', function() {
                var city_id = $(this).val();
                get_zone(city_id);
            });

            function get_states(country_id) {
                $('[name="state_id"]').html("");
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
            }

            function get_city(state_id) {
                $('[name="city_id"]').html("");
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
            }

            function get_zone(city_id) {
                $('[name="zone_id"]').html("");
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
            }
		})(jQuery);

</script>
@endsection