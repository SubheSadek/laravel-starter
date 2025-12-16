<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CompanyListRequest;
use App\Http\Requests\CompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CompanyController extends Controller
{
    /*
     * Get a list of companies.
     */
    public function companyList(CompanyListRequest $request): Response
    {
        $companies = Company::query()
            ->when(! empty($request->search_txt), function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search_txt}%");
            })
            ->cursorPaginate($request->get('limit', 20));

        return withSuccessResourceList(CompanyResource::collection($companies));
    }

    /*
     * Create a company.
     */
    public function createCompany(CompanyRequest $request): Response
    {
        $company = Company::create($request->validated());

        return withSuccess(new CompanyResource($company), 'Company created successfully!');
    }

    /*
     * Get a company by id.
     */
    public function companyDetails(Request $request, int $id): Response
    {
        $company = Company::find($id);

        if (empty($company)) {
            return withError('Company not found!');
        }

        return withSuccess(new CompanyResource($company));
    }

    /*
     * Update a company by id.
     */
    public function updateCompany(CompanyRequest $request, int $id): Response
    {
        $company = Company::find($id);

        if (empty($company)) {
            return withError('Company not found!');
        }

        $company->update($request->validated());

        return withSuccess(new CompanyResource($company), 'Company updated successfully!');
    }

    /*
     * Delete a company by id.
     */
    public function deleteCompany(int $id): Response
    {
        $company = Company::find($id);

        if (empty($company)) {
            return withError('Company not found!');
        }

        $company->delete();

        return withSuccess(message: 'Company deleted successfully!');
    }
}
