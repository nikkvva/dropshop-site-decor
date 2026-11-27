<?php

namespace App\Http\Controllers;

use App\Services\NovaPoshta;
use Illuminate\Http\Request;

/** Подсказки для полей «Місто» и «Відділення» на оформлении заказа (JSON для public/js/checkout.js). */
class NovaPoshtaController extends Controller
{
    public function cities(Request $request, NovaPoshta $np)
    {
        $query = $request->string('q', strict: true)->trim()->toString();

        return response()->json(mb_strlen($query) >= 2 ? $np->cities(mb_substr($query, 0, 50)) : []);
    }

    public function points(Request $request, NovaPoshta $np)
    {
        $data = $request->validate([
            'city_ref' => ['required', 'uuid'],
            'type' => ['required', 'in:branch,postomat'],
            'q' => ['nullable', 'string', 'max:50'],
        ]);

        // отделения — весь список города; почтоматы — только поиск (их тысячи)
        if ($data['type'] === 'branch') {
            return response()->json($np->branches($data['city_ref']));
        }

        $query = trim((string) ($data['q'] ?? ''));

        return response()->json($query === '' ? [] : $np->postomats($data['city_ref'], $query));
    }
}
