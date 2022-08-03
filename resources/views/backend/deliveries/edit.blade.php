@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{translate('Delivery Information')}}</h5>
</div>

<div class="row">
  <div class="col-lg-8 mx-auto">
      <div class="card">
          <div class="card-body p-0">
              <ul class="nav nav-tabs nav-fill border-light">
    				@foreach (\App\Models\Language::all() as $key => $language)
    					<li class="nav-item">
    						<a class="nav-link text-reset @if ($language->code == $lang) active @else bg-soft-dark border-light border-left-0 @endif py-3" href="{{ route('deliveries.edit', ['id'=>$delivery->id, 'lang'=> $language->code] ) }}">
    							<img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="11" class="mr-1">
    							<span>{{ $language->name }}</span>
    						</a>
    					</li>
  	            @endforeach
    			</ul>
              <form class="p-4" action="{{ route('deliveries.update', $delivery->id) }}" method="POST" enctype="multipart/form-data">
                  <input name="_method" type="hidden" value="PATCH">
                  <input type="hidden" name="lang" value="{{ $lang }}">
                  @csrf
                  <div class="form-group mb-3">
                      <label for="name">{{translate('Name')}}</label>
                      <input type="text" placeholder="{{translate('Name')}}" value="{{ $delivery->getTranslation('name', $lang) }}" name="name" class="form-control" required>
                  </div>

                  <div class="form-group">
                      <label for="state_id">{{translate('Description')}}</label>
                      <textarea name="desc" id="" cols="30" rows="10" class="form-control">{{ $delivery->getTranslation('desc', $lang) ?? '' }}</textarea>
                  </div>

                  <div class="form-group mb-3">
                      <label for="name">
                          {{ translate('Select Zones') }} <a href="javascript:void(0);" id="pushZone">{{ translate('Add New') }}</a>
                      </label>
                  </div>
                  <div class="mb-3">
                      <div class="row">
                          <div class="zoneItems"></div>
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
@section('script')
    <script type="text/javascript">
        const items = @json($zonesSelected);
        $(document).on('click', '#removeItem', function () {
            $(this).closest('.row').remove()
        })
        $(document).on('click', '#pushZone', function () {
            items.push(
                {
                    zone_id: null,
                    cost: null
                }
            )
            if (items.length > 0)
                for (let i = 0; i < items.length; i++) {
                    var html = '<div class="row mb-3">'
                    var options =  @json($zones);
                    var optionsZoneHtml = options.map(function (opt) {
                        return '<option value="' + opt.id + '">' + opt.name + '</option>';
                    }).join('');
                    var optionsCostHtml = '<input name="zone[' + i + '][cost]" type="number" placeholder="{{ translate('Price') }}" class="form-control" />';
                    var optionsDeleteHtml = '<a href="javascript:void(0);" id="removeItem" class="btn btn-soft-primary btn-icon btn-circle btn-sm"><i class="las la-trash"></i></a>';
                    var optionsTypeHtml = '<select name="zone[' + i + '][type]" class="form-control aiz-selectpicker" data-placeholder="Choose ..." data-live-search="true">';
                    optionsTypeHtml += '<option value="">'+'select type'+'</option>';
                    optionsTypeHtml += '<option value="wholesale">'+'Wholesale'+'</option>';
                    optionsTypeHtml +='<option value="retail">'+'Retail'+'</option>';
                    optionsTypeHtml += '</select>';
                    html += '<div class="col-6 mb-3">'
                    html += '<select name="zone[' + i + '][zone_id]" class="form-control aiz-selectpicker" data-placeholder="Choose ..." data-live-search="true">'
                    html += '<option> select zone</option>'
                    html += optionsZoneHtml
                    html += '</select>'
                    html += '</div>'
                    html += '<div class="col-6 mb-3">'
                    html += optionsCostHtml
                    html += '</div>'
                    html += '<div class="col-6 mb-3">'
                    html += optionsTypeHtml
                    html += '</div>'
                    html += '<div class="col-6 mb-3">'
                    html += optionsDeleteHtml
                    html += '</div>'
                    html += '</div>'
                }
            $(".zoneItems").append(html)
        });

        function getSelectedZones() {
            if (items.length > 0)
                for (let i = 0; i < items.length; i++) {
                    var html = '<div class="row mb-3">'
                    var data =  @json($zonesSelected);
                    var options =  @json($zones);
                    var optionsZoneHtml = options.map(function (opt) {
                        var selected = '';
                        if (data[i].zone_id == opt.id)
                            selected = 'selected'
                        return '<option value="' + opt.id + '" '+ selected +'>' + opt.name + '</option>';
                    }).join('');
                    var optionsCostHtml = '<input name="zone[' + i + '][cost]" value="'+data[i].cost+'" type="number" placeholder="{{ translate('Price') }}" class="form-control" />';
                    var optionsDeleteHtml = '<a href="javascript:void(0);" id="removeItem" class="btn btn-soft-primary btn-icon btn-circle btn-sm"><i class="las la-trash"></i></a>';
                    var optionsTypeHtml = '<select name="zone[' + i + '][type]" class="form-control aiz-selectpicker" data-placeholder="Choose ..." data-live-search="true">';
                    var wholesale_selected = ''
                    var retail_selected = ''
                    if(data[i].type == 'retail'){
                        retail_selected = 'selected'
                    }else if(data[i].type == 'wholesale') {
                        wholesale_selected = 'selected'
                    }
                    optionsTypeHtml += '<option value="">'+'select type'+'</option>';
                    optionsTypeHtml += '<option value="wholesale"'+wholesale_selected+'>'+'Wholesale'+'</option>';
                    optionsTypeHtml +='<option value="retail"'+retail_selected+'>'+'Retail'+'</option>';
                    optionsTypeHtml += '</select>';
                    html += '<div class="col-6 mb-3">'
                    html += '<select name="zone[' + i + '][zone_id]" class="form-control aiz-selectpicker" data-placeholder="Choose ..." data-live-search="true">'
                    html += '<option> select zone</option>'
                    html += optionsZoneHtml
                    html += '</select>'
                    html += '</div>'
                    html += '<div class="col-6 mb-3">'
                    html += optionsCostHtml
                    html += '</div>'
                    html += '<div class="col-6 mb-3">'
                    html += optionsTypeHtml
                    html += '</div>'
                    html += '<div class="col-6 mb-3">'
                    html += optionsDeleteHtml
                    html += '</div>'
                    html += '</div>'
                    $(".zoneItems").append(html)
                }
        }
        $(document).ready(function () {
            getSelectedZones();
        });
    </script>
@endsection
