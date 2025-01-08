<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\FCMService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FulfillOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderId;
    protected $userFcmToken;
    public function __construct($orderId, $userFcmToken)
    {
        $this->orderId = $orderId;
        $this->userFcmToken = $userFcmToken;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $order = Order::find($this->orderId);

            if ($order->status === "pending") {
                $order->status = "fulfilled";
                $order->save();

                if ($this->userFcmToken !== null) {
                    $fcmService = new FCMService();
                    $fcmService->singleNotification($this->userFcmToken,"Order Status 💚","Your Order has been fulfilled Successfully");
                }
            }
        } catch (\Exception $err) {
            Log::error("Failed to fulfill order {$this->orderId}:" . $err->getMessage());
        }
    }

    public function middleware()
    {
        return [new WithoutOverlapping($this->orderId)];
    }
}
