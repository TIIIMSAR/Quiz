<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Contract\ApiController;
use App\Http\Requests\Category\CreateCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryshowResource;
use App\Models\Category;
use App\Models\Owner;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CategoryController extends ApiController
{
     public function index()
     {
        //  try {
             $categories = Category::where('owner_id', Auth::id())->get();
             return $this->respondSuccess('دسته‌بندی‌ها با موفقیت دریافت شدند.', CategoryshowResource::collection($categories));
        //  } catch (\Exception $e) {
        //      return $this->respondInternalError('خطایی در دریافت دسته‌بندی‌ها رخ داده است.');
        //  }
     }
 

     public function store(CreateCategoryRequest $request)
     {
        $ownerExists = Owner::where('user_id', Auth::id())->exists();

        if (!$ownerExists) {
            Owner::create([
                'user_id' => Auth::id(),
            ]);
        }

         try {
             $request->validated();
 
             $category = new Category();
             $category->name = $request->name;
             $category->owner_id = Auth::id(); 
             $category->save();
 
             return $this->respondCreated('دسته‌بندی با موفقیت ایجاد شد.', $category);
         } catch (ValidationException $e) {
             return $this->respondInternalError('اطلاعات وارد شده معتبر نمی‌باشند.');
         } catch (\Exception $e) {
             return $this->respondInternalError('خطایی در ایجاد دسته‌بندی رخ داده است.');
         }
     }
 

     public function update(UpdateCategoryRequest $request, $id)
     {
         try {
             $category = Category::findOrFail($id);
 
             if ($category->owner_id !== Auth::id()) {
                 return $this->respondInternalError('شما دسترسی به به‌روزرسانی این دسته‌بندی را ندارید.');
             }
 
            $request->validated();

             $category->name = $request->name;
             $category->save();
 
             return $this->respondSuccess('دسته‌بندی با موفقیت به‌روزرسانی شد.', $category);
         } catch (ValidationException $e) {
             return $this->respondInternalError('اطلاعات وارد شده معتبر نمی‌باشند.');
         } catch (ModelNotFoundException $e) {
             return $this->respondNotFound('دسته‌بندی یافت نشد.');
         } catch (\Exception $e) {
             return $this->respondInternalError('خطایی در به‌روزرسانی دسته‌بندی رخ داده است.');
         }
     }
 
     public function destroy($id)
     {
         try {
             $category = Category::findOrFail($id);
 
             if ($category->owner_id !== Auth::id()) {
                 return $this->respondInternalError('شما دسترسی به حذف این دسته‌بندی را ندارید.');
             }
 
             $category->delete();
 
             return $this->respondSuccess('دسته‌بندی با موفقیت حذف شد.', null);
         } catch (ModelNotFoundException $e) {
             return $this->respondNotFound('دسته‌بندی یافت نشد.');
         } catch (\Exception $e) {
             return $this->respondInternalError('خطایی در حذف دسته‌بندی رخ داده است.');
         }
     }
}
