<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>holaSD</title>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito">
	<style>
		* {
			margin: 0;
			padding: 0;
			font-family: Nunito;
			box-sizing: border-box;
		}
		.nav {
			width: 100%;
			height: 50px;
			background: #28a745;
			display: flex;
			align-items: center;
		}
		.nav p {
			color: #FFF;
			font-size: 1.5em;
			margin-left: .5em;		
		}
		.content {
			padding: 1em !important;
		}
		.content > p {
			margin-bottom: 15px !important;
		}
		@media screen and (min-width:  992px) {
			.content {
				width: 70%;
				margin: auto !important;
			}
		}
		@media screen and (max-width:  992px) {
			.content {
				width: 100%;
			}
		}
		h1 {
			color: #28a745;
			margin-bottom: 15px;
		}
		.cont-cards {
			display: flex;
			justify-content: flex-start;
			flex-wrap: wrap;
		}
		.cont-cards > a, .cont-cards > .article-card {
			height: auto;
			color: #333;
			text-decoration: none;
		}
		@media screen and (max-width: 576px) {
			.cont-cards > a, .cont-cards > .article-card {
				margin-bottom: 1em !important;
			}
		}
		@media screen and (max-width: 778px) {
			.cont-cards > a, .cont-cards > .article-card {
				border: none !important;
			}
		}
		@media screen and (min-width: 576px) and (max-width: 768px) {
			.cont-cards > a, .cont-cards > .article-card {
				margin: 0 1% 1em !important;
				width: 48%;
			}
		}
		@media screen and (min-width: 768px) {
			.cont-cards > a, .cont-cards > .article-card {
				flex-direction: column;
				width: 32% !important;
				margin: 0 .6% 3em !important;
			}
		}
		.article-card {
			cursor: pointer;
			transition: all .2s;
			border: none !important;
			display: flex;
		}
		@media screen and (max-width: 768px) {
			.article-card {
				flex-direction: row !important;
				padding: .5em !important;
			}
			.img-container {
				width: 30%;
			}
			img {
				width: 100%;
				border-radius: .25em;
			}
			.card-article-body {
				width: 70%;
				padding: 0 1em !important;
			}
		}
		@media screen and (min-width: 768px) {
			.img-container {
				width: 100%;
			}
			img {
				width: 100%;
				border-radius: .25em .25em 0 0;
			}
			.card-article-body {
				padding: 1em !important;
			}
		}
		.name-heart .product-name {
			text-align: left;
			margin-bottom: 0;
			font-size: 1em;
		}
		.product-price {
			margin-bottom: 0;
			font-weight: bold;
			font-size: 1.1em;
			text-align: left;
		}
		.shadow-2 {
			-webkit-box-shadow: 0px 0px 6px -2px rgba(0,0,0,0.75);
			-moz-box-shadow: 0px 0px 6px -2px rgba(0,0,0,0.75);
			box-shadow: 0px 0px 6px -2px rgba(0,0,0,0.75);
		}
		.border-radius-1 {
			border-radius: .25em;
		}
	</style>
</head>
<body>
	<div class="nav">
		<p>{{ $user->company_name }}</p>
	</div>
	<div class="content">
		<h1>Hola {{ $buyer->name }}!</h1>
		<p>Pensamos que estos artículos podrian interesarte:</p>
		<div class="cont-cards">
			@foreach($articles as $article) 
				<a 
				target="_blank"
				href="http://{{ $user->online }}/articulos/{{ $article->slug }}"
				class="article-card apretable shadow-2 border-radius-1">
					<div class="img-container">
						<img src="https://res.cloudinary.com/lucas-cn/image/upload/c_crop,g_custom/{{ $article->images[0]->url }}" alt="">
					</div>
					<div
					class="card-article-body">
						<div 
						class="name-heart">
							<p class="product-name">
								{{ $article->name }}
							</p>
						</div>
						<p class="product-price">
							${{ $article->price }}
						</p>
					</div>
				</a>
			@endforeach
		</div>
	</div>
</body>
</html>