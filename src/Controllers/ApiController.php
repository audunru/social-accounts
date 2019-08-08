<?php

namespace audunru\SocialAccounts\Controllers;

use audunru\SocialAccounts\Models\SocialAccount;
use audunru\SocialAccounts\Resources\SocialAccount as SocialAccountResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

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
     * @return JsonResponse
     */
    public function destroy(SocialAccount $socialAccount): JsonResponse
    {
        if ($socialAccount->delete()) {
            return response()->json([
                'message' => 'Deleted',
            ]);
        }
    }
}
