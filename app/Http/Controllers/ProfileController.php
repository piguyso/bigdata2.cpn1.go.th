<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // Handle logo upload from Cropper (base64)
        if ($request->has('logo_data') && !empty($request->input('logo_data'))) {
            $data = $request->input('logo_data');
            
            if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                $data = substr($data, strpos($data, ',') + 1);
                $type = strtolower($type[1]); // png, jpeg, webp, etc.

                if (in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $data = base64_decode($data);
                    
                    if ($data !== false) {
                        $fileName = 'logo_' . time() . '_' . uniqid() . '.' . $type;
                        $filePath = 'logos/' . $fileName;
                        
                        // Save file
                        \Illuminate\Support\Facades\Storage::disk('public')->put($filePath, $data);
                        
                        // Delete old logo
                        if ($request->user()->logo) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($request->user()->logo);
                        }
                        
                        $request->user()->logo = $filePath;
                    }
                }
            }
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
