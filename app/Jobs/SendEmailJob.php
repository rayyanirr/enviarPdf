<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;


class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pathToFile;
    protected $filteredArray;
    protected $periodo;

    public function getPathToFile()
    {
        return $this->pathToFile;
    }

    public function getFilteredArray()
    {
        return $this->filteredArray;
    }

    public function getPeriodo()
    {
        return $this->periodo;
    }

    /**
     * Create a new job instance.
     */
    public function __construct($pathToFile, $filteredArray, $periodo)
    {
        $this->pathToFile = $pathToFile;
        $this->filteredArray = $filteredArray;
        $this->periodo = $periodo;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        Mail::raw($this->periodo, function ($message) {
            $message->from('datos.venezuela@kfc.com.ve', 'Nomina KFC');
            $message->to($this->filteredArray[1])->subject('Recibo de pago');
            $message->attach($this->pathToFile);
        });

        if (file_exists($this->pathToFile)) {
            unlink($this->pathToFile);
        }
    }
}
