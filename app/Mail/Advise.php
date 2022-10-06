<?php

namespace App\Mail;

use App\Http\Controllers\Helpers\ImageHelper;
use App\Http\Controllers\Helpers\UserHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Advise extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($article)
    {
        $this->article = $article;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user = UserHelper::getFullModel();
        return $this->from($user->email)
                    ->subject('Nuevo stock de '.$this->article->name)
                    ->markdown('emails.articles.advise', [
                        'article'       => $this->article,
                        'user'          => $user,
                        'article_url'   => $user->online.'/articulos/'.$this->article->slug,
                        'logo_url'      => ImageHelper::image($user),
                    ]);
    }
}
