<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $request->user()->domains()->latest()->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'domain'          => ['required', 'string', 'max:253', 'unique:domains,domain'],
            'check_interval'  => ['nullable', 'integer', 'min:1'],
            'request_timeout' => ['nullable', 'integer', 'min:1'],
            'check_method'    => ['nullable', 'string', 'in:GET,HEAD'],
        ]);

        $domain = $request->user()->domains()->create($data);

        return response()->json($domain, 201);
    }

    public function show(Request $request, Domain $domain): JsonResponse
    {
        $this->authorize($request, $domain);

        return response()->json($domain);
    }

    public function update(Request $request, Domain $domain): JsonResponse
    {
        $this->authorize($request, $domain);

        $data = $request->validate([
            'domain'          => ['required', 'string', 'max:253', 'unique:domains,domain,' . $domain->id],
            'check_interval'  => ['nullable', 'integer', 'min:1'],
            'request_timeout' => ['nullable', 'integer', 'min:1'],
            'check_method'    => ['nullable', 'string', 'in:GET,HEAD'],
        ]);

        $domain->update($data);

        return response()->json($domain);
    }

    public function destroy(Request $request, Domain $domain): JsonResponse
    {
        $this->authorize($request, $domain);

        $domain->delete();

        return response()->json(null, 204);
    }

    private function authorize(Request $request, Domain $domain): void
    {
        abort_if($domain->user_id !== $request->user()->id, 403, 'Forbidden');
    }
}
