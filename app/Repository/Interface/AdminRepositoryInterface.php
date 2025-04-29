<?php

namespace App\Repository\Interface;

interface AdminRepositoryInterface
{
    public function pending_properties();

    public function get_users();

    public function banUser($id);

    public function unBanUser($id);

    public function accept_pending_property($id);

    public function denied_property($user_id, $property_id);
}
