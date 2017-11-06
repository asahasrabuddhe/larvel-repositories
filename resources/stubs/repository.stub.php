<?php

namespace repository_namespace;

use Asahasrabuddhe\Repositories\Eloquent\Repository;

/**
 * Class repository_class.
 */
class repository_class extends Repository
{
    /**
     * @return string
     */
    public function model()
    {
        return 'model_path\model_name';
    }
}
