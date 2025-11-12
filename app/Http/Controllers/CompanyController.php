<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies for the current tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $companies = Company::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('cnpj', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($companies);
    }

    /**
     * Store a newly created company.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'cnpj' => 'nullable|string|max:18|unique:companies,cnpj',
        ]);

        $validated['tenant_id'] = tenant('id');

        $company = Company::create($validated);

        return response()->json($company, 201);
    }

    /**
     * Display the specified company.
     */
    public function show(Company $company): JsonResponse
    {
        return response()->json($company);
    }

    /**
     * Update the specified company.
     */
    public function update(Request $request, Company $company): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'cnpj' => [
                'nullable',
                'string',
                'max:18',
                Rule::unique('companies', 'cnpj')->ignore($company->id)
            ],
        ]);

        $company->update($validated);

        return response()->json($company);
    }

    /**
     * Remove the specified company.
     */
    public function destroy(Company $company): JsonResponse
    {
        // Verificar se a empresa tem dados associados
        $hasData = $company->users()->exists() ||
                   $company->vehicles()->exists() ||
                   $company->clients()->exists() ||
                   $company->equipment()->exists();

        if ($hasData) {
            return response()->json([
                'message' => 'Não é possível excluir a empresa pois ela possui dados associados.'
            ], 422);
        }

        $company->delete();

        return response()->json(['message' => 'Empresa excluída com sucesso.']);
    }

    /**
     * Get companies for select options.
     */
    public function options(): JsonResponse
    {
        $companies = Company::select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($companies);
    }

    /**
     * Get company statistics.
     */
    public function stats(Company $company): JsonResponse
    {
        $stats = [
            'users_count' => $company->users()->count(),
            'vehicles_count' => $company->vehicles()->count(),
            'clients_count' => $company->clients()->count(),
            'equipment_count' => $company->equipment()->count(),
            'checklists_count' => $company->checklists()->count(),
            'services_count' => $company->services()->count(),
        ];

        return response()->json($stats);
    }
}
