<?php

namespace App\Http\Controllers;

use App\Support\HotelSimulationAuth;
use Illuminate\Http\Request;

class HotelSimulationAuthController extends Controller
{
    /**
     * The hotel this request is about.
     *
     * A slug in the route means the published site, where there is no student to
     * read a team from; without one it is the builder, and the viewer's own team
     * is the hotel. Null on the published side means the slug is not a team, which
     * the callers answer as a 404 rather than explaining.
     */
    private function context(Request $request, ?string $slug): ?array
    {
        return $slug === null ? null : HotelSimulationAuth::teamContextForSlug($slug);
    }

    public function me(Request $request, ?string $slug = null)
    {
        $context = $this->context($request, $slug);
        if ($slug !== null && !$context) {
            abort(404);
        }

        // A guest who ticked "remember me" is signed back in here, before the
        // answer is composed — this is the first call the website makes.
        HotelSimulationAuth::restore($request->user(), $context);

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

    public function customerSignup(Request $request, ?string $slug = null)
    {
        $context = $this->context($request, $slug);
        if ($slug !== null && !$context) {
            abort(404);
        }

        /* The sign-up asks for a full name, because that is what a guest thinks
           they have; the account stores it in two parts because the front desk's
           own register does. Split on the last space, which is right for most
           names and wrong in a way nobody is harmed by: the guest is greeted by
           the whole thing either way. */
        if (filled($request->input('full_name')) && blank($request->input('first_name'))) {
            $full = trim(preg_replace('/\s+/', ' ', (string) $request->input('full_name')));
            $cut = mb_strrpos($full, ' ');
            $request->merge([
                'first_name' => $cut === false ? $full : mb_substr($full, 0, $cut),
                'last_name' => $cut === false ? $full : mb_substr($full, $cut + 1),
            ]);
        }

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
            /* A photograph of the guest's ID, shrunk in the browser before it is
               sent — the same ceiling every other picture on the site is held to,
               which is generous for a passport page and small enough to post. */
            'id_document' => ['required', 'string', 'max:900000'],
        ], [
            'password.confirmed' => 'The passwords do not match.',
            'id_document.required' => 'Upload a photo of your valid ID.',
            'id_document.max' => 'That image is too large. Please choose a smaller one.',
        ]);

        $result = HotelSimulationAuth::signupCustomer($request->user(), $data, $context);

        if (!($result['ok'] ?? false)) {
            return response()->json(['error' => $result['error']], $result['status'] ?? 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Guest account created — you can browse and book stays.',
            'auth' => $result['auth'],
        ]);
    }

    public function customerLogin(Request $request, ?string $slug = null)
    {
        $context = $this->context($request, $slug);
        if ($slug !== null && !$context) {
            abort(404);
        }

        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $result = HotelSimulationAuth::loginCustomer(
            $request->user(),
            $data['email'],
            $data['password'],
            $request->boolean('remember'),
            $context
        );
        if (!($result['ok'] ?? false)) {
            return response()->json(['error' => $result['error']], $result['status'] ?? 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Guest login successful — welcome to the hotel.',
            'auth' => $result['auth'],
        ]);
    }

    public function logout(Request $request, ?string $slug = null)
    {
        HotelSimulationAuth::clear();

        return response()->json([
            'success' => true,
            'message' => 'Logged out of hotel website.',
            'auth' => HotelSimulationAuth::payload(),
        ]);
    }
}
