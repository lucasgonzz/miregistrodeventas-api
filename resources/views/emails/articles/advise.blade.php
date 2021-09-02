@component('mail::message')
# Introduction

Hola {{ $buyer->name }}!
En {{ $article->user->company_name }} ya ingreso {{ $article->name }} 

@component('mail::button', ['url' => ''])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
