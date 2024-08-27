<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Contract\ApiController;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $paginate = $request->input('paginate') ?? 10;
            $sortColumn = $request->input('sort', 'id');
            $sortDirection = Str::startsWith($sortColumn, '-') ? 'desc' : 'asc';
            $sortColumn = ltrim($sortColumn, '-');
    
            $users = User::orderBy($sortColumn, $sortDirection)->simplePaginate($paginate);
            return $this->respondSuccess('لیست کاربران با موفقیت دریافت شد', $users);
        } catch (\Exception $e) {
            return $this->respondInternalError('خطایی در دریافت لیست کاربران رخ داده است');
        }
    }

    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return $this->respondSuccess('کاربر با موفقیت پیدا شد', $user);
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound('کاربر مورد نظر یافت نشد');
        } catch (\Exception $e) {
            return $this->respondInternalError('خطایی در نمایش اطلاعات کاربر رخ داده است');
        }
    }

    public function update(UpdateUserRequest $request, $id)
    {        
        try {
            $validated = $request->validated();
            
            $user = User::findOrFail($id);
            $user->update($validated);
    
            return $this->respondSuccess('کاربر با موفقیت به‌روزرسانی شد', $user);
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound('کاربر مورد نظر برای به‌روزرسانی یافت نشد');
        } catch (\Exception $e) {
            return $this->respondInternalError('خطایی در به‌روزرسانی کاربر رخ داده است');
        }
    }
}
