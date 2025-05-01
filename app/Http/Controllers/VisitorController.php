<?php

namespace App\Http\Controllers;

use App\Repository\Interface\VisitorRepositoryInterface;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    protected $visitor;
    public function __construct(VisitorRepositoryInterface $visitor){
        $this->visitor = $visitor;
    }

    public function index(){
        return $this->visitor->index();
    }

    public function show($id){
        return $this->visitor->show($id);
    }
}
