<?php

namespace App\View\Components;

use App\Support\MultiUserSupport;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SwitchAccount extends Component
{
    protected MultiUserSupport $multiUser;

    protected int $multiUserCount;

    protected string $type;

    public const string TYPE_CONTAINER = 'container';

    public const string TYPE_DROPDOWN = 'dropdown';

    public const string TYPE_ELEMENT = 'element';

    /**
     * Create a new component instance.
     */
    public function __construct(string $type = self::TYPE_CONTAINER)
    {
        $this->multiUser = new MultiUserSupport;
        $this->multiUserCount = $this->multiUser->count();
        $this->type = $type;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.switch-account', [
            'multiUser' => $this->multiUser,
            'multiUserCount' => $this->multiUserCount,
            'users' => $this->multiUser->get(),
            'type' => $this->type,
            'user' => auth('web')->user(),
        ]);
    }
}
