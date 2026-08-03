<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()
            ->addresses()
            ->with('commune')
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return AddressResource::collection($addresses);
    }

    public function store(StoreAddressRequest $request)
    {
        $user = $request->user();
        $isFirst = $user->addresses()->count() === 0;
        $isDefault = $request->boolean('is_default') || $isFirst;

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create([
            'name' => $request->validated('name'),
            'address' => $request->validated('address'),
            'commune_id' => $request->validated('commune_id'),
            'is_default' => $isDefault,
        ]);

        return new AddressResource($address->load('commune'));
    }

    public function show(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403, 'No tienes permiso para acceder a esta dirección.');
        }

        return new AddressResource($address->load('commune'));
    }

    public function update(UpdateAddressRequest $request, Address $address)
    {
        if ($request->boolean('is_default')) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($request->validated());

        return new AddressResource($address->load('commune'));
    }

    public function destroy(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403, 'No tienes permiso para eliminar esta dirección.');
        }

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $next = $request->user()->addresses()->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        return response()->noContent();
    }

    public function setDefault(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403, 'No tienes permiso para modificar esta dirección.');
        }

        $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return new AddressResource($address->load('commune'));
    }
}
