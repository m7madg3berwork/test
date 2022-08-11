@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{translate('Add New Wholesale Product')}}</h5>
</div>

<div class="">
    <form class="form form-horizontal mar-top" action="{{route('wholesale_product_store.admin')}}" method="POST"
        enctype="multipart/form-data" id="choice_form">
        <div class="row gutters-5">

            @csrf
            <input type="hidden" name="added_by" value="admin">

            <div class="col-lg-8">

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{translate('Product Information')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{translate('Product Name')}} <span
                                    class="text-danger">*</span></label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="name"
                                    placeholder="{{ translate('Product Name') }}" onchange="update_sku()" required>
                            </div>
                        </div>
                        <div class="form-group row" id="category">
                            <label class="col-md-3 col-from-label">{{translate('Category')}} <span
                                    class="text-danger">*</span></label>
                            <div class="col-md-8">
                                <select class="form-control aiz-selectpicker" name="category_id" id="category_id"
                                    data-live-search="true" required>
                                    @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->getTranslation('name') }}</option>
                                    @foreach ($category->childrenCategories as $childCategory)
                                    @include('categories.child_category', ['child_category' => $childCategory])
                                    @endforeach
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row" id="brand">
                            <label class="col-md-3 col-from-label">{{translate('Brand')}}</label>
                            <div class="col-md-8">
                                <select class="form-control aiz-selectpicker" name="brand_id" id="brand_id"
                                    data-live-search="true">
                                    <option value="">{{ translate('Select Brand') }}</option>
                                    @foreach (\App\Models\Brand::all() as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->getTranslation('name') }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{translate('Unit')}}</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="unit"
                                    placeholder="{{ translate('Unit (e.g. KG, Pc etc)') }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{translate('Minimum Purchase Qty')}} <span
                                    class="text-danger">*</span></label>
                            <div class="col-md-8">
                                <input type="number" lang="en" class="form-control" name="min_qty" value="1" min="1"
                                    required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ translate('Maximum Purchase Qty') }} <span
                                    class="text-danger">*</span></label>
                            <div class="col-md-8">
                                <input type="number" lang="en" class="form-control" name="max_qty" value="1" min="1"
                                    required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{translate('Tags')}} <span
                                    class="text-danger">*</span></label>
                            <div class="col-md-8">
                                <input type="text" class="form-control aiz-tag-input" name="tags[]"
                                    placeholder="{{ translate('Type and hit enter to add a tag') }}">
                                <small class="text-muted">{{translate('This is used for search. Input those words by
                                    which cutomer can find this product.')}}</small>
                            </div>
                        </div>

                        @if (addon_is_activated('pos_system'))
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{translate('Barcode')}}</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="barcode"
                                    placeholder="{{ translate('Barcode') }}">
                            </div>
                        </div>
                        @endif

                        @if (addon_is_activated('refund_request'))
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{translate('Refundable')}}</label>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="checkbox" name="refundable" checked>
                                    <span></span>
                                </label>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{translate('Product Images')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="signinSrEmail">{{translate('Gallery Images')}}
                                <small>(600x600)</small></label>
                            <div class="col-md-8">
                                <div class="input-group" data-toggle="aizuploader" data-type="image"
                                    data-multiple="true">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{
                                            translate('Browse')}}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                    <input type="hidden" name="photos" class="selected-files">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                                <small class="text-muted">{{translate('These images are visible in product details page
                                    gallery. Use 600x600 sizes images.')}}</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="signinSrEmail">{{translate('Thumbnail Image')}}
                                <small>(300x300)</small></label>
                            <div class="col-md-8">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{
                                            translate('Browse')}}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                    <input type="hidden" name="thumbnail_img" class="selected-files">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                                <small class="text-muted">{{translate('This image is visible in all product box. Use
                                    300x300 sizes image. Keep some blank space around main object of your image as we
                                    had to crop some edge in different devices to make it responsive.')}}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{translate('Product Videos')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{translate('Video Provider')}}</label>
                            <div class="col-md-8">
                                <select class="form-control aiz-selectpicker" name="video_provider" id="video_provider">
                                    <option value="youtube">{{translate('Youtube')}}</option>
                                    <option value="dailymotion">{{translate('Dailymotion')}}</option>
                                    <option value="vimeo">{{translate('Vimeo')}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{translate('Video Link')}}</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="video_link"
                                    placeholder="{{ translate('Video Link') }}">
                                <small class="text-muted">{{translate("Use proper link without extra parameter. Don't
                                    use short share link/embeded iframe code.")}}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{translate('Product price + stock')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ translate('Default price') }} <span
                                    class="text-danger">*</span></label>
                            <div class="col-md-6">
                                <input type="number" lang="en" min="0" value="0" step="0.01"
                                    placeholder="{{ translate('Default price') }}" name="unit_price"
                                    class="form-control" required>
                            </div>
                        </div>

                        <div class="card border-0">
                            <div class="card-header border-0">
                                <h5 class="mb-0 h6">{{ translate('Unit Price Per Cities') }}</h5>
                                <h5 class="mb-0 h6 text-right">{{ translate('Total Qty') }}: <span
                                        id="totalQty">0</span></h5>
                            </div>
                            <div class="card-body border-0">
                                <div class="form-group mb-3">
                                    <label for="name">
                                        <a href="javascript:void(0);" id="pushCity"
                                            class=" btn-sm btn btn-outline-danger"><i class="fas fa-plus"></i>{{
                                            translate('Add New') }}</a>
                                    </label>
                                    <div class="cityItems">
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(addon_is_activated('club_point'))
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">
                                {{translate('Set Point')}}
                            </label>
                            <div class="col-md-6">
                                <input type="number" lang="en" min="0" value="0" step="1"
                                    placeholder="{{ translate('1') }}" name="earn_point" class="form-control">
                            </div>
                        </div>
                        @endif

                        <div>
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">{{translate('Quantity')}} <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-6">
                                    <input type="number" lang="en" min="0" value="0" step="1"
                                        placeholder="{{ translate('Quantity') }}" name="current_stock"
                                        class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-3 col-from-label">
                                    {{translate('SKU')}}
                                </label>
                                <div class="col-md-6">
                                    <input type="text" placeholder="{{ translate('SKU') }}" name="sku"
                                        class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">
                                {{translate('Wholesale Prices')}}
                            </label>
                            <div class="col-md-6">
                                <div class="qunatity-price">
                                    <div class="row gutters-5">
                                        <div class="col-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control"
                                                    placeholder="{{translate('Min QTY')}}" name="wholesale_min_qty[]"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control"
                                                    placeholder="{{ translate('Max QTY') }}" name="wholesale_max_qty[]"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="form-group">
                                                <input type="text" class="form-control"
                                                    placeholder="{{ translate('Price per piece') }}"
                                                    name="wholesale_price[]" required>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button"
                                                class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                                data-toggle="remove-parent" data-parent=".row">
                                                <i class="las la-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-soft-secondary btn-sm" data-toggle="add-more"
                                    data-content='<div class="row gutters-5">
                                        <div class="col-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control" placeholder="{{translate(' Min
                                    Qty')}}" name="wholesale_min_qty[]" required>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="{{ translate('Max Qty') }}"
                                    name="wholesale_max_qty[]" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="{{ translate('Price per piece') }}"
                                    name="wholesale_price[]" required>
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                data-toggle="remove-parent" data-parent=".row">
                                <i class="las la-times"></i>
                            </button>
                        </div>
                    </div>'
                    data-target=".qunatity-price">
                    {{ translate('Add More') }}
                    </button>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{translate('Product Description')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{translate('Description')}}</label>
                            <div class="col-md-8">
                                <textarea class="aiz-text-editor" name="description"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 h6">{{translate('SEO Meta Tags')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{translate('Meta Title')}}</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="meta_title"
                                    placeholder="{{ translate('Meta Title') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{translate('Description')}}</label>
                            <div class="col-md-8">
                                <textarea name="meta_description" rows="8" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label" for="signinSrEmail">{{ translate('Meta Image')
                                }}</label>
                            <div class="col-md-8">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{
                                            translate('Browse')}}
                                        </div>
                                    </div>
                                    <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                    <input type="hidden" name="meta_img" class="selected-files">
                                </div>
                                <div class="file-preview box sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
</div>

</div>

<div class="col-lg-4">

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('Low Stock Quantity Warning')}}</h5>
        </div>
        <div class="card-body">
            <div class="form-group mb-3">
                <label for="name">
                    {{translate('Quantity')}}
                </label>
                <input type="number" name="low_stock_quantity" value="1" min="0" step="1" class="form-control">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">
                {{translate('Stock Visibility State')}}
            </h5>
        </div>

        <div class="card-body">

            <div class="form-group row">
                <label class="col-md-6 col-from-label">{{translate('Show Stock Quantity')}}</label>
                <div class="col-md-6">
                    <label class="aiz-switch aiz-switch-success mb-0">
                        <input type="radio" name="stock_visibility_state" value="quantity" checked>
                        <span></span>
                    </label>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-6 col-from-label">{{translate('Show Stock With Text Only')}}</label>
                <div class="col-md-6">
                    <label class="aiz-switch aiz-switch-success mb-0">
                        <input type="radio" name="stock_visibility_state" value="text">
                        <span></span>
                    </label>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-6 col-from-label">{{translate('Hide Stock')}}</label>
                <div class="col-md-6">
                    <label class="aiz-switch aiz-switch-success mb-0">
                        <input type="radio" name="stock_visibility_state" value="hide">
                        <span></span>
                    </label>
                </div>
            </div>

        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('Featured')}}</h5>
        </div>
        <div class="card-body">
            <div class="form-group row">
                <label class="col-md-6 col-from-label">{{translate('Status')}}</label>
                <div class="col-md-6">
                    <label class="aiz-switch aiz-switch-success mb-0">
                        <input type="checkbox" name="featured" value="1">
                        <span></span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('Todays Deal')}}</h5>
        </div>
        <div class="card-body">
            <div class="form-group row">
                <label class="col-md-6 col-from-label">{{translate('Status')}}</label>
                <div class="col-md-6">
                    <label class="aiz-switch aiz-switch-success mb-0">
                        <input type="checkbox" name="todays_deal" value="1">
                        <span></span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('Estimate Shipping Time')}}</h5>
        </div>
        <div class="card-body">
            <div class="form-group mb-3">
                <label for="name">
                    {{translate('Shipping Days')}}
                </label>
                <div class="input-group">
                    <input type="number" class="form-control" name="est_shipping_days" min="1" step="1"
                        placeholder="{{translate('Shipping Days')}}">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="inputGroupPrepend">{{translate('Days')}}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="col-12">
    <div class="btn-toolbar float-right mb-3" role="toolbar" aria-label="Toolbar with button groups">
        <div class="btn-group mr-2" role="group" aria-label="First group">
            <button type="submit" name="button" value="draft" class="btn btn-warning">{{ translate('Save As Draft')
                }}</button>
        </div>
        <div class="btn-group mr-2" role="group" aria-label="Third group">
            <button type="submit" name="button" value="unpublish" class="btn btn-primary">{{ translate('Save &
                Unpublish') }}</button>
        </div>
        <div class="btn-group" role="group" aria-label="Second group">
            <button type="submit" name="button" value="publish" class="btn btn-success">{{ translate('Save & Publish')
                }}</button>
        </div>
    </div>
</div>
</div>
</form>
</div>

@endsection

@section('script')

<script type="text/javascript">
    "use strict";

    function calcQty(){
        var total = 0;
        $('.stateQty').each(function(i, obj) {
            total += parseFloat($(obj).val());
        });
        $("#totalQty").text(total);
    }

    const items = []
    $(document).on('click', '#removeItem', function () {
        $(this).closest('.row').remove();
        calcQty();
    })
    $(document).on('click', '#pushCity', function () {
        items.push(
            {
                state_id: null,
                cost: null,
                qty: null,
            }
        )
        if (items.length > 0)
            for (let i = 0; i < items.length; i++) {
                var html = '<div class="row mb-3">'
                var options =  @json($states);
                var optionsstateHtml = options.map(function (opt) {
                    return '<option value="' + opt.id + '">' + opt.name + '</option>';
                }).join('');
                var optionsCostHtml = '<input name="states[' + i + '][cost]" type="number" placeholder="{{ translate('Price') }}" class="form-control" />';
                var optionsQtyHtml = '<input name="states[' + i + '][qty]" type="number" placeholder="{{ translate('Qty') }}" class="stateQty form-control" oninput="calcQty()" />';
                var optionsDeleteHtml = '<a href="javascript:void(0);" id="removeItem" class="btn btn-soft-primary btn-icon btn-circle btn-sm"><i class="las la-trash"></i></a>';
                html += '<div class="col-4">'
                html += '<select name="states[' + i + '][state_id]" class="form-control aiz-selectpicker" data-placeholder="Choose ..." data-live-search="true">'
                html += '<option>Select City</option>'
                html += optionsstateHtml
                html += '</select>'
                html += '</div>'
                html += '<div class="col-4">'
                html += optionsCostHtml
                html += '</div>'
                html += '<div class="col-3">'
                html += optionsQtyHtml
                html += '</div>'
                html += '<div class="col-1">'
                html += optionsDeleteHtml
                html += '</div>'
                html += '</div>'
            }
        $(".cityItems").append(html)
    });

    $('form').bind('submit', function (e) {
        // Disable the submit button while evaluating if the form should be submitted
        $("button[type='submit']").prop('disabled', true);

        var valid = true;

        if (!valid) {
            e.preventDefault();

            // Reactivate the button if the form was not submitted
            $("button[type='submit']").button.prop('disabled', false);
        }
    });

    $("[name=shipping_type]").on("change", function (){
        $(".flat_rate_shipping_div").hide();

        if($(this).val() == 'flat_rate'){
            $(".flat_rate_shipping_div").show();
        }

    });

</script>

@endsection