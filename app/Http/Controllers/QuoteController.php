<?php

namespace App\Http\Controllers;

use App\Events\NewNotification;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\StoreQuoteRequest;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Movie;
use App\Models\Notification;
use App\Models\Quote;

class QuoteController extends Controller
{
	public function index()
	{
		$search = urldecode(request()->input('search'));
		$type = $search ? $search[0] : '';

		switch ($type)
		{
			case '@':
				$movies = Movie::where('movie_title', 'like', '%' . ltrim($search, '@') . '%')->get();
				$results = Quote::whereIn('movie_id', $movies->pluck('id')->toArray())->paginateQuery();
				break;
			case '#':
				$results = Quote::where('quote_title', 'like', '%' . ltrim($search, '#') . '%')->paginateQuery();
				break;
			default:
				$results = Quote::where('quote_title', 'like', "%{$search}%")->paginateQuery();
		}

		return response()->json($results);
	}

	public function getQuote(Quote $quote)
	{
		return response($quote);
	}

	public function create(StoreQuoteRequest $request)
	{
		$quote = new Quote();
		$body = ['en'=>$request['quote_title_en'], 'ka'=>$request['quote_title_ka']];
		$quote->setTranslations('quote_title', $body);

		$quote['user_id'] = auth()->id();
		$quote['movie_id'] = $request['movie_id'];

		if (!$request->hasFile('quote_image'))
		{
			return response()->json(['error' => 'image error']);
		}

		$quote['quote_image'] = '/storage/' . $request->file('quote_image')->store('quotes');
		$quote->save();

		return response(['message'=>'Quote added']);
	}

	public function update(StoreQuoteRequest $request, Quote $quote)
	{
		$body = ['en'=>$request['quote_title_en'], 'ka'=>$request['quote_title_ka']];
		$quote->setTranslations('quote_title', $body);
		if (isset($request['quote_image']))
		{
			$quote['quote_image'] = '/storage/' . $request->file('quote_image')->store('quotes');
		}
		$quote->update();
		return response(['message'=>'updated']);
	}

	public function delete()
	{
		if (!$quoteId = urldecode(request()->input('id')))
		{
			return;
		}
		if (!Quote::where(['id'=>$quoteId])->delete())
		{
			return response(['message'=>'Error deleting quote']);
		}
		return response(['message'=>'Quote deleted']);
	}

	public function addComment(StoreCommentRequest $request)
	{
		if (!$comment = Comment::create($request->only('user_id', 'quote_id', 'comment')))
		{
			return response(['error' => "Couldn't create a comment"]);
		}

		$this->dispatchNotification($request, $comment->id, null);

		return response(['message' => 'Comment created']);
	}

	public function toggleLike()
	{
		$like = Like::firstOrNew($request = request()->only('user_id', 'quote_id', 'like'));

		if ($like->exists)
		{
			$like->delete();
			return response(['message' => 'like removed']);
		}
		$like->save();

		$this->dispatchNotification($request, null, $like->id);

		return response(['message' => 'like added']);
	}

	public function dispatchNotification($request, $commentId, $likeId)
	{
		$user = Quote::find($request['quote_id'])->user;
		if ($user->id === auth()->user()->id)
		{
			return;
		}

		$notification = Notification::firstOrNew([
			'destination_user_id' => $user->id,
			'user_id'             => $request['user_id'],
			'quote_id'            => $request['quote_id'],
			'like_id'             => $likeId,
			'comment_id'          => $commentId,
		]);
		$notification->save();

		NewNotification::dispatch($notification);
	}
}
