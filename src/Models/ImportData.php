<?php 
namespace Permengandum\Kmeans\Models;

use Medoo\Medoo as Database;
use Permengandum\Kmeans\Exceptions;

class ImportData extends Model
{
    /** @var Database $db */
    private $db;

    public function __construct(Database $db) 
    {
        $this->db = $db;
    }

    /**
     * Insert all provided data to db
     * 
     * @param array $data
     * @return boolean
     */
    public function insertAll(array $data)
    {
        $this->db->delete('data', []);
        foreach($data as $key => $value) {
            $this->db->insert('data', $value);
        }

        return true;
    }
}
