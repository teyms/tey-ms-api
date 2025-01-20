<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\TngFileConvert;
use Exception;

class PyTngToExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:TngToExcel {tng_file_id?}';
    // protected $signature = 'convert:TngToExcel {file=null}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tng_file_id = strtolower($this->argument('tng_file_id'));
        if($tng_file_id == null){
            echo 'tng_file_id cannot be empty!!';
            Log::info('tng_file_id cannot be empty!!');
            return;
        }

        // $db_tng_file_convert = TngFileConvert::find($tng_file_id);

        // if(!$db_tng_file_convert) throw new Exception("Failed to get the file");

        // if($db_tng_file_convert->content){
        //     $result = shell_exec("python D:\github_repo\convert_transaction\convert_transaction_history\pdf_to_excel_V1.py " . escapeshellarg($db_tng_file_convert->content));
        // }

        // to test and cater for hostinger's python version
        // $result = shell_exec("\"C:\\Users\\teymi\\AppData\\Local\\Programs\\Python\\Python36\\python.exe\" " . config('app.tng_convert_csv_python') . " ". escapeshellarg($tng_file_id));
        // the original python version to used
        $result = shell_exec("python " . config('app.tng_convert_csv_python') . " " . escapeshellarg($tng_file_id));
        // $result = shell_exec("python D:\github_repo\convert_transaction\convert_transaction_history\pdf_to_excel_V1.py " . escapeshellarg($tng_file_id));
        
        // Log::info('========================');
        // Log::info('nice one: ' . $result);
        // Log::info('nice one: ' . $tng_file_id);
        // Log::info('========================');

        $this->info($result); //this is the return output, can be capture by code -> Artisan::output();
    }
}
