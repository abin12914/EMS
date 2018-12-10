<select class="form-control select2 {{ $customClassName }}" name="{{ $selectName }}" id="{{ $selectId }}" style="width: 100%" tabindex="{{ $tabindex }}" data-index-no="{{ $indexNo }}" {{ $disabledOption ? 'disabled' : '' }}>
    <option value="">Select product</option>
    @if(!empty($productsCombo) && (count($productsCombo) > 0))
        @foreach($productsCombo as $product)
            <option value="{{ $product->id }}" {{ (old($selectName) == $product->id || $selectedProductId == $product->id) ? 'selected' : '' }} data-weighment_wastage="{{ $product->weighment_wastage ?: 0 }}">{{ $product->name }} - {{ !empty($product->malayalam_name) ? $product->malayalam_name. ' - ' : '' }} #{{ $product->product_code }}</option>
        @endforeach
    @endif
</select>