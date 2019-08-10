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
    public function __construct()
    {
        $this->authorizeResource(SocialAccount::class);
    }

    /**
     * Display the current user's social accounts.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        abort_unless(Auth::check(), 401);

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
        abort_unless(Auth::check(), 401);

        return new SocialAccountResource($socialAccount);
    }

    /**
     * Remove the specified social account from storage.
     *
     * @param SocialAccount $socialAccount
     *
     * @return JsonResponse
     */
    public function destroy(SocialAccount $socialAccount): JsonResponse
    {
        abort_unless(Auth::check(), 401);
        abort_unless($socialAccount->delete(), 500);

        return response()->json([
            'message' => 'Deleted',
        ]);
    }
}
