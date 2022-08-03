@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="row align-items-center">
            <div class="col-md-12">
                <h1 class="h3">All Zones</h1>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <form class="" id="sort_zones" action="" method="GET">
                    <div class="card-header row gutters-5">
                        <div class="col text-center text-md-left">
                            <h5 class="mb-md-0 h6">zones</h5>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="sort_zone" name="sort_zone"
                                   @isset($sort_zone) value="{{ $sort_zone }}"
                                   @endisset placeholder="Type zone name & Enter">
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-primary" type="submit">{{ translate('Filter') }}</button>
                        </div>
                    </div>
                </form>
                <div class="card-body">
                    <table class="table aiz-table mb-0">
                        <thead>
                        <tr>
                            <th data-breakpoints="lg">#</th>
                            <th>Name</th>
                            @if(!app('request')->input('city_id'))
                                <th>City Name</th>
                            @endif
                            <th>{{ translate('Customer Cost') }}</th>
                            <th>{{ translate('Seller Cost') }}</th>
                            <th data-breakpoints="lg" class="text-right">{{translate('Options')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($zones as $key => $zone)
                            <tr>
                                <td>{{ ($key+1) + ($zones->currentPage() - 1)*$zones->perPage() }}</td>
                                <td>{{ $zone->name ?? '' }}</td>
                                @if(!app('request')->input('city_id'))
                                    <td>{{ $zone->city->name ?? '' }}</td>
                                @endif
                                <td>{{ single_price($zone->customer_cost ?? '') }}</td>
                                <td>{{ single_price($zone->seller_cost ?? '') }}</td>
                                <td class="text-right">
                                    <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                       href="{{ route('zones.edit', ['id'=>$zone->id, 'lang'=>env('DEFAULT_LANGUAGE')]) }}"
                                       title="{{ translate('Edit') }}">
                                        <i class="las la-edit"></i>
                                    </a>
                                    <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                                       data-href="{{route('zones.destroy', $zone->id)}}"
                                       title="{{ translate('Delete') }}">
                                        <i class="las la-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="aiz-pagination">
                        {{ $zones->appends(request()->input())->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">Add New zone</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('zones.store') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="name">{{translate('Name')}}</label> <span class="text-danger">*</span>
                            <input type="text" placeholder="{{translate('Name')}}" name="name"
                                   class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}">
                            <div class="invalid-feedback">
                                <strong>{{ $errors->has('name') ? $errors->first('name') : '' }}</strong>
                            </div>
                        </div>

{{--                        <div class="form-group mb-3"><span class="text-danger">*</span>--}}
{{--                            <label for="name">{{translate('type')}}</label>--}}
{{--                            <select class="form-control aiz-selectpicker {{ $errors->has('type') ? 'is-invalid' : '' }}" data-live-search="true" id="sort_state"--}}
{{--                                    name="type">--}}
{{--                                <option value="">Select type</option>--}}
{{--                                <option value="seller"> Seller</option>--}}
{{--                                <option value="customer"> Customer</option>--}}
{{--                            </select>--}}
{{--                            <div class="invalid-feedback">--}}
{{--                                <strong>{{ $errors->has('type') ? $errors->first('type') : '' }}</strong>--}}
{{--                            </div>--}}
{{--                        </div>--}}
                            <div class="form-group mb-3">
                                <label for="name">{{translate('cities')}}</label>
                                <select class="form-control aiz-selectpicker {{ $errors->has('city_id') ? 'is-invalid' : '' }}" data-live-search="true" id="sort_state"
                                        name="city_id">
                                    @if(!request()->get('city_id'))
                                        <option value="">Select city</option>
                                    @endif
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}">{{ $city->name ?? '' }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    <strong>{{ $errors->has('city_id') ? $errors->first('city_id') : '' }}</strong>
                                </div>
                            </div>

                        <div class="form-group mb-3"><span class="text-danger">*</span>
                            <label for="name">{{translate('Customer Cost')}}</label>
                            <input type="number" min="0" step="0.01" placeholder="{{translate('Customer Cost')}}" name="customer_cost"
                                   class="form-control {{ $errors->has('customer_cost') ? 'is-invalid' : '' }}">
                            <div class="invalid-feedback">
                                <strong>{{ $errors->has('customer_cost') ? $errors->first('customer_cost') : '' }}</strong>
                            </div>
                        </div>
                        <div class="form-group mb-3"><span class="text-danger">*</span>
                            <label for="name">{{translate('Seller Cost')}}</label>
                            <input type="number" min="0" step="0.01" placeholder="{{translate('Seller Cost')}}" name="seller_cost"
                                   class="form-control {{ $errors->has('seller_cost') ? 'is-invalid' : '' }}">
                            <div class="invalid-feedback">
                                <strong>{{ $errors->has('seller_cost') ? $errors->first('seller_cost') : '' }}</strong>
                            </div>
                        </div>
                        <div class="form-group mb-3 text-right">
                            <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection


@section('script')
    <script type="text/javascript">
    </script>
@endsection
