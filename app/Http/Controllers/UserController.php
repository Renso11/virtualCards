<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;

class UserController extends Controller
{
    public function users(Request $request)
    {
        try{
            $users = User::where('deleted',0)->get();
            $roles = Role::where('deleted',0)->get();
            return view('users.index',compact('users','roles'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function userAdd(Request $request)
    {
        try{
            User::create([
                'id' => Uuid::uuid4()->toString(),
                'name' => $request->name,
                'lastname' => $request->lastname,
                'username' => unaccent(strtolower($request->name[0].''.explode(' ',$request->lastname)[0])),
                'password' => Hash::make("12345678"),
                'role_id' => $request->role,
                'status' => 1,
                'deleted' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            return back()->withSuccess("Utilisateur crée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function userEdit(Request $request)
    {   
        try{
            $user = User::where('id',$request->id)->where('deleted',0)->first();

            $user->name = $request->name;
            $user->lastname = $request->lastname;
            $user->role_id = $request->role;
            $user->updated_at = Carbon::now();
            $user->save();
            return back()->withSuccess("Modification effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function userDelete(Request $request)
    {
        try{
            $user = User::where('id',$request->id)->where('deleted',0)->first();

            $user->deleted = 1;
            $user->updated_at = Carbon::now();
            $user->save();
            return redirect(route('users'))->withSuccess("Supression effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function userDetails(Request $request)
    {
        try{
            $roles = Role::where('deleted',0)->get();
            $user = User::where('id',$request->id)->where('deleted',0)->first();
            return view('users.detail',compact('user','roles'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function userResetPassword(Request $request)
    {
        try{
            $userClient = User::where('id',$request->id)->where('deleted',0)->first();

            $userClient->password = Hash::make(12345678);
            $userClient->updated_at = Carbon::now();
            $userClient->save();
            return back()->withSuccess("Reinitialisation effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function profile(Request $request)
    {
        try{
            $user = Auth::user();
            return view('users.profile',compact('user'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function profileInformationsEdit(Request $request)
    {
        try{
            $user = User::where('id',Auth::user()->id);
            $user->name = $request->name;
            $user->username = $request->username;
            $user->lastname = $request->lastname;
            $user->updated_at = Carbon::now();
            $user->save();
            return back()->withSuccess("Modification effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function profilePasswordChange(Request $request)
    {
        try{
            $request->validate([
                'password' => 'required|min:8'
            ]);

            $user = User::where('id',Auth::user()->id);
            $user->password = Hash::make($request->password);
            $user->updated_at = Carbon::now();
            $user->save();
            return back()->withSuccess("Mot de passe changé avec succès");
            return view('users.profile',compact('user'));
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function userActivation(Request $request)
    {
        try{
            $user = User::where('id',$request->id)->where('deleted',0)->first();

            $user->status = 1;
            $user->updated_at = Carbon::now();
            $user->save();
            return back()->withSuccess("Activation effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function userDesactivation(Request $request)
    {
        try{
            $user = User::where('id',$request->id)->where('deleted',0)->first();

            $user->status = 0;
            $user->updated_at = Carbon::now();
            $user->save();
            return back()->withSuccess("Desactivation effectuée avec succès");
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
}
