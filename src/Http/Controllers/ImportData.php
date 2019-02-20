<?php
namespace Permengandum\Kmeans\Http\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Permengandum\Kmeans\Http\Controllers\Controller;
use Permengandum\Kmeans\Models\ImportData as Model;
use Permengandum\Kmeans\Views\General as View;
use PHPExcel_IOFactory;

class ImportData extends Controller
{
    /** Model $model */
    private $model;

    /** View $view */
    private $view;

    public function __construct(Model $model, View $view)
    {
        $this->model = $model;
        $this->view = $view;
    }

    /**
     * Import excel form
     */
    public function index()
    {
        $response = $this->view->render(
            'Import/Index.html'
        );

        return webResponse($response);
    }

    /**
     * Import imported data
     * 
     * @param Request $request
     * @return Response
     */
    public function import(Request $request)
    {
        $data = $this->loadData($request);
        $this->model->insertAll($data);
        $response = $this->view->render(
            'Import/Index.html'
        );

        return webResponse($response);
    }

    /**
     * Upload and format data
     * 
     * @param Request $request
     * @return array
     */
    private function loadData(Request $request)
    {
        $files = $request->files->get('xlsx_file');
        $path = __DIR__.'/../../../resource/upload/';
        $filename = $files->getClientOriginalName();
        $files->move($path, $filename);
        $fileName = $path . $filename;
        $excelReader = PHPExcel_IOFactory::createReaderForFile($fileName);
        $excelReader->setReadDataOnly();
        $loadSheets = array('data asli');
        $excelReader->setLoadSheetsOnly($loadSheets);
        $excelObj = $excelReader->load($fileName);
        $data = $excelObj->getActiveSheet()->toArray(null, true,true,true);
        unset($fileName);
        array_shift($data);
        $result = [];
        foreach ($data as $key => $value) {
            if (!is_null($value['A'])) {
                $result[] = [
                    'chop' => $value['A'],
                    'jenis' => $value['B'],
                    'bentuk' => $value['C'],
                    'warna' => $value['D'],
                    'warna_seduhan' => $value['E'],
                    'ampas' => $value['F']
                ];
            }
        }
        return $result;
    }
}