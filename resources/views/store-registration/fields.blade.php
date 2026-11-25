@foreach($fields as $field)
@php
    [$name,$label,$required] = $field;
    $type = $field[3] ?? 'text';
@endphp
<div class="form-group"><label for="{{ $name }}">{{ $label }}{{ $required ? ' *' : '' }}</label>
@if($type === 'textarea')<textarea class="form-control" id="{{ $name }}" name="{{ $name }}" @required($required) maxlength="2000">{{ old($name) }}</textarea>
@else<input class="form-control" id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name) }}" @required($required) maxlength="{{ in_array($name, ['website','social_media']) ? 500 : 255 }}">@endif
@error($name)<div class="field-error">{{ $message }}</div>@enderror</div>
@endforeach
