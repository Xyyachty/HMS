<?php

namespace App\Http\Controllers;

use App\Models\HotelPromo;
use App\Models\StudentGroup;
use App\Support\HotelComplaintAccess;
use App\Support\HotelImageStore;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Front Desk's Promos and Packages screen in the Simulation, and the feed the
 * team's own site reads. Every query is scoped to the caller's team, so a
 * team can only ever see or change its own promos.
 */
class HotelPromoController extends Controller
{
    public function index()
    {
        $membership = HotelComplaintAccess::membership();
        if (!$membership) {
            return response()->json(['items' => [], 'has_promos' => false, 'can_manage' => false]);
        }

        $items = HotelPromo::forTeam($membership->group_name, $membership->faculty_id)
            ->get()
            ->map->toTemplateArray();

        return response()->json([
            'items'      => $items,
            'has_promos' => $items->isNotEmpty(),
            'can_manage' => HotelPromo::canManage($membership),
        ]);
    }

    public function store(Request $request)
    {
        $membership = $this->manager();
        $data = $request->validate($this->rules(true), $this->messages());

        $promo = new HotelPromo([
            'group_name' => $membership->group_name,
            'faculty_id' => $membership->faculty_id,
            'group_id'   => $membership->group_id,
        ]);
        $this->fill($promo, $data);

        $promo->image = HotelImageStore::persist($data['image'], $membership->faculty_id, $membership->group_name);
        if ($promo->image === null) {
            return response()->json(['message' => 'The promo image could not be saved. Please try again.'], 422);
        }
        $promo->save();

        return response()->json(['item' => $promo->toTemplateArray()], 201);
    }

    public function update(Request $request, $id)
    {
        $membership = $this->manager();
        $promo = $this->find($membership, $id);
        $data = $request->validate($this->rules(false), $this->messages());

        $this->fill($promo, $data);

        if (array_key_exists('image', $data)) {
            $image = HotelImageStore::persist($data['image'], $membership->faculty_id, $membership->group_name);
            // A failed upload keeps the current picture rather than blanking it.
            if ($image === null) {
                return response()->json(['message' => 'That image could not be saved. The promo keeps its current one.'], 422);
            }
            $promo->image = $image;
        }
        $promo->save();

        return response()->json(['item' => $promo->toTemplateArray()]);
    }

    public function destroy($id)
    {
        $this->find($this->manager(), $id)->delete();

        return response()->json(['deleted' => true]);
    }

    private function manager(): StudentGroup
    {
        $membership = HotelComplaintAccess::membership();
        abort_if(!$membership, 404, 'Join a hotel team first.');
        abort_unless(HotelPromo::canManage($membership), 403, 'Only Front Desk staff can manage promos.');

        return $membership;
    }

    private function find(StudentGroup $membership, $id): HotelPromo
    {
        return HotelPromo::forTeam($membership->group_name, $membership->faculty_id)
            ->where('hotel_promo_id', $id)
            ->firstOrFail();
    }

    /** On an edit every field is optional, but one that is sent may not be blank. */
    private function rules(bool $creating): array
    {
        $req = $creating ? 'required' : 'sometimes|required';

        return [
            'name'           => "$req|string|max:120",
            'description'    => "$req|string|max:600",
            'image'          => "$req|string|max:900000",
            'original_price' => 'sometimes|nullable|integer|min:1|max:9999999',
            'promo_price'    => "$req|integer|min:0|max:9999999",
            'start_date'     => "$req|date",
            'end_date'       => "$req|date",
            'status'         => "$req|in:active,inactive",
        ];
    }

    private function messages(): array
    {
        return [
            'image.required' => 'Choose a promo image.',
            'image.max'      => 'That image is too large. Please choose a smaller one.',
        ];
    }

    /**
     * Prices and dates are checked against each other after merging with the
     * stored row, so an edit that sends only one side is still held to it.
     */
    private function fill(HotelPromo $promo, array $data): void
    {
        foreach (['name', 'description'] as $field) {
            if (array_key_exists($field, $data)) $promo->$field = trim($data[$field]);
        }
        foreach (['original_price', 'promo_price', 'start_date', 'end_date'] as $field) {
            if (array_key_exists($field, $data)) $promo->$field = $data[$field];
        }
        if (array_key_exists('status', $data)) $promo->is_active = $data['status'] === 'active';

        $errors = [];
        if ($promo->original_price !== null && $promo->original_price <= $promo->promo_price) {
            $errors['original_price'] = 'The original price must be higher than the promo price.';
        }
        if ($promo->end_date->lt($promo->start_date)) {
            $errors['end_date'] = 'The end date cannot be before the start date.';
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }
}
