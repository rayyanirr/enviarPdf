<?php

namespace App\Http\Controllers;

use App\Imports\PDFImport;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Smalot\PdfParser\Parser;

class SendPdfController extends Controller
{
    public function sendPdf(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'path_pdf' => 'required',
            'periodo' => 'required',
        ]);

        $mailsFailure = [];

        $archivosPdf = $this->readFilesFromDirectory($request->input('path_pdf'));

        $collection = Excel::toArray(new PDFImport, $request->file('file'));


        foreach ($archivosPdf as  $value) {
            $cedulaEmpleado = $this->readPdf($request->input('path_pdf') . '/' . $value);



            $filteredArray = Arr::first($collection[0], function ($value, $key) use ($cedulaEmpleado) {

                return $value[0] == $cedulaEmpleado;
            });


            $pathToFile = $request->input('path_pdf') . '/' . $value;


            Mail::raw($request->periodo, function ($message) use ($pathToFile, $filteredArray) {
                $message->from('datos.venezuela@kfc.com.ve', 'Nomina KFC');
                $message->to($filteredArray[1])->subject('Recibo de pago');
                $message->attach($pathToFile);
            });
        }


        return response()->json([
            'message' => 'Emails enviados',
            'mails_failure' => $mailsFailure
        ]);
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
}
