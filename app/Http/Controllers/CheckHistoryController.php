<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckHistoryController extends Controller
{
    public function index(Request $request, Domain $domain): JsonResponse
    {
        abort_if($domain->user_id !== $request->user()->id, 403, 'Forbidden');

        $history = $domain->checkHistories()
            ->latest()
            ->paginate(50);

        return response()->json($history);
    }
}
