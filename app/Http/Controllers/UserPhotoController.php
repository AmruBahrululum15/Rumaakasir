<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserPhotoController extends Controller
{
    public function __invoke(Request $request, int $id)
    {
        $actor = $request->user();
        $target = User::withoutGlobalScope('business')->findOrFail($id);
        $businessId = $actor->hasRole('Super Admin')
            ? $request->session()->get('super_admin_business_id')
            : $actor->business_id;

        abort_unless($actor->is($target) || (
            (int) $target->business_id === (int) $businessId
            && $actor->can('users.view')
            && (! $target->hasRole('Super Admin') || $actor->hasRole('Super Admin'))
            && $target->business?->allowsOperations()
        ), 404);

        $path = $target->photoPath();
        abort_unless($path && ! str_contains($path, '..')
            && preg_match('#^(users|profiles|profile)/[a-zA-Z0-9_./-]+$#', $path), 404);
        $disk = Storage::disk($target->photoDisk());
        abort_unless($disk->exists($path), 404);

        return $disk->response($path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
