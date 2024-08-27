<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Contract\ApiController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CreateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends ApiController
{
   
    public function register(CreateUserRequest $request)
    {
      $validated = $request->validated();

    //   try {
          $userId = User::max('id') + 1; 

          if ($request->hasFile('image')) {
              $imageName = $this->storePhoto($request->file('image'), $userId);
              $validated['image'] = $imageName;
          }
  
  
          $validated['password'] = Hash::make($validated['password']);
          $user = User::create([
              'name' => $validated['name'],
              'email' => $validated['email'],
              'password' => $validated['password'],
              'image' => $validated['image'] ?? null, 
          ]);

          $token = $user->createToken('Personal Access Token')->plainTextToken;
          
          return $this->respondCreated('کاربر با موفقیت ایجاد شد', [
              'user' => $user,
              'token' => $token,
          ]);
          
    //   } catch (\Exception $e) {
    //       return $this->respondInternalError('(ایمیل باید یونیک باشد):خطایی در ایجاد کاربر رخ داده است');
    //   }
    }



  public function login(Request $request)
{
  $validator = Validator::make($request->all(), [
      'email' => 'required|string|email',
      'password' => 'required|string',
  ]);

  if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
  }

  $user = User::where('email', $request->email)->first();

  if (!$user || !Hash::check($request->password, $user->password)) {
      return response()->json(['message' => 'اطلاعات ورود اشتباه است'], 401);
  }

  $token = $user->createToken('Personal Access Token')->plainTextToken;
  
  return response()->json([
      'message' => 'ورود موفقیت‌آمیز',
      'user' => $user,
      'token' => $token,
  ]);
}

public function logout()
{
  try {
      $user = auth()->user();

      if (!$user) {
          return $this->respondNotFound('کاربر یافت نشد');
      }

      $user->currentAccessToken()->delete();

      return $this->respondSuccess('کاربر با موفقیت لاگ‌اوت شد', null);
  } catch (\Exception $e) {
      return $this->respondInternalError('خطایی در خروج از حساب کاربری رخ داده است');
  }
}


public function deleteUser()
{
  try {
      $user = auth()->user();

      if (!$user) {
          return $this->respondNotFound('کاربر یافت نشد');
      }

      $this->deleteUserPhotos($user);
      $user->tokens()->delete();
      $user->delete();

      return $this->respondSuccess('کاربر و تمام اطلاعات مربوط به او با موفقیت حذف شد', null);
  } catch (\Exception $e) {
      return $this->respondInternalError('خطایی در حذف کاربر رخ داده است');
  }
}


  // image User Profile
  protected function storePhoto($file, $userId)
  {
      $imageName = time() . rand(100, 10000) . '.' . $file->getClientOriginalExtension();
      $userFolderPath = public_path('images/UserProfile/' . $userId);
  
      if (!File::exists($userFolderPath)) {
          File::makeDirectory($userFolderPath, 0755, true);
      }
  
      $file->move($userFolderPath, $imageName);
      return $imageName;
  }


  protected function deleteUserPhotos(User $user)
  {
      $userFolderPath = public_path('images/UserProfile/' . $user->id);
  
      if (File::exists($userFolderPath)) {
          File::deleteDirectory($userFolderPath);
      }
  }
}
