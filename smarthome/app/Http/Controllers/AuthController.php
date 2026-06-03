<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //
    public function me()
    {
        //Récupérer les informations de l'utilisateur connecté
        $user = Auth::user();
        return response()->json([
            'success' => true,
            'data' => $user
        ], 200);
    }

    public function login(Request $request)
    {
        //Authentifier un utilisateur
        //se souvenir de moi
        $request->merge(['remember_me' => $request->has('remember_me')]);// $request->remember_me = $request->has('remember_me');
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'mdp' => 'required|string|min:8',
        ]);
        if ($validator->fails()) {
            return redirect()
                    ->route('login')
                    ->with([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user || !password_verify($request->mdp, $user->mdp)) {
            return redirect()
                    ->route('login')        
                    ->with([
                'success' => false,
                'message' => 'Identifiants invalides'
            ], 401);
        }
        
        Auth::login($user);
        //$token = $user->createToken('auth_token')->plainTextToken;
        return redirect()
            ->route('dashboard')
            ->with([
                'success' => true,
                'message' => 'Utilisateur connecté avec succès',
                'data' => $user,
                // 'token' => $token
            ]);
    }

    public function logout(Request $request)
    {
        //Déconnecter un utilisateur
        // $user = Auth::user();
        // $user = $request->user();
        // if (!$user) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Utilisateur non authentifié',
        //         'data' => $user
        //     ], 401);
        // }
        // $request->user()->currentAccessToken()->delete();
        // return response()->json([
        //     'success' => true,
        //     'message' => 'Utilisateur déconnecté avec succès',
        //     'data' => $user
        // ], 200);
        if(!Auth::check())
        {
            return redirect()
                ->route('login.index')
                ->with([
                    'success' => false,
                    'message' => 'user not connected'
                ]);
        };

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.index');
    }

    public function register(Request $request)
    {
        //Enreguistrer un nouvel utilisateur
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'mdp' => 'required|string|min:8',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'mdp' => $request->mdp,
        ]);
        //Création du token d'authentification pour l'utilisateur
        $token = $user->createToken('auth_token')->plainTextToken;
        return redirect()->route('dashboard');
        // response()->json([
        //     'success' => true,
        //     'message' => 'Utilisateur créé avec succès',
        //     'data' => $user,
        //     'token' => $token
        // ], 201);
    }
}
