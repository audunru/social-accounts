<?php

namespace audunru\SocialAccounts\Controllers;

use audunru\SocialAccounts\Models\SocialAccount;
use audunru\SocialAccounts\Resources\SocialAccount as SocialAccountResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(SocialAccount::class);
    }

    /**
     * Display the current user's social accounts.
     */
    public function index(): AnonymousResourceCollection
    {
        return SocialAccountResource::collection(Auth::user()->socialAccounts);
    }

    /**
     * Display the specified social account.
     */
    public function show(SocialAccount $socialAccount): SocialAccountResource
    {
        return new SocialAccountResource($socialAccount);
    }

    /**
     * Remove the specified social account from storage.
     */
    public function destroy(SocialAccount $socialAccount): JsonResponse
    {
        abort_unless($socialAccount->delete(), 500);

        return response()->json([
            'message' => 'Deleted',
        ]);
    }
}
