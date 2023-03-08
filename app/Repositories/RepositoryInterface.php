<?php

namespace App\Repositories;

interface RepositoryInterface
{

    /**
     * Get all
     * @return array|object
     */
    public function getAll():array|object;

    /**
     * Get one
     * @param $id
     * @return array|object
     */
    public function find($id):array|object;

    /**
     * Create
     * @param array $attributes
     * @return array|object
     */
    public function create(array $attributes):array|object;

    /**
     * Update
     * @param $id
     * @param array $attributes
     * @return array|object
     */
    public function update($id, array $attributes):array|object;

    /**
     * Delete
     * @param $id
     * @return bool
     */
    public function delete($id):bool;

    /**
     * updateOrInsert
     * @param array $conditions
     * @param array $attributes
     * @return array|object
     */
    public function updateOrInsert(array $conditions,array $attributes):array|object;
}
