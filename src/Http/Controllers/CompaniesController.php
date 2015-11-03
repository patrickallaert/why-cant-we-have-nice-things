<?php
namespace History\Http\Controllers;

use History\Entities\Models\Company;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param array                  $parameters
     *
     * @return \Zend\Diactoros\Response\HtmlResponse
     */
    public function show(ServerRequestInterface $request, ResponseInterface $response, $parameters)
    {
        $company = Company::with('users')->where('slug', $parameters['company'])->firstOrFail();

        return $this->render('companies/show.twig', [
           'company' => $company,
        ]);
    }
}
