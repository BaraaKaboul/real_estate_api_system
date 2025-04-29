<?php

namespace App\Http\Controllers;

use App\Repository\Interface\AdminRepositoryInterface;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $admin;

    public function __construct(AdminRepositoryInterface $admin)
    {
        $this->admin = $admin;
    }

    public function pending_properties(){
        return $this->admin->pending_properties();
    }

    public function get_users(){
        return $this->admin->get_users();
    }

    public function banUser($id){
        return $this->admin->banUser($id);
    }

    public function unBanUser($id){
        return $this->admin->unBanUser($id);
    }

    public function accept_pending_property($id){
        return $this->admin->accept_pending_property($id);
    }

    public function denied_property($user_id, $property_id){
        return $this->admin->denied_property($user_id, $property_id);
    }
}
