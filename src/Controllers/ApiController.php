<?php

namespace audunru\SocialAccounts\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use audunru\SocialAccounts\Models\SocialAccount;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use audunru\SocialAccounts\Resources\SocialAccount as SocialAccountResource;

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
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return SocialAccountResource::collection(Auth::user()->socialAccounts);
    }

    /**
     * Display the specified social account.
     *
     * @param SocialAccount $socialAccount
     *
     * @return SocialAccountResource
     */
    public function show(SocialAccount $socialAccount): SocialAccountResource
    {
        return new SocialAccountResource($socialAccount);
    }

    /**
     * Remove the specified social account from storage.
     *
     * @param SocialAccount $socialAccount
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(SocialAccount $socialAccount): JsonResponse
    {
        abort_unless($socialAccount->delete(), 500);

        return response()->json([
            'message' => 'Deleted',
        ]);
    }
}
