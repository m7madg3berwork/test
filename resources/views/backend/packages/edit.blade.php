@extends('backend.layouts.app')
@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="align-items-center">
        <h1 class="h3">{{translate('Update Package Information')}}</h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-body p-0">
                <ul class="nav nav-tabs nav-fill border-light">
                    @foreach (\App\Models\Language::all() as $key => $language)
                    <li class="nav-item">
                        <a class="nav-link text-reset @if ($language->code == $lang) active @else bg-soft-dark border-light border-left-0 @endif py-3"
                            href="{{ route('packages.edit', ['id'=>$package->id, 'lang'=> $language->code] ) }}">
                            <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="11"
                                class="mr-1">
                            <span>{{$language->name}}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>

                <form class="p-4" action="{{ route('packages.update', $package->id) }}" method="POST">

                    <input type="hidden" name="_method" value="PATCH">
                    <input type="hidden" name="lang" value="{{ $lang }}">

                    @csrf

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label">
                            {{translate('Name')}}
                            <i class="las la-language text-danger" title="{{ translate('Translatable') }}"></i>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" value="{{ $package->getTranslation('name', $lang) }}"
                                placeholder="{{translate('Name')}}" id="name" name="name"
                                class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}">
                            <div class="invalid-feedback">
                                <strong>{{ $errors->has('name') ? $errors->first('name') : '' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{translate('Description')}}
                            <i class="las la-language text-danger" title="{{ translate('Translatable') }}"></i>
                        </label>
                        <div class="col-sm-9">
                            <textarea name="desc" required id="" cols="30" rows="10"
                                class="form-control {{ $errors->has('desc') ? 'is-invalid' : '' }}">{{ $package->getTranslation('desc', $lang)  }}</textarea>
                            <div class="invalid-feedback">
                                <strong>{{ $errors->has('desc') ? $errors->first('desc') : '' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{ translate('Price') }}</label>
                        <div class="col-sm-9">
                            <input type="number" required value="{{ $package->price }}" lang="{{ $lang }}" min="0"
                                step="1" placeholder="{{translate('Price')}}" id="amount" name="price"
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
                                <option value="">{{ translate('Select type') }}</option>
                                <option value="wholesale" {{ $package->customer_type == 'wholesale' ? ' selected ' : ''
                                    }}>
                                    {{translate('wholesale') }}</option>
                                <option value="retail" {{ $package->customer_type == 'retail' ? ' selected ' : ''
                                    }}</option>
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
                                <option value="monthly" {{ $package->shipping_type == 'monthly' ? ' selected ' : '' }} >
                                    {{translate('monthly') }}</option>
                                <option value="weekly" {{ $package->shipping_type == 'weekly' ? ' selected ' : '' }}>
                                </option>
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
                            <input type="number" lang="{{ $lang }}" required value="{{ $package->duration }}" min="0"
                                step="1" placeholder="{{translate('duration')}}" id="duration" name="duration"
                                class="form-control {{ $errors->has('duration') ? 'is-invalid' : '' }}">
                            <div class="invalid-feedback">
                                <strong>{{ $errors->has('duration') ? $errors->first('duration') : '' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{translate('number of visits')}}</label>
                        <div class="col-sm-9">
                            <input type="number" lang="{{ $lang }}" required value="{{ $package->visits_num }}" min="0"
                                step="1" placeholder="{{translate('visits number')}}" id="visits_num" name="visits_num"
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
                                <input type="checkbox" name="active" value="{{ $package->active }}" checked="">
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
                                <input type="hidden" name="logo" value="{{$package->logo}}" class="selected-files">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{translate('Cities')}}</label>
                        <div class="col-md-9">
                            <select data-live-search="true"
                                class="form-control aiz-selectpicker {{ $errors->has('states.*') ? 'is-invalid' : '' }}"
                                required multiple name="states[]">
                                <option id="">{{translate('Select City')}}</option>
                                @foreach($states as $k => $v)
                                <option {{ in_array($k,$states_ids) ? ' selected ' : '' }} value="{{ $k }}">{{ $v }}
                                </option>
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
                            <span>{{ translate('Total Price') }}: <span id="totalPrice">0.0</span></span>
                        </div>
                    </div>

                    @php
                    $next = count($package->products) + 1;
                    $counter = 1;
                    @endphp
                    <input type="hidden" id="productsCount" value="{{ $next }}">
                    <div id="productDiv">
                        @foreach ($package->products as $product)
                        <div class="form-group row" id="productRecord{{ $counter }}">
                            <div class="col-md-4">
                                <select class="form-control aiz-selectpicker" name="products[]" data-live-search="true"
                                    id="productSelect{{ $counter }}" onchange="getProductPrice{{ $counter }}" required>
                                    <option value="">{{ translate('Select Product') }}</option>
                                    @foreach ($productsSort as $k => $v)
                                    <option value="{{ $v['id'] }}" {{ $v['id']==$product->id ? ' selected ' : '' }}>{{
                                        $v['name'] }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="number" lang="{{ $lang }}" id="productQty{{ $counter }}" min="0" step="1"
                                    value="{{ $product->pivot->qty }}" oninput="calcPrice()" name="qty[]" required
                                    placeholder="{{ translate('Enter Quantity') }}" class="form-control">
                            </div>
                            <div class="col-md-4 d-flex justify-content-between align-items-center">
                                <p class="m-0">{{ translate('Price') }}: <span index="{{ $counter }}"
                                        class="productPrice" id="productPrice{{ $counter }}">{{
                                        $products[$product->id]['price'] }}</span>
                                </p>
                                <button type="button" onclick="deleteProductFunction({{ $counter }})"
                                    class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                    title="{{ translate('Add') }}">
                                    <i class="las la-trash"></i>
                                </button>
                            </div>
                        </div>
                        @php
                        $counter++;
                        @endphp
                        @endforeach

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
<script>
    $(function(){
        calcPrice();
    });
</script>
@endsection