@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
    	<div class="row align-items-center">
    		<div class="col-md-12">
    			<h1 class="h3">{{translate('All deliveries')}}</h1>
    		</div>
    	</div>
    </div>
    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <form class="" id="sort_delivery" action="" method="GET">
                    <div class="card-header row gutters-5">
                        <div class="col text-center text-md-left">
                            <h5 class="mb-md-0 h6">{{ translate('deliveries') }}</h5>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="sort_city" name="sort_city" @isset($sort_delivery) value="{{ $sort_delivery }}" @endisset placeholder="{{ translate('Type delivery name & Enter') }}">
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
                                <th>{{translate('Name')}}</th>
                                <th>{{translate('Description')}}</th>
                                <th data-breakpoints="lg" class="text-right">{{translate('Options')}}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deliveries as $key => $delivery)
                                <tr>
                                    <td>{{ ($key+1)  }}</td>
                                    <td>{{ $delivery->name ?? '' }}</td>
                                    <td>{{ $delivery->desc ?? '' }}</td>
                                    <td class="text-right">
                                        <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                                           href="{{ route('deliveries.edit', ['id'=>$delivery->id, 'lang'=>env('DEFAULT_LANGUAGE')]) }}"
                                           title="{{ translate('Edit') }}">
                                            <i class="las la-edit"></i>
                                        </a>
                                        <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete"
                                           data-href="{{route('deliveries.destroy', $delivery->id)}}"
                                           title="{{ translate('Delete') }}">
                                            <i class="las la-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="aiz-pagination">
                        {{ $deliveries->appends(request()->input())->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
    		<div class="card">
    			<div class="card-header">
    				<h5 class="mb-0 h6">{{ translate('Add New delivery') }}</h5>
    			</div>
    			<div class="card-body">
    				<form action="{{ route('deliveries.store') }}" method="POST">
    					@csrf
    					<div class="form-group mb-3">
    						<label for="name">{{translate('Name')}}</label>
    						<input type="text" placeholder="{{translate('Name')}}" name="name" class="form-control" required>
    					</div>
    					<div class="form-group mb-3">
    						<label for="name">{{translate('Description')}}</label>
                            <textarea name="desc" cols="30" rows="10" class="form-control"></textarea>
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
        function sort_delivery(el){
            $('#sort_delivery').submit();
        }
        const items = []
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
    </script>
@endsection
