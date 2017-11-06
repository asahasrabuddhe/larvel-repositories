<?php

namespace Asahasrabuddhe\Repositories\Rule;

use Asahasrabuddhe\Repositories\Contracts\RepositoryInterface as Repository;

abstract class Rule
{
    /**
     * @param $model
     * @param Repository $repository
     * @return mixed
     */
    abstract public function apply($model, Repository $repository);
}
