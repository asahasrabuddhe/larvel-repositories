<?php

namespace rule_namespace;

use Asahasrabuddhe\Repositories\Contracts\RepositoryInterface as Repository;
use Asahasrabuddhe\Repositories\Criteria\Criteria;

/**
 * Class rule_class.
 */
class rule_class extends Criteria
{
    /**
     * @param            $model
     * @param Repository $repository
     *
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model;
    }
}
