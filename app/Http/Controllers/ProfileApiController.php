<?php

namespace App\Http\Controllers;

use App\Models\Employ;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileApiController extends Controller
{
    public function index($id)
    {
//        $user = User::find($id);
        return view('profile', compact('id'));
    }

    public function checkUser()
    {
        $user = Auth::user();
        if ($user) {
            return response()->json(['status' => true, 'user' => $user]);
        }
        return response()->json(['status' => false]);
    }

    public function updateFCM(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $userModel = User::find($user->id)->first();
            $userModel->fcm_registration_id = $request->fcm_registration_id;
            $userModel->save();
            return response()->json(['status' => true, 'user' => $userModel]);
        }
        return response()->json(['status' => false]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadCvFile(Request $request)
    {
        
        $userId = $request->userId;
        if ($userId) {
            $cvFile = $this->saveFile($request);
            if ($cvFile) {
                $userModel = Employ::whereUserId($userId)->first();
                $userModel->cv = $cvFile;
                $userModel->save();
                User::find($userId)->update(['status' => env('STATUS_CV')]);

                return response()->json(['error' => false, 'message' => 'file add successful']);

            } else {
                return response()->json(['error' => true, 'message' => 'no file uploaded', 'eq' => $cvFile]);
            }
        }
        return response()->json(['error' => true, 'message' => 'user not found']);

    }

    public function uploadImage(Request $request)
    {
        $userId = $request->userId;
        if ($userId) {
            $picFile = $this->saveImage($request);
            if ($picFile) {
                $userModel = User::find($userId)->first();
                $userModel->image = $picFile;
                $userModel->phone = $request->phone;
                $userModel->email = $request->email;   
                $userModel->save();
                return response()->json(['error' => false, 'message' => 'file add successful', 'user' => $userModel]);
            }
        } else {
            return response()->json(['error' => true, 'message' => 'no file uploaded', $picFile]);
        }
        return response()->json(['error' => true, 'message' => 'user not found']);
    }


    public function saveFile($request)
    {
        $random = Str::random(5);
        if ($request->hasfile('cv')) {
            $image = $request->file('cv');
            $name = $random . 'cv_' . Carbon::now()->format('y-m-d') . ".pdf";
            $image->move(base_path() . '/public/cv/', $name);
            return $name = url("cv/$name");
        }
        return $request;
    }

    public function saveImage($request)
    {
        $random = Str::random(5);
        if ($request->hasfile('image')) {
            $image = $request->file('image');
            $name = $random . Carbon::now()->format('y-m-d') . ".jpg";
            $image->move(base_path() . '/public/profiles/', $name);
            $name = url("profiles/$name");
            return $name;
        }
        return false;
    }
}
