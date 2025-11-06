<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\{
    Url,
    Computed,
    Layout
};

#[Layout('components.layouts.app')]
class HomePage extends Component
{
    public string $currentPage = 'books';

    public function setPage(string $p): void
    {
        $this->currentPage = in_array($p, ['subjects','authors','books']) ? $p : 'books';
    }

    public function render()
    {
        return view('livewire.home-page');
    }
}

