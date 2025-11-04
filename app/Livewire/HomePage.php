<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class HomePage extends Component
{
    public string $currentPage = 'books'; // subjects|authors|books

    // Recebe o "section" vindo da rota (via defaults acima)
    public function mount(string $section = 'subjects'): void
    {
        $this->currentPage = in_array($section, ['subjects','authors','books'])
            ? $section
            : 'subjects';
    }

    public function setPage(string $p): void
    {
        // permanece útil se você quiser trocar via ação, mas com links não é necessário
        $this->currentPage = in_array($p, ['subjects','authors','books']) ? $p : 'subjects';
    }

    public function render()
    {
        return view('livewire.home-page');
    }
}
