<?php

namespace App\Http\Controllers;

use App\Imports\PDFImport;
use App\Jobs\SendEmailJob;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Storage;

class SendPdfController extends Controller
{
    public function sendPdf(Request $request)
    {

        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'pdfs.*' => 'required|mimes:pdf',
            'periodo' => 'required',
        ]);

        DB::table('failed_jobs')->truncate();

        $this->borrarPdfs(Storage::disk('public')->files('pdfs'));

        $this->guardarPdf($request->file('pdfs'));

        $archivosPdf =  Storage::disk('public')->files('pdfs');

        $collection = Excel::toArray(new PDFImport, $request->file('file'));


        foreach ($archivosPdf as  $value) {
            $cedulaEmpleado = $this->readPdf(Storage::disk('public')->path($value));

            $filteredArray = Arr::first($collection[0], function ($value, $key) use ($cedulaEmpleado) {
                return $value[0] == $cedulaEmpleado;
            });

            $pathToFile = Storage::disk('public')->path($value);

            SendEmailJob::dispatch($pathToFile, $filteredArray, $request->periodo);

        }
        return redirect('/dashboard')->banner('Los correos se van a enviar en segundo plano, por favor espere.');
    }

    public function readFilesFromDirectory($path)
    {
        $directoryPath = $path;

        if (File::isDirectory($directoryPath)) {
            $files = File::files($directoryPath);

            $fileNames = [];
            foreach ($files as $file) {
                $fileNames[] = pathinfo($file)['basename'];
            }

            return $fileNames;
        } else {
            throw ValidationException::withMessages([
                'path_pdf' => 'El directorio proporcionado no existe.',
            ]);
        }
    }

    public function readPdf($path)
    {

        $parser = new Parser();

        // Carga el contenido del PDF
        $pdf = $parser->parseFile($path);

        // Extrae el texto del PDF
        $text = $pdf->getText();

        // Convierte el texto en una colección
        $collection = collect(explode("\n", $text));


        $cedula = explode("\t", $collection[12]);

        if (count($cedula) == 1) {
            $cedula = explode(" ", $collection[12]);
        }

        return  trim($cedula[0]);
    }

    public function guardarPdf($pdfs){

        foreach ($pdfs as $pdf) {
            $pdf->store('pdfs', 'public');
        }
    }

    public function FailedJobs()
    {
        $payloads = [];
        $failedJobs = DB::table('failed_jobs')->pluck('payload');

        foreach ($failedJobs as $payload) {

            $payload = json_decode($payload, true);

            $job = unserialize($payload['data']['command']);

            $payloads[] = [
                'pathToFile' => $job->getPathToFile(),
                'cedula' => $job->getFilteredArray()[0],
                'email' => $job->getFilteredArray()[1],
                'periodo' => $job->getPeriodo(),

            ];


        }

        return response()->json($payloads, 200);
    }

    public function borrarPdfs($pdfs)
    {
        foreach ($pdfs as $pdf) {
            Storage::disk('public')->delete($pdf);
        }
    }
}
