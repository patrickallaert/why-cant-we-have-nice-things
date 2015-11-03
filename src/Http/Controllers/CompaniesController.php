<?php
namespace History\Http\Controllers;

use History\Entities\Models\Company;

class CompaniesController extends AbstractController
{
    /**
     * @return \Zend\Diactoros\Response\HtmlResponse
     */
    public function index()
    {
        $companies = Company::with('users')->get();

        return $this->render('companies/index.twig', [
            'companies' => $companies,
        ]);
    }
}
