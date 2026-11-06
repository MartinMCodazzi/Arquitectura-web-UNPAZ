@use(Illuminate\Support\Number)

@props(['cents'])

{{-- Precio en céntimos formateado en ARS/es_AR. Las clases tipográficas
     se pasan desde quien llama vía $attributes. --}}
<span {{ $attributes }}>
    {{ Number::currency($cents / 100, config('store.currency'), config('store.locale')) }}
</span>
