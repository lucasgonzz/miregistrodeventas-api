@component('mail::message')
# Hola {{ $buyer->name }}!


En {{ $article->user->company_name }} ya ingreso {{ $article->name }} 

@component('mail::button', ['url' => $url])
Ver {{ $article->name }} 
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
