<?php

namespace App\Http\Controllers\Admin\Me;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\Auth\UpdateProfileRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $user = auth('api')->user();
        return $this->successResponse($user, 'User profile retrieved successfully');
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = auth('api')->user();
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
            $user->fresh(),
            'Profile updated successfully'
        );
    }
}
