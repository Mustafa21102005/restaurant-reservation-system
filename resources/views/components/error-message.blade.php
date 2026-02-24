@error($field)
    <div {{ $attributes->merge(['class' => 'text-danger mt-2']) }}>{{ $message }}</div>
@enderror
