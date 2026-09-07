@props(['amount'])

@php
    use App\Support\Money;
@endphp

<span {{ $attributes }}>{{ Money::format((int) $amount) }}</span>
