<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use App\Libraries\PicPay;

class ProcessPicpay implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $coupon;
    protected $cpf;
    protected $value;
    protected $notWithDrawable;
    public $uniqueFor = 60;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($coupon, $cpf, $value, $notWithDrawable)
    {
        $this->coupon = $coupon;
        $this->cpf = $cpf;
        $this->value = $value;
        $this->notWithDrawable = $notWithDrawable;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->coupon->refresh();

        if($this->coupon->status == 1 && $this->coupon->picpay_return === null){
            Log::debug('Picpay: Passei na condição');
            $picpay = new PicPay();
            $transfer = $picpay->transfer($this->cpf, $this->value, false);

            $this->coupon->picpay_return = $transfer->getBody()->getContents();
            $this->coupon->save();
        }
        
    }

    public function failed($exception){
        $this->coupon->picpay_error = $exception->getMessage();
        $this->coupon->save();
    }

    public function uniqueId()
    {
        return $this->coupon->id;
    }
}
