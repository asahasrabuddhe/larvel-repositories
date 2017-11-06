<?php

namespace Asahasrabuddhe\Repositories\Eloquent;

use Asahasrabuddhe\Repositories\Contracts\RepositoryInterface;
use Asahasrabuddhe\Repositories\Contracts\RuleInterface;
use Asahasrabuddhe\Repositories\Exceptions\RepositoryException;
use Asahasrabuddhe\Repositories\Rule\Rule;
use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class Repository.
 */
abstract class Repository implements RepositoryInterface, RuleInterface
{
    /**
     * @var App
     */
    private $app;
    /**
     * @var
     */
    protected $model;

    protected $newModel;
    /**
     * @var Collection
     */
    protected $rule;
    /**
     * @var bool
     */
    protected $skipRule = false;
    /**
     * Prevents from overwriting same rule in chain usage.
     * @var bool
     */
    protected $preventRuleOverwriting = true;

    /**
     * @param App $app
     * @param Collection $collection
     * @throws \Asahasrabuddhe\Repositories\Exceptions\RepositoryException
     */
    public function __construct(App $app, Collection $collection)
    {
        $this->app = $app;
        $this->rule = $collection;
        $this->resetScope();
        $this->makeModel();
    }

    /**
     * Specify Model class name.
     *
     * @return mixed
     */
    abstract public function model();

    /**
     * @param array $columns
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        $this->applyRule();

        return $this->model->get($columns);
    }

    /**
     * @param array $relations
     * @return $this
     */
    public function with(array $relations)
    {
        $this->model = $this->model->with($relations);

        return $this;
    }

    /**
     * @param  string $value
     * @param  string $key
     * @return array
     */
    public function lists($value, $key = null)
    {
        $this->applyRule();
        $lists = $this->model->lists($value, $key);
        if (is_array($lists)) {
            return $lists;
        }

        return $lists->all();
    }

    /**
     * @param int $perPage
     * @param array $columns
     * @return mixed
     */
    public function paginate($perPage = 25, $columns = ['*'])
    {
        $this->applyRule();

        return $this->model->paginate($perPage, $columns);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * save a model without massive assignment.
     *
     * @param array $data
     * @return bool
     */
    public function saveModel(array $data)
    {
        foreach ($data as $k => $v) {
            $this->model->$k = $v;
        }

        return $this->model->save();
    }

    /**
     * @param array $data
     * @param $id
     * @param string $attribute
     * @return mixed
     */
    public function update(array $data, $id, $attribute = 'id')
    {
        return $this->model->where($attribute, '=', $id)->update($data);
    }

    /**
     * @param  array $data
     * @param  $id
     * @return mixed
     */
    public function updateRich(array $data, $id)
    {
        if (! ($model = $this->model->find($id))) {
            return false;
        }

        return $model->fill($data)->save();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        $this->applyRule();

        return $this->model->find($id, $columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findBy($attribute, $value, $columns = ['*'])
    {
        $this->applyRule();

        return $this->model->where($attribute, '=', $value)->first($columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findAllBy($attribute, $value, $columns = ['*'])
    {
        $this->applyRule();

        return $this->model->where($attribute, '=', $value)->get($columns);
    }

    /**
     * Find a collection of models by the given query conditions.
     *
     * @param array $where
     * @param array $columns
     * @param bool $or
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function findWhere($where, $columns = ['*'], $or = false)
    {
        $this->applyRule();
        $model = $this->model;
        foreach ($where as $field => $value) {
            if ($value instanceof \Closure) {
                $model = (! $or)
                    ? $model->where($value)
                    : $model->orWhere($value);
            } elseif (is_array($value)) {
                if (count($value) === 3) {
                    list($field, $operator, $search) = $value;
                    $model = (! $or)
                        ? $model->where($field, $operator, $search)
                        : $model->orWhere($field, $operator, $search);
                } elseif (count($value) === 2) {
                    list($field, $search) = $value;
                    $model = (! $or)
                        ? $model->where($field, '=', $search)
                        : $model->orWhere($field, '=', $search);
                }
            } else {
                $model = (! $or)
                    ? $model->where($field, '=', $value)
                    : $model->orWhere($field, '=', $value);
            }
        }

        return $model->get($columns);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws RepositoryException
     */
    public function makeModel()
    {
        return $this->setModel($this->model());
    }

    /**
     * Set Eloquent Model to instantiate.
     *
     * @param $eloquentModel
     * @return Model
     * @throws RepositoryException
     */
    public function setModel($eloquentModel)
    {
        $this->newModel = $this->app->make($eloquentModel);
        if (! $this->newModel instanceof Model) {
            throw new RepositoryException("Class {$this->newModel} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }
        return $this->model = $this->newModel;
    }

    /**
     * @return $this
     */
    public function resetScope()
    {
        $this->skipRule(false);

        return $this;
    }

    /**
     * @param bool $status
     * @return $this
     */
    public function skipRule($status = true)
    {
        $this->skipRule = $status;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * @param Rule $rule
     * @return $this
     */
    public function getByRule(Rule $rule)
    {
        $this->model = $rule->apply($this->model, $this);

        return $this;
    }

    /**
     * @param Rule $rule
     * @return $this
     */
    public function pushRule(Rule $rule)
    {
        if ($this->preventRuleOverwriting) {
            // Find existing rule
            $key = $this->rule->search(function ($item) use ($rule) {
                return is_object($item) && (get_class($item) == get_class($rule));
            });
            // Remove old rule
            if (is_int($key)) {
                $this->rule->offsetUnset($key);
            }
        }
        $this->rule->push($rule);

        return $this;
    }

    /**
     * @return $this
     */
    public function applyRule()
    {
        if ($this->skipRule === true) {
            return $this;
        }
        foreach ($this->getRule() as $rule) {
            if ($rule instanceof Rule) {
                $this->model = $rule->apply($this->model, $this);
            }
        }

        return $this;
    }
}
