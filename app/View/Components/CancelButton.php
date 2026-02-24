<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CancelButton extends Component
{
    public string $href;
    /**
     * Create a new component instance.
     */
    public function __construct(string $href)
    {
        $this->href = $href;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.cancel-button');
    }
}
