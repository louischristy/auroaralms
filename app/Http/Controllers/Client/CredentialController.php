<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\ExternalCredential;
use App\Models\User;
use Illuminate\Http\Request;

class CredentialController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles'])
            ->withCount([
                'courseEnrollments as certificate_count' => function ($q) {
                    // Count enrollments that have certificates
                },
            ])
            ->active();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(25)->withQueryString();

        // Get credential counts per user
        $userIds = $users->pluck('id');

        $internalCounts = Certificate::whereIn('user_id', $userIds)
            ->selectRaw('user_id, count(*) as count')
            ->groupBy('user_id')
            ->pluck('count', 'user_id');

        $externalCounts = ExternalCredential::whereIn('user_id', $userIds)
            ->selectRaw('user_id, count(*) as count')
            ->groupBy('user_id')
            ->pluck('count', 'user_id');

        return view('client.credentials.index', compact('users', 'internalCounts', 'externalCounts'));
    }

    public function show(User $user)
    {
        $certificates = Certificate::where('user_id', $user->id)
            ->with('course')
            ->orderByDesc('issued_at')
            ->get();

        $externalCredentials = ExternalCredential::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return view('client.credentials.show', compact('user', 'certificates', 'externalCredentials'));
    }
}
