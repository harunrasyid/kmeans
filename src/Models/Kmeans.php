<?php 
namespace Permengandum\Kmeans\Models;

use Medoo\Medoo as Database;
use Permengandum\Kmeans\Exceptions;

class Kmeans extends Model
{
    /** @var Database $db */
    private $db;

    public function __construct(Database $db) 
    {
        $this->db = $db;
    }

    /**
     * Get raw data from database// ambil data dari database 
     * 
     * @return array
     */
    public function getData()
    {
        $data = $this->db->select('data', [
            'id_teh', 'jenis', 'bentuk', 'warna', 'warna_seduhan', 'ampas'
        ]);

        $result = [];
        foreach($data as $key => $value) {
            $result[] = $this->categorize($value);
        }

        return $result;
    }

    /**
     * Categorize based on it's shape//mengkategorikan data teh menjadi 1,2,3
     * 
     * @param array $data 
     * @return array
     */
    private function categorize($data)
    {
        $bentuk = array_get($data, 'bentuk', 0);
        if ($bentuk >= 31) {
            $cat = 1;
        } elseif ($bentuk >= 21) {
            $cat = 2;
        } else {
            $cat = 3;
        }
        return array_merge($data, [
            'category' => $cat
        ]);
    }

    /**
     * Run one iteration/ menjalankan iterasi 1
     * 
     * @param array $params 
     * @param int $iteration
     * @return array
     */
    public function iterate($params, $iteration)
    {
        $centroid = array_get($params, 'iterations.' . $iteration . '.centroid', []);
        $data = array_get($params, 'data', []);
        $result = [];
        foreach($data as $key => $value) {
            $clusterData = [
                'distance_0' => $this->getDistance($value, $centroid[0]),
                'distance_1' => $this->getDistance($value, $centroid[1]),
                'distance_2' => $this->getDistance($value, $centroid[2]),
            ];
            
            $clusterData['cluster'] = $this->getCluster($clusterData);
            $result[] = $clusterData;
        }

        $params['iterations'][$iteration]['cluster_data'] = $result;
        $meansData = $this->getMeans($data, $result);
        $params['iterations'][$iteration]['means'] = $meansData;
        $params['iterations'][$iteration + 1]['centroid'] = $this->calculateNextCentroid($meansData);

        return $params;
    }

    private function calculateNextCentroid($meansData)
    {
        $centroid0 = implode(',', [
            array_get($meansData, 'means_0.value.0') / array_get($meansData, 'means_0.count'),
            array_get($meansData, 'means_0.value.1') / array_get($meansData, 'means_0.count'),
            array_get($meansData, 'means_0.value.2') / array_get($meansData, 'means_0.count'),
        ]);

        $centroid1 = implode(',', [
            array_get($meansData, 'means_1.value.0') / array_get($meansData, 'means_1.count'),
            array_get($meansData, 'means_1.value.1') / array_get($meansData, 'means_1.count'),
            array_get($meansData, 'means_1.value.2') / array_get($meansData, 'means_1.count'),
        ]);

        $centroid2 = implode(',', [
            array_get($meansData, 'means_2.value.0') / array_get($meansData, 'means_2.count'),
            array_get($meansData, 'means_2.value.1') / array_get($meansData, 'means_2.count'),
            array_get($meansData, 'means_2.value.2') / array_get($meansData, 'means_2.count'),
        ]);

        return [
            $centroid0, $centroid1, $centroid2
        ];
    }

    /**
     * Get centroid means/ hasil centeroid M1 M2 M3
     */
    private function getMeans($data, $clusterData)
    {
        $centroid0 = $this->getInitCentroid();
        $centroid1 = $this->getInitCentroid();
        $centroid2 = $this->getInitCentroid();
        
        foreach ($clusterData as $key => $value) {
            switch ($value['cluster']) {
                case 'M1':
                    (float) $centroid0['value'][0] += $data[$key]['column_0'];
                    (float) $centroid0['value'][1] += $data[$key]['column_1'];
                    (float) $centroid0['value'][2] += $data[$key]['column_2'];
                    (float) $centroid0['count']++;
                    break;
                case 'M2':
                    (float) $centroid1['value'][0] += $data[$key]['column_0'];
                    (float) $centroid1['value'][1] += $data[$key]['column_1'];
                    (float) $centroid1['value'][2] += $data[$key]['column_2'];
                    (float) $centroid1['count']++;
                    break;
                case 'M3':
                    (float) $centroid2['value'][0] += $data[$key]['column_0'];
                    (float) $centroid2['value'][1] += $data[$key]['column_1'];
                    (float) $centroid2['value'][2] += $data[$key]['column_2'];
                    (float) $centroid2['count']++;
                    break;
            }
        }

        return [
            'means_0' => $centroid0,
            'means_1' => $centroid1,
            'means_2' => $centroid2,
        ];
    }

    /**
     * Generate initial value for centroid 
     * 
     * @return array
     */
    private function getInitCentroid()
    {
        return [
            'value' => [0, 0, 0],
            'count' => 0
        ];
    }

    /**
     * Calculate distance/ perhitungan jarak 
     * 
     * @param array $data 
     * @param string $centroid
     * @return float
     */
    private function getDistance($data, $centroid)
    {
        $centroid = explode(',', $centroid);
        $centroidArray = [];
        foreach($centroid as $key => $value) {
            $centroidArray[] = (float) trim($value);
        }
        // return sqrt(
        //     pow(($centroidArray[0] - $data['column_0']), 2) +
        //     pow(($centroidArray[1] - $data['column_1']), 2)
        // ) + pow(($centroidArray[2] - $data['column_2']), 2);
        return sqrt(
            pow(($centroidArray[0] - $data['column_0']), 2) +
            pow(($centroidArray[1] - $data['column_1']), 2) +
            pow(($centroidArray[2] - $data['column_2']), 2)
        );
    }

    /**
     * Get cluster classification/ hasil cluster yang sudah di clasifikasi
     * 
     * @param array $clusterData
     * @return string
     */
    private function getCluster($clusterData)
    {
        $cluster = min(
            $clusterData['distance_0'],
            $clusterData['distance_1'],
            $clusterData['distance_2']
        );

        if ($cluster === $clusterData['distance_0']) {
            return 'M1';
        } elseif($cluster === $clusterData['distance_1']) {
            return 'M2';
        } else {
            return 'M3';
        }
    }
}
