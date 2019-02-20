<?php
namespace Permengandum\Kmeans\Http\Controllers;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Permengandum\Kmeans\Http\Controllers\Controller;
use Permengandum\Kmeans\Models\Kmeans as Model;
use Permengandum\Kmeans\Views\General as View;

class Kmeans extends Controller
{
    /** Model $model */
    private $model;

    /** View $view */
    private $view;

    /** UrlGenerator $url */
    private $url;

    public function __construct(Model $model, View $view, UrlGenerator $url)
    {
        $this->model = $model;
        $this->view = $view;
        $this->url = $url;
    }

    /**
     * Data awal
     */
    public function index()
    {
        $result = $this->model->getData();
        $response = $this->view->render(
            'Kmeans.html',
            ['data' => $result]
        );

        return webResponse($response);
    }

    /**
     * Prerun
     */
    public function prerun()
    {
        $result = $this->model->getData();
        $response = $this->view->render(
            'Prerun.html',
            ['data' => $result]
        );

        return webResponse($response);
    }

    /**
     * Initiate data
     * 
     * @param Request $request
     */
    public function initiate(Request $request)
    {
        $uuid1 = Uuid::uuid1();
        $id = $uuid1->toString();
        $initialData = $this->generateInitialData($request, $id);
        $this->storeData($id, $initialData);
        $url = $this->url->generate('iteration', [
            'id' => $id,
            'iteration' => 0
        ]);
        return RedirectResponse($url);
    }

    /**
     * Iterate data
     * 
     * @param int $id
     */
    public function iterate($id, $iteration)
    {
        $data = $this->loadData($id);
        $result = $this->model->iterate($data, $iteration);
        $this->storeData($id, json_encode($result));
        $iterationData = $this->generateViewData($result, $iteration);
        
        $response = $this->view->render(
            'Iteration.html',
            $iterationData
        );

        return webResponse($response);
    }

    /**
     * Get result by it's centroid
     * 
     * @param $id
     * @param $selectedCluster
     * @return array
     */
    public function getResult($id, $selectedCluster)
    {
        $data = $this->loadData($id);
        $bind = ['M1' => 0, 'M2' => 1, 'M3' => 2];
        $lastIter = count($data['iterations']) - 2;
        $lastIterData = $data['iterations'][$lastIter];
        $clusters = $lastIterData['cluster_data'];
        $centroidStr = $lastIterData['centroid'][array_get($bind, $selectedCluster, 0)];
        $centroidArr = explode(',', $centroidStr);
        $centroid = [];
        foreach ($centroidArr as $val) {
            $centroid[] = round($val, 2);
        }
        $centroid = [
            'str' => implode(', ', $centroid),
            'arr' => $centroid
        ];

        $result = [];
        foreach ($clusters as $key => $value) {
            if ($value['cluster'] === $selectedCluster) {
                $result[] = array_merge(
                    $data['data'][$key], 
                    $value
                );
            }
        }

        $response = $this->view->render(
            'Result.html', [
                'selected_cluster' => $selectedCluster,
                'centroid' => $centroid,
                'data' => $result
            ]
        );

        return webResponse($response);
    }

