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
        {{-- <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('Name') }}</label>
                        <div class="col-md-8">
                            <input readonly type="text" class="form-control" placeholder="{{ translate('Name') }}"
                                name="name" value="{{ $user->name ?? '' }}">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('Phone')
                            }}</label>
                        <div class="col-md-8">
                            <input readonly type="text" class="form-control" placeholder="{{ translate('Phone')}}"
                                name="phone" value="{{ $user->phone ?? ''}}">
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('Country')
                            }}</label>
                        <div class="col-md-8">
                            <input readonly type="text" class="form-control" placeholder="{{ translate('Country') }}"
                                name="country" value="{{ $user->state_data ? $user->state_data->country->name : '' }}">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('State')
                            }}</label>
                        <div class="col-md-8">
                            <input readonly type="text" class="form-control" placeholder="{{ translate('State')}}"
                                name="state" value="{{ $user->state_data ? $user->state_data->name : '' }}">
                        </div>
                    </div>
                </div>

            </div>
            {{--
        </form> --}}
    </div>
</div>

@if($user->customer_type == 'wholesale')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ translate('Wholesale Check Paper')}}</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('Owner Name')
                        }}</label>
                    <div class="col-md-8">
                        <input readonly type="text" class="form-control" placeholder="{{ translate('Owner Name')}}"
                            name="owner_name" value="{{ $user->owner_name ?? ''}}">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('Tax Number')
                        }}</label>
                    <div class="col-md-8">
                        <input readonly type="text" class="form-control" placeholder="{{ translate('Tax Number')}}"
                            name="tax_number" value="{{ $user->tax_number ?? ''}}">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('Tax Number
                        Certificate')
                        }}</label>
                    <div class="col-md-8">
                        <input readonly type="text" class="form-control"
                            placeholder="{{ translate('Tax Number Certificate')}}" name="tax_number_certificate"
                            value="{{ $user->tax_number_certificate ?? ''}}">
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('Commercial Name')
                        }}</label>
                    <div class="col-md-8">
                        <input readonly type="text" class="form-control" placeholder="{{ translate('Commercial Name')}}"
                            name="commercial_name" value="{{ $user->commercial_name ?? ''}}">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('Commercial
                        Registration No.')
                        }}</label>
                    <div class="col-md-8">
                        <input readonly type="text" class="form-control"
                            placeholder="{{ translate('Commercial Registration No.')}}"
                            name="commercial_registration_no" value="{{ $user->commercial_registration_no ?? ''}}">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-md-4 col-form-label" style="font-weight: bold">{{ translate('Commercial Registry')
                        }}</label>
                    <div class="col-md-8">
                        <img src="{{ asset($imageName) }}" class="rounded float-start" alt="...">
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endif
@endsection