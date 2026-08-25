<?php

namespace App\Http\Controllers;

use App\Support\HotelSimulationAuth;
use Illuminate\Http\Request;

class HotelSimulationAuthController extends Controller
{
    public function me(Request $request)
    {
        return response()->json(HotelSimulationAuth::payload());
    }

    public function staffLogin(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $result = HotelSimulationAuth::loginStaff($request->user(), $data['email'], $data['password']);
        if (!($result['ok'] ?? false)) {
            return response()->json(['error' => $result['error']], $result['status'] ?? 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Staff login successful — you can redesign your assigned pages.',
            'auth' => $result['auth'],
        ]);
    }

    public function customerSignup(Request $request)
    {
        $data = $request->validate([
            'last_name' => ['required', 'string', 'max:60'],
            'first_name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', 'max:255'],
            // Loose on format on purpose: the simulation is run with made-up
            // numbers, and a strict pattern would reject them for no gain.
            'contact_number' => ['required', 'string', 'max:32'],
            // 'confirmed' pairs this with password_confirmation, so the two
            // fields are checked here rather than trusted from the browser.
            'password' => ['required', 'string', 'min:4', 'max:100', 'confirmed'],
        ], [
            'password.confirmed' => 'The passwords do not match.',
        ]);

        $result = HotelSimulationAuth::signupCustomer($request->user(), $data);

        if (!($result['ok'] ?? false)) {
            return response()->json(['error' => $result['error']], $result['status'] ?? 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Guest account created — you can browse and book stays.',
            'auth' => $result['auth'],
        ]);
    }

    public function customerLogin(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $result = HotelSimulationAuth::loginCustomer($request->user(), $data['email'], $data['password']);
        if (!($result['ok'] ?? false)) {
            return response()->json(['error' => $result['error']], $result['status'] ?? 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Guest login successful — welcome to the hotel.',
            'auth' => $result['auth'],
        ]);
    }

    public function logout(Request $request)
    {
        HotelSimulationAuth::clear();

        return response()->json([
            'success' => true,
            'message' => 'Logged out of hotel website.',
            'auth' => HotelSimulationAuth::payload(),
        ]);
    }
}
