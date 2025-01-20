<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\TngFileConvertRequest;
use App\Models\TngFileConvert;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class TngFileConvertController extends Controller
{
    public function store(TngFileConvertRequest $request){
        $params = [];
        if($request->has('name') && isset($request->name)) $params['name'] = $request->name;
        // if($request->has('content') && isset($request->content)) $params['content'] = $request->content;
        if($request->has('content') && isset($request->content)) $params['content'] = file_get_contents($request->content->path());
        if($request->has('size') && isset($request->size)) $params['size'] = $request->size;
        if($request->has('type') && isset($request->type)) $params['type'] = $request->type;
        if(($request->getClientIp() !== null)) $params['ip_address'] = $request->getClientIp();

        // $binaryData = file_get_contents($params['content']->path());
        // $params['content'] = $binaryData;

        try{
            $db_insert_tng_file = TngFileConvert::create($params);

            // Failed to insert file
            if(!$db_insert_tng_file) throw new Exception("Failed to process file " . $params['name'] ?? '');

            if($db_insert_tng_file->id){
                $returnValue = Artisan::call('convert:TngToExcel', ['tng_file_id' => $db_insert_tng_file->id]);

                if ($returnValue !== 0) {
                    throw new Exception("Failed to process file " . $params['name'] ?? '');
                }

                $command_output = Artisan::output();
                Log::info('$command_output');
                Log::info(json_decode($command_output));

                $command_output_json = json_decode($command_output);
                if($command_output_json && !$command_output_json->success) throw new Exception("Failed to process file " . $params['name'] ?? '');

                $db_get_tng_file = TngFileConvert::find($db_insert_tng_file->id);

                if($db_get_tng_file){                    
                    $result = [
                        'success'   => 1,
                        'msg'       => "Successfully converted file " . ($params['name'] ?? '') . " to .CSV",
                        'data'      => [
                            'converted_name'      => $db_get_tng_file->converted_name ?? null,
                            'converted_content'   => base64_encode($db_get_tng_file->converted_content) ?? null,
                            'converted_size'      => $db_get_tng_file->converted_size ?? null,
                            'converted_type'      => $db_get_tng_file->converted_type ?? null
                        ]
                    ];
                    $result_code = 200;
            
                    return response()->json($result, $result_code);
                }
                
            }


        } catch (Exception $error){
            $result = [
                'success'   => 0,
                'msg'       => $error,
                'data'      => null
            ];
            $result_code = 422;

            return response()->json($result, $result_code);
        }
    }
}
