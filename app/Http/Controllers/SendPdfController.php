<?php

namespace App\Http\Controllers;

use App\Imports\PDFImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

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

        $collection = Excel::toCollection(new PDFImport, $request->file('file'));

        foreach ($collection[0] as $value) {

            $filename = "$value[0].pdf";

            if (in_array($filename, $archivosPdf)) {

                $pathToFile = $request->input('path_pdf') . '/' . $filename;

                try {
                    Mail::raw($request->periodo, function ($message) use ($pathToFile, $value) {
                        $message->from('datos.venezuela@kfc.com.ve', 'Nomina KFC');
                        $message->to($value[1])->subject('Recibo de pago');
                        $message->attach($pathToFile);
                    });
                } catch (\Throwable $th) {
                    $mailsFailure[] = $value[1];
                }
            }
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
}
