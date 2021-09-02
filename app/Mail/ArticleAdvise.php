<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ArticleAdvise extends Mailable
{
    use Queueable, SerializesModels;

    public $buyer;
    public $article;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($buyer, $article)
    {
        $this->buyer = $buyer;
        $this->article = $article;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->getSubject())
                    ->markdown('emails.articles.advise');
    }

    function getSubject() {
        return 'Ingreso '.$this->article->name;
    }
}