<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ErrorMessage extends Component
{
    public string $field;

    /**
     * Create a new component instance.
     */
    public function __construct(string $field)
    {
        $this->field = $field;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.error-message');
    }
}