    public function writeTofile($id)
    {
        $path = __DIR__.'/../../../resource/download/';
        $data = $this->loadData($id);
        $lastIter = count($data['iterations']) - 2;
        $lastIterData = $data['iterations'][$lastIter];
        $clusters = $lastIterData['cluster_data'];
        foreach ($clusters as $key => $value) {
            $clusters[$key]['jenis'] = $data['data'][$key]['jenis'];
            $clusters[$key]['column_0'] = $data['data'][$key]['column_0'];
            $clusters[$key]['column_1'] = $data['data'][$key]['column_1'];
            $clusters[$key]['column_2'] = $data['data'][$key]['column_2'];
        }
        
        $m1 = [];
        $m2 = [];
        $m3 = [];
        
        foreach ($clusters as $key => $value) {
            if ($value['cluster'] === 'M1') {
                $m1[] = $value;
            }
            if ($value['cluster'] === 'M2') {
                $m2[] = $value;
            }
            if ($value['cluster'] === 'M3') {
                $m3[] = $value;
            }
        }

        $clusters = array_merge($m1, $m2, $m3);

        $objPHPExcel = new \PHPExcel(); 
        $objPHPExcel->setActiveSheetIndex(0); 
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Nomor');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Jenis Teh');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Warna');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Warna Air Seduhan');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Ampas');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Jarak Centroid 1 (' . $lastIterData['centroid'][0] . ')');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Jarak Centroid 2 (' . $lastIterData['centroid'][1] . ')');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Jarak Centroid 3 (' . $lastIterData['centroid'][2] . ')');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'Hasil Clustering');

        foreach ($clusters as $key => $value) {
            $number = $key + 2;
            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $number, $key + 1);
            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $number, $clusters[$key]['jenis']);
            $objPHPExcel->getActiveSheet()->SetCellValue('C' . $number, $clusters[$key]['column_0']);
            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $number, $clusters[$key]['column_1']);
            $objPHPExcel->getActiveSheet()->SetCellValue('E' . $number, $clusters[$key]['column_2']);
            $objPHPExcel->getActiveSheet()->SetCellValue('F' . $number, $clusters[$key]['distance_0']);
            $objPHPExcel->getActiveSheet()->SetCellValue('G' . $number, $clusters[$key]['distance_1']);
            $objPHPExcel->getActiveSheet()->SetCellValue('H' . $number, $clusters[$key]['distance_2']);
            $objPHPExcel->getActiveSheet()->SetCellValue('I' . $number, $clusters[$key]['cluster']);
        }

        $file = $path . $id;
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007'); 
        $objWriter->save(str_replace('.php', '.xlsx', $file));

        $myfile = fopen($file, 'r');
        $result = fread($myfile, filesize($file));
        fclose($myfile);
        unset($file);
        
        return webResponse($result, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment;filename="' . $id . '.xlsx"',
            'Content-Control' => 'max-age=0',
        ]);
    }

    /**
     * Arrange data for view
     * 
     * @param array $data 
     * @param int $iteration
     * @return array
     */
    private function generateViewData($data, $iteration)
    {
        $viewData = [];
        foreach($data['data'] as $key => $value) {
            $viewData[] = array_merge(
                $value, 
                $data['iterations'][$iteration]['cluster_data'][$key]
            );
        }

        $current = $data['iterations'][$iteration]['centroid'];
        $next = $data['iterations'][$iteration + 1]['centroid'];
        $needNextIteration = $current !== $next;

        return [
            'id' => $data['id'],
            'iteration' => $iteration + 1,
            'centroid' => [
                'current' => $current,
                'next' => $next,
                'need_next' => $needNextIteration
            ],
            'means' => $data['iterations'][$iteration]['means'],
            'data' => $viewData
        ];
    }

    /**
     * Generate initial data
     * 
     * @param Request $request
     * @param int $id
     */
    private function generateInitialData(Request $request, $id)
    {
        return json_encode([
            'id' => $id,
            'data' => $this->getConvertedData(),
            'iterations' => [
                [
                    'centroid' => [
                        $request->request->get('centroid_0'),
                        $request->request->get('centroid_1'),
                        $request->request->get('centroid_2'),
                    ],
                ]
            ]
        ]);
    }

    /**
     * Convert to kmeans data format
     * 
     * @return array
     */
    private function getConvertedData()
    {
        $raw = $this->model->getData();
        $result = [];
        foreach ($raw as $key => $value) {
            $result[] = [
                'id' => $value['id_teh'],
                'jenis' => $value['jenis'],
                'category' => $value['category'],
                'column_0' => $value['warna'],
                'column_1' => $value['warna_seduhan'],
                'column_2' => $value['ampas']
            ];
        }

        return $result;
    }

    /**
     * Store iteration data
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    private function storeData($id, $data = [])
    {
        $path = __DIR__.'/../../../resource/kmeans/';
        $myfile = fopen($path . $id . '.json', 'w');
        fwrite($myfile, $data);
        fclose($myfile);
        return true;
    }

    /**
     * Load iteration data
     * 
     * @param int $id
     * @return array
     */
    private function loadData($id)
    {
        $path = __DIR__.'/../../../resource/kmeans/';
        $file = $path . $id . '.json';
        $myfile = fopen($file, 'r');
        $data = fread($myfile, filesize($file));
        fclose($myfile);
        return json_decode($data, true);
    }
}