<script>
    var products = @json($products);
    var productsSort = @json($productsSort);

    function deleteProductFunction(index)
    {
        $("#productRecord"+index).remove();
        calcPrice();
    }

    function getProductPrice(index)
    {
        var id = $("#productSelect"+index).val();
        var price = products[id].price;
        $("#productPrice"+index).text(price);
        calcPrice();
    }

    function calcPrice()
    {
        var total = 0;
        var index,price,qty;
        $('.productPrice').each(function(i, obj) {
            qty = parseFloat( $("#productQty"+$(obj).attr('index')).val() );
            price = parseFloat($(obj).text());
            if(isNaN(qty))
            {
                qty = 0;
            }
            total += (price*qty);
        });
        if( isNaN(total) )
        {
            $("#totalPrice").text("0");
        }
        else
        {
            $("#totalPrice").text(total);
        }
    }

    function addNewProductFunction()
    {
        var index = parseInt($("#productsCount").val());
        $("#productsCount").val( index + 1 );

        var content = `
        <div class="form-group row" id="productRecord`+index+`">
        <div class="col-md-4">  
            <select class="form-control aiz-selectpicker" name="products[]" id="productSelect`+index+`" data-live-search="true"
                onchange="getProductPrice(`+index+`)" required>
                <option value="">{{ translate('Select Product') }}</option>`;
            
        Object.keys(productsSort).forEach(function(key) {
            content += '<option value="'+productsSort[key].id+'">'+productsSort[key].name+'</option>';
        });

        content += `
        </select>
        </div>
        <div class="col-md-4">
            <input type="number" lang="{{ app()->getLocale() }}" id="productQty`+index+`" min="0" step="1" name="qty[]" value="0" oninput="calcPrice()"
                required placeholder="{{ translate('Enter Quantity') }}" class="form-control">
        </div>
        <div class="col-md-4 d-flex justify-content-between align-items-center">
            <p class="m-0">{{ translate('Price') }}: <span index="`+index+`" class="productPrice" id="productPrice`+index+`">0</span></p>
            <button type="button" onclick="deleteProductFunction(`+index+`)"
                class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                title="{{ translate('Add') }}">
                <i class="las la-trash"></i>
            </button>
        </div>
        </div>
        `;
        
        $("#productDiv").append(content);
        AIZ.plugins.bootstrapSelect();
    }
</script>