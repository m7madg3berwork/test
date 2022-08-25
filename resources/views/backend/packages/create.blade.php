@extends('backend.layouts.app')
@section('content')

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Create New Package')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('packages.store') }}" method="POST">

                    @csrf

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{translate('Name')}}</label>
                        <div class="col-sm-9">
                            <input type="text" value="{{old('name')}}" placeholder="{{translate('Name')}}" id="name"
                                required name="name"
                                class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}">
                            <div class="invalid-feedback">
                                <strong>{{ $errors->has('name') ? $errors->first('name') : '' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{translate('Description')}}</label>
                        <div class="col-sm-9">
                            <textarea name="desc" required id="" cols="30" rows="10"
                                class="form-control {{ $errors->has('desc') ? 'is-invalid' : '' }}">{{ old('desc') }}</textarea>
                            <div class="invalid-feedback">
                                <strong>{{ $errors->has('desc') ? $errors->first('desc') : '' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{translate('Price')}}</label>
                        <div class="col-sm-9">
                            <input type="number" required value="{{old('price')}}" lang="{{ app()->getLocale() }}"
                                min="0" step="1" placeholder="{{translate('Price')}}" id="amount" name="price"
                                class="form-control {{ $errors->has('price') ? 'is-invalid' : '' }}">
                            <div class="invalid-feedback">
                                <strong>{{ $errors->has('price') ? $errors->first('price') : '' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{translate('customer type')}} </label>
                        <div class="col-md-9">
                            <select
                                class="form-control aiz-selectpicker {{ $errors->has('user_type') ? 'is-invalid' : '' }}"
                                required name="customer_type">
                                <option id="">{{translate('Select type')}}</option>
                                <option value="wholesale" @if(old('customer_type')=='wholesale' ) selected @endif>
                                    {{translate('wholesale') }}</option>
                                <option value="retail" @if(old('customer_type')=='retail' ) selected @endif>
                                    {{translate('retail') }}</option>
                            </select>
                            <div class="invalid-feedback">
                                <strong>{{ $errors->has('customer_type') ? $errors->first('customer_type') : ''
                                    }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{translate('shipping type')}}</label>
                        <div class="col-md-9">
                            <select required
                                class="form-control aiz-selectpicker {{ $errors->has('shipping_type') ? 'is-invalid' : '' }}"
                                name="shipping_type">
                                <option id="">{{translate('Select type')}}</option>
                                <option value="monthly" @if(old('shipping_type')=='monthly' ) selected @endif>
                                    {{translate('monthly') }}</option>
                                <option value="weekly" @if(old('shipping_type')=='weekly' ) selected @endif>
                                    {{translate('weekly') }}</option>
                            </select>
                            <div class="invalid-feedback">
                                <strong>{{ $errors->has('shipping_type') ? $errors->first('shipping_type') : ''
                                    }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{translate('duration of shipping
                            type')}}</label>
                        <div class="col-sm-9">
                            <input type="number" lang="{{ app()->getLocale() }}" required value="{{old('duration')}}"
                                min="0" step="1" placeholder="{{translate('duration')}}" id="duration" name="duration"
                                class="form-control {{ $errors->has('duration') ? 'is-invalid' : '' }}">
                            <div class="invalid-feedback">
                                <strong>{{ $errors->has('duration') ? $errors->first('duration') : '' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{translate('number of visits')}}</label>
                        <div class="col-sm-9">
                            <input type="number" lang="{{ app()->getLocale() }}" required value="{{old('visits_num')}}"
                                min="0" step="1" placeholder="{{translate('visits number')}}" id="visits_num"
                                name="visits_num"
                                class="form-control {{ $errors->has('visits_num') ? 'is-invalid' : '' }}">
                            <div class="invalid-feedback">
                                <strong>{{ $errors->has('visits_num') ? $errors->first('visits_num') : '' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{ translate('active') }}</label>
                        <div class="col-md-9">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" name="active" value="1" checked="">
                                <span></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="signinSrEmail">{{translate('logo')}}</label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{
                                        translate('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                <input type="hidden" required name="logo" class="selected-files">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{translate('Cities')}}</label>
                        <div class="col-md-9">
                            <select
                                class="form-control aiz-selectpicker {{ $errors->has('states.*') ? 'is-invalid' : '' }}"
                                data-live-search="true" required multiple name="states[]">
                                <option id="">{{translate('Select City')}}</option>
                                @foreach($states as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">
                                <strong>{{ $errors->has('states.*') ? $errors->first('states.*') : '' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{ translate('Products') }}</label>
                        <div class="col-md-9">
                            <button type="button" onclick="addNewProductFunction()"
                                class="btn btn-soft-primary btn-icon btn-circle btn-sm" title="{{ translate('Add') }}">
                                <i class="las la-plus"></i>
                            </button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <span>{{ translate('Total Price') }}: <span id="totalPrice">0</span></span>
                        </div>
                    </div>

                    <input type="hidden" id="productsCount" value="1">
                    <div id="productDiv">
                    </div>

                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
@include('backend.packages.actions')
@endsection