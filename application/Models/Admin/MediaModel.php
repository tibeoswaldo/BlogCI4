<?php namespace App\Models\Admin;

use CodeIgniter\Model;
use Config\Database;

/**
 * Class MediaModel
 *
 * @package App\Models\Admin
 */
class MediaModel extends Model
{

    /**
     * @var \CodeIgniter\Database\BaseBuilder
     */
    protected $medias_table;

    /**
     * MediaModel constructor.
     *
     * @param array ...$params
     */
    public function __construct(...$params)
    {
        parent::__construct(...$params);
        $this->db = Database::connect();
        $this->medias_table = $this->db->table('medias');
    }

    /**
     * @param string $slug
     * @param string $name
     *
     * @return int
     */
    public function Add(string $slug, string $name)
    {
        $data = [
            'slug' => $slug,
            'name' => $name
        ];
        $this->medias_table->insert($data);
        return $this->db->insertID();
    }
}
