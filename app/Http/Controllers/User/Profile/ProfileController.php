<?php

namespace App\Http\Controllers\User\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Profile\ChangePasswordRequest;
use App\Http\Requests\User\Profile\UpdateProfileRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    use ApiResponseTrait;

    public function show()
    {
        return $this->successResponse(auth('api')->user());
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = auth('api')->user();
        $data = $request->validated();

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete(
                    str_replace('/storage/', '', $user->profile_image)
                );
            }

            $file     = $request->file('profile_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path     = $file->storeAs('uploads/profiles', $filename, 'public');

            $data['profile_image'] = '/storage/' . $path;
        }

        $user->update($data);

        return $this->successResponse($user->fresh(), 'Profile updated successfully');
    }
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = auth('api')->user();

        if (!Hash::check($request->validated('current_password'), $user->password)) {
            return $this->errorResponse('Current password is incorrect', 422);
        }

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return $this->successResponse(null, 'Password changed successfully');
    }
}
