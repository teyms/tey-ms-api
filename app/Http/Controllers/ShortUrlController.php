<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\ShortUrlRequest;

use App\Models\ShortUrl;


class ShortUrlController extends Controller
{
    public function getRedirectUrl($shorturl){
        $short_url_exist = ShortUrl::where('url', $shorturl)->first();

        $result = [
            'success'   => 0,
            'msg'       => 'failed. not found',
            'ori_url'   => null
        ];
        $result_code = 422;

        if($short_url_exist && $short_url_exist->ori_url){

            $short_url_exist->used_count += 1;
            $short_url_exist->save();

            $result = [
                'success'   => 1,
                'msg'       => 'Successfully Added ShortURl',
                'ori_url'   => $short_url_exist->ori_url
            ];
            $result_code = 200;
        } 

        return response()->json($result, $result_code);
    }

    public function store(ShortUrlRequest $request){
        $params = [];
        if($request->has('ori_url') && isset($request->ori_url)) $params['ori_url'] = $request->ori_url;
        if(($request->getClientIp() !== null)) $params['ip_address'] = $request->getClientIp();
        
        //prevent having same code
        do{
            $random_code = Str::random(8);
            $random_code_exist = ShortUrl::where('url', $random_code)->first();
        }while($random_code_exist);

        $params['url'] = $random_code;

        $url_exist = ShortUrl::where('ori_url', $params['ori_url'])->first();

        if(!$url_exist){
            try{
                $create = ShortUrl::create($params);

            } catch (Exception $error){
                $result = [
                    'success'   => 0,
                    'msg'       => $error
                ];
                $result_code = 422;

            }
        } else{
            $random_code = $url_exist->url;
        }

        $result = [
            'success'   => 1,
            'msg'       => 'Successfully Added ShorURl',
            'data'      => [
                'shorten_url'   => $random_code
            ]
        ];
        $result_code = 200;

        return response()->json($result, $result_code);
    }
}
