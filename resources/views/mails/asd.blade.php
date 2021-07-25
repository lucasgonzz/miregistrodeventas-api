<!DOCTYPE html>
<html lang="en" style="margin: 0; padding: 0; font-family: Nunito; box-sizing: border-box;">
<body style="margin: 0; padding: 0; font-family: Nunito; box-sizing: border-box;">
	<div class="nav" style="margin: 0; padding: 0; font-family: Nunito; box-sizing: border-box; width: 100%; height: 50px; background: #28a745; display: flex; align-items: center;">
		<p style="margin: 0; padding: 0; font-family: Nunito; box-sizing: border-box; color: #FFF; font-size: 1.5em; margin-left: .5em;">111</p>
	</div>
	<div class="content" style="margin: 0; font-family: Nunito; box-sizing: border-box; padding: 1em;">
		<h1 style="margin: 0; padding: 0; font-family: Nunito; box-sizing: border-box; color: #28a745; margin-bottom: 15px;">Hola 111!</h1>
		<p style="margin: 0; padding: 0; font-family: Nunito; box-sizing: border-box; margin-bottom: 15px;">Pensamos que estos artículos podrian interesarte:</p>
		<div class="cont-cards" style="margin: 0; padding: 0; font-family: Nunito; box-sizing: border-box; display: flex; justify-content: flex-start; flex-wrap: wrap;">
			@foreach($articles as $article)
				<a target="_blank" href="http://{{ $user->online }}/articulos/{{ $article->slug }}" class="article-card apretable shadow-2 border-radius-1" style="margin: 0; padding: 0; font-family: Nunito; box-sizing: border-box; cursor: pointer; transition: all .2s; display: flex; -webkit-box-shadow: 0px 0px 6px -2px rgba(0,0,0,0.75); -moz-box-shadow: 0px 0px 6px -2px rgba(0,0,0,0.75); box-shadow: 0px 0px 6px -2px rgba(0,0,0,0.75); border-radius: .25em; height: auto; color: #333; text-decoration: none; border: none;">
					<div class="img-container" style="margin: 0; padding: 0; font-family: Nunito; box-sizing: border-box;">
						<img src="https://res.cloudinary.com/lucas-cn/image/upload/c_crop,g_custom/{{ $article->images[0]->url }}" alt="" style="margin: 0; padding: 0; font-family: Nunito; box-sizing: border-box;">
					</div>
					<div class="card-article-body" style="margin: 0; padding: 0; font-family: Nunito; box-sizing: border-box;">
						<div class="name-heart" style="margin: 0; padding: 0; font-family: Nunito; box-sizing: border-box;">
							<p class="product-name" style="margin: 0; padding: 0; font-family: Nunito; box-sizing: border-box; text-align: left; margin-bottom: 0; font-size: 1em;">
								111
							</p>
						</div>
						<p class="product-price" style="margin: 0; padding: 0; font-family: Nunito; box-sizing: border-box; margin-bottom: 0; font-weight: bold; font-size: 1.1em; text-align: left;">
							111
						</p>
					</div>
				</a>
			@endforeach
		</div>
	</div>
</body>
</html>