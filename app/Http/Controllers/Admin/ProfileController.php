<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Profile\UpdateProfileRequest;
use App\Http\Requests\Admin\Profile\UpdatePasswordRequest;
use App\Models\User;
use Doctrine\DBAL\Query\QueryException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    private $user;
    public function __construct()
    {
        $this->user = new User();
    }

    public function edit()
    {
        $user = auth()->user();
        return view("admin.profile.edit", get_defined_vars());
    }

    public function update(UpdateProfileRequest $request)
    {
        try
        {
            DB::beginTransaction();
            $inputs = $request->all();
            $user = $this->user->newQuery()->whereId(auth()->user()->id)->first();
            $user->fill($inputs);
            if($request->hasFile('image'))
            {
                $this->deleteFile(auth()->user()->image);
                $this->uploadFile(request('image'), $user, 'image', false, 'user-'.auth()->user()->id."-profile-image");
            }
            if ($user->save()) {
                DB::commit();
                return redirect()->route('admin.profile.edit')->with('success', 'Saved Successfully');
            }
            DB::rollback();
            return redirect()->back()->with('error', 'Error while updating profile');
        } catch (QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $user = $this->user->newQuery()->whereId(auth()->user()->id)->first();
            if (!Hash::check($inputs['old_password'], $user->password))
            {
                DB::rollback();
                return redirect()->back()->with('error', 'Current Password is incorrect');
            }
            $user->password = Hash::make($inputs['password']);
            if (!$user->save()) {
                DB::rollback();
                return redirect()->back()->with('error', 'Error while changing password');
            }
            DB::commit();
            return redirect()->route('admin.profile.edit')->with('success', 'Password Changed Successfully');
        } catch (QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
        return view("admin.profile.edit", get_defined_vars());
    }

}
