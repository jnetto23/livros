<?php

namespace App\Application\Usecases;

interface UseCaseInterface
{
    /**
     * @param object $inputDTO
     * @return object
     */
    public function execute(object $inputDTO): object;
}

