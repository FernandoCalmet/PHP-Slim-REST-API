<?php declare(strict_types=1);

namespace App\Controller\Usuarios;

class Update extends Base
{
    public function __invoke($request, $response, array $args)
    {
        $input = $request->getParsedBody();
        $usuarios = $this->getUsuariosService()->updateUsuarios($input, (int) $args['id']);

        $payload = json_encode($usuarios);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
