<?php

namespace App\Repository\Interface;

use App\Models\Property;

interface PropertyRepositoryInterface
{
    public function index();

    public function store($request);

    public function show($request);

    public function update($request, Property $property);

    public function delete($id);

    public function saved_property($request, Property $property);

    public function show_saved_property();

    public function remove_saved_property($id);
}
