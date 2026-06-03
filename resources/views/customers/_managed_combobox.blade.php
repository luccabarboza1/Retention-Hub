{{-- Reutiliza a partial de managed combobox com URL de customer-options --}}
@php $saveUrl = route('settings.customer-options', $type); @endphp
@include('cards._managed_combobox')
