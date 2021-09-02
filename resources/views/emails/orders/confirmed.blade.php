@component('mail::message')
# Introduction

{{ $buyer->name }}, {{ $commerce->company_name }} confirmó tu pedido.

@component('mail::panel')
This is the panel content.
@endcomponent

@component('mail::button', ['url' => 'https://kioscoverde.com', 'color' => 'success'])
Ver pedido
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
