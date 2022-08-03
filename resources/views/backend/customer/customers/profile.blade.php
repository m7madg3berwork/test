@extends('backend.layouts.app')

@section('content')

    <div class="aiz-titlebar mt-2 mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3">{{ translate('Manage Profile') }}</h1>
            </div>
        </div>
    </div>

    <!-- Basic Info-->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('Basic Info')}}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('Name') }}</label>
                            <div class="col-md-8">
                                <input readonly type="text" class="form-control"
                                       placeholder="{{ translate('Name') }}" name="name"
                                       value="{{ $user->name ?? '' }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('Phone') }}</label>
                            <div class="col-md-8">
                                <input readonly type="text" class="form-control"
                                       placeholder="{{ translate('Phone')}}" name="phone"
                                       value="{{ $user->phone ?? ''}}">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('Country') }}</label>
                            <div class="col-md-8">
                                <input readonly type="text" class="form-control"
                                       placeholder="{{ translate('Country')}}" name="country"
                                       value="{{ $user->country ?? ''}}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('State') }}</label>
                            <div class="col-md-8">
                                <input readonly type="text" class="form-control"
                                       placeholder="{{ translate('State')}}" name="state"
                                       value="{{ $user->state ?? ''}}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('City') }}</label>
                            <div class="col-md-8">
                                <input readonly type="text" class="form-control"
                                       placeholder="{{ translate('City')}}" name="city"
                                       value="{{ $user->city ?? ''}}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('Zone') }}</label>
                            <div class="col-md-8">
                                <input readonly type="text" class="form-control"
                                       placeholder="{{ translate('Zone')}}" name="zone"
                                       value="{{ $user->zone ?? ''}}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('Address') }}</label>
                            <div class="col-md-8">
                            <textarea readonly class="form-control  mb-3"
                                      placeholder="{{ translate('Address') }}" rows="1" name="address"
                                      required>{{ $user->address }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Email Change -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('Change Customer email')}}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('user.change.email') }}" method="POST">
                @csrf
                <input type="hidden" value="{{$user->id}}" name="id">
                <div class="row">
                    <div class="col-md-2">
                        <label>{{ translate('Email') }}</label>
                    </div>
                    <div class="col-md-8">
                        <div class="input readonly-group mb-3">
                            <input type="email" class="form-control" placeholder="{{ translate('Email')}}"
                                   name="email" value="{{ $user->email }}"/>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="btn-group" role="group" aria-label="Second group">
                            <button type="submit" name="button" value="save"
                                    class="btn btn-success action-btn">{{ translate('Update') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($user->customer_type == 'wholesale')
        <!-- Commercial Wholesale -->
        <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('Wholesale Check Paper')}}</h5>
        </div>
        <div class="card-body">
            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('Commercial Name') }}</label>
                    <div class="col-md-8">
                        <input readonly type="text" class="form-control"
                               placeholder="{{ translate('Commercial Name')}}" name="commercial_name"
                               value="{{ $user->commercial_name ?? ''}}">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('Commercial Registration No') }}</label>
                    <div class="col-md-8">
                        <input readonly type="text" class="form-control"
                               placeholder="{{ translate('Commercial Registration No')}}" name="owner_name"
                               value="{{ $user->commercial_registration_no ?? ''}}">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('Owner Name') }}</label>
                    <div class="col-md-8">
                        <input readonly type="text" class="form-control"
                               placeholder="{{ translate('Owner Name')}}" name="owner_name"
                               value="{{ $user->owner_name ?? ''}}">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('Tax Number') }}</label>
                    <div class="col-md-8">
                        <input readonly type="text" class="form-control"
                               placeholder="{{ translate('Tax Number')}}" name="phone"
                               value="{{ $user->tax_number ?? ''}}">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                @if($user->commercial_registry)
                    <div class="btn-group" role="group" aria-label="Second group">
                        <a href="{{ my_asset($user->commercial_registry) }}" target="_blank" class="btn btn-success">
                            {{ translate('Download Commercial Registry') }}
                        </a>
                    </div>
                @else
                    <div class="btn-group" role="group" aria-label="Second group">
                        <a href="javascript:;" target="_blank" class="btn btn-danger disabled">
                            {{ translate('No Commercial Registry') }}
                        </a>
                    </div>
                @endif

                @if($user->tax_number_certificate)
                    <div class="btn-group" role="group" aria-label="Second group">
                        <a href="{{ my_asset($user->tax_number_certificate) }}" target="_blank"  class="btn btn-success">
                            {{ translate('Tax Number Certificate') }}
                        </a>
                    </div>
                @else
                    <div class="btn-group" role="group" aria-label="Second group">
                        <a href="javascript:;" target="_blank" class="btn btn-danger disabled">
                            {{ translate('No Tax Number Certificate') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif
@endsection

