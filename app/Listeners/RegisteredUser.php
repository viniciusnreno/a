<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\UserRegistered;
use Illuminate\Support\Facades\Mail;

class RegisteredUser
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Registered  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        if(env('PROMO_EMAIL_ENABLED') === true){
            Mail::to($event->user->email)->queue(new UserRegistered());
        }
    }
}
