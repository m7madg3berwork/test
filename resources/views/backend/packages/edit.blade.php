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
                                    <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}"
                                         height="11" class="mr-1">
                                    <span>{{$language->name}}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <form class="p-4" action="{{ route('packages.update', $package->id) }}"
                          method="POST">
                        <input type="hidden" name="_method" value="PATCH">
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        @csrf
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{translate('Package Name')}} <i
                                    class="las la-language text-danger"
                                    title="{{translate('Translatable')}}"></i></label>
                            <div class="col-sm-9">
                                <input type="text" value="{{ $package->getTranslation('name', $lang) ?? '' }}" placeholder="{{translate('Name')}}" id="name" name="name"
                                       class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}">
                                <div class="invalid-feedback">
                                    <strong>{{ $errors->has('name') ? $errors->first('name') : '' }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label" for="name">{{translate('Price')}}</label>
                            <div class="col-sm-9">
                                <input type="number" value="{{ $package->price ?? 0 }}" lang="en" min="0" step="0.01" placeholder="{{translate('Price')}}"
                                       id="amount" name="price" class="form-control {{ $errors->has('price') ? 'is-invalid' : '' }}">
                                <div class="invalid-feedback">
                                    <strong>{{ $errors->has('price') ? $errors->first('price') : '' }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label" for="name">{{translate('Qty')}}</label>
                            <div class="col-sm-9">
                                <input type="number" value="{{ $package->qty ?? 0 }}" lang="en" min="0" step="0.01" placeholder="{{translate('Qty')}}"
                                       id="qty" name="qty" class="form-control {{ $errors->has('qty') ? 'is-invalid' : '' }}">
                                <div class="invalid-feedback">
                                    <strong>{{ $errors->has('qty') ? $errors->first('qty') : '' }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{translate('customer type')}} <span
                                    class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker {{ $errors->has('user_type') ? 'is-invalid' : '' }}" name="customer_type">
                                    <option id="">{{translate('Select type')}}</option>
                                    <option value="wholesale" @if($package->customer_type == 'wholesale') selected @endif>{{translate('wholesale') }}</option>
                                    <option value="retail" @if($package->customer_type == 'retail') selected @endif>{{translate('retail') }}</option>
                                </select>
                                <div class="invalid-feedback">
                                    <strong>{{ $errors->has('customer_type') ? $errors->first('customer_type') : '' }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{translate('Products')}} <span
                                    class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker" multiple name="products[]">
                                    <option id="">{{translate('Select Product')}}</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}"
                                                @if((in_array($product->id, $product_ids))) selected @endif>{{ $product->getTranslation('name', $lang) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label" for="name">{{translate('Description')}}</label>
                            <div class="col-sm-9">
                                <textarea name="desc" id="" cols="30" rows="10" class="form-control">{{ $product->getTranslation('desc', $lang) ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label"
                                   for="signinSrEmail">{{translate('Package logo')}}</label>
                            <div class="col-md-9">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div
                                            class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                    <input type="hidden" name="logo" value="{{$package->logo}}"
                                           class="selected-files">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                            </div>
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
