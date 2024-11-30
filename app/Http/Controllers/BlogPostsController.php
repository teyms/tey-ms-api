<?php

namespace App\Http\Controllers;

use App\Models\BlogPosts;

class BlogPostsController extends Controller
{
    public function get($slug){
        $blog_post_exist = BlogPosts::where('slug', strtolower($slug))->first();

        if($blog_post_exist && $blog_post_exist->status === BlogPosts::STATUS['PUBLISHED']){

            $result = [
                'success'   => 1,
                'msg'       => 'Successfully Added ShortURl',
                'data'      => [
                    'slug'      => $blog_post_exist->slug,
                    'title'     => $blog_post_exist->title,
                    'content'   => $blog_post_exist->content,
                    'status'    => $blog_post_exist->status
                ]
            ];
            $result_code = 200;
            
            return response()->json($result, $result_code);
        } 

        $result = [
            'success'   => 0,
            'msg'       => 'failed. Blog Post not found!',
            'data'      => null
        ];
        $result_code = 422;

        return response()->json($result, $result_code);
    }
}
