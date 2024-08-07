<?php

namespace Sophy\View;

interface ViewStrategy
{
    public function render(string $pathFilename, array $params = []);
}
