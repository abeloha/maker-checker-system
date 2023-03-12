<?php

namespace App\Jobs;

use App\Mail\PendingRequestEmail;
use App\Models\Admin;
use App\Models\ChangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyAdmin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $changeRequest;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ChangeRequest $changeRequest)
    {
        $this->changeRequest = $changeRequest;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $admins = Admin::where('id', '!=', $this->changeRequest->admin->id)->get();

        try {
            Mail::to($admins)->send(new PendingRequestEmail($this->changeRequest));
        } catch (\Throwable $th) {
           Log::channel('mail_errors')->info($th->getMessage());
        }
    }
}
