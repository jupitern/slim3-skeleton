<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\AbstractPaginator as Paginator;

/**
 * Class Repository
 * @package App\Repository
 * @author Jerfeson Guerreiro <jerfeson_guerreiro@hotmail.com>
 */
abstract class Repository
{
    /**
     * @var String
     */
    protected $model;

    /**
     * @return String
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * @return mixed
     */
    protected function newQuery()
    {
        return (new $this->model)->newQuery();
    }

    /**
     * @param $item
     *
     * @return mixed
     */
    public function insert($item)
    {
        $qb = $this->newQuery();
        return $qb->create($item);
    }

    /**
     * @param EloquentQueryBuilder|QueryBuilder $query
     * @param int                               $take
     * @param bool                              $paginate
     *
     * @return EloquentCollection|Paginator
     */
    protected function doQuery($query = null, $take = 15, $paginate = true)
    {
        if (is_null($query)) {
            $query = $this->newQuery();
        }

        if (true == $paginate) {
            return $query->paginate($take, ['*'], 'page', $page = null);
        }

        if ($take > 0 || false !== $take) {
            $query->take($take);
        }

        return $query->get();
    }

    /**
     * Returns all records.
     * If $take is false then brings all records
     * If $paginate is true returns Paginator instance.
     *
     * @param int  $take
     * @param bool $paginate
     *
     * @return EloquentCollection|Paginator
     */
    public function getAll($take = 15, $paginate = true)
    {
        return $this->doQuery(null, $take, $paginate);
    }

    /**
     * @param string      $column
     * @param string|null $key
     *
     * @return \Illuminate\Support\Collection
     */
    public function lists($column, $key = null)
    {
        return $this->newQuery()->lists($column, $key);
    }

    /**
     * Retrieves a record by his id
     * If fail is true $ fires ModelNotFoundException.
     *
     * @param int  $id
     * @param bool $fail
     *
     * @return Model
     */
    public function findById($id, $fail = true)
    {
        if ($fail) {
            return $this->newQuery()->findOrFail($id);
        }

        return $this->newQuery()->find($id);
    }
}
