<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\ShortUrlRequest;
use Illuminate\Support\Facades\Log;

use App\Models\ShortUrl;

use function PHPUnit\Framework\isEmpty;

class ShortUrlController extends Controller
{
    public function getRedirectUrlGuest($shorturl){
        $short_url_exist = ShortUrl::where('url', $shorturl)->first();


        if($short_url_exist){

            if($short_url_exist->expires_at && $short_url_exist->expires_at < Carbon::now()){
                $result = [
                    'success'   => 0,
                    'msg'       => 'ShortUrl Expired',
                    'data'      => [
                        'original_url'   => null
                    ]
                ];
                $result_code = 422;
                return response()->json($result, $result_code);
            }

            if($short_url_exist->original_url){
                $short_url_exist->click_count += 1;
                $short_url_exist->save();
    
                $result = [
                    'success'   => 1,
                    'msg'       => 'Successfully Added ShortURl',
                    'data'      => [
                        'original_url'   => $short_url_exist->original_url
                    ]
                ];
                $result_code = 200;
                
                return response()->json($result, $result_code);
            }
        } 

        $result = [
            'success'   => 0,
            'msg'       => 'failed. not found',
            'data'      => [
                'original_url'   => null
            ]
        ];
        $result_code = 422;

        return response()->json($result, $result_code);
    }

    public function getRedirectUrl($customPath, $shorturl){
        $short_url_exist = ShortUrl::leftJoin('users', 'short_url.user_id', '=', 'users.id')
                            ->Where('short_url.url', $shorturl)
                            ->Where('users.short_url_path', $customPath)
                            ->first();

        if($short_url_exist){
            if($short_url_exist->expires_at && $short_url_exist->expires_at < Carbon::now()){
                $result = [
                    'success'   => 0,
                    'msg'       => 'ShortUrl Expired',
                    'data'      => [
                        'original_url'   => null
                    ]
                ];
                $result_code = 422;
                return response()->json($result, $result_code);
            }
    
            if($short_url_exist->original_url){
    
                $short_url_exist->click_count += 1;
                $short_url_exist->save();
     
                $result = [
                    'success'   => 1,
                    'msg'       => 'Successfully Added ShortURl',
                    'data'      => [
                        'original_url'   => $short_url_exist->original_url
                    ]
                ];
                $result_code = 200;
                
                return response()->json($result, $result_code);
            } 
        }

        $result = [
            'success'   => 0,
            'msg'       => 'failed. not found',
            'data'      => [
                'original_url'   => null
            ]
        ];
        $result_code = 422;

        return response()->json($result, $result_code);
    }

    public function store(ShortUrlRequest $request){
        $params = [];
        if($request->has('original_url') && isset($request->original_url)) $params['original_url'] = $request->original_url;
        if(($request->getClientIp() !== null)) $params['ip_address'] = $request->getClientIp();
        if(($request->getClientIp() !== null)) $params['guest_identifier'] = $request->getClientIp();

        
        //prevent having same code
        do{
            $random_code = Str::random(8);
            $random_code_exist = ShortUrl::where('url', $random_code)->first();
        }while($random_code_exist);

        $params['url'] = $random_code;

        $url_exist = ShortUrl::where('original_url', $params['original_url'])->first();

        if(!$url_exist){
            try{
                $create = ShortUrl::create($params);

            } catch (Exception $error){
                $result = [
                    'success'   => 0,
                    'msg'       => $error,
                    'data'      => [
                        'shorten_url'   => null
                    ]
                ];
                $result_code = 422;
                return response()->json($result, $result_code);
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

    public function storeAuth(ShortUrlRequest $request){
        try{
            $params = [];
            if($request->has('original_url') && isset($request->original_url))  $params['original_url'] = $request->original_url;
            if($request->has('customPath') && isset($request->customPath))      $params['url'] = $request->customPath;
            if($request->has('title') && isset($request->title))                $params['title'] = $request->title;
            if($request->has('description') && isset($request->description))    $params['description'] = $request->description;
            if($request->has('expires_at') && isset($request->expires_at) && !isEmpty($request->expires_at))      $params['expires_at'] = $request->expires_at;
            if(($request->getClientIp() !== null)){
                $params['ip_address'] = $request->getClientIp();
                $params['guest_identifier'] = $request->getClientIp();
            } 
    
            $user = $request->get('user')?? null;
    
            $result = [
                'success'   => 0,
                'msg'       => 'Unexpected Error',
                'data'      => [
                    'shorten_url'   => null
                ]
            ];
            $result_code = 422;

            if(!$user){
                $result['msg'] = 'Invalid User';
                return response()->json($result, $result_code);            
            }
    
            if(!$user->short_url_path){
                $result['msg'] = 'Please set your default unique path first before using the shorturl service';
                return response()->json($result, $result_code);  
            }
    
            $shortUrlExist = ShortUrl::leftJoin('users', 'short_url.user_id', '=', 'users.id')
                                    // ->leftJoin('member_accounts', function($join) {$join->on('deposits.member_account_id', '=', 'member_accounts.id');})
                                    ->Where('short_url.url', $params['url'])
                                    ->Where('users.short_url_path', $user->short_url_path)
                                    ->exists();                                   
    
            if($shortUrlExist){
                $result['msg'] = "The URL path {$user->short_url_path}/{$params['url']} has been occupied";
                return response()->json($result, $result_code);
            }                                 
    
            $createdShortUrl = ShortUrl::Create([
                'user_id'       => $user->id,
                'url'           => isset($params['url'])? $params['url']: null,
                'original_url'  => isset($params['original_url'])? $params['original_url']: null,
                'title'         => isset($params['title'])? $params['title']: null,
                'description'   => isset($params['description'])? $params['description']: null,
                'ip_address'    => isset($params['ip_address'])? $params['ip_address']: null,
                'expires_at'    => isset($params['expires_at'])? $params['expires_at']: null,
            ]);

            if(!$createdShortUrl){
                $result['msg'] = `Failed to create shorturl for {$params['url']}`;
                return response()->json($result, $result_code);             

            }

            $result = [
                'success'   => 1,
                'msg'       => 'Successfully Added ShorURl',
                'data'      => [
                    'shorten_url'   => $createdShortUrl->url
                ]
            ];
            $result_code = 200;
            return response()->json($result, $result_code);

        }catch(Exception $error){
            Log::info(['$error' => $error->getMessage()]);
            $result['msg'] = 'Unexpected Error';
            $result_code = 422;
            return response()->json($result, $result_code);
        }
    }


}
