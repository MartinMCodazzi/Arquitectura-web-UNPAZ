@use(Illuminate\Support\Number)

@props(['cents'])

{{-- Precio en céntimos (ADR-0002) formateado en ARS/es_AR (ADR-0003). Las
     clases tipográficas se pasan desde quien llama vía $attributes. --}}
<span {{ $attributes }}>
    {{ Number::currency($cents / 100, config('store.currency'), config('store.locale')) }}
</span>
