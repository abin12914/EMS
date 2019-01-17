<select class="form-control select2" name="{{ $selectName }}" id="{{ $selectName }}" style="width: 100%" tabindex="{{ $tabindex }}">
    <option value="">Select excavator</option>
    @if(!empty($excavatorsCombo) && (count($excavatorsCombo) > 0))
        @foreach($excavatorsCombo as $excavator)
            <option value="{{ $excavator->id }}" {{ (old($selectName) == $excavator->id || $selectedExcavatorId == $excavator->id) ? 'selected' : '' }} data-breaker-rate="{{ $excavator->breaker_rate }}" data-bucket-rate="{{ $excavator->bucket_rate }}">{{ $excavator->name }}</option>
        @endforeach
    @endif
</select>