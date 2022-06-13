@component('mail::message')
# Nuevo Pedido

Hola {{ $provider_order->provider->name }}, te acercamos nuestro pedido mediante el siguiente documento PDF

@component('mail::button', ['url' => $url])
VER PDF
@endcomponent

Gracias
@endcomponent
