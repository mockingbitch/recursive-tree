<?php

function getDataAPI($apiUrl)
{
    $jsonData = file_get_contents($apiUrl);

    return json_decode($jsonData, true);
}

class Travel
{
    public $data;

    public function __construct()
    {
        $response = getDataAPI('https://5f27781bf5d27e001612e057.mockapi.io/webprovise/travels');
        $this->data = $response;
    }
}

class Company
{
    public $data;

    public function __construct()
    {
        $response = getDataAPI('https://5f27781bf5d27e001612e057.mockapi.io/webprovise/companies');
        $this->data = $response;
    }
}

class TestScript
{
    private $travels;
    private $companies;

    public function __construct()
    {
        $this->travels = (new Travel)->data;
        $this->companies = (new Company)->data;
    }

    public function execute()
    {
        $start = microtime(true);
        $data = $this->buildTree($this->companies, $this->travels);
        echo json_encode($data);
        echo 'Total time: '.  (microtime(true) - $start);
    }

    public function buildTree(array $companies, array $travels, $parentId = 0)
    {
        $branch = array();
        foreach ($companies as $company) :
            if (
                $key = array_search(
                    $company['id'],
                    array_column($travels, 'companyId')
                    )
                ) :
                $company['travel'] = $travels[$key];
            endif;
            if ($company['parentId'] == $parentId) :
                $children = $this->buildTree(
                    $companies,
                    $travels,
                    $company['id']
                );
                if ($children) :
                    $company['children'] = $children;
                endif;
                $branch[] = $company;
            endif;
        endforeach;

        return $branch;
    }
}

(new TestScript())->execute();
