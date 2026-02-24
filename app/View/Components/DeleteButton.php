<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DeleteButton extends Component
{
    public $resourceId;
    public $resourceRoute;

    /**
     * Create a new component instance.
     */
    public function __construct($resourceId, $resourceRoute)
    {
        $this->resourceId = $resourceId;
        $this->resourceRoute = $resourceRoute;
    }
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.delete-button');
    }
}
