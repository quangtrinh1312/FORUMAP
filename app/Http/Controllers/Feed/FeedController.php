<?php

namespace App\Http\Controllers\Feed;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use Illuminate\Http\Request;
use App\Models\Feed;
use App\Models\Like;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Expr\FuncCall;
use App\Models\Comment;
use App\Http\Requests\CommentRequest;
class FeedController extends Controller
{
    public function index()
    {
        $feeds = Feed::with('user')->latest()->get();
        return response(
            [
                'feeds'=>$feeds
            ],
            200
        );
    }
    public function store(PostRequest $request)
    {
        $request->validated();
        auth()->user()->feeds()->create([
            'contents' => $request->content
        ]);
        return response([
            'message' => 'success'
        ],201);
    }
    public function likePost($feed_id)
    {
        $feed = Feed::whereId($feed_id)->first();
        if(!$feed)
        {
            return response(
                [
                    'message' => '404 not found'
                ],
                500
            );
        }
        //simple code like or unlike
        // if($feed->user_id == auth()->id()){
        //     Like::whereFeedId($feed_id)->delete();
        //     return response(
        //         [
        //             'message'=>'unliked'
        //         ]
        //     );
        // }else{
        //     Like::create(
        //         [
        //             'user_id'=>auth()->id(),
        //             'feed_id'=>$feed_id
        //         ]
        //     );
        //     return response(
        //         [
        //             'message'=>'Liked'
        //         ],
        //         200
        //     );
        // }
        //like or unlike
        
        $unLike_post = Like::where('user_id',Auth::id())->where('feed_id',$feed_id)->delete();
        if($unLike_post){
            return response(
                [
                    'message'=>'Unliked'
                ],
                200
            ); 
        }
        $like_post = Like::create(
            [
                'user_id'=>auth()->id(),
                'feed_id'=>$feed_id,
            ]
        );
        if($like_post){
            return response(
                [
                    'message'=>'Liked'
                ],
                200
            ); 
        }
    }
    public Function comment($feed_id,CommentRequest $request)
    {
        $request->validated();
        $comment = Comment::create(
            [
                'user_id'=>auth()->id(),
                'feed_id'=>$feed_id,
                'body'=> $request->body
            ]
        );
        return response([
            'message'=>'Success'
            ],
            201
        );
    }
    public function getComments($feed_id)
    {
            $comments = Comment::with('feed','user')->whereFeedId($feed_id)->latest()->get();
            return response([
                'comment'=>$comments
                ],
                200
            );
    }
}
