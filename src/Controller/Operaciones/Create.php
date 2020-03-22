<?php

declare(strict_types=1);

namespace App\Controller\Operaciones;

class Create extends Base
{
    public function __invoke($request, $response)
    {
        $input = $request->getParsedBody();
        $operaciones = $this->getOperacionesService()->create($input);

        $payload = json_encode($operaciones);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }
}
