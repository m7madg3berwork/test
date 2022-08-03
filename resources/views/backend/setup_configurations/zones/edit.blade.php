@extends('backend.layouts.app')

@section('content')

    <div class="aiz-titlebar text-left mt-2 mb-3">
        <h5 class="mb-0 h6">{{translate('City Information')}}</h5>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-body p-0">
                    <ul class="nav nav-tabs nav-fill border-light">
                        @foreach (\App\Models\Language::all() as $key => $language)
                            <li class="nav-item">
                                <a class="nav-link text-reset @if ($language->code == $lang) active @else bg-soft-dark border-light border-left-0 @endif py-3"
                                   href="{{ route('zones.edit', ['id'=>$zone->id, 'lang'=> $language->code] ) }}">
                                    <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}"
                                         height="11" class="mr-1">
                                    <span>{{ $language->name }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <form class="p-4" action="{{ route('zones.update', $zone->id) }}" method="POST"
                          enctype="multipart/form-data">
                        <input name="_method" type="hidden" value="PATCH">
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        <input type="hidden" name="id" value="{{ $zone->id }}">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="name">{{translate('Name')}}</label>
                            <input type="text" placeholder="{{translate('Name')}}"
                                   value="{{ $zone->getTranslation('name', $lang) }}" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}">
                            <div class="invalid-feedback">
                                <strong>{{ $errors->has('name') ? $errors->first('name') : '' }}</strong>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="name">{{translate('type')}}</label>
                            <select class="form-control aiz-selectpicker" data-live-search="true" id="sort_state" name="type">
                                <option value="">Select type</option>
                                <option value="seller" @if($zone->type == 'seller') selected @endif> Seller </option>
                                <option value="customer" @if($zone->type == 'customer') selected @endif> Customer </option>
                            </select>
                            <div class="invalid-feedback">
                                <strong>{{ $errors->has('type') ? $errors->first('type') : '' }}</strong>
                            </div>
                        </div>

                        <div class="form-group mb-3"><span class="text-danger">*</span>
                            <label for="name">{{translate('Customer Cost')}}</label>
                            <input type="number" min="0" step="0.01" value="{{ $zone->customer_cost }}" placeholder="{{translate('Customer Cost')}}" name="customer_cost"
                                   class="form-control {{ $errors->has('customer_cost') ? 'is-invalid' : '' }}">
                            <div class="invalid-feedback">
                                <strong>{{ $errors->has('customer_cost') ? $errors->first('customer_cost') : '' }}</strong>
                            </div>
                        </div>

                        <div class="form-group mb-3"><span class="text-danger">*</span>
                            <label for="name">{{translate('Seller Cost')}}</label>
                            <input type="number" min="0" step="0.01" value="{{ $zone->seller_cost }}" placeholder="{{translate('Seller Cost')}}" name="seller_cost"
                                   class="form-control {{ $errors->has('seller_cost') ? 'is-invalid' : '' }}">
                            <div class="invalid-feedback">
                                <strong>{{ $errors->has('seller_cost') ? $errors->first('seller_cost') : '' }}</strong>
                            </div>
                        </div>


                        <div class="form-group mb-3 text-right">
                            <button type="submit" class="btn btn-primary">{{translate('Update')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
