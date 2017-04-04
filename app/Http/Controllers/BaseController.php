<?php

namespace App\Http\Controllers;

use App\Component;
use Boyhagemann\Storage\Contracts\Entity;
use Boyhagemann\Storage\Contracts\EntityRepository;
use Boyhagemann\Storage\Contracts\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Helpers\DataBuilder;

class BaseController extends Controller
{
    /**
     * @var Component
     */
    protected $components;

    /**
     * ComponentController constructor.
     *
     * @param Component $repository
     */
    public function __construct(Component $repository)
    {
        $this->components = $repository;
    }

}
