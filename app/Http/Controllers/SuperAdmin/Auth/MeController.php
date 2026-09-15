<?php

namespace App\Http\Controllers\SuperAdmin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\Auth\UpdateProfileRequest;
use App\Http\Resources\User\Me\MeResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class MeController extends Controller
{
    use ApiResponseTrait;
    public function me(Request $request)
    {
        return response()->json(new MeResource($request->user()));
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $data['profile_image'] = $request->file('profile_image')
                ->store('profile_images', 'public');
        }


        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return $this->successResponse(
            new MeResource($user),
            'Profile updated successfully'
        );
    }
}
